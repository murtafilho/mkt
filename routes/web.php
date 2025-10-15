<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\SellerController as AdminSellerController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Seller\DashboardController as SellerDashboardController;
use App\Http\Controllers\Seller\OrderController as SellerOrderController;
use App\Http\Controllers\Seller\ProductController as SellerProductController;
use App\Http\Controllers\Seller\ProfileController as SellerProfileController;
use App\Http\Controllers\SellerRegistrationController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

// Search (autocomplete API)
Route::get('/api/search/suggestions', [SearchController::class, 'suggestions'])->name('api.search.suggestions');

// Product catalog (public)
Route::get('/produtos', [ProductController::class, 'index'])->name('products.index');
Route::get('/produtos/{product:slug}', [ProductController::class, 'show'])->name('products.show');

// Categories (public)
Route::get('/categorias', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categorias/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');

// Shopping cart (public)
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [App\Http\Controllers\CartController::class, 'index'])->name('index');
    Route::get('/data', [App\Http\Controllers\CartController::class, 'data'])->name('data');
    Route::post('/add', [App\Http\Controllers\CartController::class, 'add'])->name('add');
    Route::patch('/update/{cartItemId}', [App\Http\Controllers\CartController::class, 'update'])->name('update');
    Route::delete('/remove/{cartItemId}', [App\Http\Controllers\CartController::class, 'remove'])->name('remove');
    Route::delete('/clear', [App\Http\Controllers\CartController::class, 'clear'])->name('clear');
});

// Checkout (authenticated only)
Route::middleware('auth')->prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [App\Http\Controllers\CheckoutController::class, 'index'])->name('index');
    Route::post('/process', [App\Http\Controllers\CheckoutController::class, 'process'])->name('process');
    Route::get('/success', [App\Http\Controllers\CheckoutController::class, 'success'])->name('success');
    Route::get('/failure', [App\Http\Controllers\CheckoutController::class, 'failure'])->name('failure');
    Route::get('/pending', [App\Http\Controllers\CheckoutController::class, 'pending'])->name('pending');
});

/*
|--------------------------------------------------------------------------
| Webhook Routes (public, no CSRF verification)
|--------------------------------------------------------------------------
*/

Route::post('/webhook/mercadopago', [WebhookController::class, 'mercadopago'])->name('webhook.mercadopago');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    // Dashboard: redirect based on role
    Route::get('/dashboard', function () {
        $user = auth()->user();

        // Admin redirect
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        // Seller redirect
        if ($user->hasRole('seller')) {
            return redirect()->route('seller.dashboard');
        }

        // Customer: redirect to home
        return redirect()->route('home');
    })->middleware('verified')->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Seller registration (authenticated users only)
    Route::get('/become-seller', [SellerRegistrationController::class, 'create'])->name('seller.register');
    Route::post('/become-seller', [SellerRegistrationController::class, 'store'])->name('seller.store');
});

/*
|--------------------------------------------------------------------------
| Customer Routes (requires authentication)
|--------------------------------------------------------------------------
*/

Route::prefix('meus-pedidos')->name('customer.orders.')->middleware('auth')->group(function () {
    Route::get('/', [CustomerOrderController::class, 'index'])->name('index');
    Route::get('/{order}', [CustomerOrderController::class, 'show'])->name('show');
    Route::post('/{order}/cancelar', [CustomerOrderController::class, 'cancel'])->name('cancel');
});

/*
|--------------------------------------------------------------------------
| Seller Routes (requires seller role - can access before approval)
|--------------------------------------------------------------------------
*/

Route::prefix('seller')->name('seller.')->middleware(['auth', 'seller'])->group(function () {
    Route::get('/dashboard', [SellerDashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [SellerProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [SellerProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [SellerProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/images/{media}', [SellerProfileController::class, 'deleteImage'])->name('profile.deleteImage');

    // Product management
    Route::resource('products', SellerProductController::class)->except(['show']);
    Route::post('products/{product}/publish', [SellerProductController::class, 'publish'])->name('products.publish');
    Route::post('products/{product}/unpublish', [SellerProductController::class, 'unpublish'])->name('products.unpublish');
    Route::delete('products/{product}/images/{media}', [SellerProductController::class, 'deleteImage'])->name('products.deleteImage');

    // Order management
    Route::get('/orders', [SellerOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [SellerOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/update-status', [SellerOrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::post('/orders/{order}/cancel', [SellerOrderController::class, 'cancel'])->name('orders.cancel');
});

/*
|--------------------------------------------------------------------------
| Admin Routes (requires admin role)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Category management
    Route::resource('categories', AdminCategoryController::class);
    Route::post('categories/{category}/toggle', [AdminCategoryController::class, 'toggleStatus'])->name('categories.toggle');

    // Seller management
    // Bulk actions MUST come BEFORE resource routes (to avoid route model binding conflict)
    Route::post('sellers/bulk/approve', [AdminSellerController::class, 'bulkApprove'])->name('sellers.bulk.approve');
    Route::post('sellers/bulk/suspend', [AdminSellerController::class, 'bulkSuspend'])->name('sellers.bulk.suspend');

    Route::resource('sellers', AdminSellerController::class)->only(['index', 'show']);
    Route::post('sellers/{seller}/approve', [AdminSellerController::class, 'approve'])->name('sellers.approve');
    Route::post('sellers/{seller}/suspend', [AdminSellerController::class, 'suspend'])->name('sellers.suspend');
    Route::post('sellers/{seller}/reactivate', [AdminSellerController::class, 'reactivate'])->name('sellers.reactivate');

    // Order management
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/update-status', [AdminOrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::delete('/orders/{order}/cancel', [AdminOrderController::class, 'cancel'])->name('orders.cancel');

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [AdminReportController::class, 'index'])->name('index');
        Route::get('/sales', [AdminReportController::class, 'sales'])->name('sales');
        Route::get('/sales/export', [AdminReportController::class, 'exportSales'])->name('sales.export');
        Route::get('/products', [AdminReportController::class, 'products'])->name('products');
        Route::get('/sellers', [AdminReportController::class, 'sellers'])->name('sellers');
    });

    // Site Settings
    Route::get('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');
    Route::delete('/settings/hero-image', [App\Http\Controllers\Admin\SettingsController::class, 'deleteHeroImage'])->name('settings.deleteHeroImage');
});

/*
|--------------------------------------------------------------------------
| Seller Public Routes (must be last to avoid catching /seller/dashboard)
|--------------------------------------------------------------------------
*/

Route::get('/seller/{slug}', [SellerRegistrationController::class, 'show'])->name('seller.show');

require __DIR__.'/auth.php';
