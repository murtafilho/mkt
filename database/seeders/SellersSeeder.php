<?php

namespace Database\Seeders;

use App\Models\Seller;
use App\Models\SellerAddress;
use App\Models\SellerPayment;
use App\Models\User;
use Illuminate\Database\Seeder;

class SellersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 50 sellers with complete data (increased for massive data)
        for ($i = 1; $i <= 50; $i++) {
            // Create user for seller
            $user = User::factory()->create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
            ]);
            $user->assignRole('seller');

            // Create seller profile
            $seller = Seller::factory()->create([
                'user_id' => $user->id,
                'status' => fake()->randomElement(['pending', 'active', 'active', 'active', 'suspended']), // 60% active
            ]);

            // Create seller address
            SellerAddress::factory()->create([
                'seller_id' => $seller->id,
            ]);

            // Create seller payment info
            SellerPayment::factory()->create([
                'seller_id' => $seller->id,
            ]);
        }

        // Ensure the test seller@valedosol.org has complete profile
        $testSeller = User::where('email', 'seller@valedosol.org')->first();
        if ($testSeller && ! $testSeller->seller) {
            $seller = Seller::factory()->create([
                'user_id' => $testSeller->id,
                'store_name' => 'Loja Teste Vale do Sol',
                'slug' => 'loja-teste-vale-do-sol',
                'status' => 'active',
            ]);

            SellerAddress::factory()->create([
                'seller_id' => $seller->id,
            ]);

            SellerPayment::factory()->create([
                'seller_id' => $seller->id,
            ]);
        }
    }
}
