<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAddress extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'recipient_name',
        'label',
        'postal_code',
        'street',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'reference',
        'is_default',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Get the user that owns the address.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        // Garante que apenas um endereço seja padrão por usuário
        static::saving(function (UserAddress $address) {
            if ($address->is_default) {
                // Remove is_default de outros endereços do mesmo usuário
                static::where('user_id', $address->user_id)
                    ->where('id', '!=', $address->id ?? 0)
                    ->update(['is_default' => false]);
            }
        });

        // Se é o primeiro endereço do usuário, marca como padrão automaticamente
        static::creating(function (UserAddress $address) {
            if ($address->user_id) {
                $hasOtherAddresses = static::where('user_id', $address->user_id)->exists();
                if (! $hasOtherAddresses && ! isset($address->is_default)) {
                    $address->is_default = true;
                }
            }
        });
    }
}
