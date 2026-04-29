<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NetWorthSnapshot extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'captured_on',
        'total_assets',
        'total_by_account',
        'monthly_income',
        'monthly_expenses',
        'savings_rate',
    ];

    protected function casts(): array
    {
        return [
            'captured_on' => 'date',
            'total_assets' => 'decimal:2',
            'total_by_account' => 'array',
            'monthly_income' => 'decimal:2',
            'monthly_expenses' => 'decimal:2',
            'savings_rate' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
