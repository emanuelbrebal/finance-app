<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Streak extends Model
{
    public const KIND_WEEKLY_LOGGING = 'weekly_logging';
    public const KIND_POSITIVE_MONTHS = 'positive_months';

    protected $fillable = [
        'user_id',
        'kind',
        'current_count',
        'best_count',
        'current_started_on',
        'last_extended_on',
    ];

    protected function casts(): array
    {
        return [
            'current_count' => 'integer',
            'best_count' => 'integer',
            'current_started_on' => 'date',
            'last_extended_on' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
