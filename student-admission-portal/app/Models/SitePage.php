<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SitePage extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'content',
        'is_published',
        'is_system',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'is_system' => 'boolean',
        ];
    }

    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Get the route key name for implicit model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
