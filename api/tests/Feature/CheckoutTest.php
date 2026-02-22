<?php

namespace Tests\Feature;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Product;
use App\Services\Payment\PaymentManager;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ProductSeeder::class);
    }

    private function validCheckoutData(array $overrides = []): array
    {
        return array_merge([
            'product_id' => Product::first()->id,
            'gateway' => 'stripe',
            'customer_name' => 'João Silva',
            'customer_email' => 'joao@example.com',
            'customer_cpf_cnpj' => '12345678901',
        ], $overrides);
    }

    /**
     * Testa criação de pedido com gateway Stripe.
     */
    public function test_can_create_order_with_stripe(): void
    {
        $mockGateway = Mockery::mock(PaymentGatewayInterface::class);
        $mockGateway->shouldReceive('createCharge')
            ->once()
            ->andReturn([
                'external_id' => 'pi_stripe_test_123',
                'checkout_url' => 'https://checkout.stripe.com/test',
                'status' => 'pending',
            ]);

        $mockManager = Mockery::mock(PaymentManager::class);
        $mockManager->shouldReceive('gateway')
            ->with('stripe')
            ->andReturn($mockGateway);

        $this->app->instance(PaymentManager::class, $mockManager);

        $response = $this->postJson('/api/v1/checkout', $this->validCheckoutData());

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);

        $this->assertDatabaseHas('orders', [
            'gateway' => 'stripe',
            'status' => 'pending',
            'customer_email' => 'joao@example.com',
        ]);
    }

    /**
     * Testa criação de pedido com gateway Asaas.
     */
    public function test_can_create_order_with_asaas(): void
    {
        $mockGateway = Mockery::mock(PaymentGatewayInterface::class);
        $mockGateway->shouldReceive('createCharge')
            ->once()
            ->andReturn([
                'external_id' => 'pay_asaas_test_789',
                'invoice_url' => 'https://sandbox.asaas.com/invoice/test',
                'status' => 'pending',
            ]);

        $mockManager = Mockery::mock(PaymentManager::class);
        $mockManager->shouldReceive('gateway')
            ->with('asaas')
            ->andReturn($mockGateway);

        $this->app->instance(PaymentManager::class, $mockManager);

        $response = $this->postJson('/api/v1/checkout', $this->validCheckoutData([
            'gateway' => 'asaas',
        ]));

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);

        $this->assertDatabaseHas('orders', [
            'gateway' => 'asaas',
            'status' => 'pending',
            'customer_email' => 'joao@example.com',
        ]);
    }

    /**
     * Testa que produto inválido retorna 422.
     */
    public function test_invalid_product_returns_422(): void
    {
        $response = $this->postJson('/api/v1/checkout', $this->validCheckoutData([
            'product_id' => 99999,
        ]));

        $response->assertStatus(422);
    }
}
