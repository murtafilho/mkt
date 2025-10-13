<?php

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use App\Models\UserAddress;
use App\Services\OrderService;

beforeEach(function () {
    // Seed roles and permissions
    $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

    $this->orderService = new OrderService;
    $this->user = User::factory()->create();
    $this->user->assignRole('customer');
    $this->seller = Seller::factory()->create(['status' => 'active', 'approved_at' => now()]);
    $this->address = UserAddress::factory()->create(['user_id' => $this->user->id]);
});

test('can create order from cart items', function () {
    $product1 = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'stock' => 10,
        'sale_price' => 100.00,
        'status' => 'published',
    ]);
    $product2 = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'stock' => 5,
        'sale_price' => 50.00,
        'status' => 'published',
    ]);

    $cartItem1 = CartItem::factory()->create([
        'user_id' => $this->user->id,
        'product_id' => $product1->id,
        'quantity' => 2,
    ]);
    $cartItem2 = CartItem::factory()->create([
        'user_id' => $this->user->id,
        'product_id' => $product2->id,
        'quantity' => 1,
    ]);

    $cartItems = collect([$cartItem1, $cartItem2]);

    $orders = $this->orderService->createOrdersFromCart($this->user, $cartItems, $this->address);

    expect($orders)->toHaveCount(1); // Same seller, one order
    expect($orders->first())->toBeInstanceOf(Order::class);
    expect($orders->first()->user_id)->toBe($this->user->id);
    expect($orders->first()->seller_id)->toBe($this->seller->id);
    expect($orders->first()->status)->toBe('awaiting_payment');
});

test('creates separate orders for different sellers', function () {
    $seller2 = Seller::factory()->create(['status' => 'active', 'approved_at' => now()]);

    $product1 = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'stock' => 10,
        'sale_price' => 100.00,
        'status' => 'published',
    ]);
    $product2 = Product::factory()->create([
        'seller_id' => $seller2->id,
        'stock' => 5,
        'sale_price' => 50.00,
        'status' => 'published',
    ]);

    $cartItem1 = CartItem::factory()->create([
        'user_id' => $this->user->id,
        'product_id' => $product1->id,
        'quantity' => 2,
    ]);
    $cartItem2 = CartItem::factory()->create([
        'user_id' => $this->user->id,
        'product_id' => $product2->id,
        'quantity' => 1,
    ]);

    $cartItems = collect([$cartItem1, $cartItem2]);

    $orders = $this->orderService->createOrdersFromCart($this->user, $cartItems, $this->address);

    expect($orders)->toHaveCount(2); // Different sellers, two orders
    expect($orders->pluck('seller_id')->unique())->toHaveCount(2);
});

test('calculates order total correctly', function () {
    $product1 = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'stock' => 10,
        'sale_price' => 100.00,
        'status' => 'published',
    ]);
    $product2 = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'stock' => 5,
        'sale_price' => 50.00,
        'status' => 'published',
    ]);

    $cartItem1 = CartItem::factory()->create([
        'user_id' => $this->user->id,
        'product_id' => $product1->id,
        'quantity' => 2, // 200.00
    ]);
    $cartItem2 = CartItem::factory()->create([
        'user_id' => $this->user->id,
        'product_id' => $product2->id,
        'quantity' => 3, // 150.00
    ]);

    $cartItems = collect([$cartItem1, $cartItem2]);

    $orders = $this->orderService->createOrdersFromCart($this->user, $cartItems, $this->address);

    $order = $orders->first();

    expect($order->subtotal)->toBe('350.00');
    expect($order->total)->toBe('350.00'); // No shipping cost added in this test
});

test('creates order items for each cart item', function () {
    $product1 = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'stock' => 10,
        'sale_price' => 100.00,
        'status' => 'published',
    ]);
    $product2 = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'stock' => 5,
        'sale_price' => 50.00,
        'status' => 'published',
    ]);

    $cartItem1 = CartItem::factory()->create([
        'user_id' => $this->user->id,
        'product_id' => $product1->id,
        'quantity' => 2,
    ]);
    $cartItem2 = CartItem::factory()->create([
        'user_id' => $this->user->id,
        'product_id' => $product2->id,
        'quantity' => 1,
    ]);

    $cartItems = collect([$cartItem1, $cartItem2]);

    $orders = $this->orderService->createOrdersFromCart($this->user, $cartItems, $this->address);

    $order = $orders->first();
    $orderItems = $order->items;

    expect($orderItems)->toHaveCount(2);
    expect($orderItems->first())->toBeInstanceOf(OrderItem::class);
});

