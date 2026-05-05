<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportBatch extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PREVIEW_READY = 'preview_ready';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REVERTED = 'reverted';

    protected $fillable = [
        'user_id',
        'account_id',
        'importer',
        'original_filename',
        'file_hash',
        'rows_total',
        'rows_imported',
        'rows_duplicated',
        'status',
        'preview_payload',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'preview_payload' => 'array',
            'rows_total' => 'integer',
            'rows_imported' => 'integer',
            'rows_duplicated' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isPreviewReady(): bool
    {
        return $this->status === self::STATUS_PREVIEW_READY;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }
}
