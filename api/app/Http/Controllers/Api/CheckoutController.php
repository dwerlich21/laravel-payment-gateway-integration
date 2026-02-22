<?php

namespace App\Http\Controllers\Api;

use App\Jobs\CreatePaymentCharge;
use App\Models\Product;
use App\Services\OrderService;
use App\Traits\ExceptionHandlerTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CheckoutController
{
    use ExceptionHandlerTrait;

    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Lista os produtos ativos disponíveis para compra.
     */
    public function index(): JsonResponse
    {
        return $this->handleWithoutTransaction(function () {

            $products = Product::where('active', true)
                ->get(['id', 'name', 'description', 'price']);

            return $this->successResponse($products);

        }, 'Erro ao buscar produtos');
    }

    /**
     * Cria um pedido e processa a cobrança no gateway de pagamento.
     */
    public function store(Request $request): JsonResponse
    {
        return $this->handleWithTransaction(function () use ($request) {

            $configuredGateways = array_keys(config('payment.gateways', []));

            $validator = Validator::make($request->all(), [
                'product_id' => 'required|exists:products,id',
                'gateway' => ['required', 'in:'.implode(',', $configuredGateways)],
                'quantity' => 'sometimes|integer|min:1',
                'payment_method' => 'sometimes|string',
                'customer_name' => 'required|string|max:255',
                'customer_email' => 'required|email|max:255',
                'customer_cpf_cnpj' => 'required|string|max:20',
                'customer_phone' => 'sometimes|string|max:20',
            ], [], [
                'product_id' => 'produto',
                'gateway' => 'gateway de pagamento',
                'quantity' => 'quantidade',
                'payment_method' => 'método de pagamento',
                'customer_name' => 'nome do cliente',
                'customer_email' => 'e-mail do cliente',
                'customer_cpf_cnpj' => 'CPF/CNPJ',
                'customer_phone' => 'telefone',
            ]);

            if ($validator->fails()) {
                throw new \App\Exceptions\ValidationException($validator->errors()->toArray());
            }

            $order = $this->orderService->createFromCheckout($request->all());

            CreatePaymentCharge::dispatchSync($order);

            $order->refresh();

            return $this->successResponse($order->load('product'), 'Pedido criado com sucesso!', 201);

        }, 'Não foi possível processar o pedido');
    }
}
