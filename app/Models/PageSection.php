<?php

namespace App\Models;

use App\Enums\SectionType;
use Database\Factories\PageSectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'page_id',
    'type',
    'content',
    'settings',
    'sort_order',
    'is_visible',
])]
class PageSection extends Model
{
    /** @use HasFactory<PageSectionFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => SectionType::class,
            'content' => 'array',
            'settings' => 'array',
            'sort_order' => 'integer',
            'is_visible' => 'boolean',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * Resolve the URL-safe slug for an article/event item.
     *
     * @param  array<string, mixed>  $item
     */
    public static function articleSlug(array $item): string
    {
        $explicitSlug = isset($item['slug']) ? trim((string) $item['slug']) : '';

        if ($explicitSlug !== '') {
            return Str::slug($explicitSlug);
        }

        return Str::slug((string) ($item['title'] ?? ''));
    }

    /**
     * Ensure each item in an articles section carries its canonical slug.
     */
    public function enrichArticleSlugs(): self
    {
        if ($this->type !== SectionType::Articles) {
            return $this;
        }

        $content = $this->content ?? [];

        if (! isset($content['items']) || ! is_array($content['items'])) {
            return $this;
        }

        $content['items'] = array_map(
            function (mixed $item): mixed {
                if (! is_array($item)) {
                    return $item;
                }

                $item['slug'] = self::articleSlug($item);

                return $item;
            },
            $content['items']
        );

        $this->setAttribute('content', $content);

        return $this;
    }

    /**
     * Resolve the URL-safe slug for a directory partner entry.
     *
     * @param  array<string, mixed>  $entry
     */
    public static function directoryEntrySlug(array $entry): string
    {
        $explicitSlug = isset($entry['slug']) ? trim((string) $entry['slug']) : '';

        if ($explicitSlug !== '') {
            return Str::slug($explicitSlug);
        }

        return Str::slug((string) ($entry['name'] ?? ''));
    }

    /**
     * Ensure each entry in a directory section carries its canonical slug.
     */
    public function enrichDirectorySlugs(): self
    {
        if ($this->type !== SectionType::Directory) {
            return $this;
        }

        $content = $this->content ?? [];

        if (! isset($content['entries']) || ! is_array($content['entries'])) {
            return $this;
        }

        $content['entries'] = array_map(
            function (mixed $entry): mixed {
                if (! is_array($entry)) {
                    return $entry;
                }

                $entry['slug'] = self::directoryEntrySlug($entry);

                return $entry;
            },
            $content['entries']
        );

        $this->setAttribute('content', $content);

        return $this;
    }
}
