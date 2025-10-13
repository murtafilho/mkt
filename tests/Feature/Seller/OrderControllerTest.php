<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create roles and permissions
    Role::firstOrCreate(['name' => 'admin']);
    $sellerRole = Role::firstOrCreate(['name' => 'seller']);
    Role::firstOrCreate(['name' => 'customer']);

    // Create seller permissions
    Permission::firstOrCreate(['name' => 'orders.view']);
    Permission::firstOrCreate(['name' => 'orders.update-status']);
    Permission::firstOrCreate(['name' => 'orders.cancel']);

    $sellerRole->givePermissionTo(['orders.view', 'orders.update-status', 'orders.cancel']);
});

test('seller can view their orders list', function () {
    $seller = Seller::factory()->create(['status' => 'active', 'approved_at' => now()]);
    $sellerUser = $seller->user;
    $sellerUser->assignRole('seller');

    $customer = User::factory()->create();
    $product = Product::factory()->create(['seller_id' => $seller->id, 'stock' => 10]);

    // Create 3 orders for this seller
    $orders = Order::factory()
        ->count(3)
        ->for($customer, 'user')
        ->for($seller)
        ->create();

    foreach ($orders as $order) {
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
        ]);
    }

    // Create 2 orders for another seller (should not appear)
    $otherSeller = Seller::factory()->create(['status' => 'active', 'approved_at' => now()]);
    Order::factory()
        ->count(2)
        ->for($customer, 'user')
        ->for($otherSeller)
        ->create();

    $response = $this->actingAs($sellerUser)->get(route('seller.orders.index'));

    $response->assertOk();
    $response->assertViewHas('orders');
    $response->assertSee($orders[0]->order_number);
    $response->assertSee($orders[1]->order_number);
    $response->assertSee($orders[2]->order_number);
});

test('seller can filter orders by status', function () {
    $seller = Seller::factory()->create(['status' => 'active', 'approved_at' => now()]);
    $sellerUser = $seller->user;
    $sellerUser->assignRole('seller');

    $customer = User::factory()->create();

    $paidOrder = Order::factory()
        ->for($customer, 'user')
        ->for($seller)
        ->create(['status' => 'paid']);

    $shippedOrder = Order::factory()
        ->for($customer, 'user')
        ->for($seller)
        ->create(['status' => 'shipped']);

    $response = $this->actingAs($sellerUser)
        ->get(route('seller.orders.index', ['status' => 'paid']));

    $response->assertOk();
    $response->assertSee($paidOrder->order_number);
    $response->assertDontSee($shippedOrder->order_number);
});

test('seller can search orders by order number', function () {
    $seller = Seller::factory()->create(['status' => 'active', 'approved_at' => now()]);
    $sellerUser = $seller->user;
    $sellerUser->assignRole('seller');

    $customer = User::factory()->create();

    $order1 = Order::factory()
        ->for($customer, 'user')
        ->for($seller)
        ->create(['order_number' => 'ORD-12345']);

    $order2 = Order::factory()
        ->for($customer, 'user')
        ->for($seller)
        ->create(['order_number' => 'ORD-67890']);

    $response = $this->actingAs($sellerUser)
        ->get(route('seller.orders.index', ['search' => '123']));

    $response->assertOk();
    $response->assertSee('ORD-12345');
    $response->assertDontSee('ORD-67890');
});

test('seller can search orders by customer name', function () {
    $seller = Seller::factory()->create(['status' => 'active', 'approved_at' => now()]);
    $sellerUser = $seller->user;
    $sellerUser->assignRole('seller');

    $customer1 = User::factory()->create(['name' => 'João Silva']);
    $customer2 = User::factory()->create(['name' => 'Maria Santos']);

    $order1 = Order::factory()
        ->for($customer1, 'user')
        ->for($seller)
        ->create();

    $order2 = Order::factory()
        ->for($customer2, 'user')
        ->for($seller)
        ->create();

    $response = $this->actingAs($sellerUser)
        ->get(route('seller.orders.index', ['search' => 'João']));

    $response->assertOk();
    $response->assertSee('João Silva');
    $response->assertDontSee('Maria Santos');
});

