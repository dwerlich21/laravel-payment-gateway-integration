<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Exceptions\PaymentException;
use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Webhook;

/**
 * Serviço de integração com o Stripe.
 *
 * Implementa a interface de gateway utilizando a API do Stripe
 * para criação de cobranças via Checkout Session e consulta de status.
 */
class StripeGatewayService implements PaymentGatewayInterface
{
    private string $secretKey;

    private string $webhookSecret;

    public function __construct(string $secretKey, string $webhookSecret)
    {
        $this->secretKey = $secretKey;
        $this->webhookSecret = $webhookSecret;

        Stripe::setApiKey($this->secretKey);
    }

    /**
     * Cria uma cobrança no Stripe via PaymentIntent.
     *
     * Recebe os dados do pedido (amount, description, currency, metadata)
     * e cria um PaymentIntent. Retorna o ID externo, client_secret e status.
     */
    public function createCharge(array $data): array
    {
        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => (int) round($data['amount'] * 100),
                'currency' => $data['currency'] ?? 'brl',
                'description' => $data['description'] ?? 'Pagamento',
                'metadata' => array_merge($data['metadata'] ?? [], [
                    'order_id' => $data['order_id'] ?? null,
                ]),
            ]);

            return [
                'external_id' => $paymentIntent->id,
                'client_secret' => $paymentIntent->client_secret,
                'status' => 'pending',
            ];
        } catch (\Exception $e) {
            throw new PaymentException("Erro ao criar cobrança no Stripe: {$e->getMessage()}");
        }
    }

    /**
     * Consulta o status de uma cobrança no Stripe pelo ID do PaymentIntent.
     *
     * Mapeia os status do Stripe para o formato interno:
     * succeeded → paid, canceled → failed, demais → pending.
     */
    public function getChargeStatus(string $externalId): string
    {
        try {
            $paymentIntent = PaymentIntent::retrieve($externalId);

            return match ($paymentIntent->status) {
                'succeeded' => 'paid',
                'canceled' => 'failed',
                default => 'pending',
            };
        } catch (\Exception $e) {
            throw new PaymentException("Erro ao consultar status no Stripe: {$e->getMessage()}");
        }
    }

    /**
     * Normaliza o payload do webhook do Stripe para o formato interno.
     *
     * Extrai dados do evento payment_intent: ID externo,
     * status normalizado, valor e data de pagamento.
     */
    public function normalizeWebhookPayload(array $payload): array
    {
        try {
            $object = $payload['data']['object'] ?? [];

            $status = match ($object['status'] ?? '') {
                'succeeded' => 'paid',
                'canceled' => 'failed',
                'requires_payment_method' => 'failed',
                default => 'pending',
            };

            $amount = ($object['amount'] ?? 0) / 100;

            return [
                'external_id' => $object['id'] ?? null,
                'status' => $status,
                'amount' => $amount,
                'paid_at' => $status === 'paid' && isset($object['created'])
                    ? date('Y-m-d H:i:s', $object['created'])
                    : null,
            ];
        } catch (\Exception $e) {
            throw new PaymentException("Erro ao normalizar webhook do Stripe: {$e->getMessage()}");
        }
    }

    /**
     * Valida a assinatura do webhook do Stripe.
     *
     * Utiliza o header Stripe-Signature e o webhook_secret configurado
     * para verificar a autenticidade da requisição via Webhook::constructEvent.
     */
    public function validateWebhookSignature(Request $request): bool
    {
        try {
            Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature', ''),
                $this->webhookSecret
            );

            return true;
        } catch (SignatureVerificationException $e) {
            return false;
        }
    }
}
