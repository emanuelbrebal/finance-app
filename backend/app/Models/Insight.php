<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Insight extends Model
{
    public $updatedAt = false;

    public const SEVERITY_POSITIVE = 'positive';
    public const SEVERITY_INFO = 'info';
    public const SEVERITY_WARNING = 'warning';

    protected $fillable = [
        'user_id',
        'type',
        'severity',
        'title',
        'body',
        'payload',
        'dedup_key',
        'read_at',
        'dismissed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'read_at' => 'datetime',
            'dismissed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function isDismissed(): bool
    {
        return $this->dismissed_at !== null;
    }
}
