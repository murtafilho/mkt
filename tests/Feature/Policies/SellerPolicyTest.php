<?php

use App\Models\Seller;
use App\Models\User;
use App\Policies\SellerPolicy;

beforeEach(function () {
    // Seed roles and permissions
    $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

    $this->policy = new SellerPolicy;
    $this->user = User::factory()->create();
    $this->user->assignRole('seller');
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

test('admin can view any seller', function () {
    $seller = Seller::factory()->create();

    expect($this->policy->viewAny($this->admin))->toBeTrue();
});

test('seller cannot view all sellers in admin context', function () {
    $sellerUser = User::factory()->create();
    $sellerUser->assignRole('seller');

    expect($this->policy->viewAny($sellerUser))->toBeFalse();
});

test('customer cannot view any seller in admin context', function () {
    $customer = User::factory()->create();
    $customer->assignRole('customer');

    expect($this->policy->viewAny($customer))->toBeFalse();
});

test('anyone can view individual seller profile', function () {
    $seller = Seller::factory()->create(['status' => 'active', 'approved_at' => now()]);

    expect($this->policy->view(null, $seller))->toBeTrue();
    expect($this->policy->view($this->user, $seller))->toBeTrue();
});

test('seller owner can update own seller', function () {
    $seller = Seller::factory()->create(['user_id' => $this->user->id]);

    expect($this->policy->update($this->user, $seller))->toBeTrue();
});

test('user cannot update another users seller', function () {
    $otherUser = User::factory()->create();
    $seller = Seller::factory()->create(['user_id' => $otherUser->id]);

    expect($this->policy->update($this->user, $seller))->toBeFalse();
});

test('admin can update any seller', function () {
    $seller = Seller::factory()->create();

    expect($this->policy->update($this->admin, $seller))->toBeTrue();
});

test('seller cannot delete own seller', function () {
    $seller = Seller::factory()->create(['user_id' => $this->user->id]);

    expect($this->policy->delete($this->user, $seller))->toBeFalse();
});

test('user cannot delete another users seller', function () {
    $otherUser = User::factory()->create();
    $seller = Seller::factory()->create(['user_id' => $otherUser->id]);

    expect($this->policy->delete($this->user, $seller))->toBeFalse();
});

test('admin can delete any seller', function () {
    $seller = Seller::factory()->create();

    expect($this->policy->delete($this->admin, $seller))->toBeTrue();
});

test('admin can approve seller', function () {
    $seller = Seller::factory()->create(['status' => 'pending']);

    expect($this->policy->approve($this->admin, $seller))->toBeTrue();
});

test('non-admin cannot approve seller', function () {
    $seller = Seller::factory()->create(['status' => 'pending']);

    expect($this->policy->approve($this->user, $seller))->toBeFalse();
});

test('admin can reject seller', function () {
    $seller = Seller::factory()->create(['status' => 'pending']);

    expect($this->policy->reject($this->admin, $seller))->toBeTrue();
});

test('non-admin cannot reject seller', function () {
    $seller = Seller::factory()->create(['status' => 'pending']);

    expect($this->policy->reject($this->user, $seller))->toBeFalse();
});

test('admin can suspend seller', function () {
    $seller = Seller::factory()->create(['status' => 'active']);

    expect($this->policy->suspend($this->admin, $seller))->toBeTrue();
});

test('non-admin cannot suspend seller', function () {
    $seller = Seller::factory()->create(['status' => 'active']);

    expect($this->policy->suspend($this->user, $seller))->toBeFalse();
});

test('seller owner can view own seller orders', function () {
    $seller = Seller::factory()->create(['user_id' => $this->user->id]);

    expect($this->policy->viewOrders($this->user, $seller))->toBeTrue();
});

test('user cannot view another sellers orders', function () {
    $otherUser = User::factory()->create();
    $seller = Seller::factory()->create(['user_id' => $otherUser->id]);

    expect($this->policy->viewOrders($this->user, $seller))->toBeFalse();
});

test('admin can view any seller orders', function () {
    $seller = Seller::factory()->create();

    expect($this->policy->viewOrders($this->admin, $seller))->toBeTrue();
});

test('seller owner can view own sales report', function () {
    $seller = Seller::factory()->create(['user_id' => $this->user->id]);

    expect($this->policy->viewSalesReport($this->user, $seller))->toBeTrue();
});

test('user cannot view another sellers sales report', function () {
    $otherUser = User::factory()->create();
    $seller = Seller::factory()->create(['user_id' => $otherUser->id]);

    expect($this->policy->viewSalesReport($this->user, $seller))->toBeFalse();
});

test('admin can view any seller sales report', function () {
    $seller = Seller::factory()->create();

    expect($this->policy->viewSalesReport($this->admin, $seller))->toBeTrue();
});

test('only approved sellers can access seller dashboard', function () {
    $approvedSeller = Seller::factory()->create([
        'user_id' => $this->user->id,
        'status' => 'active',
        'approved_at' => now(),
    ]);

    $pendingSeller = Seller::factory()->create([
        'status' => 'pending',
    ]);

    expect($this->policy->accessDashboard($this->user, $approvedSeller))->toBeTrue();
    expect($this->policy->accessDashboard($this->user, $pendingSeller))->toBeFalse();
});

test('admin can access any seller dashboard', function () {
    $seller = Seller::factory()->create(['status' => 'pending']);

    expect($this->policy->accessDashboard($this->admin, $seller))->toBeTrue();
});
