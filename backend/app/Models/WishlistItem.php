<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WishlistItem extends Model
{
    public const STATUS_WAITING = 'waiting';
    public const STATUS_READY_TO_BUY = 'ready_to_buy';
    public const STATUS_PURCHASED = 'purchased';
    public const STATUS_ABANDONED = 'abandoned';

    protected $fillable = [
        'user_id',
        'name',
        'target_price',
        'current_price',
        'reference_url',
        'photo_path',
        'priority',
        'category_id',
        'quarantine_days',
        'status',
        'purchased_transaction_id',
        'abandoned_at',
        'last_review_prompt_at',
    ];

    protected function casts(): array
    {
        return [
            'target_price' => 'decimal:2',
            'current_price' => 'decimal:2',
            'priority' => 'integer',
            'quarantine_days' => 'integer',
            'abandoned_at' => 'datetime',
            'last_review_prompt_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function purchasedTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'purchased_transaction_id');
    }

    public function priceChecks(): HasMany
    {
        return $this->hasMany(PriceCheck::class);
    }
}
