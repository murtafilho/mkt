<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;

class HomeController extends Controller
{
    /**
     * Display the marketplace home page.
     */
    public function index()
    {
        // Get featured products (published, from approved sellers) with images
        $featuredProducts = Product::with(['seller.media', 'category', 'media'])
            ->availableForMarketplace()
            ->featured()
            ->inStock()
            ->latest()
            ->take(8)
            ->get();

        // Get active parent categories with product count (for grid)
        $mainCategories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->withCount(['products' => function ($query) {
                $query->availableForMarketplace();
            }])
            ->orderBy('order')
            ->take(8)  // 8 categorias na grid
            ->get();

        // Get latest products with images
        $latestProducts = Product::with(['seller.media', 'category', 'media'])
            ->availableForMarketplace()
            ->inStock()
            ->latest()
            ->take(12)
            ->get();

        // Stats for stats bar
        $stats = [
            'sellers_count' => \App\Models\Seller::where('status', 'active')->count(),
            'products_count' => Product::published()->count(),
        ];

        return view('home', compact('featuredProducts', 'mainCategories', 'latestProducts', 'stats'));
    }
}
