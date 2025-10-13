<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use App\Services\SellerService;

beforeEach(function () {
    // Seed roles and permissions
    $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

    $this->sellerService = new SellerService;
    $this->user = User::factory()->create();
});

test('can create seller with valid data', function () {
    $data = [
        'store_name' => 'Loja Teste',
        'slug' => 'loja-teste',
        'description' => 'Descrição da loja',
        'document_number' => '12345678000199',
        'person_type' => 'business',
        'business_phone' => '11987654321',
        'business_email' => 'contato@loja.com',
    ];

    $seller = $this->sellerService->createSeller($this->user, $data);

    expect($seller)->toBeInstanceOf(Seller::class);
    expect($seller->user_id)->toBe($this->user->id);
    expect($seller->store_name)->toBe('Loja Teste');
    expect($seller->status)->toBe('pending');
});

test('throws exception when user already has seller', function () {
    Seller::factory()->create(['user_id' => $this->user->id]);

    $data = [
        'store_name' => 'Nova Loja',
        'slug' => 'nova-loja',
        'document_number' => '12345678000199',
        'person_type' => 'business',
        'business_phone' => '11987654321',
        'business_email' => 'contato@loja.com',
    ];

    expect(fn () => $this->sellerService->createSeller($this->user, $data))
        ->toThrow(Exception::class, 'Usuário já possui cadastro como vendedor');
});

test('can update seller information', function () {
    $seller = Seller::factory()->create([
        'user_id' => $this->user->id,
        'store_name' => 'Loja Original',
    ]);

    $data = [
        'store_name' => 'Loja Atualizada',
        'description' => 'Nova descrição',
    ];

    $updated = $this->sellerService->updateSeller($seller, $data);

    expect($updated->store_name)->toBe('Loja Atualizada');
    expect($updated->description)->toBe('Nova descrição');
});

test('can approve seller', function () {
    $seller = Seller::factory()->create(['status' => 'pending']);

    $approved = $this->sellerService->approveSeller($seller);

    expect($approved->status)->toBe('active');
    expect($approved->approved_at)->not->toBeNull();
});

test('can reject seller', function () {
    $seller = Seller::factory()->create(['status' => 'pending']);

    $rejected = $this->sellerService->rejectSeller($seller);

    expect($rejected->status)->toBe('inactive');
    expect($rejected->approved_at)->toBeNull();
});

test('can suspend seller', function () {
    $seller = Seller::factory()->create(['status' => 'active']);

    $suspended = $this->sellerService->suspendSeller($seller);

    expect($suspended->status)->toBe('suspended');
});

test('can reactivate seller', function () {
    $seller = Seller::factory()->create(['status' => 'suspended']);

    $reactivated = $this->sellerService->reactivateSeller($seller);

    expect($reactivated->status)->toBe('active');
});

test('can get approved sellers', function () {
    Seller::factory()->count(3)->create(['status' => 'active', 'approved_at' => now()]);
    Seller::factory()->count(2)->create(['status' => 'pending']);

    $approved = $this->sellerService->getApprovedSellers();

    expect($approved)->toHaveCount(3);
    expect($approved->first()->status)->toBe('active');
});

test('can get pending sellers', function () {
    Seller::factory()->count(3)->create(['status' => 'pending']);
    Seller::factory()->count(2)->create(['status' => 'active']);

    $pending = $this->sellerService->getPendingSellers();

    expect($pending)->toHaveCount(3);
    expect($pending->first()->status)->toBe('pending');
});

test('can get seller by slug', function () {
    $seller = Seller::factory()->create(['slug' => 'loja-unica']);

    $found = $this->sellerService->getSellerBySlug('loja-unica');

    expect($found)->toBeInstanceOf(Seller::class);
    expect($found->id)->toBe($seller->id);
});

test('returns null when seller slug not found', function () {
    $found = $this->sellerService->getSellerBySlug('slug-inexistente');

    expect($found)->toBeNull();
});

test('can check if seller is approved', function () {
    $approvedSeller = Seller::factory()->create([
        'status' => 'active',
        'approved_at' => now(),
    ]);
    $pendingSeller = Seller::factory()->create(['status' => 'pending']);

    expect($this->sellerService->isApproved($approvedSeller))->toBeTrue();
    expect($this->sellerService->isApproved($pendingSeller))->toBeFalse();
});

test('can calculate total sales for seller', function () {
    $seller = Seller::factory()->create();

    Order::factory()->create([
        'seller_id' => $seller->id,
        'total' => 100.00,
        'status' => 'paid',
    ]);
    Order::factory()->create([
        'seller_id' => $seller->id,
        'total' => 200.00,
        'status' => 'paid',
    ]);
    Order::factory()->create([
        'seller_id' => $seller->id,
        'total' => 150.00,
        'status' => 'awaiting_payment', // Not paid, shouldn't count
    ]);

    $total = $this->sellerService->calculateTotalSales($seller);

    expect($total)->toBe('300.00');
});

test('can calculate total earnings for seller', function () {
    $seller = Seller::factory()->create(['commission_percentage' => 10.00]);

    Order::factory()->create([
        'seller_id' => $seller->id,
        'total' => 100.00,
        'status' => 'paid',
    ]);
    Order::factory()->create([
        'seller_id' => $seller->id,
        'total' => 200.00,
        'status' => 'paid',
    ]);

    // Total sales: 300.00
    // Commission (10%): 30.00
    // Earnings: 300.00 - 30.00 = 270.00

    $earnings = $this->sellerService->calculateEarnings($seller);

    expect($earnings)->toBe('270.00');
});

test('can get seller products count', function () {
    $seller = Seller::factory()->create();

    Product::factory()->count(5)->create(['seller_id' => $seller->id]);

    $count = $this->sellerService->getProductsCount($seller);

    expect($count)->toBe(5);
});

test('can get seller orders count', function () {
    $seller = Seller::factory()->create();

    Order::factory()->count(3)->create(['seller_id' => $seller->id]);

    $count = $this->sellerService->getOrdersCount($seller);

    expect($count)->toBe(3);
});

test('can get seller sales report', function () {
    $seller = Seller::factory()->create(['commission_percentage' => 10.00]);

    Order::factory()->count(2)->create([
        'seller_id' => $seller->id,
        'total' => 100.00,
        'status' => 'paid',
    ]);

    $report = $this->sellerService->getSalesReport($seller);

    expect($report)->toHaveKeys(['total_sales', 'total_orders', 'total_earnings', 'commission_paid']);
    expect($report['total_orders'])->toBe(2);
    expect($report['total_sales'])->toBe('200.00');
    expect($report['total_earnings'])->toBe('180.00'); // 200 - 20 (10% commission)
    expect($report['commission_paid'])->toBe('20.00');
});
