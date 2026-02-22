<?php

namespace Tests\Feature;

use App\Contracts\PaymentGatewayInterface;
use App\Jobs\ProcessPaymentWebhook;
use App\Services\Payment\PaymentManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa que webhook Stripe com assinatura inválida retorna 400.
     */
    public function test_stripe_webhook_with_invalid_signature_returns_400(): void
    {
        $response = $this->postJson('/api/v1/webhooks/stripe', [
            'type' => 'checkout.session.completed',
            'data' => ['object' => []],
        ], [
            'Stripe-Signature' => 'invalid_signature',
        ]);

        $response->assertStatus(400);
        $response->assertJsonStructure(['error']);
    }

    /**
     * Testa que webhook Asaas com token inválido retorna 400.
     */
    public function test_asaas_webhook_with_invalid_signature_returns_400(): void
    {
        $response = $this->postJson('/api/v1/webhooks/asaas', [
            'event' => 'PAYMENT_CONFIRMED',
            'payment' => ['id' => 'pay_123'],
        ], [
            'asaas-access-token' => 'wrong_token',
        ]);

        $response->assertStatus(400);
        $response->assertJsonStructure(['error']);
    }

    /**
     * Testa que webhook Stripe válido despacha o job ProcessPaymentWebhook.
     */
    public function test_valid_stripe_webhook_dispatches_job(): void
    {
        Queue::fake();

        $mockGateway = Mockery::mock(PaymentGatewayInterface::class);
        $mockGateway->shouldReceive('validateWebhookSignature')
            ->once()
            ->andReturn(true);

        $mockManager = Mockery::mock(PaymentManager::class);
        $mockManager->shouldReceive('gateway')
            ->with('stripe')
            ->andReturn($mockGateway);

        $this->app->instance(PaymentManager::class, $mockManager);

        $payload = [
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'payment_intent' => 'pi_test_123',
                    'status' => 'complete',
                    'amount_total' => 2999,
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/webhooks/stripe', $payload);

        $response->assertStatus(200);
        Queue::assertPushed(ProcessPaymentWebhook::class);
    }

    /**
     * Testa que webhook Asaas válido despacha o job ProcessPaymentWebhook.
     */
    public function test_valid_asaas_webhook_dispatches_job(): void
    {
        Queue::fake();

        $mockGateway = Mockery::mock(PaymentGatewayInterface::class);
        $mockGateway->shouldReceive('validateWebhookSignature')
            ->once()
            ->andReturn(true);

        $mockManager = Mockery::mock(PaymentManager::class);
        $mockManager->shouldReceive('gateway')
            ->with('asaas')
            ->andReturn($mockGateway);

        $this->app->instance(PaymentManager::class, $mockManager);

        $payload = [
            'event' => 'PAYMENT_CONFIRMED',
            'payment' => [
                'id' => 'pay_asaas_456',
                'status' => 'CONFIRMED',
                'value' => 29.99,
            ],
        ];

        $response = $this->postJson('/api/v1/webhooks/asaas', $payload);

        $response->assertStatus(200);
        Queue::assertPushed(ProcessPaymentWebhook::class);
    }

    /**
     * Testa que o webhook retorna 200 antes de processar (job na fila).
     */
    public function test_webhook_returns_200_before_processing(): void
    {
        Queue::fake();

        $mockGateway = Mockery::mock(PaymentGatewayInterface::class);
        $mockGateway->shouldReceive('validateWebhookSignature')
            ->once()
            ->andReturn(true);

        $mockManager = Mockery::mock(PaymentManager::class);
        $mockManager->shouldReceive('gateway')
            ->with('stripe')
            ->andReturn($mockGateway);

        $this->app->instance(PaymentManager::class, $mockManager);

        $response = $this->postJson('/api/v1/webhooks/stripe', [
            'type' => 'checkout.session.completed',
            'data' => ['object' => ['payment_intent' => 'pi_test']],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Webhook recebido']);

        Queue::assertPushed(ProcessPaymentWebhook::class, 1);
    }
}
