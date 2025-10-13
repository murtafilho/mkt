<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Seller;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(3, true);
        $originalPrice = fake()->randomFloat(2, 50, 5000);
        $salePrice = fake()->randomFloat(2, $originalPrice * 0.7, $originalPrice);

        return [
            'seller_id' => Seller::factory()->approved(),
            'category_id' => Category::factory(),
            'name' => ucfirst($name),
            'slug' => str()->slug($name).'-'.fake()->unique()->numberBetween(1, 9999),
            'description' => fake()->paragraphs(3, true),
            'short_description' => fake()->optional()->sentence(),
            'sku' => strtoupper(fake()->unique()->bothify('??###???###')),
            'cost_price' => fake()->optional()->randomFloat(2, 20, $salePrice * 0.7),
            'original_price' => $originalPrice,
            'sale_price' => $salePrice,
            'stock' => fake()->numberBetween(0, 100),
            'min_stock' => fake()->numberBetween(5, 20),
            'weight' => fake()->optional()->randomFloat(2, 100, 5000),
            'width' => fake()->optional()->randomFloat(2, 10, 100),
            'height' => fake()->optional()->randomFloat(2, 5, 50),
            'depth' => fake()->optional()->randomFloat(2, 10, 100),
            'has_variations' => false,
            'is_featured' => fake()->boolean(20),
            'status' => 'draft',
            'views' => fake()->numberBetween(0, 1000),
        ];
    }

    /**
     * Indicate that the product is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
        ]);
    }

    /**
     * Indicate that the product is out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'out_of_stock',
            'stock' => 0,
        ]);
    }

    /**
     * Indicate that the product is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    /**
     * Indicate that the product is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the product has variations.
     */
    public function withVariations(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_variations' => true,
        ]);
    }
}
