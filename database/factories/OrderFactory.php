<?php

namespace Database\Factories;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 100, 2000);
        $discount = fake()->randomFloat(2, 0, $subtotal * 0.2);
        $shippingFee = fake()->randomFloat(2, 10, 50);
        $total = $subtotal - $discount + $shippingFee;

        return [
            'user_id' => User::factory(),
            'seller_id' => Seller::factory()->approved(),
            'order_number' => strtoupper(fake()->unique()->bothify('ORD-########')),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'shipping_fee' => $shippingFee,
            'total' => $total,
            'status' => 'awaiting_payment',
            'payment_method' => null,
            'notes' => fake()->optional()->sentence(),
            'mercadopago_preference_id' => null,
            'mercadopago_payment_id' => null,
            'mercadopago_status' => null,
            'mercadopago_details' => null,
            'paid_at' => null,
            'shipped_at' => null,
            'delivered_at' => null,
            'cancelled_at' => null,
        ];
    }

    /**
     * Indicate that the order is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'payment_method' => 'mercadopago',
            'mercadopago_preference_id' => fake()->uuid(),
            'mercadopago_payment_id' => fake()->numerify('##########'),
            'mercadopago_status' => 'approved',
            'paid_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * Indicate that the order is preparing.
     */
    public function preparing(): static
    {
        return $this->paid()->state(fn (array $attributes) => [
            'status' => 'preparing',
        ]);
    }

    /**
     * Indicate that the order is shipped.
     */
    public function shipped(): static
    {
        return $this->preparing()->state(fn (array $attributes) => [
            'status' => 'shipped',
            'shipped_at' => fake()->dateTimeBetween($attributes['paid_at'], 'now'),
        ]);
    }

    /**
     * Indicate that the order is delivered.
     */
    public function delivered(): static
    {
        return $this->shipped()->state(fn (array $attributes) => [
            'status' => 'delivered',
            'delivered_at' => fake()->dateTimeBetween($attributes['shipped_at'], 'now'),
        ]);
    }

    /**
     * Indicate that the order is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'cancelled_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * Indicate that the order is refunded.
     */
    public function refunded(): static
    {
        return $this->paid()->state(fn (array $attributes) => [
            'status' => 'refunded',
            'mercadopago_status' => 'refunded',
        ]);
    }
}
