<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all customers
        $customers = User::role('customer')->get();

        // Create some customer users if none exist (increased to 30 for massive data)
        if ($customers->isEmpty()) {
            for ($i = 0; $i < 30; $i++) {
                $user = User::factory()->create();
                $user->assignRole('customer');
                $customers->push($user);
            }
        }

        // Get all active sellers with published products
        $sellers = Seller::where('status', 'active')
            ->whereHas('products', function ($query) {
                $query->where('status', 'published');
            })
            ->get();

        if ($sellers->isEmpty()) {
            $this->command->warn('No sellers with published products found.');

            return;
        }

        // Create 200 orders (increased for massive data)
        for ($i = 0; $i < 200; $i++) {
            $customer = $customers->random();
            $seller = $sellers->random();

            // Get random products from this seller
            $products = Product::where('seller_id', $seller->id)
                ->where('status', 'published')
                ->inRandomOrder()
                ->limit(fake()->numberBetween(1, 4))
                ->get();

            if ($products->isEmpty()) {
                continue;
            }

            // Calculate order totals
            $subtotal = 0;
            $orderItems = [];

            foreach ($products as $product) {
                $quantity = fake()->numberBetween(1, 3);
                $price = $product->sale_price;
                $itemTotal = $price * $quantity;
                $subtotal += $itemTotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $itemTotal,
                ];
            }

            $shippingFee = fake()->randomFloat(2, 10, 50);
            $total = $subtotal + $shippingFee;

            // Create order
            $order = Order::factory()->create([
                'user_id' => $customer->id,
                'seller_id' => $seller->id,
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'total' => $total,
                'status' => fake()->randomElement(['awaiting_payment', 'paid', 'preparing', 'shipped', 'delivered', 'cancelled']),
            ]);

            // Create order address
            OrderAddress::factory()->create([
                'order_id' => $order->id,
            ]);

            // Create order items
            foreach ($orderItems as $itemData) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $itemData['product_id'],
                    'product_name' => $itemData['product_name'],
                    'sku' => $itemData['sku'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['price'],
                    'subtotal' => $itemData['subtotal'],
                ]);
            }

            // Order history will be created automatically by observers/events
        }

        $this->command->info('Orders seeded successfully!');
    }
}
