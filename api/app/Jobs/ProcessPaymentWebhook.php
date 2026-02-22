<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Payment\PaymentManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPaymentWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $gateway;

    protected array $payload;

    /**
     * Cria uma nova instância do job.
     */
    public function __construct(string $gateway, array $payload)
    {
        $this->gateway = $gateway;
        $this->payload = $payload;
    }

    /**
     * Executa o job: normaliza o payload, localiza o pedido,
     * cria o registro de pagamento e atualiza o status do pedido.
     */
    public function handle(PaymentManager $paymentManager): void
    {
        try {
            Log::info('[ProcessPaymentWebhook] Iniciando processamento', [
                'gateway' => $this->gateway,
                'event_type' => $this->payload['type'] ?? 'unknown',
            ]);

            $normalized = $paymentManager->gateway($this->gateway)
                ->normalizeWebhookPayload($this->payload);

            Log::info('[ProcessPaymentWebhook] Payload normalizado', $normalized);

            $order = Order::where('external_id', $normalized['external_id'])->first();

            if (! $order) {
                Log::warning('[ProcessPaymentWebhook] Pedido não encontrado', [
                    'gateway'     => $this->gateway,
                    'external_id' => $normalized['external_id'],
                ]);

                return;
            }

            Log::info('[ProcessPaymentWebhook] Pedido encontrado', [
                'order_id' => $order->id,
                'status_atual' => $order->status,
                'status_novo' => $normalized['status'],
            ]);

            Payment::create([
                'order_id'            => $order->id,
                'external_id'         => $normalized['external_id'],
                'gateway'             => $this->gateway,
                'status'              => $normalized['status'],
                'amount'              => $normalized['amount'] ?? 0,
                'fees'                => $normalized['fees'] ?? 0,
                'net_amount'          => $normalized['net_amount'] ?? 0,
                'paid_at'             => $normalized['paid_at'] ?? null,
                'raw_payload'         => $this->payload,
                'normalized_payload'  => $normalized,
            ]);

            $order->update(['status' => $normalized['status']]);

            Log::info('[ProcessPaymentWebhook] Pedido atualizado com sucesso', [
                'order_id' => $order->id,
                'status' => $normalized['status'],
            ]);
        } catch (\Throwable $e) {
            Log::error('[ProcessPaymentWebhook] Falha ao processar webhook', [
                'gateway' => $this->gateway,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
        }
    }
}
