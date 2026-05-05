<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceCheck extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'wishlist_item_id',
        'source',
        'store_name',
        'price',
        'url',
        'found_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'found_at' => 'datetime',
        ];
    }

    public function wishlistItem(): BelongsTo
    {
        return $this->belongsTo(WishlistItem::class);
    }
}