test('seller can view their order details', function () {
    $seller = Seller::factory()->create(['status' => 'active', 'approved_at' => now()]);
    $sellerUser = $seller->user;
    $sellerUser->assignRole('seller');

    $customer = User::factory()->create();
    $product = Product::factory()->create(['seller_id' => $seller->id, 'stock' => 10]);

    $order = Order::factory()
        ->for($customer, 'user')
        ->for($seller)
        ->has(\App\Models\OrderAddress::factory(), 'address')
        ->create();

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
    ]);

    $response = $this->actingAs($sellerUser)
        ->get(route('seller.orders.show', $order));

    $response->assertOk();
    $response->assertSee($order->order_number);
    $response->assertSee($customer->name);
    $response->assertSee(number_format($order->total, 2, ',', '.'));
});

test('seller cannot view other sellers orders', function () {
    $seller1 = Seller::factory()->create(['status' => 'active', 'approved_at' => now()]);
    $seller1User = $seller1->user;
    $seller1User->assignRole('seller');

    $seller2 = Seller::factory()->create(['status' => 'active', 'approved_at' => now()]);

    $customer = User::factory()->create();

    $order = Order::factory()
        ->for($customer, 'user')
        ->for($seller2)
        ->create();

    $response = $this->actingAs($seller1User)
        ->get(route('seller.orders.show', $order));

    $response->assertForbidden();
});

test('seller can update order status from paid to preparing', function () {
    $seller = Seller::factory()->create(['status' => 'active', 'approved_at' => now()]);
    $sellerUser = $seller->user;
    $sellerUser->assignRole('seller');

    $customer = User::factory()->create();
    $product = Product::factory()->create(['seller_id' => $seller->id, 'stock' => 10]);

    $order = Order::factory()
        ->for($customer, 'user')
        ->for($seller)
        ->create(['status' => 'paid']);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
    ]);

    $response = $this->actingAs($sellerUser)
        ->post(route('seller.orders.updateStatus', $order), [
            'status' => 'preparing',
            'note' => 'Pedido em preparação',
        ]);

    $response->assertRedirect(route('seller.orders.show', $order));
    $response->assertSessionHas('success');

    $order->refresh();
    expect($order->status)->toBe('preparing');
});

test('seller can update order status from preparing to shipped with tracking code', function () {
    $seller = Seller::factory()->create(['status' => 'active', 'approved_at' => now()]);
    $sellerUser = $seller->user;
    $sellerUser->assignRole('seller');

    $customer = User::factory()->create();

    $order = Order::factory()
        ->for($customer, 'user')
        ->for($seller)
        ->create(['status' => 'preparing']);

    $response = $this->actingAs($sellerUser)
        ->post(route('seller.orders.updateStatus', $order), [
            'status' => 'shipped',
            'tracking_code' => 'BR123456789',
            'note' => 'Enviado via Correios',
        ]);

    $response->assertRedirect(route('seller.orders.show', $order));

    $order->refresh();
    expect($order->status)->toBe('shipped');
    expect($order->tracking_code)->toBe('BR123456789');
    expect($order->shipped_at)->not->toBeNull();
});

test('seller can update order status from shipped to delivered', function () {
    $seller = Seller::factory()->create(['status' => 'active', 'approved_at' => now()]);
    $sellerUser = $seller->user;
    $sellerUser->assignRole('seller');

    $customer = User::factory()->create();

    $order = Order::factory()
        ->for($customer, 'user')
        ->for($seller)
        ->create(['status' => 'shipped', 'tracking_code' => 'BR123456789']);

    $response = $this->actingAs($sellerUser)
        ->post(route('seller.orders.updateStatus', $order), [
            'status' => 'delivered',
        ]);

    $response->assertRedirect(route('seller.orders.show', $order));

    $order->refresh();
    expect($order->status)->toBe('delivered');
    expect($order->delivered_at)->not->toBeNull();
});

test('seller cannot update status to shipped without tracking code', function () {
    $seller = Seller::factory()->create(['status' => 'active', 'approved_at' => now()]);
    $sellerUser = $seller->user;
    $sellerUser->assignRole('seller');

    $customer = User::factory()->create();

    $order = Order::factory()
        ->for($customer, 'user')
        ->for($seller)
        ->create(['status' => 'preparing']);

    $response = $this->actingAs($sellerUser)
        ->post(route('seller.orders.updateStatus', $order), [
            'status' => 'shipped',
            // Missing tracking_code
        ]);

    $response->assertSessionHasErrors('tracking_code');

    $order->refresh();
    expect($order->status)->toBe('preparing');
});

