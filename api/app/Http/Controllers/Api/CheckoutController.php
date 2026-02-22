<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use App\Services\Payment\PaymentManager;
use App\Traits\ExceptionHandlerTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CheckoutController
{
    use ExceptionHandlerTrait;

    protected OrderService $orderService;

    protected PaymentManager $paymentManager;

    public function __construct(OrderService $orderService, PaymentManager $paymentManager)
    {
        $this->orderService = $orderService;
        $this->paymentManager = $paymentManager;
    }

    /**
     * Lista os produtos ativos disponíveis para compra.
     */
    public function index(): JsonResponse
    {
        return $this->handleWithoutTransaction(function () {

            $products = Product::where('active', true)
                ->get(['id', 'name', 'description', 'price']);

            return $this->successResponse([
                'products' => $products,
                'stripe_publishable_key' => config('payment.gateways.stripe.publishable_key'),
            ]);

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

            $gateway = $this->paymentManager->gateway($order->gateway);
            $chargeResult = $gateway->createCharge([
                'order_id' => $order->id,
                'amount' => $order->total_amount,
                'description' => "Pedido #{$order->id}",
            ]);

            $order->update([
                'external_id' => $chargeResult['external_id'] ?? null,
            ]);

            $response = $order->load('product')->toArray();
            $response['client_secret'] = $chargeResult['client_secret'] ?? null;

            return $this->successResponse($response, 'Pedido criado com sucesso!', 201);

        }, 'Não foi possível processar o pedido');
    }

    /**
     * Confirma o pagamento de um pedido consultando o status no gateway.
     * Atualiza também os dados do comprador caso tenham sido corrigidos.
     */
    public function confirm(Request $request, int $orderId): JsonResponse
    {
        return $this->handleWithTransaction(function () use ($request, $orderId) {

            $order = Order::findOrFail($orderId);

            if ($order->isPaid()) {
                return $this->successResponse($order->load('product'), 'Pedido já está pago.');
            }

            $gateway = $this->paymentManager->gateway($order->gateway);
            $status = $gateway->getChargeStatus($order->external_id);

            $updateData = ['status' => $status];

            $customerFields = ['customer_name', 'customer_email', 'customer_cpf_cnpj', 'customer_phone'];
            foreach ($customerFields as $field) {
                if ($request->filled($field)) {
                    $updateData[$field] = $request->input($field);
                }
            }

            $order->update($updateData);

            return $this->successResponse($order->load('product'), 'Status do pedido atualizado.');

        }, 'Não foi possível confirmar o pagamento');
    }
}
