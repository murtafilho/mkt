<?php

use App\Models\CartItem;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Support\Facades\Session;

beforeEach(function () {
    $this->cartService = new CartService;
    $this->seller = Seller::factory()->create(['status' => 'active', 'approved_at' => now()]);
    $this->product = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'stock' => 10,
        'sale_price' => 100.00,
        'status' => 'published',
    ]);
});

test('guest can add product to cart', function () {
    Session::put('guest_cart_id', 'guest-123');

    $item = $this->cartService->addToCart($this->product, 2, null);

    expect($item)->toBeInstanceOf(CartItem::class);
    expect($item->product_id)->toBe($this->product->id);
    expect($item->quantity)->toBe(2);
    expect($item->guest_cart_id)->toBe('guest-123');
    expect($item->user_id)->toBeNull();
});

test('authenticated user can add product to cart', function () {
    $user = User::factory()->create();

    $item = $this->cartService->addToCart($this->product, 2, $user);

    expect($item)->toBeInstanceOf(CartItem::class);
    expect($item->product_id)->toBe($this->product->id);
    expect($item->quantity)->toBe(2);
    expect($item->user_id)->toBe($user->id);
    expect($item->guest_cart_id)->toBeNull();
});

test('adding same product increases quantity', function () {
    $user = User::factory()->create();

    $this->cartService->addToCart($this->product, 2, $user);
    $item = $this->cartService->addToCart($this->product, 3, $user);

    expect($item->quantity)->toBe(5);
    expect(CartItem::where('user_id', $user->id)->count())->toBe(1);
});

test('cannot add more than available stock', function () {
    $user = User::factory()->create();

    expect(fn () => $this->cartService->addToCart($this->product, 15, $user))
        ->toThrow(Exception::class, 'Quantidade solicitada excede o estoque disponível');
});

test('can update cart item quantity', function () {
    $user = User::factory()->create();
    $item = $this->cartService->addToCart($this->product, 2, $user);

    $updated = $this->cartService->updateQuantity($item, 5);

    expect($updated->quantity)->toBe(5);
});

test('cannot update to quantity exceeding stock', function () {
    $user = User::factory()->create();
    $item = $this->cartService->addToCart($this->product, 2, $user);

    expect(fn () => $this->cartService->updateQuantity($item, 15))
        ->toThrow(Exception::class, 'Quantidade solicitada excede o estoque disponível');
});

test('can remove item from cart', function () {
    $user = User::factory()->create();
    $item = $this->cartService->addToCart($this->product, 2, $user);

    $result = $this->cartService->removeFromCart($item);

    expect($result)->toBeTrue();
    $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
});

test('can get user cart items', function () {
    $user = User::factory()->create();
    $product1 = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'stock' => 10,
        'status' => 'published',
    ]);
    $product2 = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'stock' => 10,
        'status' => 'published',
    ]);

    $this->cartService->addToCart($product1, 2, $user);
    $this->cartService->addToCart($product2, 1, $user);

    $items = $this->cartService->getCartItems($user);

    expect($items)->toHaveCount(2);
});

test('can get guest cart items', function () {
    Session::put('guest_cart_id', 'guest-456');

    $product1 = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'stock' => 10,
        'status' => 'published',
    ]);

    $this->cartService->addToCart($product1, 2, null);

    $items = $this->cartService->getGuestCartItems('guest-456');

    expect($items)->toHaveCount(1);
    expect($items->first()->guest_cart_id)->toBe('guest-456');
});

test('can calculate cart total for user', function () {
    $user = User::factory()->create();
    $product1 = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'stock' => 10,
        'sale_price' => 100.00,
        'status' => 'published',
    ]);
    $product2 = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'stock' => 10,
        'sale_price' => 50.00,
        'status' => 'published',
    ]);

    $this->cartService->addToCart($product1, 2, $user); // 200.00
    $this->cartService->addToCart($product2, 3, $user); // 150.00

    $total = $this->cartService->calculateCartTotal($user);

    expect($total)->toBe('350.00');
});

test('can calculate guest cart total', function () {
    Session::put('guest_cart_id', 'guest-789');

    $product1 = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'stock' => 10,
        'sale_price' => 100.00,
        'status' => 'published',
    ]);

    $this->cartService->addToCart($product1, 2, null); // 200.00

    $total = $this->cartService->calculateGuestCartTotal('guest-789');

    expect($total)->toBe('200.00');
});

