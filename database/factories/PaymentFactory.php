<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'payment_method' => fake()->randomElement(['credit_card', 'debit_card', 'pix', 'boleto']),
            'payment_type' => fake()->randomElement(['credit_card', 'debit_card', 'bank_transfer', 'ticket']),
            'amount' => fake()->randomFloat(2, 10, 1000),
            'installments' => fake()->numberBetween(1, 12),
            'fee_amount' => fake()->randomFloat(2, 0, 50),
            'net_amount' => fake()->randomFloat(2, 10, 950),
            'status' => fake()->randomElement(['pending', 'approved', 'rejected', 'refunded']),
            'external_payment_id' => 'MP-'.fake()->numerify('##########'),
            'paid_at' => fake()->boolean(70) ? fake()->dateTimeBetween('-30 days', 'now') : null,
            'metadata' => [
                'transaction_id' => fake()->uuid(),
                'card_brand' => fake()->randomElement(['visa', 'mastercard', 'elo', 'amex']),
                'card_last_four' => fake()->numerify('####'),
            ],
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
     * Indicate that the payment is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'paid_at' => now(),
        ]);
    }

    /**
     * Indicate that the payment is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'paid_at' => null,
        ]);
    }
}
