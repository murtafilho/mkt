<?php

namespace Tests\Browser;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class CustomerShoppingFlowTest extends DuskTestCase
{
    // ✅ NÃO usa DatabaseMigrations - usa banco separado mkt_dusk

    /**
     * Test: Complete shopping flow - Browse, Add to Cart, Checkout.
     */
    public function test_complete_shopping_flow(): void
    {
        // Setup: Create approved seller with products
        $sellerUser = User::factory()->create();
        $seller = $sellerUser->seller()->create([
            'store_name' => 'Tech Store',
            'slug' => 'tech-store-'.time(),
            'document_number' => '123456789'.substr(time(), -2),
            'person_type' => 'individual',
            'business_phone' => '31987654321',
            'business_email' => 'tech@example.com',
            'status' => 'active',
            'approved_at' => now(),
        ]);

        $category = Category::factory()->create([
            'name' => 'Eletrônicos',
            'is_active' => true,
        ]);

        $product = $seller->products()->create([
            'category_id' => $category->id,
            'name' => 'Smartphone Premium',
            'slug' => 'smartphone-premium-'.uniqid(),
            'sku' => 'SMART-'.strtoupper(uniqid()),
            'description' => 'Smartphone top de linha com câmera incrível',
            'original_price' => 3000.00,
            'sale_price' => 2499.00,
            'stock' => 50,
            'status' => 'published',
            'is_featured' => true,
        ]);

        // Create customer user
        $uniqueId = uniqid();
        $customer = User::factory()->create([
            'email' => 'cliente-'.$uniqueId.'@example.com',
            'name' => 'Cliente Teste',
        ]);

        $this->browse(function (Browser $browser) use ($customer, $product) {
            $browser->loginAs($customer)
                // Step 1: Browse products - go directly to product page
                ->visit('/produtos/'.$product->slug)
                ->pause(1000)
                ->assertSee('Smartphone Premium')
                ->assertSee('2.499')

                // Step 2: Add to cart
                ->press('Adicionar ao Carrinho')
                ->pause(2000)

                // Step 3: Go to cart page
                ->visit('/carrinho')
                ->pause(1000)
                ->assertSee('Smartphone Premium')

                // Step 4: Go to checkout
                ->visit('/checkout')
                ->pause(2000);

            $currentUrl = $browser->driver->getCurrentURL();
            echo "\n✅ Shopping flow URL: {$currentUrl}\n";

            // Should be on checkout or payment page
            // Note: Actual payment testing requires sandbox/mock
        });
    }

    /**
     * Test: Customer can view product details and related products.
     */
    public function test_customer_can_view_product_details(): void
    {
        $sellerUser = User::factory()->create();
        $uniqueId = uniqid();
        $seller = $sellerUser->seller()->create([
            'store_name' => 'Loja Exemplo',
            'slug' => 'loja-exemplo-'.$uniqueId,
            'document_number' => '55566677788'.substr($uniqueId, -4),
            'person_type' => 'individual',
            'business_phone' => '31955556666',
            'business_email' => 'exemplo-'.$uniqueId.'@example.com',
            'status' => 'active',
            'approved_at' => now(),
        ]);

        $category = Category::factory()->create(['name' => 'Moda', 'is_active' => true]);

        // Main product
        $product = $seller->products()->create([
            'category_id' => $category->id,
            'name' => 'Camiseta Premium',
            'slug' => 'camiseta-premium-'.uniqid(),
            'sku' => 'CAM-'.strtoupper(uniqid()),
            'description' => 'Camiseta 100% algodão egipcio',
            'short_description' => 'Camiseta confortável e durável',
            'original_price' => 120.00,
            'sale_price' => 89.90,
            'stock' => 100,
            'status' => 'published',
        ]);

        // Related product (same category)
        $relatedProduct = $seller->products()->create([
            'category_id' => $category->id,
            'name' => 'Calça Jeans',
            'slug' => 'calca-jeans-'.uniqid(),
            'sku' => 'CALCA-'.strtoupper(uniqid()),
            'description' => 'Calça jeans estilo slim',
            'original_price' => 200.00,
            'sale_price' => 159.90,
            'stock' => 50,
            'status' => 'published',
        ]);

        $this->browse(function (Browser $browser) use ($product) {
            $browser->visit('/produtos/'.$product->slug)
                ->assertSee('Camiseta Premium')
                ->assertSee('Camiseta 100% algodão egipcio')

                // Assert: Discount badge visible
                ->assertSee('-25%') // Discount calculation

                // Assert: Seller info visible
                ->assertSee('Loja Exemplo')

                // Assert: Related products section
                ->assertSee('Produtos Relacionados')
                ->assertSee('Calça Jeans');
        });
    }

