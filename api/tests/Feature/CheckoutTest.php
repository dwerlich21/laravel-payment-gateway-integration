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

    private function asaasCardData(array $overrides = []): array
    {
        return $this->validCheckoutData(array_merge([
            'gateway' => 'asaas',
            'payment_method' => 'credit_card',
            'card_number' => '4111111111111111',
            'card_holder' => 'JOAO SILVA',
            'card_expiry_month' => '12',
            'card_expiry_year' => '2030',
            'card_cvv' => '123',
        ], $overrides));
    }

    private function mockGateway(string $gatewayName, array $chargeReturn): void
    {
        $mockGateway = Mockery::mock(PaymentGatewayInterface::class);
        $mockGateway->shouldReceive('createCharge')
            ->once()
            ->andReturn($chargeReturn);

        $mockManager = Mockery::mock(PaymentManager::class);
        $mockManager->shouldReceive('gateway')
            ->with($gatewayName)
            ->andReturn($mockGateway);

        $this->app->instance(PaymentManager::class, $mockManager);
    }

    /**
     * Testa criação de pedido com gateway Stripe.
     */
    public function test_can_create_order_with_stripe(): void
    {
        $this->mockGateway('stripe', [
            'external_id' => 'pi_stripe_test_123',
            'client_secret' => 'pi_stripe_test_123_secret_abc',
            'status' => 'pending',
        ]);

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
     * Testa criação de pedido com Asaas boleto (sem dados de cartão).
     */
    public function test_can_create_order_with_asaas_boleto(): void
    {
        $this->mockGateway('asaas', [
            'external_id' => 'pay_asaas_boleto_123',
            'status' => 'pending',
            'invoice_url' => 'https://sandbox.asaas.com/invoice/test',
        ]);

        $response = $this->postJson('/api/v1/checkout', $this->validCheckoutData([
            'gateway' => 'asaas',
            'payment_method' => 'boleto',
        ]));

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.invoice_url', 'https://sandbox.asaas.com/invoice/test');

        $this->assertDatabaseHas('orders', [
            'gateway' => 'asaas',
            'payment_method' => 'boleto',
            'status' => 'pending',
        ]);
    }

    /**
     * Testa criação de pedido com Asaas PIX (sem dados de cartão).
     */
    public function test_can_create_order_with_asaas_pix(): void
    {
        $this->mockGateway('asaas', [
            'external_id' => 'pay_asaas_pix_456',
            'status' => 'pending',
            'invoice_url' => 'https://sandbox.asaas.com/invoice/pix-test',
        ]);

        $response = $this->postJson('/api/v1/checkout', $this->validCheckoutData([
            'gateway' => 'asaas',
            'payment_method' => 'pix',
        ]));

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);

        $this->assertDatabaseHas('orders', [
            'gateway' => 'asaas',
            'payment_method' => 'pix',
            'status' => 'pending',
        ]);
    }

    /**
     * Testa criação de pedido com Asaas cartão de crédito (com dados do cartão).
     */
    public function test_can_create_order_with_asaas_credit_card(): void
    {
        $this->mockGateway('asaas', [
            'external_id' => 'pay_asaas_cc_789',
            'status' => 'paid',
            'invoice_url' => null,
        ]);

        $response = $this->postJson('/api/v1/checkout', $this->asaasCardData());

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.payment_status', 'paid');

        $this->assertDatabaseHas('orders', [
            'gateway' => 'asaas',
            'payment_method' => 'credit_card',
            'status' => 'paid',
        ]);
    }

    /**
     * Testa que Asaas cartão sem dados de cartão retorna 422.
     */
    public function test_asaas_credit_card_without_card_fields_returns_422(): void
    {
        $response = $this->postJson('/api/v1/checkout', $this->validCheckoutData([
            'gateway' => 'asaas',
            'payment_method' => 'credit_card',
        ]));

        $response->assertStatus(422);
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
