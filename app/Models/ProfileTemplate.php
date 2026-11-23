<?php

namespace App\Models;

use Database\Factories\ProfileTemplateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'title',
    'description',
    'link_label',
    'file_path',
    'external_url',
    'is_active',
    'sort_order',
    'created_by',
])]
class ProfileTemplate extends Model
{
    /** @use HasFactory<ProfileTemplateFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
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

    public function getDownloadUrlAttribute(): ?string
    {
        if (filled($this->file_path)) {
            if (
                str_starts_with($this->file_path, 'http://')
                || str_starts_with($this->file_path, 'https://')
                || str_starts_with($this->file_path, '/')
            ) {
                return $this->file_path;
            }

            return Storage::disk('public')->url($this->file_path);
        }

        if (filled($this->external_url)) {
            return $this->external_url;
        }

        return null;
    }

    public function getResolvedLinkLabelAttribute(): string
    {
        if (filled($this->link_label)) {
            return $this->link_label;
        }

        $source = $this->file_path ?: $this->external_url;

        if (filled($source)) {
            $path = (string) parse_url($source, PHP_URL_PATH);
            $extension = strtoupper((string) pathinfo($path, PATHINFO_EXTENSION));

            if ($extension !== '') {
                return "Download ({$extension})";
            }
        }

        return 'Download';
    }
}
