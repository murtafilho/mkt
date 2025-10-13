<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'seller_id',
        'category_id',
        'name',
        'slug',
        'description',
        'short_description',
        'sku',
        'cost_price',
        'original_price',
        'sale_price',
        'stock',
        'min_stock',
        'weight',
        'width',
        'height',
        'depth',
        'has_variations',
        'is_featured',
        'status',
        'views',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'cost_price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'depth' => 'decimal:2',
        'has_variations' => 'boolean',
        'is_featured' => 'boolean',
        'stock' => 'integer',
        'min_stock' => 'integer',
        'views' => 'integer',
    ];

    /**
     * Get the seller that owns the product.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'seller_id');
    }

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the variations for the product.
     */
    public function variations(): HasMany
    {
        return $this->hasMany(ProductVariation::class);
    }

    /**
     * Get the order items for the product.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the cart items for the product.
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Scope a query to only include available products for marketplace.
     * Only published products from approved sellers.
     */
    public function scopeAvailableForMarketplace($query)
    {
        return $query->where('products.status', 'published')
            ->whereHas('seller', function ($q) {
                $q->where('status', 'active')
                    ->whereNotNull('approved_at');
            });
    }

    /**
     * Scope a query to only include published products.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope a query to only include products with stock.
     */
    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    /**
     * Scope a query to only include featured products.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Check if product is available for purchase.
     * Must be published, have stock, and seller must be approved.
     */
    public function isAvailable(): bool
    {
        /** @var \App\Models\Seller|null $seller */
        $seller = $this->seller;

        return $this->status === 'published'
            && $this->stock > 0
            && $seller !== null
            && $seller->isApproved();
    }

    /**
     * Check if product can be displayed in marketplace.
     * Must be published and seller must be approved.
     */
    public function canBeDisplayed(): bool
    {
        /** @var \App\Models\Seller|null $seller */
        $seller = $this->seller;

        return $this->status === 'published'
            && $seller !== null
            && $seller->isApproved();
    }

    /**
     * Register media collections for product.
     * Products can have 0-4 images. The first image is considered the main/featured image.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('product_images')
            ->acceptsMimeTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp'])
            ->useFallbackUrl('/images/product-placeholder.png')
            ->useFallbackPath(public_path('/images/product-placeholder.png'));
    }

    /**
     * Register media conversions for product images.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        // Product images (1:1 aspect ratio - 2048x2048px ideal)
        $this->addMediaConversion('thumb')
            ->performOnCollections('product_images')
            ->width(150)
            ->height(150)
            ->format('webp')
            ->sharpen(10);

        $this->addMediaConversion('medium')
            ->performOnCollections('product_images')
            ->width(600)
            ->height(600)
            ->format('webp')
            ->sharpen(10);

        $this->addMediaConversion('large')
            ->performOnCollections('product_images')
            ->width(1200)
            ->height(1200)
            ->format('webp')
            ->sharpen(10);
    }

    /**
     * Get the main (first) image of the product.
     */
    public function getMainImage(): ?Media
    {
        return $this->getFirstMedia('product_images');
    }

    /**
     * Check if product has at least one image.
     */
    public function hasImages(): bool
    {
        return $this->getMedia('product_images')->isNotEmpty();
    }

    /**
     * Check if product can be published.
     * A product requires at least one image to be published.
     */
    public function canBePublished(): bool
    {
        return $this->hasImages();
    }

    /**
     * Get discount percentage.
     * Calculate discount from original price to sale price.
     */
    public function getDiscountPercentAttribute(): float
    {
        if ($this->original_price <= 0 || $this->sale_price >= $this->original_price) {
            return 0.0;
        }

        $discount = (($this->original_price - $this->sale_price) / $this->original_price) * 100;

        return round($discount, 1);
    }

    /**
     * Check if product has discount.
     */
    public function hasDiscount(): bool
    {
        return $this->sale_price < $this->original_price;
    }
}
