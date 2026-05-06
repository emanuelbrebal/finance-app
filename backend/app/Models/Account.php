<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Account extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'user_id'];

    protected function casts(): array
    {
        return [
            'initial_balance' => 'decimal:2',
            'archived_at' => 'datetime',
        ];
    }

    protected $attributes = [
        'currency' => 'BRL',
        'initial_balance' => 0,
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
}