test('seller can cancel paid order', function () {
    $seller = Seller::factory()->create(['status' => 'active', 'approved_at' => now()]);
    $sellerUser = $seller->user;
    $sellerUser->assignRole('seller');

    $customer = User::factory()->create();
    $product = Product::factory()->create(['seller_id' => $seller->id, 'stock' => 10]);

    $order = Order::factory()
        ->for($customer, 'user')
        ->for($seller)
        ->create(['status' => 'paid']);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $initialStock = $product->stock;

    $response = $this->actingAs($sellerUser)
        ->post(route('seller.orders.cancel', $order));

    $response->assertRedirect(route('seller.orders.show', $order));

    $order->refresh();
    $product->refresh();

    expect($order->status)->toBe('cancelled');
    expect($product->stock)->toBe($initialStock + 2); // Stock restored
});

test('seller can cancel preparing order', function () {
    $seller = Seller::factory()->create(['status' => 'active', 'approved_at' => now()]);
    $sellerUser = $seller->user;
    $sellerUser->assignRole('seller');

    $customer = User::factory()->create();

    $order = Order::factory()
        ->for($customer, 'user')
        ->for($seller)
        ->create(['status' => 'preparing']);

    $response = $this->actingAs($sellerUser)
        ->post(route('seller.orders.cancel', $order));

    $response->assertRedirect(route('seller.orders.show', $order));

    $order->refresh();
    expect($order->status)->toBe('cancelled');
});

test('seller cannot cancel shipped order', function () {
    $seller = Seller::factory()->create(['status' => 'active', 'approved_at' => now()]);
    $sellerUser = $seller->user;
    $sellerUser->assignRole('seller');

    $customer = User::factory()->create();

    $order = Order::factory()
        ->for($customer, 'user')
        ->for($seller)
        ->create(['status' => 'shipped']);

    $response = $this->actingAs($sellerUser)
        ->post(route('seller.orders.cancel', $order));

    $response->assertForbidden();

    $order->refresh();
    expect($order->status)->toBe('shipped');
});

test('seller cannot cancel delivered order', function () {
    $seller = Seller::factory()->create(['status' => 'active', 'approved_at' => now()]);
    $sellerUser = $seller->user;
    $sellerUser->assignRole('seller');

    $customer = User::factory()->create();

    $order = Order::factory()
        ->for($customer, 'user')
        ->for($seller)
        ->create(['status' => 'delivered']);

    $response = $this->actingAs($sellerUser)
        ->post(route('seller.orders.cancel', $order));

    $response->assertForbidden();

    $order->refresh();
    expect($order->status)->toBe('delivered');
});

test('seller cannot update status of other sellers orders', function () {
    $seller1 = Seller::factory()->create(['status' => 'active', 'approved_at' => now()]);
    $seller1User = $seller1->user;
    $seller1User->assignRole('seller');

    $seller2 = Seller::factory()->create(['status' => 'active', 'approved_at' => now()]);

    $customer = User::factory()->create();

    $order = Order::factory()
        ->for($customer, 'user')
        ->for($seller2)
        ->create(['status' => 'paid']);

    $response = $this->actingAs($seller1User)
        ->post(route('seller.orders.updateStatus', $order), [
            'status' => 'preparing',
        ]);

    $response->assertForbidden();

    $order->refresh();
    expect($order->status)->toBe('paid');
});

test('guest cannot access seller orders', function () {
    $response = $this->get(route('seller.orders.index'));

    $response->assertRedirect(route('login'));
});

test('customer cannot access seller orders', function () {
    $customer = User::factory()->create();
    $customer->assignRole('customer');

    $response = $this->actingAs($customer)->get(route('seller.orders.index'));

    $response->assertForbidden();
});

test('user without seller profile cannot access seller orders', function () {
    $user = User::factory()->create();
    $user->assignRole('seller');

    $response = $this->actingAs($user)->get(route('seller.orders.index'));

    $response->assertStatus(403);
    $response->assertSee('Você não possui uma loja cadastrada.');
});
