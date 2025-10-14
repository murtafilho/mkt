<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Seller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * API endpoint for search suggestions (autocomplete).
     * Returns products and sellers matching the query.
     */
    public function suggestions(Request $request): JsonResponse
    {
        $query = $request->input('q', '');

        // Minimum 2 characters to search
        if (strlen($query) < 2) {
            return response()->json([
                'products' => [],
                'sellers' => [],
            ]);
        }

        // Search products (limit 5)
        $products = Product::with(['category', 'seller', 'media'])
            ->availableForMarketplace()
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('description', 'LIKE', "%{$query}%");
            })
            ->limit(5)
            ->get()
            ->map(function ($product) {
                return [
                    'type' => 'product',
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'category' => $product->category->name ?? '',
                    'thumbnail' => $product->getFirstMediaUrl('images', 'thumb'),
                    'price' => $product->sale_price,
                ];
            });

        // Search sellers (limit 3, only approved)
        $sellers = Seller::with(['media', 'addresses'])
            ->where('status', 'active')
            ->whereNotNull('approved_at')
            ->where(function ($q) use ($query) {
                $q->where('store_name', 'LIKE', "%{$query}%")
                    ->orWhere('description', 'LIKE', "%{$query}%");
            })
            ->limit(3)
            ->get()
            ->map(function ($seller) {
                /** @var \App\Models\SellerAddress|null $address */
                $address = $seller->addresses->first();
                $location = '';
                if ($address && isset($address->city) && isset($address->state)) {
                    $location = $address->city . ', ' . $address->state;
                }
                return [
                    'type' => 'seller',
                    'name' => $seller->store_name,
                    'slug' => $seller->slug,
                    'location' => $location,
                ];
            });

        return response()->json([
            'products' => $products,
            'sellers' => $sellers,
        ]);
    }
}

