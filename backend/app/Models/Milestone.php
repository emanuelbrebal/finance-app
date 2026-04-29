<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Milestone extends Model
{
    public $updatedAt = false;

    public const TIER_SMALL = 'small';
    public const TIER_MEDIUM = 'medium';
    public const TIER_LARGE = 'large';
    public const TIER_EPIC = 'epic';

    protected $fillable = [
        'user_id',
        'type',
        'tier',
        'title',
        'body',
        'payload',
        'dedup_key',
        'achieved_at',
        'celebrated_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'achieved_at' => 'datetime',
            'celebrated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isCelebrated(): bool
    {
        return $this->celebrated_at !== null;
    }
}
