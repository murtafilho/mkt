<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Seller extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'store_name',
        'slug',
        'description',
        'document_number',
        'person_type',
        'company_name',
        'trade_name',
        'state_registration',
        'business_phone',
        'business_email',
        'commission_percentage',
        'mercadopago_account_id',
        'status',
        'approved_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'commission_percentage' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the user that owns the seller.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the addresses for the seller.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(SellerAddress::class, 'seller_id');
    }

    /**
     * Get the products for the seller.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'seller_id');
    }

    /**
     * Get the orders for the seller.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'seller_id');
    }

    /**
     * Get the payments for the seller.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(SellerPayment::class, 'seller_id');
    }

    /**
     * Scope a query to only include approved sellers.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'active')
            ->whereNotNull('approved_at');
    }

    /**
     * Check if seller is approved and active.
     */
    public function isApproved(): bool
    {
        return $this->status === 'active' && $this->approved_at !== null;
    }

    /**
     * Check if seller is pending approval.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if seller is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Check if seller is inactive.
     */
    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }

    /**
     * Check if seller can be approved.
     * Must have business address.
     */
    public function canBeApproved(): bool
    {
        // Check if has business address
        $hasAddress = $this->addresses()->where('type', 'business')->exists();

        return $hasAddress && $this->isPending();
    }

    /**
     * Get seller's business address.
     *
     * @return SellerAddress|null
     */
    public function getBusinessAddress()
    {
        /** @var SellerAddress|null */
        return $this->addresses()->where('type', 'business')->first();
    }

    /**
     * Get status label in Portuguese.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pendente',
            'active' => 'Ativo',
            'inactive' => 'Inativo',
            'suspended' => 'Suspenso',
        };
    }

    /**
     * Register media collections for seller.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('seller_logo')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->singleFile()
            ->withResponsiveImages() // Generate srcset automatically
            ->useFallbackUrl('/images/seller-logo-placeholder.png')
            ->useFallbackPath(public_path('/images/seller-logo-placeholder.png'));

        $this->addMediaCollection('seller_banner')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->singleFile()
            ->withResponsiveImages() // Generate srcset automatically
            ->useFallbackUrl('/images/seller-banner-placeholder.png')
            ->useFallbackPath(public_path('/images/seller-banner-placeholder.png'));
    }

    /**
     * Register media conversions for seller images.
     */
    public function registerMediaConversions(?\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {
        // Logo conversions (1:1 aspect ratio - 400x400px ideal)
        $this
            ->addMediaConversion('thumb')
            ->performOnCollections('seller_logo')
            ->width(100)
            ->height(100)
            ->format('webp')
            ->sharpen(10);

        $this
            ->addMediaConversion('medium')
            ->performOnCollections('seller_logo')
            ->width(200)
            ->height(200)
            ->format('webp')
            ->sharpen(10);

        $this
            ->addMediaConversion('large')
            ->performOnCollections('seller_logo')
            ->width(400)
            ->height(400)
            ->format('webp')
            ->sharpen(10);

        // Banner conversions (16:9 aspect ratio - 1920x480px ideal)
        $this
            ->addMediaConversion('thumb')
            ->performOnCollections('seller_banner')
            ->width(320)
            ->height(180)
            ->format('webp')
            ->sharpen(10);

        $this
            ->addMediaConversion('medium')
            ->performOnCollections('seller_banner')
            ->width(960)
            ->height(540)
            ->format('webp')
            ->sharpen(10);

        $this
            ->addMediaConversion('large')
            ->performOnCollections('seller_banner')
            ->width(1920)
            ->height(1080)
            ->format('webp')
            ->sharpen(10);

        // Preview conversion for both
        $this
            ->addMediaConversion('preview')
            ->performOnCollections('seller_logo', 'seller_banner')
            ->width(300)
            ->height(300)
            ->sharpen(5);
    }
}
