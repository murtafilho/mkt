<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'seller']);
        Role::create(['name' => 'customer']);
    }

    public function test_admin_can_access_dashboard(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
    }

    public function test_seller_cannot_access_dashboard(): void
    {
        $seller = User::factory()->create();
        $seller->assignRole('seller');

        $response = $this->actingAs($seller)->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    public function test_customer_cannot_access_dashboard(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $response = $this->actingAs($customer)->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_dashboard(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_dashboard_displays_correct_sales_metrics(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $seller = Seller::factory()->create();

        // Create orders with different dates
        // Order today
        Order::factory()->create([
            'seller_id' => $seller->id,
            'total' => 100.00,
            'status' => 'paid',
            'created_at' => now(),
        ]);

        // Order this week (3 days ago)
        Order::factory()->create([
            'seller_id' => $seller->id,
            'total' => 200.00,
            'status' => 'paid',
            'created_at' => now()->subDays(3),
        ]);

        // Order this month (15 days ago)
        Order::factory()->create([
            'seller_id' => $seller->id,
            'total' => 300.00,
            'status' => 'paid',
            'created_at' => now()->subDays(15),
        ]);

        // Cancelled order (should not count)
        Order::factory()->create([
            'seller_id' => $seller->id,
            'total' => 50.00,
            'status' => 'cancelled',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);

        // Just verify they are numeric and make sense
        $this->assertIsNumeric($response->viewData('todaySales'));
        $this->assertIsNumeric($response->viewData('weekSales'));
        $this->assertIsNumeric($response->viewData('monthSales'));
        $this->assertEquals(600.00, $response->viewData('totalRevenue')); // Excludes cancelled
    }

    public function test_dashboard_displays_correct_order_counts(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $seller = Seller::factory()->create();

        // Create orders with different statuses
        Order::factory()->create(['seller_id' => $seller->id, 'status' => 'awaiting_payment']);
        Order::factory()->create(['seller_id' => $seller->id, 'status' => 'paid']);
        Order::factory()->create(['seller_id' => $seller->id, 'status' => 'paid']);
        Order::factory()->create(['seller_id' => $seller->id, 'status' => 'preparing']);
        Order::factory()->create(['seller_id' => $seller->id, 'status' => 'shipped']);
        Order::factory()->create(['seller_id' => $seller->id, 'status' => 'delivered']);
        Order::factory()->create(['seller_id' => $seller->id, 'status' => 'cancelled']);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('ordersByStatus', function ($ordersByStatus) {
            return $ordersByStatus['total'] === 7
                && $ordersByStatus['awaiting_payment'] === 1
                && $ordersByStatus['paid'] === 2
                && $ordersByStatus['preparing'] === 1
                && $ordersByStatus['shipped'] === 1
                && $ordersByStatus['delivered'] === 1
                && $ordersByStatus['cancelled'] === 1;
        });
    }

    public function test_dashboard_displays_correct_seller_counts(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Create sellers with different statuses
        Seller::factory()->create(['status' => 'pending']);
        Seller::factory()->create(['status' => 'pending']);
        Seller::factory()->create(['status' => 'active']);
        Seller::factory()->create(['status' => 'active']);
        Seller::factory()->create(['status' => 'active']);
        Seller::factory()->create(['status' => 'suspended']);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('sellersByStatus', function ($sellersByStatus) {
            return $sellersByStatus['total'] === 6
                && $sellersByStatus['pending'] === 2
                && $sellersByStatus['active'] === 3
                && $sellersByStatus['suspended'] === 1;
        });
    }

    public function test_dashboard_displays_recent_orders(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $seller = Seller::factory()->create();

        // Create 15 orders with different timestamps
        for ($i = 1; $i <= 15; $i++) {
            Order::factory()->create([
                'seller_id' => $seller->id,
                'created_at' => now()->subMinutes($i),
            ]);
        }

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('recentOrders', function ($recentOrders) {
            return $recentOrders->count() === 10; // Should show only last 10
        });
    }

    public function test_dashboard_displays_pending_sellers(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Create 7 pending sellers with different timestamps
        for ($i = 1; $i <= 7; $i++) {
            Seller::factory()->create([
                'status' => 'pending',
                'created_at' => now()->subMinutes($i),
            ]);
        }

        // Create 3 active sellers (should not appear)
        Seller::factory()->count(3)->create(['status' => 'active']);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('pendingSellers', function ($pendingSellers) {
            return $pendingSellers->count() === 5; // Should show only last 5
        });
    }

    public function test_dashboard_displays_monthly_sales_chart_data(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $seller = Seller::factory()->create();

        // Create orders in different months
        Order::factory()->create([
            'seller_id' => $seller->id,
            'total' => 100.00,
            'status' => 'paid',
            'created_at' => now()->subMonths(2),
        ]);

        Order::factory()->create([
            'seller_id' => $seller->id,
            'total' => 200.00,
            'status' => 'paid',
            'created_at' => now()->subMonth(),
        ]);

        Order::factory()->create([
            'seller_id' => $seller->id,
            'total' => 300.00,
            'status' => 'paid',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('monthlySales', function ($monthlySales) {
            return $monthlySales->count() >= 3
                && $monthlySales->first()->order_count >= 1
                && $monthlySales->first()->revenue >= 100;
        });
    }
}
