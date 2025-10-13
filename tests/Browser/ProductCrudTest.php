<?php

namespace Tests\Browser;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ProductCrudTest extends DuskTestCase
{
    // ✅ NÃO usa DatabaseMigrations - usa banco separado mkt_dusk

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles/categories se necessário
        if (\Spatie\Permission\Models\Role::count() === 0) {
            $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        }
    }

    /**
     * Helper: Create authenticated seller with unique data.
     */
    protected function createSeller(): User
    {
        $user = User::factory()->create();
        $docNumber = '999'.substr(md5(uniqid()), 0, 8); // Unique document

        $user->seller()->create([
            'store_name' => 'Test Store '.uniqid(),
            'slug' => 'test-store-'.uniqid(),
            'document_number' => $docNumber,
            'person_type' => 'individual',
            'business_phone' => '31'.rand(900000000, 999999999),
            'business_email' => 'store'.uniqid().'@test.com',
            'status' => 'active',
            'approved_at' => now(),
        ]);

        $user->assignRole('seller');

        return $user;
    }

    /**
     * Test: Seller can create a product (full flow).
     */
    public function test_seller_can_create_product(): void
    {
        $user = $this->createSeller();

        $category = Category::firstOrCreate(
            ['name' => 'Eletrônicos'],
            ['slug' => 'eletronicos', 'is_active' => true]
        );

        $this->browse(function (Browser $browser) use ($user, $category) {
            $browser->loginAs($user)
                ->visit('/seller/products')
                ->assertSee('Meus Produtos')

                // Click "New Product" button
                ->clickLink('Adicionar Produto')
                ->pause(500)
                ->assertPathIs('/seller/products/create')

                // Fill Basic Information
                ->type('name', 'Smartphone XYZ Pro')
                ->type('short_description', 'Smartphone premium com câmera de 108MP')
                ->type('description', 'Smartphone top de linha com processador octa-core, 8GB RAM, 256GB armazenamento e câmera tripla de 108MP + 12MP ultra-wide + 5MP macro.')

                // Category
                ->select('category_id', $category->id)

                // SKU (optional)
                ->type('sku', 'PHONE-XYZ-001')

                // Pricing
                ->type('cost_price', '1500.00')
                ->type('original_price', '3000.00')
                ->type('sale_price', '2499.00')
                ->pause(500) // Wait for margin calculation

                // Assert: Margin calculated
                ->assertSeeIn('[x-text="formatPercent(margin)"]', '%')

                // Stock
                ->type('stock', '50')
                ->type('min_stock', '10')

                // Dimensions
                ->type('weight', '0.195')
                ->type('width', '15.8')
                ->type('height', '7.6')
                ->type('depth', '0.8')

                // Status
                ->select('status', 'draft')
                ->check('is_featured')

                // Submit
                ->scrollIntoView('button[type="submit"]')
                ->press('Criar Produto')
                ->pause(4000)
                ->screenshot('create-after-submit')

                // Assert: Redirected to products list or edit page
                ->assertPathBeginsWith('/seller/products');
        });

        // Assert: Product created in database with basic data
        $this->assertDatabaseHas('products', [
            'seller_id' => $user->seller->id,
            'category_id' => $category->id,
            'status' => 'draft',
        ]);
    }

    /**
     * Test: Seller can edit existing product.
     */
    public function test_seller_can_edit_product(): void
    {
        $user = User::factory()->create();
        $uniqueId = uniqid();
        $seller = $user->seller()->create([
            'store_name' => 'Tech Store',
            'slug' => 'tech-store-'.$uniqueId,
            'document_number' => '98765432100'.substr($uniqueId, -4),
            'person_type' => 'individual',
            'business_phone' => '31988887777',
            'business_email' => 'tech-'.$uniqueId.'@example.com',
            'status' => 'active',
            'approved_at' => now(),
        ]);

        $category = Category::factory()->create(['is_active' => true]);
        $product = $seller->products()->create([
            'category_id' => $category->id,
            'name' => 'Produto Original',
            'slug' => 'produto-original-'.uniqid(),
            'sku' => 'ORIG-'.strtoupper(uniqid()),
            'description' => 'Descrição original',
            'original_price' => 100.00,
            'sale_price' => 80.00,
            'stock' => 10,
            'status' => 'draft',
        ]);

        $user->assignRole('seller');

        $this->browse(function (Browser $browser) use ($user, $product) {
            $browser->loginAs($user)
                ->visit('/seller/products')
                ->assertSee('Meus Produtos')

                // Click edit button
                ->clickLink('Editar')
                ->pause(500)
                ->assertPathIs('/seller/products/'.$product->id.'/edit')

                // Change product name
                ->clear('name')
                ->type('name', 'Produto Atualizado')

                // Change price
                ->clear('sale_price')
                ->type('sale_price', '90.00')

                // Change stock
                ->clear('stock')
                ->type('stock', '25')

                // Submit (AJAX form with alert)
                ->press('Salvar Alterações')
                ->pause(3000)
                ->acceptDialog() // Handle success alert
                ->pause(1000)

                // Assert: Redirected to products list
                ->assertPathIs('/seller/products');
        });

        // Assert: Changes saved in database
        $product->refresh();
        $this->assertEquals('Produto Atualizado', $product->name);
        $this->assertEquals(90.00, (float) $product->sale_price);
        $this->assertEquals(25, $product->stock);
    }

    /**
     * Test: Seller can publish a product (with validation).
     */
    public function test_seller_cannot_publish_product_without_image(): void
    {
        $user = User::factory()->create();
        $uniqueId = uniqid();
        $seller = $user->seller()->create([
            'store_name' => 'My Store',
            'slug' => 'my-store-'.$uniqueId,
            'document_number' => '11122233344'.substr($uniqueId, -4),
            'person_type' => 'individual',
            'business_phone' => '31999998888',
            'business_email' => 'mystore-'.$uniqueId.'@example.com',
            'status' => 'active',
            'approved_at' => now(),
        ]);

        $category = Category::factory()->create(['is_active' => true]);
        $product = $seller->products()->create([
            'category_id' => $category->id,
            'name' => 'Produto Sem Imagem',
            'slug' => 'produto-sem-imagem-'.uniqid(),
            'sku' => 'SEMIMG-'.strtoupper(uniqid()),
            'description' => 'Produto para teste',
            'original_price' => 100.00,
            'sale_price' => 100.00,
            'stock' => 10,
            'status' => 'draft',
        ]);

        $user->assignRole('seller');

        $this->browse(function (Browser $browser) use ($user, $product) {
            $browser->loginAs($user)
                ->visit('/seller/products/'.$product->id.'/edit')

                // Try to publish without image
                ->press('Publicar')
                ->pause(1000)

                // Assert: Error message
                ->assertSee('Não é possível publicar produto sem pelo menos uma imagem');
        });

        // Assert: Product still draft
        $product->refresh();
        $this->assertEquals('draft', $product->status);
    }

    /**
     * Test: Seller can delete product.
     */
    public function test_seller_can_delete_product(): void
    {
        $user = User::factory()->create();
        $uniqueId = uniqid();
        $seller = $user->seller()->create([
            'store_name' => 'Test Store',
            'slug' => 'test-store-'.$uniqueId,
            'document_number' => '55566677788'.substr($uniqueId, -4),
            'person_type' => 'individual',
            'business_phone' => '31955556666',
            'business_email' => 'test-'.$uniqueId.'@example.com',
            'status' => 'active',
            'approved_at' => now(),
        ]);

        $category = Category::factory()->create(['is_active' => true]);
        $product = $seller->products()->create([
            'category_id' => $category->id,
            'name' => 'Produto Para Deletar',
            'slug' => 'produto-para-deletar-'.uniqid(),
            'sku' => 'DEL-'.strtoupper(uniqid()),
            'description' => 'Será deletado',
            'original_price' => 50.00,
            'sale_price' => 50.00,
            'stock' => 5,
            'status' => 'draft',
        ]);

        $user->assignRole('seller');

        $this->browse(function (Browser $browser) use ($user, $product) {
            $browser->loginAs($user)
                ->visit('/seller/products/'.$product->id.'/edit')

                // Click delete button and confirm
                ->press('Excluir')
                ->acceptDialog() // Handle confirmation alert
                ->pause(2000)

                // Assert: Redirected to products list
                ->assertPathIs('/seller/products');
        });

        // Assert: Product soft deleted
        $this->assertSoftDeleted('products', [
            'id' => $product->id,
        ]);
    }

    /**
     * Test: Products list has filters working.
     */
    public function test_seller_can_filter_products_list(): void
    {
        $user = User::factory()->create();
        $uniqueId = uniqid();
        $seller = $user->seller()->create([
            'store_name' => 'Filter Test Store',
            'slug' => 'filter-test-store-'.$uniqueId,
            'document_number' => '99988877766'.substr($uniqueId, -4),
            'person_type' => 'individual',
            'business_phone' => '31911112222',
            'business_email' => 'filter-'.$uniqueId.'@example.com',
            'status' => 'active',
            'approved_at' => now(),
        ]);

        $category1 = Category::factory()->create(['name' => 'Categoria A', 'is_active' => true]);
        $category2 = Category::factory()->create(['name' => 'Categoria B', 'is_active' => true]);

        // Create products with different statuses and categories
        $seller->products()->createMany([
            [
                'category_id' => $category1->id,
                'name' => 'Produto Publicado',
                'slug' => 'produto-publicado-'.uniqid(),
                'sku' => 'PUB-'.strtoupper(uniqid()),
                'description' => 'Desc 1',
                'original_price' => 100,
                'sale_price' => 100,
                'stock' => 10,
                'status' => 'published',
            ],
            [
                'category_id' => $category2->id,
                'name' => 'Produto Rascunho',
                'slug' => 'produto-rascunho-'.uniqid(),
                'sku' => 'DRAFT-'.strtoupper(uniqid()),
                'description' => 'Desc 2',
                'original_price' => 100,
                'sale_price' => 100,
                'stock' => 10,
                'status' => 'draft',
            ],
        ]);

        $user->assignRole('seller');

        $this->browse(function (Browser $browser) use ($user, $category1) {
            $browser->loginAs($user)
                ->visit('/seller/products')

                // Filter by status
                ->select('status', 'published')
                ->press('Filtrar')
                ->pause(1000)
                ->assertSee('Produto Publicado')
                ->assertDontSee('Produto Rascunho')

                // Clear filters
                ->clickLink('Limpar')
                ->pause(500)
                ->assertSee('Produto Publicado')
                ->assertSee('Produto Rascunho')

                // Filter by category
                ->select('category_id', $category1->id)
                ->press('Filtrar')
                ->pause(1000)
                ->assertSee('Produto Publicado')
                ->assertDontSee('Produto Rascunho');
        });
    }
}