test('decreases product stock after order creation', function () {
    $product = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'stock' => 10,
        'sale_price' => 100.00,
        'status' => 'published',
    ]);

    $cartItem = CartItem::factory()->create([
        'user_id' => $this->user->id,
        'product_id' => $product->id,
        'quantity' => 3,
    ]);

    $cartItems = collect([$cartItem]);

    $this->orderService->createOrdersFromCart($this->user, $cartItems, $this->address);

    expect($product->fresh()->stock)->toBe(7);
});

test('can update order status', function () {
    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'seller_id' => $this->seller->id,
        'status' => 'awaiting_payment',
    ]);

    $updated = $this->orderService->updateOrderStatus($order, 'paid');

    expect($updated->status)->toBe('paid');
});

test('can mark order as paid', function () {
    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'seller_id' => $this->seller->id,
        'status' => 'awaiting_payment',
    ]);

    $updated = $this->orderService->markAsPaid($order);

    expect($updated->status)->toBe('paid');
    expect($updated->paid_at)->not->toBeNull();
});

test('can cancel order', function () {
    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'seller_id' => $this->seller->id,
        'status' => 'awaiting_payment',
    ]);

    $cancelled = $this->orderService->cancelOrder($order);

    expect($cancelled->status)->toBe('cancelled');
});

test('restores stock when order is cancelled', function () {
    $product = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'stock' => 10,
        'sale_price' => 100.00,
        'status' => 'published',
    ]);

    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'seller_id' => $this->seller->id,
        'status' => 'paid',
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 3,
    ]);

    // Decrease stock to simulate order
    $product->decrement('stock', 3);
    expect($product->fresh()->stock)->toBe(7);

    // Cancel order
    $this->orderService->cancelOrder($order);

    // Stock should be restored
    expect($product->fresh()->stock)->toBe(10);
});

test('can get user orders', function () {
    Order::factory()->count(3)->create([
        'user_id' => $this->user->id,
        'seller_id' => $this->seller->id,
    ]);

    $user2 = User::factory()->create();
    Order::factory()->count(2)->create([
        'user_id' => $user2->id,
        'seller_id' => $this->seller->id,
    ]);

    $orders = $this->orderService->getUserOrders($this->user);

    expect($orders)->toHaveCount(3);
});

test('can get seller orders', function () {
    $seller2 = Seller::factory()->create();

    Order::factory()->count(4)->create([
        'user_id' => $this->user->id,
        'seller_id' => $this->seller->id,
    ]);
    Order::factory()->count(2)->create([
        'user_id' => $this->user->id,
        'seller_id' => $seller2->id,
    ]);

    $orders = $this->orderService->getSellerOrders($this->seller);

    expect($orders)->toHaveCount(4);
});

test('can get order by id', function () {
    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'seller_id' => $this->seller->id,
    ]);

    $found = $this->orderService->getOrderById($order->id);

    expect($found)->toBeInstanceOf(Order::class);
    expect($found->id)->toBe($order->id);
});

test('can calculate order subtotal', function () {
    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'seller_id' => $this->seller->id,
        'subtotal' => 0,
        'total' => 0,
    ]);

    $product1 = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'sale_price' => 100.00,
    ]);
    $product2 = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'sale_price' => 50.00,
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product1->id,
        'quantity' => 2,
        'unit_price' => 100.00,
        'subtotal' => 200.00,
    ]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product2->id,
        'quantity' => 3,
        'unit_price' => 50.00,
        'subtotal' => 150.00,
    ]);

    $subtotal = $this->orderService->calculateOrderSubtotal($order);

    expect($subtotal)->toBe('350.00');
});

test('can apply shipping fee to order', function () {
    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'seller_id' => $this->seller->id,
        'subtotal' => 100.00,
        'shipping_fee' => 0,
        'total' => 100.00,
    ]);

    $updated = $this->orderService->applyShippingFee($order, 15.50);

    expect($updated->shipping_fee)->toBe('15.50');
    expect($updated->total)->toBe('115.50');
});

test('can filter orders by status', function () {
    Order::factory()->count(3)->create([
        'user_id' => $this->user->id,
        'seller_id' => $this->seller->id,
        'status' => 'paid',
    ]);
    Order::factory()->count(2)->create([
        'user_id' => $this->user->id,
        'seller_id' => $this->seller->id,
        'status' => 'awaiting_payment',
    ]);

    $paidOrders = $this->orderService->getOrdersByStatus('paid');

    expect($paidOrders)->toHaveCount(3);
    expect($paidOrders->first()->status)->toBe('paid');
});

test('can get pending orders', function () {
    Order::factory()->count(2)->create([
        'user_id' => $this->user->id,
        'seller_id' => $this->seller->id,
        'status' => 'awaiting_payment',
    ]);
    Order::factory()->count(3)->create([
        'user_id' => $this->user->id,
        'seller_id' => $this->seller->id,
        'status' => 'paid',
    ]);

    $pending = $this->orderService->getPendingOrders();

    expect($pending)->toHaveCount(2);
});
