<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    public const DIRECTION_IN = 'in';

    public const DIRECTION_OUT = 'out';

    protected $fillable = [
        'user_id',
        'account_id',
        'category_id',
        'occurred_on',
        'description',
        'amount',
        'direction',
        'notes',
        'tags',
        'out_of_scope',
        'dedup_hash',
    ];

    protected function casts(): array
    {
        return [
            'occurred_on' => 'date',
            'amount' => 'decimal:2',
            'tags' => 'array',
            'out_of_scope' => 'boolean',
        ];
    }

    protected $attributes = [
        'tags' => '[]',
        'out_of_scope' => false,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Compute sha256 deduplication hash from the transaction's key fields.
     * Concatenation order: occurred_on|amount|direction|description|account_id
     */
    public static function computeHash(
        string $occurredOn,
        string $amount,
        string $direction,
        string $description,
        int $accountId,
    ): string {
        return hash('sha256', implode('|', [
            $occurredOn,
            $amount,
            $direction,
            $description,
            $accountId,
        ]));
    }
}
