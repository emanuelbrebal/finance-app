<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends Model
{
    use HasFactory;

    public const KIND_INCOME = 'income';

    public const KIND_EXPENSE = 'expense';

    protected $guarded = ['id', 'user_id'];

    protected function casts(): array
    {
        return [
            'is_essential' => 'boolean',
            'monthly_budget' => 'decimal:2',
            'archived_at' => 'datetime',
        ];
    }

    protected $attributes = [
        'is_essential' => true,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    public function scopeOfKind($query, string $kind)
    {
        return $query->where('kind', $kind);
    }
}
