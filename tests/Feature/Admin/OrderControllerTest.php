<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'seller']);
        Role::create(['name' => 'customer']);

        // Create permissions
        Permission::create(['name' => 'orders.view-all']);
    }

    public function test_admin_can_view_all_orders(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $seller1 = Seller::factory()->create();
        $seller2 = Seller::factory()->create();

        Order::factory()->create(['seller_id' => $seller1->id]);
        Order::factory()->create(['seller_id' => $seller2->id]);

        $response = $this->actingAs($admin)->get(route('admin.orders.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.orders.index');
        $response->assertViewHas('orders', function ($orders) {
            return $orders->count() === 2;
        });
    }

    public function test_admin_can_filter_orders_by_status(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $seller = Seller::factory()->create();

        Order::factory()->create(['seller_id' => $seller->id, 'status' => 'paid']);
        Order::factory()->create(['seller_id' => $seller->id, 'status' => 'paid']);
        Order::factory()->create(['seller_id' => $seller->id, 'status' => 'preparing']);

        $response = $this->actingAs($admin)->get(route('admin.orders.index', ['status' => 'paid']));

        $response->assertStatus(200);
        $response->assertViewHas('orders', function ($orders) {
            return $orders->count() === 2 && $orders->every(fn ($order) => $order->status === 'paid');
        });
    }

    public function test_admin_can_filter_orders_by_seller(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $seller1 = Seller::factory()->create();
        $seller2 = Seller::factory()->create();

        Order::factory()->count(2)->create(['seller_id' => $seller1->id]);
        Order::factory()->create(['seller_id' => $seller2->id]);

        $response = $this->actingAs($admin)->get(route('admin.orders.index', ['seller_id' => $seller1->id]));

        $response->assertStatus(200);
        $response->assertViewHas('orders', function ($orders) use ($seller1) {
            return $orders->count() === 2 && $orders->every(fn ($order) => $order->seller_id === $seller1->id);
        });
    }

    public function test_admin_can_filter_orders_by_date_range(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $seller = Seller::factory()->create();

        Order::factory()->create(['seller_id' => $seller->id, 'created_at' => '2025-10-01']);
        Order::factory()->create(['seller_id' => $seller->id, 'created_at' => '2025-10-05']);
        Order::factory()->create(['seller_id' => $seller->id, 'created_at' => '2025-10-10']);

        $response = $this->actingAs($admin)->get(route('admin.orders.index', [
            'date_from' => '2025-10-03',
            'date_to' => '2025-10-07',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('orders', function ($orders) {
            return $orders->count() === 1; // Only the one on 10-05
        });
    }

    public function test_admin_can_search_orders_by_order_number(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $seller = Seller::factory()->create();

        $order = Order::factory()->create([
            'seller_id' => $seller->id,
            'order_number' => 'ORD-12345',
        ]);

        Order::factory()->create(['seller_id' => $seller->id, 'order_number' => 'ORD-99999']);

        $response = $this->actingAs($admin)->get(route('admin.orders.index', ['search' => 'ORD-12345']));

        $response->assertStatus(200);
        $response->assertViewHas('orders', function ($orders) use ($order) {
            return $orders->count() === 1 && $orders->first()->id === $order->id;
        });
    }

    public function test_admin_can_search_orders_by_customer_name(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $seller = Seller::factory()->create();

        $customer = User::factory()->create(['name' => 'João Silva']);
        $order = Order::factory()->create([
            'seller_id' => $seller->id,
            'user_id' => $customer->id,
        ]);

        $otherCustomer = User::factory()->create(['name' => 'Maria Santos']);
        Order::factory()->create(['seller_id' => $seller->id, 'user_id' => $otherCustomer->id]);

        $response = $this->actingAs($admin)->get(route('admin.orders.index', ['search' => 'João']));

        $response->assertStatus(200);
        $response->assertViewHas('orders', function ($orders) use ($order) {
            return $orders->count() === 1 && $orders->first()->id === $order->id;
        });
    }

    public function test_admin_can_view_order_details(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $admin->givePermissionTo('orders.view-all');

        $seller = Seller::factory()->create();
        $order = Order::factory()
            ->hasAddress()
            ->hasItems(1)
            ->create(['seller_id' => $seller->id]);

        $response = $this->actingAs($admin)->get(route('admin.orders.show', $order));

        $response->assertStatus(200);
        $response->assertViewIs('admin.orders.show');
        $response->assertViewHas('order', function ($viewOrder) use ($order) {
            return $viewOrder->id === $order->id;
        });
    }

    public function test_admin_can_update_order_status(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $seller = Seller::factory()->create();
        $order = Order::factory()->create([
            'seller_id' => $seller->id,
            'status' => 'paid',
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.orders.updateStatus', $order), [
            'status' => 'preparing',
            'note' => 'Pedido em preparação',
        ]);

        $response->assertRedirect(route('admin.orders.show', $order));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'preparing',
        ]);

        $this->assertDatabaseHas('order_history', [
            'order_id' => $order->id,
            'previous_status' => 'paid',
            'new_status' => 'preparing',
        ]);
    }

    public function test_admin_can_update_order_status_to_shipped_with_tracking_code(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $seller = Seller::factory()->create();
        $order = Order::factory()->create([
            'seller_id' => $seller->id,
            'status' => 'preparing',
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.orders.updateStatus', $order), [
            'status' => 'shipped',
            'tracking_code' => 'BR123456789BR',
            'note' => 'Pedido enviado',
        ]);

        $response->assertRedirect(route('admin.orders.show', $order));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'shipped',
            'tracking_code' => 'BR123456789BR',
        ]);

        $order->refresh();
        $this->assertNotNull($order->shipped_at);
    }

    public function test_admin_cannot_update_order_status_to_shipped_without_tracking_code(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $seller = Seller::factory()->create();
        $order = Order::factory()->create([
            'seller_id' => $seller->id,
            'status' => 'preparing',
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.orders.updateStatus', $order), [
            'status' => 'shipped',
            // tracking_code omitted
        ]);

        $response->assertSessionHasErrors('tracking_code');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'preparing', // Status should not change
        ]);
    }

    public function test_admin_can_cancel_order(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $seller = Seller::factory()->create();
        $product = \App\Models\Product::factory()->create([
            'seller_id' => $seller->id,
            'stock' => 10,
        ]);

        $order = Order::factory()->create([
            'seller_id' => $seller->id,
            'status' => 'paid',
        ]);

        $orderItem = \App\Models\OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.orders.cancel', $order));

        $response->assertRedirect(route('admin.orders.show', $order));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
        ]);

        // Stock should be restored
        $product->refresh();
        $this->assertEquals(12, $product->stock); // 10 + 2
    }

    public function test_seller_cannot_access_admin_orders(): void
    {
        $seller = User::factory()->create();
        $seller->assignRole('seller');

        $response = $this->actingAs($seller)->get(route('admin.orders.index'));

        $response->assertStatus(403);
    }

    public function test_customer_cannot_access_admin_orders(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $response = $this->actingAs($customer)->get(route('admin.orders.index'));

        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_admin_orders(): void
    {
        $response = $this->get(route('admin.orders.index'));

        $response->assertRedirect(route('login'));
    }
}
