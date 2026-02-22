<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Exceptions\PaymentException;
use Illuminate\Http\Request;
use Stripe\Checkout\Session as StripeSession;
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
     * Cria uma cobrança no Stripe via Checkout Session.
     *
     * Recebe os dados do pedido (amount, description, success_url, cancel_url)
     * e cria uma sessão de checkout. Retorna o ID externo, URL de checkout e status.
     */
    public function createCharge(array $data): array
    {
        try {
            $session = StripeSession::create([
                'mode' => 'payment',
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => $data['currency'] ?? 'brl',
                            'product_data' => [
                                'name' => $data['description'] ?? 'Pagamento',
                            ],
                            'unit_amount' => (int) round($data['amount'] * 100),
                        ],
                        'quantity' => 1,
                    ],
                ],
                'success_url' => $data['success_url'] ?? config('app.url').'/pagamento/sucesso',
                'cancel_url' => $data['cancel_url'] ?? config('app.url').'/pagamento/cancelado',
                'metadata' => array_merge($data['metadata'] ?? [], [
                    'order_id' => $data['order_id'] ?? null,
                ]),
            ]);

            return [
                'external_id' => $session->id,
                'checkout_url' => $session->url,
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
     * succeeded → paid, canceled/expired → failed, demais → pending.
     */
    public function getChargeStatus(string $externalId): string
    {
        try {
            $session = StripeSession::retrieve($externalId);

            if ($session->payment_intent) {
                $paymentIntent = PaymentIntent::retrieve($session->payment_intent);

                return match ($paymentIntent->status) {
                    'succeeded' => 'paid',
                    'canceled' => 'failed',
                    default => 'pending',
                };
            }

            return match ($session->status) {
                'complete' => 'paid',
                'expired' => 'failed',
                default => 'pending',
            };
        } catch (\Exception $e) {
            throw new PaymentException("Erro ao consultar status no Stripe: {$e->getMessage()}");
        }
    }

    /**
     * Normaliza o payload do webhook do Stripe para o formato interno.
     *
     * Extrai dados do evento checkout.session.completed: ID externo,
     * status normalizado, valor, taxas, valor líquido e data de pagamento.
     */
    public function normalizeWebhookPayload(array $payload): array
    {
        try {
            $session = $payload['data']['object'] ?? [];

            $status = match ($session['status'] ?? '') {
                'complete' => 'paid',
                'expired' => 'failed',
                'open' => 'pending',
                default => 'pending',
            };

            $amountTotal = ($session['amount_total'] ?? 0) / 100;

            return [
                'external_id' => $session['id'] ?? null,
                'status' => $status,
                'amount' => $amountTotal,
                'paid_at' => isset($session['created']) ? date('Y-m-d H:i:s', $session['created']) : null,
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
