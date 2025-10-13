<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create roles
    Role::firstOrCreate(['name' => 'admin']);
    Role::firstOrCreate(['name' => 'seller']);
    Role::firstOrCreate(['name' => 'customer']);
});

test('customer can view their orders list', function () {
    $customer = User::factory()->create();
    $customer->assignRole('customer');

    $seller = Seller::factory()->create();
    $product = Product::factory()->create(['seller_id' => $seller->id, 'stock' => 10]);

    // Create 3 orders for the customer
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

    // Create 2 orders for another customer (should not appear)
    $otherCustomer = User::factory()->create();
    $otherCustomer->assignRole('customer');
    Order::factory()
        ->count(2)
        ->for($otherCustomer, 'user')
        ->for($seller)
        ->create();

    $response = $this->actingAs($customer)->get(route('customer.orders.index'));

    $response->assertOk();
    $response->assertViewHas('orders');
    $response->assertSee($orders[0]->order_number);
    $response->assertSee($orders[1]->order_number);
    $response->assertSee($orders[2]->order_number);
});

test('customer can filter orders by status', function () {
    $customer = User::factory()->create();
    $customer->assignRole('customer');

    $seller = Seller::factory()->create();

    $paidOrder = Order::factory()
        ->for($customer, 'user')
        ->for($seller)
        ->create(['status' => 'paid']);

    $shippedOrder = Order::factory()
        ->for($customer, 'user')
        ->for($seller)
        ->create(['status' => 'shipped']);

    $response = $this->actingAs($customer)
        ->get(route('customer.orders.index', ['status' => 'paid']));

    $response->assertOk();
    $response->assertSee($paidOrder->order_number);
    $response->assertDontSee($shippedOrder->order_number);
});

test('customer can search orders by order number', function () {
    $customer = User::factory()->create();
    $customer->assignRole('customer');

    $seller = Seller::factory()->create();

    $order1 = Order::factory()
        ->for($customer, 'user')
        ->for($seller)
        ->create(['order_number' => 'ORD-12345']);

    $order2 = Order::factory()
        ->for($customer, 'user')
        ->for($seller)
        ->create(['order_number' => 'ORD-67890']);

    $response = $this->actingAs($customer)
        ->get(route('customer.orders.index', ['search' => '123']));

    $response->assertOk();
    $response->assertSee('ORD-12345');
    $response->assertDontSee('ORD-67890');
});

test('customer can view their order details', function () {
    $customer = User::factory()->create();
    $customer->assignRole('customer');

    $seller = Seller::factory()->create();
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

    $response = $this->actingAs($customer)
        ->get(route('customer.orders.show', $order));

    $response->assertOk();
    $response->assertSee($order->order_number);
    $response->assertSee(number_format($order->total, 2, ',', '.'));
});

test('customer cannot view other customers orders', function () {
    $customer = User::factory()->create();
    $customer->assignRole('customer');

    $otherCustomer = User::factory()->create();
    $otherCustomer->assignRole('customer');

    $seller = Seller::factory()->create();

    $otherOrder = Order::factory()
        ->for($otherCustomer, 'user')
        ->for($seller)
        ->create();

    $response = $this->actingAs($customer)
        ->get(route('customer.orders.show', $otherOrder));

    $response->assertForbidden();
});

test('customer can cancel awaiting_payment order', function () {
    $customer = User::factory()->create();
    $customer->assignRole('customer');

    $seller = Seller::factory()->create();
    $product = Product::factory()->create(['seller_id' => $seller->id, 'stock' => 10]);

    $order = Order::factory()
        ->for($customer, 'user')
        ->for($seller)
        ->create(['status' => 'awaiting_payment']);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $initialStock = $product->stock;

    $response = $this->actingAs($customer)
        ->post(route('customer.orders.cancel', $order));

    $response->assertRedirect(route('customer.orders.show', $order));
    $response->assertSessionHas('success');

    $order->refresh();
    $product->refresh();

    expect($order->status)->toBe('cancelled');
    expect($product->stock)->toBe($initialStock + 2); // Stock restored
});

test('customer can cancel paid order', function () {
    $customer = User::factory()->create();
    $customer->assignRole('customer');

    $seller = Seller::factory()->create();
    $product = Product::factory()->create(['seller_id' => $seller->id, 'stock' => 10]);

    $order = Order::factory()
        ->for($customer, 'user')
        ->for($seller)
        ->create(['status' => 'paid']);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $response = $this->actingAs($customer)
        ->post(route('customer.orders.cancel', $order));

    $response->assertRedirect(route('customer.orders.show', $order));

    $order->refresh();
    expect($order->status)->toBe('cancelled');
});

test('customer cannot cancel shipped order', function () {
    $customer = User::factory()->create();
    $customer->assignRole('customer');

    $seller = Seller::factory()->create();

    $order = Order::factory()
        ->for($customer, 'user')
        ->for($seller)
        ->create(['status' => 'shipped']);

    $response = $this->actingAs($customer)
        ->post(route('customer.orders.cancel', $order));

    $response->assertForbidden();

    $order->refresh();
    expect($order->status)->toBe('shipped');
});

test('customer cannot cancel delivered order', function () {
    $customer = User::factory()->create();
    $customer->assignRole('customer');

    $seller = Seller::factory()->create();

    $order = Order::factory()
        ->for($customer, 'user')
        ->for($seller)
        ->create(['status' => 'delivered']);

    $response = $this->actingAs($customer)
        ->post(route('customer.orders.cancel', $order));

    $response->assertForbidden();

    $order->refresh();
    expect($order->status)->toBe('delivered');
});

test('guest cannot access orders list', function () {
    $response = $this->get(route('customer.orders.index'));

    $response->assertRedirect(route('login'));
});

test('guest cannot view order details', function () {
    $seller = Seller::factory()->create();
    $customer = User::factory()->create();
    $order = Order::factory()
        ->for($customer, 'user')
        ->for($seller)
        ->create();

    $response = $this->get(route('customer.orders.show', $order));

    $response->assertRedirect(route('login'));
});
