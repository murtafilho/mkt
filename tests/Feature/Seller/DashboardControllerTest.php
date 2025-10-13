<?php

namespace Tests\Feature\Seller;

use App\Models\Order;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $sellerUser;

    protected Seller $seller;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        // Create seller user with seller role
        $this->sellerUser = User::factory()->create();
        $this->sellerUser->assignRole('seller');

        $this->seller = Seller::factory()->create([
            'user_id' => $this->sellerUser->id,
            'status' => 'active',
        ]);
    }

    public function test_seller_can_access_dashboard(): void
    {
        $response = $this->withoutExceptionHandling()
            ->actingAs($this->sellerUser)
            ->get(route('seller.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('seller.dashboard');
        $response->assertViewHas('stats');
    }

    public function test_customer_cannot_access_seller_dashboard(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $response = $this->actingAs($customer)->get(route('seller.dashboard'));

        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_seller_dashboard(): void
    {
        $response = $this->get(route('seller.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_without_seller_profile_redirects_to_registration(): void
    {
        $user = User::factory()->create();
        $user->assignRole('seller');

        $response = $this->actingAs($user)->get(route('seller.dashboard'));

        $response->assertRedirect(route('seller.register'));
        $response->assertSessionHas('info', 'Você precisa criar um perfil de vendedor primeiro.');
    }

    public function test_dashboard_shows_correct_total_products(): void
    {
        Product::factory()->count(5)->create(['seller_id' => $this->seller->id]);

        $response = $this->actingAs($this->sellerUser)->get(route('seller.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('stats', function ($stats) {
            return $stats['total_products'] === 5;
        });
    }

    public function test_dashboard_shows_correct_published_products(): void
    {
        Product::factory()->count(3)->create([
            'seller_id' => $this->seller->id,
            'status' => 'published',
        ]);

        Product::factory()->count(2)->create([
            'seller_id' => $this->seller->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->sellerUser)->get(route('seller.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('stats', function ($stats) {
            return $stats['published_products'] === 3
                && $stats['total_products'] === 5;
        });
    }

    public function test_dashboard_shows_correct_total_orders(): void
    {
        $customer = User::factory()->create();

        Order::factory()->count(7)->create([
            'seller_id' => $this->seller->id,
            'user_id' => $customer->id,
        ]);

        $response = $this->actingAs($this->sellerUser)->get(route('seller.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('stats', function ($stats) {
            return $stats['total_orders'] === 7;
        });
    }

    public function test_dashboard_shows_correct_pending_orders(): void
    {
        $customer = User::factory()->create();

        Order::factory()->count(2)->create([
            'seller_id' => $this->seller->id,
            'user_id' => $customer->id,
            'status' => 'awaiting_payment',
        ]);

        Order::factory()->count(3)->create([
            'seller_id' => $this->seller->id,
            'user_id' => $customer->id,
            'status' => 'preparing',
        ]);

        Order::factory()->create([
            'seller_id' => $this->seller->id,
            'user_id' => $customer->id,
            'status' => 'delivered',
        ]);

        $response = $this->actingAs($this->sellerUser)->get(route('seller.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('stats', function ($stats) {
            return $stats['pending_orders'] === 5
                && $stats['total_orders'] === 6;
        });
    }

    public function test_dashboard_shows_correct_completed_orders(): void
    {
        $customer = User::factory()->create();

        Order::factory()->count(4)->create([
            'seller_id' => $this->seller->id,
            'user_id' => $customer->id,
            'status' => 'delivered',
        ]);

        Order::factory()->count(2)->create([
            'seller_id' => $this->seller->id,
            'user_id' => $customer->id,
            'status' => 'shipped',
        ]);

        $response = $this->actingAs($this->sellerUser)->get(route('seller.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('stats', function ($stats) {
            return $stats['completed_orders'] === 4
                && $stats['total_orders'] === 6;
        });
    }

    public function test_dashboard_calculates_total_revenue_correctly(): void
    {
        $customer = User::factory()->create();

        Order::factory()->create([
            'seller_id' => $this->seller->id,
            'user_id' => $customer->id,
            'status' => 'preparing',
            'total' => 150.00,
        ]);

        Order::factory()->create([
            'seller_id' => $this->seller->id,
            'user_id' => $customer->id,
            'status' => 'shipped',
            'total' => 250.00,
        ]);

        Order::factory()->create([
            'seller_id' => $this->seller->id,
            'user_id' => $customer->id,
            'status' => 'delivered',
            'total' => 100.00,
        ]);

        // Should not be counted (awaiting payment)
        Order::factory()->create([
            'seller_id' => $this->seller->id,
            'user_id' => $customer->id,
            'status' => 'awaiting_payment',
            'total' => 300.00,
        ]);

        // Should not be counted (cancelled)
        Order::factory()->create([
            'seller_id' => $this->seller->id,
            'user_id' => $customer->id,
            'status' => 'cancelled',
            'total' => 200.00,
        ]);

        $response = $this->actingAs($this->sellerUser)->get(route('seller.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('stats', function ($stats) {
            return $stats['total_revenue'] == 500.00; // 150 + 250 + 100
        });
    }

    public function test_dashboard_calculates_monthly_revenue_correctly(): void
    {
        $customer = User::factory()->create();

        // Current month orders
        Order::factory()->create([
            'seller_id' => $this->seller->id,
            'user_id' => $customer->id,
            'status' => 'preparing',
            'total' => 100.00,
            'created_at' => now(),
        ]);

        Order::factory()->create([
            'seller_id' => $this->seller->id,
            'user_id' => $customer->id,
            'status' => 'delivered',
            'total' => 150.00,
            'created_at' => now(),
        ]);

        // Previous month orders (should not be counted)
        Order::factory()->create([
            'seller_id' => $this->seller->id,
            'user_id' => $customer->id,
            'status' => 'delivered',
            'total' => 300.00,
            'created_at' => now()->subMonth(),
        ]);

        $response = $this->actingAs($this->sellerUser)->get(route('seller.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('stats', function ($stats) {
            return $stats['monthly_revenue'] == 250.00; // 100 + 150
        });
    }

    public function test_dashboard_shows_zero_stats_for_new_seller(): void
    {
        $response = $this->actingAs($this->sellerUser)->get(route('seller.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('stats', function ($stats) {
            return $stats['total_products'] === 0
                && $stats['published_products'] === 0
                && $stats['total_orders'] === 0
                && $stats['pending_orders'] === 0
                && $stats['completed_orders'] === 0
                && $stats['total_revenue'] == 0
                && $stats['monthly_revenue'] == 0;
        });
    }

    public function test_dashboard_only_shows_seller_own_data(): void
    {
        $anotherSeller = Seller::factory()->create(['status' => 'active']);
        $customer = User::factory()->create();

        // Create data for another seller
        Product::factory()->count(10)->create(['seller_id' => $anotherSeller->id]);
        Order::factory()->count(5)->create([
            'seller_id' => $anotherSeller->id,
            'user_id' => $customer->id,
            'total' => 100.00,
        ]);

        // Create data for current seller
        Product::factory()->count(2)->create(['seller_id' => $this->seller->id]);
        Order::factory()->create([
            'seller_id' => $this->seller->id,
            'user_id' => $customer->id,
            'status' => 'preparing',
            'total' => 50.00,
        ]);

        $response = $this->actingAs($this->sellerUser)->get(route('seller.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('stats', function ($stats) {
            return $stats['total_products'] === 2
                && $stats['total_orders'] === 1
                && $stats['total_revenue'] == 50.00;
        });
    }

    public function test_dashboard_shows_seller_status_warning_when_not_active(): void
    {
        // Update existing seller to pending status
        $this->seller->update(['status' => 'pending']);

        $response = $this->withoutExceptionHandling()
            ->actingAs($this->sellerUser)
            ->get(route('seller.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Seu cadastro está em análise');
        $response->assertSee('aguardando aprovação do administrador');
    }
}
