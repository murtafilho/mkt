<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductVariation>
 */
class ProductVariationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $variationTypes = [
            'size' => ['P', 'M', 'G', 'GG'],
            'color' => ['Preto', 'Branco', 'Azul', 'Vermelho', 'Verde'],
            'voltage' => ['110V', '220V', 'Bivolt'],
            'capacity' => ['32GB', '64GB', '128GB', '256GB'],
        ];

        $type = fake()->randomElement(array_keys($variationTypes));
        $value = fake()->randomElement($variationTypes[$type]);

        return [
            'product_id' => Product::factory()->withVariations(),
            'name' => ucfirst($type),
            'value' => $value,
            'sku' => fake()->optional()->unique()->regexify('[A-Z]{2}[0-9]{3}[A-Z]{2}[0-9]{3}'),
            'additional_price' => fake()->randomFloat(2, 0, 50),
            'stock' => fake()->numberBetween(0, 50),
            'order' => fake()->numberBetween(0, 10),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the variation is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
