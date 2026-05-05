<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategorizationRule extends Model
{
    public const MATCH_CONTAINS = 'contains';
    public const MATCH_STARTS_WITH = 'starts_with';
    public const MATCH_EXACT = 'exact';
    public const MATCH_REGEX = 'regex';

    protected $fillable = [
        'user_id',
        'match_type',
        'pattern',
        'category_id',
        'priority',
        'auto_learned',
        'hits',
    ];

    protected function casts(): array
    {
        return [
            'priority' => 'integer',
            'auto_learned' => 'boolean',
            'hits' => 'integer',
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
