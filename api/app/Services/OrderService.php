<?php

namespace App\Services;

use App\Jobs\CreatePaymentCharge;
use App\Models\Order;
use App\Models\Product;
use App\Repositories\OrderRepository;

class OrderService extends BaseService
{
    public function __construct(OrderRepository $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Cria um pedido e despacha o job de cobrança no gateway.
     */
    public function create(array $data): mixed
    {
        $product = Product::findOrFail($data['product_id']);
        $quantity = $data['quantity'] ?? 1;
        $gateway = $data['gateway'] ?? config('payment.default');

        $order = $this->repository->create([
            'user_id'           => auth()->id(),
            'product_id'        => $product->id,
            'quantity'          => $quantity,
            'total_amount'      => $product->price * $quantity,
            'status'            => Order::STATUS_PENDING,
            'gateway'           => $gateway,
            'payment_method'    => $data['payment_method'] ?? 'credit_card',
            'customer_name'     => $data['customer_name'] ?? null,
            'customer_email'    => $data['customer_email'] ?? null,
            'customer_cpf_cnpj' => $data['customer_cpf_cnpj'] ?? null,
            'customer_phone'    => $data['customer_phone'] ?? null,
        ]);

        CreatePaymentCharge::dispatch($order);

        return $order->load('product');
    }
}
