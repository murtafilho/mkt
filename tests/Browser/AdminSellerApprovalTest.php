<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AdminSellerApprovalTest extends DuskTestCase
{
    // ✅ NÃO usa DatabaseMigrations - usa banco separado mkt_dusk

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure roles exist
        if (\Spatie\Permission\Models\Role::count() === 0) {
            $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        }
    }

    /**
     * Test: Admin can view pending sellers list.
     */
    public function test_admin_can_view_pending_sellers(): void
    {
        // Create admin user
        $uniqueId = uniqid();
        $admin = User::factory()->create(['email' => 'admin-'.$uniqueId.'@mkt.test']);
        $admin->assignRole('admin');

        // Create pending seller
        $sellerUser = User::factory()->create(['name' => 'João Vendedor']);
        $uniqueId = uniqid();
        $seller = $sellerUser->seller()->create([
            'store_name' => 'Loja Pendente',
            'slug' => 'loja-pendente-'.$uniqueId,
            'document_number' => '12345678901'.substr($uniqueId, -4),
            'person_type' => 'individual',
            'business_phone' => '31987654321',
            'business_email' => 'loja-'.$uniqueId.'@example.com',
            'status' => 'pending',
        ]);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visit('/admin/sellers')
                ->pause(1000)
                ->assertSee('Gerenciar Vendedores')
                ->assertSee('Loja Pendente')
                ->assertSee('João Vendedor')
                ->assertSee('Pendente'); // Status badge
        });
    }

    /**
     * Test: Admin can approve seller.
     */
    public function test_admin_can_approve_seller(): void
    {
        $uniqueId = uniqid();
        $admin = User::factory()->create(['email' => 'admin-'.$uniqueId.'@test.com']);
        $admin->assignRole('admin');

        $sellerUser = User::factory()->create(['name' => 'Maria Vendedora']);
        $uniqueId = uniqid();
        $seller = $sellerUser->seller()->create([
            'store_name' => 'Loja da Maria',
            'slug' => 'loja-da-maria-'.$uniqueId,
            'document_number' => '98765432100'.substr($uniqueId, -4),
            'person_type' => 'individual',
            'business_phone' => '31999998888',
            'business_email' => 'maria-'.$uniqueId.'@example.com',
            'status' => 'pending',
        ]);

        // Create business address (required for approval)
        $seller->addresses()->create([
            'type' => 'business',
            'postal_code' => '30130-010',
            'street' => 'Rua Teste',
            'number' => '123',
            'neighborhood' => 'Centro',
            'city' => 'Belo Horizonte',
            'state' => 'MG',
            'is_default' => true,
        ]);

        $this->browse(function (Browser $browser) use ($admin, $seller) {
            $browser->loginAs($admin)
                ->visit('/admin/sellers/'.$seller->id)
                ->assertSee('Loja da Maria')
                ->assertSee('Maria Vendedora')
                ->assertSee('Pendente')

                // Click approve button
                ->press('Aprovar Vendedor')
                ->acceptDialog() // Handle confirmation alert
                ->pause(2000)

                // Assert: Status changed to Ativo (within the specific seller card/section)
                ->assertSee('Ativo')
                ->assertSee('Aprovado em:'); // Unique text that appears only when seller is approved
        });

        // Assert: Seller approved in database
        $seller->refresh();
        $this->assertEquals('active', $seller->status);
        $this->assertNotNull($seller->approved_at);
    }

    /**
     * Test: Admin can suspend active seller.
     */
    public function test_admin_can_suspend_seller(): void
    {
        $uniqueId = uniqid();
        $admin = User::factory()->create(['email' => 'admin-'.$uniqueId.'@test.com']);
        $admin->assignRole('admin');

        $sellerUser = User::factory()->create(['name' => 'Pedro Vendedor']);
        $uniqueId = uniqid();
        $seller = $sellerUser->seller()->create([
            'store_name' => 'Loja Problemática',
            'slug' => 'loja-problematica-'.$uniqueId,
            'document_number' => '11122233344'.substr($uniqueId, -4),
            'person_type' => 'individual',
            'business_phone' => '31977776666',
            'business_email' => 'pedro-'.$uniqueId.'@example.com',
            'status' => 'active',
            'approved_at' => now(),
        ]);

        $category = \App\Models\Category::factory()->create(['is_active' => true]);

        // Create published product
        $product = $seller->products()->create([
            'category_id' => $category->id,
            'name' => 'Produto Publicado',
            'slug' => 'produto-publicado-'.uniqid(),
            'sku' => 'PUB-'.strtoupper(uniqid()),
            'description' => 'Descrição',
            'original_price' => 100.00,
            'sale_price' => 100.00,
            'stock' => 10,
            'status' => 'published',
        ]);

        $this->browse(function (Browser $browser) use ($admin, $seller) {
            $browser->loginAs($admin)
                ->visit('/admin/sellers/'.$seller->id)
                ->assertSee('Loja Problemática')
                ->assertSee('Ativo')

                // Click suspend button
                ->press('Suspender Vendedor')
                ->acceptDialog() // Handle confirmation alert
                ->pause(2000)

                // Assert: Status changed to Suspenso
                ->assertSee('Suspenso')
                ->assertDontSee('Ativo');
        });

        // Assert: Seller suspended
        $seller->refresh();
        $this->assertEquals('suspended', $seller->status);
        $this->assertNull($seller->approved_at);

        // Assert: Product unpublished
        $product->refresh();
        $this->assertEquals('draft', $product->status);
    }

    /**
     * Test: Admin can reactivate suspended seller.
     */
    public function test_admin_can_reactivate_suspended_seller(): void
    {
        $uniqueId = uniqid();
        $admin = User::factory()->create(['email' => 'admin-'.$uniqueId.'@test.com']);
        $admin->assignRole('admin');

        $sellerUser = User::factory()->create();
        $seller = $sellerUser->seller()->create([
            'store_name' => 'Loja Suspensa',
            'slug' => 'loja-suspensa-'.$uniqueId,
            'document_number' => '44455566677'.substr($uniqueId, -4),
            'person_type' => 'individual',
            'business_phone' => '31966665555',
            'business_email' => 'suspensa-'.$uniqueId.'@example.com',
            'status' => 'suspended',
            'approved_at' => null,
        ]);

        $this->browse(function (Browser $browser) use ($admin, $seller) {
            $browser->loginAs($admin)
                ->visit('/admin/sellers/'.$seller->id)
                ->assertSee('Loja Suspensa')
                ->assertSee('Suspenso')

                // Click reactivate button
                ->press('Reativar Vendedor')
                ->acceptDialog() // Handle confirmation alert
                ->pause(2000)

                // Assert: Status changed to Ativo
                ->assertSee('Ativo')
                ->assertDontSee('Suspenso');
        });

        // Assert: Seller reactivated
        $seller->refresh();
        $this->assertEquals('active', $seller->status);
        $this->assertNotNull($seller->approved_at);
    }

    /**
     * Test: Admin can search sellers.
     */
    public function test_admin_can_search_sellers(): void
    {
        $uniqueId = uniqid();
        $admin = User::factory()->create(['email' => 'admin-'.$uniqueId.'@test.com']);
        $admin->assignRole('admin');

        $seller1User = User::factory()->create(['name' => 'João Silva']);
        $seller1 = $seller1User->seller()->create([
            'store_name' => 'Loja do João',
            'slug' => 'loja-do-joao-'.$uniqueId,
            'document_number' => '11111111111'.substr($uniqueId, -4),
            'person_type' => 'individual',
            'business_phone' => '31911111111',
            'business_email' => 'joao-'.$uniqueId.'@example.com',
            'status' => 'active',
            'approved_at' => now(),
        ]);

        $seller2User = User::factory()->create(['name' => 'Maria Santos']);
        $seller2 = $seller2User->seller()->create([
            'store_name' => 'Loja da Maria',
            'slug' => 'loja-da-maria-'.$uniqueId,
            'document_number' => '22222222222'.substr($uniqueId, -4),
            'person_type' => 'individual',
            'business_phone' => '31922222222',
            'business_email' => 'maria-'.$uniqueId.'@example.com',
            'status' => 'pending',
        ]);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visit('/admin/sellers')

                // Search by store name
                ->type('search', 'João')
                ->press('Filtrar')
                ->pause(1000)
                ->assertSee('Loja do João')
                ->assertDontSee('Loja da Maria')

                // Clear and search by user name
                ->clickLink('Limpar')
                ->pause(500)
                ->type('search', 'Maria')
                ->press('Filtrar')
                ->pause(1000)
                ->assertSee('Loja da Maria')
                ->assertDontSee('Loja do João');
        });
    }
}
