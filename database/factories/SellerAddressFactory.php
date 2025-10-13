<?php

namespace Database\Factories;

use App\Models\Seller;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SellerAddress>
 */
class SellerAddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'seller_id' => Seller::factory(),
            'type' => fake()->randomElement(['business', 'return']),
            'postal_code' => fake()->regexify('[0-9]{5}-[0-9]{3}'),
            'street' => fake()->streetName(),
            'number' => fake()->buildingNumber(),
            'complement' => fake()->optional()->word(),
            'neighborhood' => fake()->citySuffix(),
            'city' => fake()->city(),
            'state' => fake()->randomElement(['SP', 'RJ', 'MG', 'RS', 'BA', 'PR', 'SC', 'PE', 'CE', 'PA']),
            'is_default' => false,
        ];
    }

    /**
     * Indicate that the address is the default address.
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    /**
     * Indicate that the address is a business address.
     */
    public function business(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'business',
        ]);
    }

    /**
     * Indicate that the address is a return address.
     */
    public function return(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'return',
        ]);
    }
}
