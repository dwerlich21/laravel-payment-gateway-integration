<?php

namespace Tests\Unit;

use App\Services\Payment\AsaasGatewayService;
use App\Services\Payment\StripeGatewayService;
use PHPUnit\Framework\TestCase;

class PaymentNormalizationTest extends TestCase
{
    /**
     * Testa se o payload do Stripe é normalizado corretamente.
     */
    public function test_stripe_payload_normalizes_correctly(): void
    {
        $service = new StripeGatewayService('sk_test_fake', 'whsec_fake');

        $timestamp = 1708617600; // 2024-02-22 16:00:00 UTC

        $payload = [
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_abc123',
                    'status' => 'succeeded',
                    'amount' => 2999,
                    'created' => $timestamp,
                ],
            ],
        ];

        $normalized = $service->normalizeWebhookPayload($payload);

        $this->assertEquals('pi_abc123', $normalized['external_id']);
        $this->assertEquals('paid', $normalized['status']);
        $this->assertEquals(29.99, $normalized['amount']);
        $this->assertEquals(date('Y-m-d H:i:s', $timestamp), $normalized['paid_at']);
    }

    /**
     * Testa se o payload do Asaas é normalizado corretamente.
     */
    public function test_asaas_payload_normalizes_correctly(): void
    {
        $service = new AsaasGatewayService('fake_api_key', 'fake_webhook_token');

        $payload = [
            'event' => 'PAYMENT_CONFIRMED',
            'payment' => [
                'id' => 'pay_asaas_456',
                'status' => 'CONFIRMED',
                'value' => 29.99,
                'paymentDate' => '2024-02-22',
            ],
        ];

        $normalized = $service->normalizeWebhookPayload($payload);

        $this->assertEquals('pay_asaas_456', $normalized['external_id']);
        $this->assertEquals('paid', $normalized['status']);
        $this->assertEquals(29.99, $normalized['amount']);
        $this->assertEquals('2024-02-22', $normalized['paid_at']);
    }

    /**
     * Testa se o formato normalizado contém os 4 campos obrigatórios.
     */
    public function test_normalized_format_has_required_fields(): void
    {
        $requiredKeys = ['external_id', 'status', 'amount', 'paid_at'];

        // Stripe
        $stripe = new StripeGatewayService('sk_test_fake', 'whsec_fake');
        $stripeNormalized = $stripe->normalizeWebhookPayload([
            'data' => [
                'object' => [
                    'id' => 'pi_test',
                    'status' => 'succeeded',
                    'amount' => 1000,
                    'created' => time(),
                ],
            ],
        ]);

        $this->assertEquals($requiredKeys, array_keys($stripeNormalized));

        // Asaas
        $asaas = new AsaasGatewayService('fake_key', 'fake_token');
        $asaasNormalized = $asaas->normalizeWebhookPayload([
            'payment' => [
                'id' => 'pay_test',
                'status' => 'RECEIVED',
                'value' => 10.00,
                'paymentDate' => '2024-01-01',
            ],
        ]);

        $this->assertEquals($requiredKeys, array_keys($asaasNormalized));
    }
}
