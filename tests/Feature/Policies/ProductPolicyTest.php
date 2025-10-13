<?php

use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use App\Policies\ProductPolicy;

beforeEach(function () {
    // Seed roles and permissions
    $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

    $this->policy = new ProductPolicy;
    $this->user = User::factory()->create();
    $this->user->assignRole('seller');
    $this->seller = Seller::factory()->create([
        'user_id' => $this->user->id,
        'status' => 'active',
        'approved_at' => now(),
    ]);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

test('anyone can view published products', function () {
    $product = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'status' => 'published',
    ]);

    expect($this->policy->view(null, $product))->toBeTrue();
    expect($this->policy->view($this->user, $product))->toBeTrue();
});

test('guest cannot view draft products', function () {
    $product = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'status' => 'draft',
    ]);

    expect($this->policy->view(null, $product))->toBeFalse();
});

test('seller owner can view own draft products', function () {
    $product = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'status' => 'draft',
    ]);

    expect($this->policy->view($this->user, $product))->toBeTrue();
});

test('admin can view any product', function () {
    $product = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'status' => 'draft',
    ]);

    expect($this->policy->view($this->admin, $product))->toBeTrue();
});

test('approved seller can create products', function () {
    expect($this->policy->create($this->user))->toBeTrue();
});

test('user without seller cannot create products', function () {
    $userWithoutSeller = User::factory()->create();

    expect($this->policy->create($userWithoutSeller))->toBeFalse();
});

test('pending seller CAN create products (as drafts)', function () {
    $pendingSeller = Seller::factory()->create([
        'user_id' => User::factory()->create()->id,
        'status' => 'pending',
        'approved_at' => null,
    ]);

    $pendingSeller->user->assignRole('seller');

    // Pending sellers CAN create products (drafts)
    // They just cannot PUBLISH them
    expect($this->policy->create($pendingSeller->user))->toBeTrue();
});

test('admin can create products', function () {
    expect($this->policy->create($this->admin))->toBeTrue();
});

test('seller owner can update own products', function () {
    $product = Product::factory()->create(['seller_id' => $this->seller->id]);

    expect($this->policy->update($this->user, $product))->toBeTrue();
});

test('user cannot update another sellers products', function () {
    $otherSeller = Seller::factory()->create();
    $product = Product::factory()->create(['seller_id' => $otherSeller->id]);

    expect($this->policy->update($this->user, $product))->toBeFalse();
});

test('admin can update any product', function () {
    $product = Product::factory()->create(['seller_id' => $this->seller->id]);

    expect($this->policy->update($this->admin, $product))->toBeTrue();
});

test('seller owner can delete own products', function () {
    $product = Product::factory()->create(['seller_id' => $this->seller->id]);

    expect($this->policy->delete($this->user, $product))->toBeTrue();
});

test('user cannot delete another sellers products', function () {
    $otherSeller = Seller::factory()->create();
    $product = Product::factory()->create(['seller_id' => $otherSeller->id]);

    expect($this->policy->delete($this->user, $product))->toBeFalse();
});

test('admin can delete any product', function () {
    $product = Product::factory()->create(['seller_id' => $this->seller->id]);

    expect($this->policy->delete($this->admin, $product))->toBeTrue();
});

test('seller owner can publish own draft products', function () {
    $product = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'status' => 'draft',
    ]);

    expect($this->policy->publish($this->user, $product))->toBeTrue();
});

test('user cannot publish another sellers products', function () {
    $otherSeller = Seller::factory()->create();
    $product = Product::factory()->create([
        'seller_id' => $otherSeller->id,
        'status' => 'draft',
    ]);

    expect($this->policy->publish($this->user, $product))->toBeFalse();
});

test('admin can publish any product', function () {
    $product = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'status' => 'draft',
    ]);

    expect($this->policy->publish($this->admin, $product))->toBeTrue();
});

test('seller owner can unpublish own products', function () {
    $product = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'status' => 'published',
    ]);

    expect($this->policy->unpublish($this->user, $product))->toBeTrue();
});

test('user cannot unpublish another sellers products', function () {
    $otherSeller = Seller::factory()->create();
    $product = Product::factory()->create([
        'seller_id' => $otherSeller->id,
        'status' => 'published',
    ]);

    expect($this->policy->unpublish($this->user, $product))->toBeFalse();
});

test('admin can unpublish any product', function () {
    $product = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'status' => 'published',
    ]);

    expect($this->policy->unpublish($this->admin, $product))->toBeTrue();
});

test('seller owner can manage own product stock', function () {
    $product = Product::factory()->create(['seller_id' => $this->seller->id]);

    expect($this->policy->manageStock($this->user, $product))->toBeTrue();
});

test('user cannot manage another sellers product stock', function () {
    $otherSeller = Seller::factory()->create();
    $product = Product::factory()->create(['seller_id' => $otherSeller->id]);

    expect($this->policy->manageStock($this->user, $product))->toBeFalse();
});

test('admin can manage any product stock', function () {
    $product = Product::factory()->create(['seller_id' => $this->seller->id]);

    expect($this->policy->manageStock($this->admin, $product))->toBeTrue();
});

test('suspended seller cannot update products', function () {
    $suspendedSeller = Seller::factory()->create([
        'status' => 'suspended',
        'approved_at' => now(),
    ]);
    $product = Product::factory()->create(['seller_id' => $suspendedSeller->id]);

    expect($this->policy->update($suspendedSeller->user, $product))->toBeFalse();
});

test('suspended seller cannot create products', function () {
    $suspendedSeller = Seller::factory()->create([
        'status' => 'suspended',
        'approved_at' => now(),
    ]);

    expect($this->policy->create($suspendedSeller->user))->toBeFalse();
});

test('seller can view own products list', function () {
    expect($this->policy->viewAny($this->user))->toBeTrue();
});

test('admin can view all products list', function () {
    expect($this->policy->viewAny($this->admin))->toBeTrue();
});

test('customer can view published products list', function () {
    $customer = User::factory()->create();
    $customer->assignRole('customer');

    expect($this->policy->viewAny($customer))->toBeTrue();
});

test('guest can view published products list', function () {
    expect($this->policy->viewAny(null))->toBeTrue();
});
