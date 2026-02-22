<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = fake()->randomFloat(2, 9.99, 299.99);
        $fees = round($amount * 0.0399, 2);

        return [
            'order_id' => Order::factory(),
            'external_id' => fake()->uuid(),
            'gateway' => fake()->randomElement(['stripe', 'asaas']),
            'status' => 'paid',
            'amount' => $amount,
            'fees' => $fees,
            'net_amount' => $amount - $fees,
            'paid_at' => now(),
            'raw_payload' => [],
            'normalized_payload' => [],
        ];
    }

    /**
     * Indicate that the payment is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'paid_at' => null,
        ]);
    }

    /**
     * Indicate that the payment has failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'paid_at' => null,
        ]);
    }
}