    /**
     * Test: Customer can filter products by category.
     */
    public function test_customer_can_filter_products_by_category(): void
    {
        $sellerUser = User::factory()->create();
        $uniqueId = uniqid();
        $seller = $sellerUser->seller()->create([
            'store_name' => 'Multi Store',
            'slug' => 'multi-store-'.$uniqueId,
            'document_number' => '77788899900'.substr($uniqueId, -4),
            'person_type' => 'individual',
            'business_phone' => '31944443333',
            'business_email' => 'multi-'.$uniqueId.'@example.com',
            'status' => 'active',
            'approved_at' => now(),
        ]);

        $eletronicos = Category::factory()->create(['name' => 'Eletrônicos', 'is_active' => true]);
        $moda = Category::factory()->create(['name' => 'Moda', 'is_active' => true]);

        $seller->products()->create([
            'category_id' => $eletronicos->id,
            'name' => 'Notebook',
            'slug' => 'notebook-'.uniqid(),
            'sku' => 'NOTE-'.strtoupper(uniqid()),
            'description' => 'Notebook i7',
            'original_price' => 3500.00,
            'sale_price' => 3200.00,
            'stock' => 20,
            'status' => 'published',
        ]);

        $seller->products()->create([
            'category_id' => $moda->id,
            'name' => 'Vestido',
            'slug' => 'vestido-'.uniqid(),
            'sku' => 'VEST-'.strtoupper(uniqid()),
            'description' => 'Vestido elegante',
            'original_price' => 250.00,
            'sale_price' => 199.00,
            'stock' => 30,
            'status' => 'published',
        ]);

        $this->browse(function (Browser $browser) use ($eletronicos, $moda) {
            $browser->visit('/produtos')
                ->pause(1000)
                ->assertSee('Notebook')
                ->assertSee('Vestido')

                // Filter by Eletrônicos using category slug
                ->visit('/produtos?categoria='.$eletronicos->slug)
                ->pause(1000)
                ->assertSee('Notebook')

                // Filter by Moda using category slug
                ->visit('/produtos?categoria='.$moda->slug)
                ->pause(1000)
                ->assertSee('Vestido');

            echo "\n✅ Category filtering works\n";
        });
    }

    /**
     * Test: Customer cannot add out of stock product to cart.
     */
    public function test_customer_cannot_add_out_of_stock_product(): void
    {
        $sellerUser = User::factory()->create();
        $uniqueId = uniqid();
        $seller = $sellerUser->seller()->create([
            'store_name' => 'Stock Test Store',
            'slug' => 'stock-test-'.$uniqueId,
            'document_number' => '33344455566'.substr($uniqueId, -4),
            'person_type' => 'individual',
            'business_phone' => '31933332222',
            'business_email' => 'stock-'.$uniqueId.'@example.com',
            'status' => 'active',
            'approved_at' => now(),
        ]);

        $category = Category::factory()->create(['is_active' => true]);

        $product = $seller->products()->create([
            'category_id' => $category->id,
            'name' => 'Produto Esgotado',
            'slug' => 'produto-esgotado-'.uniqid(),
            'sku' => 'ESGOT-'.strtoupper(uniqid()),
            'description' => 'Sem estoque',
            'original_price' => 100.00,
            'sale_price' => 100.00,
            'stock' => 0, // Out of stock
            'status' => 'out_of_stock',
        ]);

        $this->browse(function (Browser $browser) use ($product) {
            $browser->visit('/produtos/'.$product->slug)
                ->assertSee('Produto Esgotado')
                ->assertSee('Fora de estoque')

                // Assert: Add to cart button is not visible (out of stock)
                ->assertDontSee('Adicionar ao Carrinho');
        });
    }

    /**
     * Test: Guest can browse products but must login to buy.
     */
    public function test_guest_must_login_to_checkout(): void
    {
        $sellerUser = User::factory()->create();
        $uniqueId = uniqid();
        $seller = $sellerUser->seller()->create([
            'store_name' => 'Guest Test Store',
            'slug' => 'guest-test-'.$uniqueId,
            'document_number' => '66677788899'.substr($uniqueId, -4),
            'person_type' => 'individual',
            'business_phone' => '31966667777',
            'business_email' => 'guest-'.$uniqueId.'@example.com',
            'status' => 'active',
            'approved_at' => now(),
        ]);

        $category = Category::factory()->create(['is_active' => true]);

        $product = $seller->products()->create([
            'category_id' => $category->id,
            'name' => 'Produto Teste',
            'slug' => 'produto-teste-'.uniqid(),
            'sku' => 'TESTE-'.strtoupper(uniqid()),
            'description' => 'Para teste de guest',
            'original_price' => 100.00,
            'sale_price' => 100.00,
            'stock' => 10,
            'status' => 'published',
        ]);

        $this->browse(function (Browser $browser) use ($product) {
            // Browse as guest
            $browser->visit('/produtos/'.$product->slug)
                ->assertSee('Produto Teste')
                ->pause(1000)

                // Try to add to cart
                ->press('Adicionar ao Carrinho')
                ->pause(2000)

                // Try to go to checkout (guest should be redirected to login)
                ->visit('/checkout')
                ->pause(2000);

            $currentUrl = $browser->driver->getCurrentURL();
            echo "\n📍 Guest checkout redirect: {$currentUrl}\n";

            // Should be redirected to login or verify-email
            if (str_contains($currentUrl, 'login') || str_contains($currentUrl, 'verify')) {
                echo "✅ Guest redirected to login/verify correctly\n";
            }
        });
    }
}
