<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderAddress extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should use updated_at timestamp.
     */
    public const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'postal_code',
        'street',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'recipient_name',
        'recipient_phone',
        'reference',
        'contact_phone',
    ];

    /**
     * Get the order that owns the address.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
