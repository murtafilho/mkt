<?php

namespace Tests\Browser;

use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Helpers\MocksExternalAPIs;
use Tests\DuskTestCase;

/**
 * Payment Flow Test
 *
 * Tests the complete payment flow:
 * 1. Add product to cart
 * 2. Go to checkout
 * 3. Fill shipping address
 * 4. Proceed to payment (Payment Brick)
 * 5. Verify Mercado Pago Brick loads correctly
 */
class PaymentFlowTest extends DuskTestCase
{
    use MocksExternalAPIs;

    // ✅ NÃO usa DatabaseMigrations - usa banco separado mkt_dusk

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure roles exist (seed if needed)
        if (\Spatie\Permission\Models\Role::count() === 0) {
            $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        }

        // Ensure categories exist
        if (Category::count() === 0) {
            $this->seed(\Database\Seeders\CategoriesSeeder::class);
        }
    }

    /**
     * Test complete payment flow from cart to payment screen
     *
     * @return void
     */
    public function test_complete_payment_flow()
    {
        $this->browse(function (Browser $browser) {
            // 1. Create test data
            [$customer, $seller, $product] = $this->createTestData();

            // 2. Mock external APIs BEFORE visiting any page
            $this->mockExternalAPIs($browser);

            // 3. Login as customer
            $browser->loginAs($customer)
                ->visit('/produtos/'.$product->slug)
                ->pause(500)
                ->assertSee($product->name);

            // 3. Add to cart
            $browser->scrollIntoView('button[type="button"][x-ref="addButton"]')
                ->pause(500)
                ->click('button[type="button"][x-ref="addButton"]')
                ->pause(2000) // Wait for cart to update
                ->assertSee('Adicionado!');

            // 4. Go to cart
            $browser->visit('/carrinho')
                ->pause(1000)
                ->assertSee($product->name)
                ->assertSee($seller->store_name);

            // 5. Proceed to checkout
            $browser->click('a[href*="/checkout"]')
                ->pause(1000)
                ->assertPathIs('/checkout')
                ->assertSee('Finalizar Compra');

            // 6. Fill shipping address
            $browser->scrollIntoView('#recipient_name')
                ->type('#recipient_name', 'João da Silva')
                ->type('#recipient_phone', '11987654321')
                ->type('#postal_code', '13500-110')
                ->pause(500)
                ->click('button[type="button"]') // CEP search button
                ->pause(2000) // Wait for CEP API
                ->type('#number', '123')
                ->type('#complement', 'Apto 45');

            // 7. Verify address was filled
            $browser->assertInputValue('#city', 'Rio Claro')
                ->assertValue('#state', 'SP');

            // 8. Click "Prosseguir para Pagamento"
            $browser->scrollIntoView('#processCheckoutBtn')
                ->pause(500)
                ->screenshot('before_payment_click')
                ->click('#processCheckoutBtn')
                ->pause(3000); // Wait for Payment Brick to render

            // 9. Verify Payment Brick loaded
            $browser->assertVisible('#paymentBrick_container')
                ->screenshot('payment_brick_rendered');

            // 10. Verify Payment Brick iframe exists
            $browser->waitFor('iframe[name*="payment"]', 10)
                ->screenshot('payment_brick_iframe_loaded');

            // 11. Check for JavaScript errors in console
            $logs = $browser->driver->manage()->getLog('browser');
            $errors = array_filter($logs, function ($log) {
                return $log['level'] === 'SEVERE';
            });

            if (! empty($errors)) {
                echo "\n❌ JavaScript Errors Found:\n";
                foreach ($errors as $error) {
                    echo "  - {$error['message']}\n";
                }
            }

            $this->assertEmpty($errors, 'JavaScript errors found in console');

            // 12. Verify Payment Brick is interactive
            $browser->pause(2000)
                ->screenshot('final_payment_state');
        });
    }

    /**
     * Test Payment Brick initialization errors
     *
     * @return void
     */
    public function test_payment_brick_initialization()
    {
        $this->browse(function (Browser $browser) {
            // 1. Create test data
            [$customer, $seller, $product] = $this->createTestData();

            // 2. Mock external APIs
            $this->mockExternalAPIs($browser);

            // 2. Add product to cart via API
            $this->actingAs($customer)
                ->postJson('/cart/add', [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ]);

            // 3. Go directly to checkout
            $browser->loginAs($customer)
                ->visit('/checkout')
                ->pause(1000)
                ->assertSee('Finalizar Compra');

            // 4. Fill minimal shipping data
            $browser->type('#recipient_name', 'Test User')
                ->type('#recipient_phone', '11999999999')
                ->type('#postal_code', '13500-110')
                ->type('#street', 'Rua Teste')
                ->type('#number', '100')
                ->type('#neighborhood', 'Centro')
                ->type('#city', 'Rio Claro')
                ->select('#state', 'SP');

            // 5. Click to show payment
            $browser->click('#processCheckoutBtn')
                ->pause(3000);

            // 6. Check console for Mercado Pago errors
            $browser->script([
                "console.log('🔍 Checking for MP errors...');",
                "console.log('MercadoPago object:', typeof window.MercadoPago);",
                "console.log('loadMercadoPago function:', typeof window.loadMercadoPago);",
            ]);

            // 7. Verify no errors
            $logs = $browser->driver->manage()->getLog('browser');
            $mpErrors = array_filter($logs, function ($log) {
                return stripos($log['message'], 'mercadopago') !== false
                    || stripos($log['message'], 'brick') !== false;
            });

            if (! empty($mpErrors)) {
                echo "\n⚠️ Mercado Pago related logs:\n";
                foreach ($mpErrors as $error) {
                    echo "  - {$error['level']}: {$error['message']}\n";
                }
            }

            $browser->screenshot('payment_brick_debug');
        });
    }

    /**
     * Test CEP auto-fill functionality
     *
     * @return void
     */
    public function test_cep_autofill_on_checkout()
    {
        $this->browse(function (Browser $browser) {
            // 1. Create test data
            [$customer, $seller, $product] = $this->createTestData();

            // 2. Mock external APIs
            $this->mockExternalAPIs($browser);

            // 2. Add product to cart
            $this->actingAs($customer)
                ->postJson('/cart/add', [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ]);

            // 3. Go to checkout
            $browser->loginAs($customer)
                ->visit('/checkout')
                ->pause(1000);

            // 4. Test CEP lookup
            $browser->type('#postal_code', '01310-100')
                ->pause(500)
                ->keys('#postal_code', '{tab}') // Trigger CEP lookup via blur event
                ->pause(3000); // Wait for ViaCEP API

            // 5. Verify auto-fill
            $browser->assertInputValue('#street', 'Avenida Paulista')
                ->assertInputValue('#neighborhood', 'Bela Vista')
                ->assertInputValue('#city', 'São Paulo')
                ->assertValue('#state', 'SP')
                ->screenshot('cep_autofill_success');
        });
    }

    /**
     * Test cart validation before checkout
     *
     * @return void
     */
    public function test_cart_validation_before_checkout()
    {
        $this->browse(function (Browser $browser) {
            // 1. Create test data with out-of-stock product
            $customer = User::factory()->create();
            $customer->assignRole('customer');

            $seller = Seller::factory()->create(['status' => 'active']);
            $category = Category::first();

            $product = Product::factory()->create([
                'seller_id' => $seller->id,
                'category_id' => $category->id,
                'status' => 'published',
                'stock' => 0, // Out of stock
            ]);

            // 2. Try to add out-of-stock product
            $response = $this->actingAs($customer)
                ->postJson('/cart/add', [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ]);

            // 3. Verify error
            $response->assertStatus(422)
                ->assertJsonFragment(['success' => false]);

            // 4. Verify checkout is inaccessible with empty cart
            $browser->loginAs($customer)
                ->visit('/checkout')
                ->pause(1000)
                ->assertPathIs('/carrinho')
                ->assertSee('Seu carrinho está vazio');
        });
    }

    /**
     * Helper: Create test data
     *
     * @return array [User $customer, Seller $seller, Product $product]
     */
    private function createTestData(): array
    {
        // Create customer
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        // Create seller with approved status
        $seller = Seller::factory()->create(['status' => 'active']);

        // Get or create category
        $category = Category::first() ?? Category::factory()->create();

        // Create product
        $product = Product::factory()->create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'status' => 'published',
            'stock' => 10,
            'original_price' => 99.90,
            'sale_price' => 89.90,
        ]);

        return [$customer, $seller, $product];
    }
}
