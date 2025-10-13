<?php

namespace App\Http\Controllers;

use App\Jobs\IncrementProductViews;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display product catalog with search and filters.
     */
    public function index(Request $request)
    {
        $query = Product::with(['seller.media', 'category', 'media'])
            ->availableForMarketplace()
            ->inStock();

        // Search by name and description (from header search bar)
        if ($search = $request->input('q')) {
            // Order by relevance: name matches first, then description matches
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            })
                ->orderByRaw('CASE
                WHEN name LIKE ? THEN 1
                WHEN description LIKE ? THEN 2
                ELSE 3
            END', ["%{$search}%", "%{$search}%"]);
        }

        // Filter by category
        if ($categoryId = $request->input('category')) {
            $query->where('category_id', $categoryId);
        }

        // Filter by price range
        if ($minPrice = $request->input('min_price')) {
            $query->where('sale_price', '>=', $minPrice);
        }
        if ($maxPrice = $request->input('max_price')) {
            $query->where('sale_price', '<=', $maxPrice);
        }

        // Sorting
        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'price_asc' => $query->orderBy('sale_price', 'asc'),
            'price_desc' => $query->orderBy('sale_price', 'desc'),
            'name' => $query->orderBy('name', 'asc'),
            'popular' => $query->orderBy('views_count', 'desc'),
            default => $query->latest(),
        };

        $products = $query->paginate(24)->withQueryString();

        // Get all parent categories for sidebar filters (only with available products)
        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->withCount(['products' => function ($q) {
                $q->availableForMarketplace();
            }])
            ->whereHas('products', function ($q) {
                $q->availableForMarketplace();
            })
            ->orderBy('name')
            ->get();

        return view('products.index', compact('products', 'categories'));
    }

    /**
     * Display product details.
     */
    public function show(Product $product)
    {
        // Check if product is available for marketplace
        /** @var \App\Models\Seller $seller */
        $seller = $product->seller;

        if ($product->status !== 'published' || ! $seller->isApproved()) {
            abort(404, 'Produto não encontrado ou indisponível.');
        }

        // Eager load relationships
        $product->load(['seller.media', 'category', 'media']);

        // Get related products (same category, different product)
        $relatedProducts = Product::with(['seller.media', 'category', 'media'])
            ->availableForMarketplace()
            ->inStock()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->limit(4)
            ->get();

        // Increment views count asynchronously (non-blocking)
        IncrementProductViews::dispatch($product->id);

        return view('products.show', compact('product', 'relatedProducts'));
    }
}
