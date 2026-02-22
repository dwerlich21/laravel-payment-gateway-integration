<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\Payment\PaymentManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreatePaymentCharge implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Order $order;

    /**
     * Cria uma nova instância do job.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Executa o job: cria a cobrança no gateway e salva o external_id no pedido.
     */
    public function handle(PaymentManager $paymentManager): void
    {
        try {
            $result = $paymentManager->gateway($this->order->gateway)->createCharge([
                'order_id' => $this->order->id,
                'amount' => $this->order->total_amount,
                'description' => "Pedido #{$this->order->id}",
            ]);

            $this->order->update([
                'external_id' => $result['external_id'] ?? null,
                'payment_url' => $result['checkout_url'] ?? $result['invoice_url'] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::error('Falha ao criar cobrança', [
                'order_id' => $this->order->id,
                'gateway' => $this->order->gateway,
                'error' => $e->getMessage(),
            ]);

            $this->order->update(['status' => Order::STATUS_FAILED]);
        }
    }
}
