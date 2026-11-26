<?php

namespace App\Models;

use App\Services\RichTextSanitizer;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable([
    'title',
    'slug',
    'category',
    'excerpt',
    'body',
    'cover_image',
    'attachment_path',
    'status',
    'published_at',
    'sort_order',
    'created_by',
    'updated_by',
])]
class ResourceItem extends Model
{
    use SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'sort_order' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    protected static function booted(): void
    {
        static::creating(function (ResourceItem $resource): void {
            if (blank($resource->slug) && filled($resource->title)) {
                $resource->slug = static::generateSlug($resource->title);
            }
        });

        static::saving(function (ResourceItem $resource): void {
            if ($resource->isDirty('body') && $resource->body !== null) {
                $resource->body = app(RichTextSanitizer::class)->sanitize($resource->body);
            }
        });
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        if (! $this->cover_image) {
            return null;
        }

        if (str_starts_with($this->cover_image, 'http://') || str_starts_with($this->cover_image, 'https://') || str_starts_with($this->cover_image, '/')) {
            return $this->cover_image;
        }

        return Storage::disk('public')->url($this->cover_image);
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        if (! $this->attachment_path) {
            return null;
        }

        if (str_starts_with($this->attachment_path, 'http://') || str_starts_with($this->attachment_path, 'https://') || str_starts_with($this->attachment_path, '/')) {
            return $this->attachment_path;
        }

        return Storage::disk('public')->url($this->attachment_path);
    }

    public static function generateSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
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
