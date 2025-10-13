<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, InteractsWithMedia, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'cpf_cnpj',
        'phone',
        'birth_date',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
        ];
    }

    /**
     * Get user's first name for Mercado Pago.
     */
    public function getFirstName(): string
    {
        $nameParts = explode(' ', $this->name);

        return $nameParts[0];
    }

    /**
     * Get user's last name for Mercado Pago.
     */
    public function getLastName(): string
    {
        $nameParts = explode(' ', $this->name);
        array_shift($nameParts);

        return implode(' ', $nameParts) ?: $this->name;
    }

    /**
     * Get phone area code (DDD) for Mercado Pago.
     */
    public function getAreaCode(): ?string
    {
        if (! $this->phone) {
            return null;
        }

        // Extract DDD from phone (11987654321 -> 11)
        return substr(preg_replace('/\D/', '', $this->phone), 0, 2);
    }

    /**
     * Get phone number without area code for Mercado Pago.
     */
    public function getPhoneNumber(): ?string
    {
        if (! $this->phone) {
            return null;
        }

        // Extract number without DDD (11987654321 -> 987654321)
        $cleaned = preg_replace('/\D/', '', $this->phone);

        return substr($cleaned, 2);
    }

    /**
     * Get the seller profile for the user.
     */
    public function seller(): HasOne
    {
        return $this->hasOne(Seller::class, 'user_id');
    }

    /**
     * Get the addresses for the user.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    /**
     * Get the orders for the user.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the cart items for the user.
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Register media collections for user.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->singleFile();
    }
}
