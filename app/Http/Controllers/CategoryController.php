<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of all active categories.
     */
    public function index(Request $request)
    {
        $query = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->withCount(['products' => function ($q) {
                $q->availableForMarketplace();
            }])
            ->with(['children' => function ($q) {
                $q->where('is_active', true)
                  ->withCount(['products' => function ($q) {
                      $q->availableForMarketplace();
                  }]);
            }]);

        // Search by name
        if ($search = $request->input('q')) {
            $query->where('name', 'LIKE', "%{$search}%");
        }

        $categories = $query->orderBy('order')->paginate(20)->withQueryString();

        return view('categories.index', compact('categories'));
    }

    /**
     * Display the specified category with its products.
     */
    public function show(Request $request, Category $category)
    {
        // Check if category is active
        if (!$category->is_active) {
            abort(404, 'Categoria não encontrada.');
        }

        // Get products for this category
        $products = $category->products()
            ->availableForMarketplace()
            ->inStock()
            ->with(['seller.media', 'media'])
            ->latest()
            ->paginate(24)
            ->withQueryString();

        // Get related categories (same level)
        $relatedCategories = Category::where('is_active', true)
            ->where('parent_id', $category->parent_id)
            ->where('id', '!=', $category->id)
            ->withCount(['products' => function ($q) {
                $q->availableForMarketplace();
            }])
            ->take(6)
            ->get();

        return view('categories.show', compact('category', 'products', 'relatedCategories'));
    }
}
