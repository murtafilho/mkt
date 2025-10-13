<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderHistory>
 */
class OrderHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['awaiting_payment', 'paid', 'preparing', 'shipped', 'delivered', 'cancelled', 'refunded'];
        $newStatus = fake()->randomElement($statuses);
        $previousStatuses = array_slice($statuses, 0, array_search($newStatus, $statuses));

        return [
            'order_id' => Order::factory(),
            'previous_status' => fake()->optional()->randomElement($previousStatuses ?: [null]),
            'new_status' => $newStatus,
            'note' => fake()->optional()->sentence(),
            'user_id' => User::factory(),
        ];
    }
}
