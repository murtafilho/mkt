<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Seller;
use App\Models\Setting;
use App\Policies\CategoryPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use App\Policies\SellerPolicy;
use App\Policies\SettingPolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Product::class => ProductPolicy::class,
        Seller::class => SellerPolicy::class,
        Order::class => OrderPolicy::class,
        Category::class => CategoryPolicy::class,
        Setting::class => SettingPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Use Bootstrap 5 pagination views
        Paginator::useBootstrapFive();

        // Register policies
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }

        // Share header categories with public layout
        View::composer('components.layouts.public', function ($view) {
            $headerCategories = Category::where('is_active', true)
                ->whereNull('parent_id')
                ->orderBy('order')
                ->take(6)
                ->get();

            $view->with('headerCategories', $headerCategories);
        });

        // Share logo settings with header and dashboard layouts
        View::composer(['layouts.partials.header', 'layouts.admin', 'layouts.seller'], function ($view) {
            $settingsService = app(\App\Services\SettingsService::class);

            $logoSettings = [
                'logo_svg' => $settingsService->get('logo_svg', ''),
                'logo_width' => $settingsService->get('logo_width', '180'),
                'logo_height' => $settingsService->get('logo_height', '60'),
                'site_name' => $settingsService->get('site_name', config('app.name')),
            ];

            $view->with('logoSettings', $logoSettings);
        });
    }
}
