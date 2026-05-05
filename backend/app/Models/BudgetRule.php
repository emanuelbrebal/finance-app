<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetRule extends Model
{
    public const KIND_CATEGORY_MONTHLY_CAP = 'category_monthly_cap';
    public const KIND_DAILY_NONESSENTIAL_CAP = 'daily_nonessential_cap';

    protected $fillable = [
        'user_id',
        'kind',
        'category_id',
        'amount',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'active' => 'boolean',
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
}
