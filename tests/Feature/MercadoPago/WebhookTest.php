<?php

use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;

beforeEach(function () {
    // Create test data
    $this->user = User::factory()->create([
        'cpf_cnpj' => '12345678901',
        'phone' => '11987654321',
    ]);

    $this->seller = Seller::factory()->create(['status' => 'active']);

    $this->product = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'stock' => 10,
        'status' => 'published',
    ]);

    $this->order = Order::factory()->create([
        'user_id' => $this->user->id,
        'seller_id' => $this->seller->id,
        'total' => 115.00,
        'status' => 'awaiting_payment',
    ]);

    OrderItem::factory()->create([
        'order_id' => $this->order->id,
        'product_id' => $this->product->id,
        'quantity' => 1,
        'unit_price' => 100.00,
        'subtotal' => 100.00,
    ]);

    OrderAddress::factory()->create([
        'order_id' => $this->order->id,
    ]);
});

test('webhook endpoint is accessible without csrf', function () {
    // Webhook should accept POST without CSRF token
    $response = $this->postJson('/webhook/mercadopago', [
        'action' => 'payment.updated',
        'type' => 'payment',
    ]);

    // Should return 200 (graceful handling even without payment ID)
    expect($response->status())->toBe(200);
    expect($response->json('status'))->toBe('success');
});

test('webhook handles unknown notification type gracefully', function () {
    // Send unknown notification type
    $response = $this->postJson('/webhook/mercadopago', [
        'action' => 'unknown.action',
        'type' => 'unknown',
    ]);

    // Should still return 200 (graceful handling)
    expect($response->status())->toBe(200);
    expect($response->json('status'))->toBe('success');
});

test('payment can be created for order', function () {
    $payment = Payment::create([
        'order_id' => $this->order->id,
        'payment_method' => 'credit_card',
        'payment_type' => 'credit_card',
        'amount' => 115.00,
        'installments' => 1,
        'status' => 'pending',
        'external_payment_id' => 'MP-123456',
    ]);

    expect($payment)->toBeInstanceOf(Payment::class);
    expect($payment->order_id)->toBe($this->order->id);
    expect($payment->status)->toBe('pending');
});

test('payment status can be updated', function () {
    $payment = Payment::factory()->create([
        'order_id' => $this->order->id,
        'status' => 'pending',
    ]);

    $payment->update(['status' => 'approved', 'paid_at' => now()]);

    expect($payment->fresh()->status)->toBe('approved');
    expect($payment->fresh()->paid_at)->not->toBeNull();
});

test('order status can be updated to paid', function () {
    $this->order->update([
        'status' => 'paid',
        'paid_at' => now(),
    ]);

    expect($this->order->fresh()->status)->toBe('paid');
    expect($this->order->fresh()->paid_at)->not->toBeNull();
});

test('product stock can be restored on cancellation', function () {
    $initialStock = $this->product->stock;

    // Simulate stock decrease (would happen during order creation)
    $this->product->decrement('stock', 1);
    expect($this->product->fresh()->stock)->toBe($initialStock - 1);

    // Restore stock (simulating order cancellation)
    $this->product->increment('stock', 1);
    expect($this->product->fresh()->stock)->toBe($initialStock);
});

test('order can be cancelled', function () {
    $this->order->update(['status' => 'cancelled']);

    expect($this->order->fresh()->status)->toBe('cancelled');
});

test('payment metadata can be stored as array', function () {
    $metadata = [
        'transaction_id' => 'MP-123456',
        'card_brand' => 'visa',
        'card_last_four' => '4242',
    ];

    $payment = Payment::factory()->create([
        'order_id' => $this->order->id,
        'metadata' => $metadata,
    ]);

    expect($payment->fresh()->metadata)->toBe($metadata);
    expect($payment->fresh()->metadata['card_brand'])->toBe('visa');
});

test('payment has correct casts', function () {
    $payment = Payment::factory()->create([
        'order_id' => $this->order->id,
        'amount' => 115.50,
        'installments' => 3,
        'fee_amount' => 5.50,
        'net_amount' => 110.00,
        'paid_at' => now(),
    ]);

    // Decimal cast returns string in Laravel
    expect($payment->amount)->toBeString();
    expect($payment->amount)->toBe('115.50');
    expect($payment->installments)->toBeInt();
    expect($payment->fee_amount)->toBeString();
    expect($payment->net_amount)->toBeString();
    expect($payment->paid_at)->toBeInstanceOf(DateTime::class);
});
