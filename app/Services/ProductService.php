<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Seller;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductService
{
    /**
     * Get products available for marketplace (published + approved sellers).
     */
    public function getMarketplaceProducts(array $filters = [], int $perPage = 24): LengthAwarePaginator
    {
        $query = Product::availableForMarketplace()
            ->with(['seller', 'category'])
            ->inStock();

        // Filter by category
        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Filter by search term
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by price range
        if (! empty($filters['min_price'])) {
            $query->where('sale_price', '>=', $filters['min_price']);
        }

        if (! empty($filters['max_price'])) {
            $query->where('sale_price', '<=', $filters['max_price']);
        }

        // Filter by featured
        if (! empty($filters['featured'])) {
            $query->featured();
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        $allowedSorts = ['created_at', 'sale_price', 'name', 'views'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get seller's products.
     */
    public function getSellerProducts(Seller $seller, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Product::where('seller_id', $seller->id)
            ->with(['category']);

        // Filter by status
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by search
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new product.
     */
    public function createProduct(Seller|array $sellerOrData, ?array $data = null): Product
    {
        // Support both old signature (Seller, array) and new signature (array)
        if ($sellerOrData instanceof Seller) {
            $seller = $sellerOrData;
            $productData = $data ?? [];
            $productData['seller_id'] = $seller->id;
        } else {
            $productData = $sellerOrData;
        }

        return DB::transaction(function () use ($productData) {
            $sellerId = $productData['seller_id'] ?? 1;

            // Generate slug if not provided
            if (empty($productData['slug']) && ! empty($productData['name'])) {
                $productData['slug'] = $this->generateUniqueSlug($productData['name'], $sellerId);
            }

            // Generate SKU if not provided (required by database)
            if (empty($productData['sku'])) {
                $productData['sku'] = $this->generateUniqueSku($sellerId);
            }

            // Set default status
            if (! isset($productData['status'])) {
                $productData['status'] = 'draft';
            }

            $product = Product::create($productData);

            return $product;
        });
    }

    /**
     * Update a product.
     */
    public function updateProduct(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            // Update slug if name changed
            if (! empty($data['name']) && $data['name'] !== $product->name) {
                $data['slug'] = $this->generateUniqueSlug($data['name'], $product->seller_id, $product->id);
            }

            $product->update($data);

            return $product->fresh();
        });
    }

    /**
     * Publish a product.
     */
    public function publishProduct(Product $product): Product
    {
        // Check if product can be published
        if ($product->stock <= 0) {
            throw new \Exception('Não é possível publicar produto sem estoque.');
        }

        if (! $product->hasImages()) {
            throw new \Exception('Não é possível publicar produto sem pelo menos uma imagem.');
        }

        /** @var \App\Models\Seller $seller */
        $seller = $product->seller;

        if (! $seller->isApproved()) {
            throw new \Exception('Não é possível publicar produto de vendedor não aprovado.');
        }

        if ($seller->status !== 'active') {
            throw new \Exception('Não é possível publicar produto de vendedor inativo.');
        }

        $product->update(['status' => 'published']);

        return $product->fresh();
    }

    /**
     * Unpublish a product (set to draft).
     */
    public function unpublishProduct(Product $product): Product
    {
        $product->update(['status' => 'draft']);

        return $product->fresh();
    }

    /**
     * Delete a product (soft delete).
     */
    public function deleteProduct(Product $product): bool
    {
        return $product->delete();
    }

    /**
     * Increment product views.
     */
    public function incrementViews(Product $product): void
    {
        $product->increment('views');
    }

    /**
     * Update product stock.
     */
    public function updateStock(Product $product, int $quantity, string $operation = 'increase'): Product
    {
        return DB::transaction(function () use ($product, $quantity, $operation) {
            if ($operation === 'increase') {
                $product->increment('stock', $quantity);
            } elseif ($operation === 'decrease') {
                if ($product->stock < $quantity) {
                    throw new \Exception('Insufficient stock.');
                }

                $product->decrement('stock', $quantity);

                // Update status if out of stock
                if ($product->stock === 0 && $product->status === 'published') {
                    $product->update(['status' => 'out_of_stock']);
                }
            }

            return $product->fresh();
        });
    }

    /**
     * Check if product has sufficient stock.
     */
    public function hasStock(Product $product, int $quantity = 1): bool
    {
        return $product->stock >= $quantity;
    }

    /**
     * Get featured products.
     */
    public function getFeaturedProducts(int $limit = 8): Collection
    {
        return Product::availableForMarketplace()
            ->featured()
            ->inStock()
            ->with(['seller', 'category'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get related products (same category).
     */
    public function getRelatedProducts(Product $product, int $limit = 4): Collection
    {
        return Product::availableForMarketplace()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->inStock()
            ->with(['seller'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get product by ID.
     */
    public function getProductById(int $id): ?Product
    {
        return Product::with(['seller', 'category'])->find($id);
    }

    /**
     * Get product by slug.
     */
    public function getProductBySlug(string $slug): ?Product
    {
        return Product::with(['seller', 'category'])
            ->where('slug', $slug)
            ->first();
    }

    /**
     * Get published products.
     */
    public function getPublishedProducts()
    {
        return Product::where('status', 'published')
            ->with(['seller', 'category'])
            ->latest()
            ->get();
    }

    /**
     * Get products by category.
     */
    public function getProductsByCategory(\App\Models\Category $category)
    {
        return Product::where('category_id', $category->id)
            ->where('status', 'published')
            ->with(['seller'])
            ->latest()
            ->get();
    }

    /**
     * Get products by seller.
     */
    public function getProductsBySeller(\App\Models\Seller $seller)
    {
        return Product::where('seller_id', $seller->id)
            ->with(['category'])
            ->latest()
            ->get();
    }

    /**
     * Search products by name.
     */
    public function searchProducts(string $query)
    {
        return Product::where('status', 'published')
            ->where('name', 'like', "%{$query}%")
            ->with(['seller', 'category'])
            ->latest()
            ->get();
    }

    /**
     * Increase product stock.
     */
    public function increaseStock(Product $product, int $quantity): Product
    {
        $product->increment('stock', $quantity);

        return $product->fresh();
    }

    /**
     * Decrease product stock.
     */
    public function decreaseStock(Product $product, int $quantity): Product
    {
        if ($product->stock < $quantity) {
            throw new \Exception('Estoque insuficiente');
        }

        $product->decrement('stock', $quantity);

        return $product->fresh();
    }

    /**
     * Check if product is in stock.
     */
    public function isInStock(Product $product): bool
    {
        return $product->stock > 0;
    }

    /**
     * Get products on sale.
     */
    public function getProductsOnSale()
    {
        return Product::where('status', 'published')
            ->whereRaw('sale_price < original_price')
            ->with(['seller', 'category'])
            ->latest()
            ->get();
    }

    /**
     * Filter products by price range.
     */
    public function filterByPriceRange(float $minPrice, float $maxPrice)
    {
        return Product::where('status', 'published')
            ->whereBetween('sale_price', [$minPrice, $maxPrice])
            ->with(['seller', 'category'])
            ->latest()
            ->get();
    }

    /**
     * Get latest products.
     */
    public function getLatestProducts(int $limit = 10)
    {
        return Product::where('status', 'published')
            ->with(['seller', 'category'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Calculate discount percentage.
     */
    public function calculateDiscountPercentage(Product $product): float
    {
        if ($product->original_price <= 0 || $product->sale_price >= $product->original_price) {
            return 0.0;
        }

        $discount = (($product->original_price - $product->sale_price) / $product->original_price) * 100;

        return round($discount, 1);
    }

    /**
     * Generate unique slug for product.
     */
    private function generateUniqueSlug(string $name, int $sellerId, ?int $excludeId = null): string
    {
        $slug = str()->slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug, $sellerId, $excludeId)) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug exists for seller.
     */
    private function slugExists(string $slug, int $sellerId, ?int $excludeId = null): bool
    {
        $query = Product::where('seller_id', $sellerId)
            ->where('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Generate unique SKU for seller.
     * Format: SELLER{seller_id}-PROD{timestamp}
     */
    private function generateUniqueSku(int $sellerId): string
    {
        $timestamp = now()->format('YmdHis');
        $sku = "S{$sellerId}-P{$timestamp}";
        $counter = 1;

        // Ensure uniqueness (unlikely to collide, but safe)
        while ($this->skuExists($sku, $sellerId)) {
            $sku = "S{$sellerId}-P{$timestamp}-{$counter}";
            $counter++;
        }

        return $sku;
    }

    /**
     * Check if SKU exists for seller.
     */
    private function skuExists(string $sku, int $sellerId): bool
    {
        return Product::where('seller_id', $sellerId)
            ->where('sku', $sku)
            ->exists();
    }
}