test('can clear user cart', function () {
    $user = User::factory()->create();

    $this->cartService->addToCart($this->product, 2, $user);

    $result = $this->cartService->clearCart($user);

    expect($result)->toBeTrue();
    expect(CartItem::where('user_id', $user->id)->count())->toBe(0);
});

test('can clear guest cart', function () {
    Session::put('guest_cart_id', 'guest-clear');

    $this->cartService->addToCart($this->product, 2, null);

    $result = $this->cartService->clearGuestCart('guest-clear');

    expect($result)->toBeTrue();
    expect(CartItem::where('guest_cart_id', 'guest-clear')->count())->toBe(0);
});

test('can merge guest cart to user cart on login', function () {
    $user = User::factory()->create();
    $guestCartId = 'guest-merge';

    Session::put('guest_cart_id', $guestCartId);

    // Create guest cart items
    $product1 = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'stock' => 10,
        'status' => 'published',
    ]);
    $product2 = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'stock' => 10,
        'status' => 'published',
    ]);

    $this->cartService->addToCart($product1, 2, null);
    $this->cartService->addToCart($product2, 1, null);

    // User already has item of product1
    $this->cartService->addToCart($product1, 3, $user);

    // Merge
    $this->cartService->mergeGuestCart($user, $guestCartId);

    // Check results
    $userItems = CartItem::where('user_id', $user->id)->get();

    expect($userItems)->toHaveCount(2);

    // product1 should have merged quantity (2 + 3 = 5)
    $product1Item = $userItems->where('product_id', $product1->id)->first();
    expect($product1Item->quantity)->toBe(5);

    // product2 should be transferred
    $product2Item = $userItems->where('product_id', $product2->id)->first();
    expect($product2Item->quantity)->toBe(1);

    // Guest cart should be empty
    expect(CartItem::where('guest_cart_id', $guestCartId)->count())->toBe(0);
});

test('can get cart items count for user', function () {
    $user = User::factory()->create();

    $product1 = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'stock' => 10,
        'status' => 'published',
    ]);
    $product2 = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'stock' => 10,
        'status' => 'published',
    ]);

    $this->cartService->addToCart($product1, 2, $user);
    $this->cartService->addToCart($product2, 3, $user);

    $count = $this->cartService->getCartItemsCount($user);

    expect($count)->toBe(5); // Total quantity
});

test('can group cart items by seller (same seller only)', function () {
    $user = User::factory()->create();

    $seller1 = Seller::factory()->create(['status' => 'active', 'approved_at' => now()]);

    $product1 = Product::factory()->create([
        'seller_id' => $seller1->id,
        'stock' => 10,
        'status' => 'published',
    ]);
    $product2 = Product::factory()->create([
        'seller_id' => $seller1->id,
        'stock' => 10,
        'status' => 'published',
    ]);

    $this->cartService->addToCart($product1, 1, $user);
    $this->cartService->addToCart($product2, 1, $user);

    $grouped = $this->cartService->groupCartItemsBySeller($user);

    expect($grouped)->toHaveCount(1);
    expect($grouped[$seller1->id])->toHaveCount(2);
});

test('cannot add product from different seller to cart', function () {
    $user = User::factory()->create();

    $seller1 = Seller::factory()->create(['status' => 'active', 'approved_at' => now()]);
    $seller2 = Seller::factory()->create(['status' => 'active', 'approved_at' => now()]);

    $product1 = Product::factory()->create([
        'seller_id' => $seller1->id,
        'stock' => 10,
        'status' => 'published',
    ]);
    $product2 = Product::factory()->create([
        'seller_id' => $seller2->id,
        'stock' => 10,
        'status' => 'published',
    ]);

    // Add product from seller1
    $this->cartService->addToCart($product1, 1, $user);

    // Try to add product from seller2 - should throw exception
    expect(fn () => $this->cartService->addToCart($product2, 1, $user))
        ->toThrow(Exception::class, 'Seu carrinho já contém produtos de');
});

test('cannot add out of stock product to cart', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'stock' => 0,
        'status' => 'published',
    ]);

    expect(fn () => $this->cartService->addToCart($product, 1, $user))
        ->toThrow(Exception::class, 'Produto não disponível para venda');
});

test('cannot add unpublished product to cart', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'stock' => 10,
        'status' => 'draft',
    ]);

    expect(fn () => $this->cartService->addToCart($product, 1, $user))
        ->toThrow(Exception::class, 'Produto não disponível para venda');
});
