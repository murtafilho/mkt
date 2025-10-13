<?php

use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use App\Services\PaymentService;

beforeEach(function () {
    // Create test user with MP required fields
    $this->user = User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'cpf_cnpj' => '12345678901',
        'phone' => '11987654321',
        'birth_date' => '1990-01-01',
    ]);

    // Create seller
    $this->seller = Seller::factory()->create([
        'status' => 'active',
    ]);

    // Create product
    $this->product = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'name' => 'Test Product',
        'sku' => 'TEST-SKU-001',
        'original_price' => 150.00,
        'sale_price' => 100.00,
        'stock' => 10,
        'status' => 'published',
    ]);

    // Create order
    $this->order = Order::factory()->create([
        'user_id' => $this->user->id,
        'seller_id' => $this->seller->id,
        'subtotal' => 100.00,
        'shipping_fee' => 15.00,
        'total' => 115.00,
        'status' => 'awaiting_payment',
    ]);

    // Create order item
    OrderItem::factory()->create([
        'order_id' => $this->order->id,
        'product_id' => $this->product->id,
        'product_name' => 'Test Product',
        'sku' => 'TEST-SKU-001',
        'quantity' => 1,
        'unit_price' => 100.00,
        'subtotal' => 100.00,
    ]);

    // Create order address
    OrderAddress::factory()->create([
        'order_id' => $this->order->id,
        'postal_code' => '01310100',
        'street' => 'Avenida Paulista',
        'number' => '1000',
        'neighborhood' => 'Bela Vista',
        'city' => 'São Paulo',
        'state' => 'SP',
        'recipient_name' => 'Test User',
        'contact_phone' => '11987654321',
    ]);

    $this->paymentService = new PaymentService;
});

test('can get payment status for order', function () {
    // Create a payment
    $payment = Payment::factory()->create([
        'order_id' => $this->order->id,
        'payment_method' => 'mercadopago',
        'amount' => 115.00,
        'status' => 'pending',
    ]);

    $result = $this->paymentService->getPaymentStatus($this->order);

    expect($result)->toBeInstanceOf(Payment::class);
    expect($result->id)->toBe($payment->id);
    expect($result->status)->toBe('pending');
});

test('returns null when no payment exists for order', function () {
    $result = $this->paymentService->getPaymentStatus($this->order);

    expect($result)->toBeNull();
});

test('user helper methods work correctly', function () {
    expect($this->user->getFirstName())->toBe('Test');
    expect($this->user->getLastName())->toBe('User');
    expect($this->user->getAreaCode())->toBe('11');
    expect($this->user->getPhoneNumber())->toBe('987654321');
});

test('order has all required relationships loaded', function () {
    $order = Order::with(['user', 'seller', 'items.product', 'address'])->find($this->order->id);

    expect($order->user)->toBeInstanceOf(User::class);
    expect($order->seller)->toBeInstanceOf(Seller::class);
    expect($order->items)->toHaveCount(1);
    expect($order->items->first()->product)->toBeInstanceOf(Product::class);
    expect($order->address)->toBeInstanceOf(OrderAddress::class);
});
