<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportControllerTest extends TestCase
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

    // === Reports Dashboard Tests ===

    public function test_admin_can_access_reports_dashboard(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get(route('admin.reports.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.reports.index');
    }

    public function test_seller_cannot_access_reports_dashboard(): void
    {
        $seller = User::factory()->create();
        $seller->assignRole('seller');

        $response = $this->actingAs($seller)->get(route('admin.reports.index'));

        $response->assertStatus(403);
    }

    public function test_customer_cannot_access_reports_dashboard(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $response = $this->actingAs($customer)->get(route('admin.reports.index'));

        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_reports_dashboard(): void
    {
        $response = $this->get(route('admin.reports.index'));

        $response->assertRedirect(route('login'));
    }

    // === Sales Report Tests ===

    public function test_admin_can_access_sales_report(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get(route('admin.reports.sales'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.reports.sales');
    }

    public function test_sales_report_displays_correct_metrics(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $seller = Seller::factory()->create();

        // Create orders with different statuses
        Order::factory()->create([
            'seller_id' => $seller->id,
            'total' => 100.00,
            'status' => 'paid',
        ]);

        Order::factory()->create([
            'seller_id' => $seller->id,
            'total' => 200.00,
            'status' => 'delivered',
        ]);

        Order::factory()->create([
            'seller_id' => $seller->id,
            'total' => 50.00,
            'status' => 'cancelled', // Should not count
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.sales'));

        $response->assertStatus(200);
        $response->assertViewHas('totalRevenue', 300.00);
        $response->assertViewHas('totalOrders', 2); // Excluding cancelled
        $this->assertIsNumeric($response->viewData('averageOrderValue'));
    }

    public function test_sales_report_can_filter_by_date_range(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $seller = Seller::factory()->create();

        // Order within range
        Order::factory()->create([
            'seller_id' => $seller->id,
            'total' => 100.00,
            'status' => 'paid',
            'created_at' => '2025-10-05',
        ]);

        // Order outside range
        Order::factory()->create([
            'seller_id' => $seller->id,
            'total' => 200.00,
            'status' => 'paid',
            'created_at' => '2025-10-15',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.sales', [
            'date_from' => '2025-10-01',
            'date_to' => '2025-10-10',
        ]));

        $response->assertStatus(200);
        // Check that metrics reflect only orders in range
        $response->assertViewHas('totalOrders', 1);
        $response->assertViewHas('totalRevenue', 100.00);
    }

    public function test_sales_report_displays_orders_by_status(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $seller = Seller::factory()->create();

        Order::factory()->create(['seller_id' => $seller->id, 'status' => 'paid', 'total' => 100]);
        Order::factory()->create(['seller_id' => $seller->id, 'status' => 'paid', 'total' => 200]);
        Order::factory()->create(['seller_id' => $seller->id, 'status' => 'delivered', 'total' => 300]);

        $response = $this->actingAs($admin)->get(route('admin.reports.sales'));

        $response->assertStatus(200);
        $response->assertViewHas('ordersByStatus', function ($ordersByStatus) {
            return $ordersByStatus->count() === 2; // paid and delivered groups
        });
    }

    public function test_sales_report_can_filter_by_seller(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $seller1 = Seller::factory()->create();
        $seller2 = Seller::factory()->create();

        Order::factory()->count(2)->create(['seller_id' => $seller1->id, 'total' => 100]);
        Order::factory()->create(['seller_id' => $seller2->id, 'total' => 300]);

        $response = $this->actingAs($admin)->get(route('admin.reports.sales', [
            'seller_id' => $seller1->id,
        ]));

        $response->assertStatus(200);
        // Check metrics reflect only seller1's orders
        $response->assertViewHas('totalOrders', 2);
        $response->assertViewHas('totalRevenue', 200.00);
    }

    public function test_admin_can_export_sales_report_to_csv(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $seller = Seller::factory()->create();
        Order::factory()
            ->hasAddress()
            ->hasItems(1)
            ->create(['seller_id' => $seller->id]);

        $response = $this->actingAs($admin)->get(route('admin.reports.sales.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
    }

    public function test_csv_export_contains_correct_headers(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get(route('admin.reports.sales.export'));

        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('Pedido', $content);
        $this->assertStringContainsString('Data', $content);
        $this->assertStringContainsString('Cliente', $content);
        $this->assertStringContainsString('Vendedor', $content);
    }

    public function test_csv_export_respects_date_filters(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $seller = Seller::factory()->create();

        Order::factory()
            ->hasAddress()
            ->hasItems(1)
            ->create([
                'seller_id' => $seller->id,
                'status' => 'paid',
                'created_at' => '2025-10-05',
            ]);

        Order::factory()
            ->hasAddress()
            ->hasItems(1)
            ->create([
                'seller_id' => $seller->id,
                'status' => 'delivered',
                'created_at' => '2025-10-15',
            ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.sales.export', [
            'date_from' => '2025-10-01',
            'date_to' => '2025-10-10',
        ]));

        $response->assertStatus(200);
        $content = $response->getContent();
        $lines = explode("\n", trim($content));
        $this->assertCount(2, $lines); // Header + 1 order
    }

    // === Products Report Tests ===

    public function test_admin_can_access_products_report(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get(route('admin.reports.products'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.reports.products');
    }

    public function test_products_report_displays_correct_metrics(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $seller = Seller::factory()->create();

        Product::factory()->create([
            'seller_id' => $seller->id,
            'status' => 'published',
            'stock' => 10,
        ]);

        Product::factory()->create([
            'seller_id' => $seller->id,
            'status' => 'published',
            'stock' => 0, // Out of stock
        ]);

        Product::factory()->create([
            'seller_id' => $seller->id,
            'status' => 'draft', // Draft
            'stock' => 5,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.products'));

        $response->assertStatus(200);
        $response->assertViewHas('totalProducts', 3);
        $response->assertViewHas('publishedProducts', 2);
        $response->assertViewHas('draftProducts', 1);
        $response->assertViewHas('outOfStockProducts', 1);
    }

    public function test_products_report_can_filter_by_seller(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $seller1 = Seller::factory()->create();
        $seller2 = Seller::factory()->create();

        Product::factory()->count(2)->create(['seller_id' => $seller1->id]);
        Product::factory()->create(['seller_id' => $seller2->id]);

        $response = $this->actingAs($admin)->get(route('admin.reports.products', [
            'seller_id' => $seller1->id,
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('products', function ($products) use ($seller1) {
            return $products->count() === 2 &&
                   $products->every(fn ($product) => $product->seller_id === $seller1->id);
        });
    }

    public function test_products_report_can_filter_by_status(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $seller = Seller::factory()->create();

        Product::factory()->create(['seller_id' => $seller->id, 'status' => 'published']);
        Product::factory()->create(['seller_id' => $seller->id, 'status' => 'published']);
        Product::factory()->create(['seller_id' => $seller->id, 'status' => 'draft']);

        $response = $this->actingAs($admin)->get(route('admin.reports.products', [
            'status' => 'published',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('products', function ($products) {
            return $products->count() === 2 &&
                   $products->every(fn ($product) => $product->status === 'published');
        });
    }

    // === Sellers Report Tests ===

    public function test_admin_can_access_sellers_report(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get(route('admin.reports.sellers'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.reports.sellers');
    }

    public function test_sellers_report_displays_correct_metrics(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Seller::factory()->create(['status' => 'active']);
        Seller::factory()->create(['status' => 'active']);
        Seller::factory()->create(['status' => 'pending']);
        Seller::factory()->create(['status' => 'suspended']);

        $response = $this->actingAs($admin)->get(route('admin.reports.sellers'));

        $response->assertStatus(200);
        $response->assertViewHas('totalSellers', 4);
        $response->assertViewHas('activeSellers', 2);
        $response->assertViewHas('pendingSellers', 1);
        $response->assertViewHas('suspendedSellers', 1);
    }

    public function test_sellers_report_can_filter_by_status(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Seller::factory()->create(['status' => 'active']);
        Seller::factory()->create(['status' => 'active']);
        Seller::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($admin)->get(route('admin.reports.sellers', [
            'status' => 'active',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('sellers', function ($sellers) {
            return $sellers->count() === 2 &&
                   $sellers->every(fn ($seller) => $seller->status === 'active');
        });
    }

    public function test_sellers_report_shows_sales_performance(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $seller = Seller::factory()->create();

        Order::factory()->create([
            'seller_id' => $seller->id,
            'total' => 100.00,
            'status' => 'delivered',
        ]);

        Order::factory()->create([
            'seller_id' => $seller->id,
            'total' => 200.00,
            'status' => 'paid',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.sellers'));

        $response->assertStatus(200);
        $response->assertViewHas('sellers', function ($sellers) use ($seller) {
            $sellerData = $sellers->firstWhere('id', $seller->id);

            return $sellerData &&
                   $sellerData->getAttribute('total_revenue') == 300.00 &&
                   $sellerData->getAttribute('total_orders') == 2;
        });
    }

    // === Authorization Tests ===

    public function test_seller_cannot_access_sales_report(): void
    {
        $seller = User::factory()->create();
        $seller->assignRole('seller');

        $response = $this->actingAs($seller)->get(route('admin.reports.sales'));

        $response->assertStatus(403);
    }

    public function test_seller_cannot_access_products_report(): void
    {
        $seller = User::factory()->create();
        $seller->assignRole('seller');

        $response = $this->actingAs($seller)->get(route('admin.reports.products'));

        $response->assertStatus(403);
    }

    public function test_seller_cannot_access_sellers_report(): void
    {
        $seller = User::factory()->create();
        $seller->assignRole('seller');

        $response = $this->actingAs($seller)->get(route('admin.reports.sellers'));

        $response->assertStatus(403);
    }

    public function test_seller_cannot_export_sales_report(): void
    {
        $seller = User::factory()->create();
        $seller->assignRole('seller');

        $response = $this->actingAs($seller)->get(route('admin.reports.sales.export'));

        $response->assertStatus(403);
    }
}
