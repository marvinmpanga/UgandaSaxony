<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable([
    'name', 'slug', 'category', 'country', 'location',
    'logo_url', 'website_url', 'description',
    'in_partnership', 'is_active', 'sort_order', 'created_by',
])]
class Partner extends Model
{
    protected function casts(): array
    {
        return [
            'in_partnership' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getLogoImageUrlAttribute(): ?string
    {
        if (! $this->logo_url) {
            return null;
        }

        if (str_starts_with($this->logo_url, 'http://') || str_starts_with($this->logo_url, 'https://') || str_starts_with($this->logo_url, '/')) {
            return $this->logo_url;
        }

        return Storage::disk('public')->url($this->logo_url);
    }

    protected static function booted(): void
    {
        static::creating(function (Partner $partner): void {
            if (blank($partner->slug) && filled($partner->name)) {
                $partner->slug = static::generateSlug($partner->name);
            }
        });
    }

    public static function generateSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (
            static::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
