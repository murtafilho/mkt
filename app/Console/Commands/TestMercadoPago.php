<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestMercadoPago extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:mercadopago {--create-order : Create a new test order}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Mercado Pago integration';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🧪 Testing Mercado Pago Integration');
        $this->newLine();

        // 1. Check configuration
        $this->info('📋 Step 1: Checking configuration...');
        $this->checkConfiguration();
        $this->newLine();

        // 2. Get test user
        $this->info('👤 Step 2: Getting test user...');
        $user = User::where('email', 'customer@valedosol.org')->first();

        if (! $user) {
            $this->error('❌ Test user not found. Run: php artisan db:seed');

            return self::FAILURE;
        }

        $this->info("✅ User: {$user->name}");
        $this->info("   Email: {$user->email}");
        $this->info("   CPF: {$user->cpf_cnpj}");
        $this->info("   Phone: {$user->phone}");
        $this->newLine();

        // 3. Get or create order
        $this->info('📦 Step 3: Getting test order...');
        $order = $this->getOrCreateOrder($user);

        if (! $order) {
            $this->error('❌ Failed to get or create test order');

            return self::FAILURE;
        }

        $this->displayOrderInfo($order);
        $this->newLine();

        // 4. Test PaymentService
        $this->info('💳 Step 4: Creating Mercado Pago preference...');

        try {
            $paymentService = new PaymentService;
            $result = $paymentService->createPreference($order);

            $this->newLine();
            $this->info('✅ SUCCESS! Preference created:');
            $this->table(
                ['Field', 'Value'],
                [
                    ['Preference ID', $result['preference_id']],
                    ['Init Point', $result['init_point']],
                    ['Sandbox Init Point', $result['sandbox_init_point'] ?? 'N/A'],
                ]
            );

            $this->newLine();
            $this->info('🎉 Integration test PASSED!');
            $this->newLine();

            $this->info('📌 Next steps:');
            $this->info('   1. Open the sandbox init point in your browser');
            $this->info('   2. Use a test card to complete the payment');
            $this->info('   3. Check webhook logs: php artisan pail');
            $this->newLine();

            $this->info('🧪 Test Cards (Mercado Pago Sandbox):');
            $this->table(
                ['Type', 'Number', 'CVV', 'Expiry'],
                [
                    ['✅ Approved', '5031 4332 1540 6351', '123', '11/25'],
                    ['❌ Rejected', '5031 7557 3453 0604', '123', '11/25'],
                    ['⏳ Pending', '5031 4332 1540 6351', '123', '11/25'],
                ]
            );

            $this->newLine();
            $this->info("🌐 Open in browser: {$result['sandbox_init_point']}");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->newLine();
            $this->error('❌ ERROR: '.$e->getMessage());
            $this->newLine();
            $this->error('Stack trace:');
            $this->error($e->getTraceAsString());

            return self::FAILURE;
        }
    }

    /**
     * Check Mercado Pago configuration.
     */
    private function checkConfiguration(): void
    {
        $accessToken = config('services.mercadopago.access_token');
        $publicKey = config('services.mercadopago.public_key');
        $webhookUrl = config('services.mercadopago.webhook_url');

        $this->table(
            ['Config', 'Value'],
            [
                ['Access Token', substr($accessToken, 0, 20).'...'],
                ['Public Key', substr($publicKey, 0, 20).'...'],
                ['Webhook URL', $webhookUrl],
                ['Test Mode', config('services.mercadopago.test_mode') ? '✅ Yes' : '❌ No'],
            ]
        );
    }

    /**
     * Get or create test order.
     */
    private function getOrCreateOrder(User $user): ?Order
    {
        // Try to get existing order
        $order = Order::where('user_id', $user->id)
            ->where('status', 'awaiting_payment')
            ->with(['items.product', 'address', 'seller'])
            ->first();

        if ($order && ! $this->option('create-order')) {
            $this->info('✅ Using existing order');

            return $order;
        }

        if ($this->option('create-order') || ! $order) {
            $this->info('⏳ Creating new test order...');

            return $this->createTestOrder($user);
        }

        return $order;
    }

    /**
     * Create a test order.
     */
    private function createTestOrder(User $user): ?Order
    {
        // Get active seller
        $seller = Seller::where('status', 'active')->first();
        if (! $seller) {
            $this->error('No active seller found');

            return null;
        }

        // Get published product
        $product = Product::where('seller_id', $seller->id)
            ->where('status', 'published')
            ->where('stock', '>', 0)
            ->first();

        if (! $product) {
            $this->error('No published product found');

            return null;
        }

        return DB::transaction(function () use ($user, $seller, $product) {
            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'seller_id' => $seller->id,
                'subtotal' => $product->sale_price,
                'shipping_fee' => 15.00,
                'total' => $product->sale_price + 15.00,
                'status' => 'awaiting_payment',
            ]);

            // Create order item
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'sku' => $product->sku,
                'quantity' => 1,
                'unit_price' => $product->sale_price,
                'subtotal' => $product->sale_price,
            ]);

            // Create order address
            OrderAddress::create([
                'order_id' => $order->id,
                'postal_code' => '01310100',
                'street' => 'Avenida Paulista',
                'number' => '1000',
                'neighborhood' => 'Bela Vista',
                'city' => 'São Paulo',
                'state' => 'SP',
                'recipient_name' => $user->name,
                'contact_phone' => $user->phone ?? '11987654321',
            ]);

            // Reload with relationships
            $order->load(['items.product', 'address', 'seller']);

            $this->info('✅ Test order created');

            return $order;
        });
    }

    /**
     * Display order information.
     */
    private function displayOrderInfo(Order $order): void
    {
        /** @var \App\Models\Seller $seller */
        $seller = $order->seller;

        $this->table(
            ['Field', 'Value'],
            [
                ['Order ID', $order->id],
                ['Seller', $seller->store_name],
                ['Status', $order->status],
                ['Subtotal', 'R$ '.number_format((float) $order->subtotal, 2, ',', '.')],
                ['Shipping', 'R$ '.number_format((float) $order->shipping_fee, 2, ',', '.')],
                ['Total', 'R$ '.number_format((float) $order->total, 2, ',', '.')],
                ['Items', $order->items->count()],
                ['Has Address', $order->address ? '✅ Yes' : '❌ No'],
            ]
        );

        if ($order->items->isNotEmpty()) {
            $this->newLine();
            $this->info('📦 Order Items:');
            $this->table(
                ['Product', 'SKU', 'Qty', 'Price', 'Subtotal'],
                $order->items->map(fn ($item) => [
                    $item->product_name,
                    $item->sku,
                    $item->quantity,
                    'R$ '.number_format((float) $item->unit_price, 2, ',', '.'),
                    'R$ '.number_format((float) $item->subtotal, 2, ',', '.'),
                ])->toArray()
            );
        }
    }
}
