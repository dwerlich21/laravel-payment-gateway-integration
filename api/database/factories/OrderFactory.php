<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $product = Product::factory();
        $quantity = fake()->numberBetween(1, 5);

        return [
            'user_id' => User::factory(),
            'product_id' => $product,
            'quantity' => $quantity,
            'total_amount' => fn (array $attributes) => Product::find($attributes['product_id'])->price * $attributes['quantity'],
            'status' => Order::STATUS_PENDING,
            'gateway' => fake()->randomElement(['stripe', 'asaas']),
            'payment_method' => fake()->randomElement(['credit_card', 'boleto', 'pix']),
            'customer_name' => fake('pt_BR')->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_cpf_cnpj' => fake()->numerify('###.###.###-##'),
            'customer_phone' => fake('pt_BR')->cellphoneNumber(false),
            'external_id' => null,
            'payment_url' => null,
        ];
    }

    /**
     * Indicate that the order is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::STATUS_PAID,
        ]);
    }

    /**
     * Indicate that the order has failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::STATUS_FAILED,
        ]);
    }

    /**
     * Set the gateway to Stripe.
     */
    public function stripe(): static
    {
        return $this->state(fn (array $attributes) => [
            'gateway' => 'stripe',
        ]);
    }

    /**
     * Set the gateway to Asaas.
     */
    public function asaas(): static
    {
        return $this->state(fn (array $attributes) => [
            'gateway' => 'asaas',
        ]);
    }
}
