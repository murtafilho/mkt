<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all active sellers
        $sellers = Seller::where('status', 'active')->get();

        if ($sellers->isEmpty()) {
            $this->command->warn('No active sellers found. Run SellersSeeder first.');

            return;
        }

        // Get all active categories
        $categories = Category::where('is_active', true)->get();

        if ($categories->isEmpty()) {
            $this->command->warn('No active categories found. Run CategoriesSeeder first.');

            return;
        }

        // Create products distributed among active sellers (each seller gets 5-15 products)
        foreach ($sellers as $seller) {
            // Each seller gets 5-15 products (increased for massive data)
            $productCount = fake()->numberBetween(5, 15);

            for ($i = 0; $i < $productCount; $i++) {
                $product = Product::factory()->create([
                    'seller_id' => $seller->id,
                    'category_id' => $categories->random()->id,
                    'status' => fake()->randomElement(['published', 'published', 'published', 'draft', 'out_of_stock']), // 60% published
                    'is_featured' => fake()->boolean(20), // 20% featured
                ]);

                // Add 1-3 product images (only for published/draft products)
                if (in_array($product->status, ['published', 'draft'])) {
                    $imageCount = fake()->numberBetween(1, 3);
                    for ($j = 0; $j < $imageCount; $j++) {
                        $product->addMediaFromUrl('https://picsum.photos/800/800?random='.uniqid())
                            ->toMediaCollection('product_images');
                    }
                }
            }
        }

        $this->command->info('Products seeded successfully!');
    }
}
