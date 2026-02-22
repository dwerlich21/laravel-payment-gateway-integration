<?php

namespace App\Http\Controllers\Api;

use App\Jobs\ProcessPaymentWebhook;
use App\Services\Payment\PaymentManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController
{
    protected PaymentManager $paymentManager;

    public function __construct(PaymentManager $paymentManager)
    {
        $this->paymentManager = $paymentManager;
    }

    /**
     * Recebe e processa webhooks do Stripe.
     */
    public function handleStripe(Request $request): JsonResponse
    {
        Log::info('[Webhook] Stripe webhook recebido', [
            'type' => $request->input('type'),
            'ip' => $request->ip(),
        ]);

        $gateway = $this->paymentManager->gateway('stripe');

        if (! $gateway->validateWebhookSignature($request)) {
            Log::warning('[Webhook] Stripe assinatura inválida', [
                'ip' => $request->ip(),
            ]);

            return response()->json(['error' => 'Assinatura inválida'], 400);
        }

        Log::info('[Webhook] Stripe assinatura válida, despachando job', [
            'type' => $request->input('type'),
            'session_id' => $request->input('data.object.id'),
        ]);

        ProcessPaymentWebhook::dispatch('stripe', $request->all());

        return response()->json(['message' => 'Webhook recebido'], 200);
    }

    /**
     * Recebe e processa webhooks do Asaas.
     */
    public function handleAsaas(Request $request): JsonResponse
    {
        $gateway = $this->paymentManager->gateway('asaas');

        if (! $gateway->validateWebhookSignature($request)) {
            Log::warning('Webhook Asaas com assinatura inválida', [
                'ip' => $request->ip(),
            ]);

            return response()->json(['error' => 'Assinatura inválida'], 400);
        }

        ProcessPaymentWebhook::dispatch('asaas', $request->all());

        return response()->json(['message' => 'Webhook recebido'], 200);
    }
}
