<?php

namespace App\Http\Controllers\Public;

use App\Enums\SectionType;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\GalleryImage;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Partner;
use App\Models\SiteStat;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function home(): Response
    {
        $page = Page::query()
            ->published()
            ->where('is_homepage', true)
            ->with(['sections' => fn ($query) => $query->where('is_visible', true)->orderBy('sort_order')])
            ->first();

        if (! $page) {
            $page = Page::query()
                ->published()
                ->with(['sections' => fn ($query) => $query->where('is_visible', true)->orderBy('sort_order')])
                ->firstOrFail();
        }

        $this->hydrateDynamicSections($page);

        // Published articles automatically power the Homepage Hero (featured prioritized first)
        $heroArticles = Article::query()
            ->published()
            ->orderByDesc('featured')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get()
            ->map(fn (Article $article): array => [
                'title' => $article->title,
                'slug' => $article->slug,
                'excerpt' => $article->excerpt,
                'cover_image' => $article->cover_image_url,
                'tag' => $article->tag,
                'event_date' => $article->event_date?->format('d M Y'),
            ])
            ->values();

        return Inertia::render('public/PageView', [
            'page' => $page,
            'featuredArticles' => $heroArticles,
        ]);
    }

    public function show(string $slug): Response
    {
        $page = Page::query()
            ->published()
            ->where('slug', $slug)
            ->with(['sections' => fn ($query) => $query->where('is_visible', true)->orderBy('sort_order')])
            ->firstOrFail();

        $this->hydrateDynamicSections($page);

        return Inertia::render('public/PageView', [
            'page' => $page,
        ]);
    }

    /**
     * Hydrate page sections directly from Filament-managed database tables
     * (SiteStats, Articles, Partners, GalleryImages) as the single source of truth.
     */
    private function hydrateDynamicSections(Page $page): void
    {
        foreach ($page->sections as $section) {
            match ($section->type) {
                SectionType::Stats => $this->hydrateStatsSection($section),
                SectionType::Articles => $this->hydrateArticlesSection($section),
                SectionType::Directory => $this->hydrateDirectorySection($section, $page->slug),
                SectionType::Gallery => $this->hydrateGallerySection($section),
                default => null,
            };
        }
    }

    private function hydrateStatsSection(PageSection $section): void
    {
        if (! Schema::hasTable('site_stats')) {
            return;
        }

        $stats = SiteStat::ordered();

        $content = $section->content ?? [];
        $content['items'] = $stats->map(fn (SiteStat $stat): array => [
            'value' => $stat->value,
            'label' => $stat->label,
        ])->values()->all();

        $section->setAttribute('content', $content);
    }

    private function hydrateArticlesSection(PageSection $section): void
    {
        if (! Schema::hasTable('articles')) {
            return;
        }

        $content = $section->content ?? [];
        $query = Article::query()
            ->published()
            ->orderByRaw('COALESCE(event_date, published_at) DESC')
            ->orderByDesc('published_at')
            ->orderByDesc('id');

        $formatArticle = fn (Article $article): array => [
            'title' => $article->title,
            'slug' => $article->slug,
            'tag' => $article->tag,
            'date' => $article->event_date?->format('d M Y') ?? $article->published_at?->format('d M Y'),
            'location' => $article->location,
            'excerpt' => $article->excerpt,
            'image_url' => $article->cover_image_url,
        ];

        $limit = isset($content['limit']) ? (int) $content['limit'] : 0;

        if ($limit > 0) {
            $content['items'] = $query->limit($limit)->get()->map($formatArticle)->values()->all();
            $section->setAttribute('content', $content);

            return;
        }

        $perPage = 6;
        $paginator = $query->paginate($perPage, ['*'], 'page', 1);

        $content['items'] = $paginator->getCollection()->map($formatArticle)->values()->all();
        $content['pagination'] = [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'has_more' => $paginator->hasMorePages(),
            'feed_url' => route('events.feed'),
        ];

        $section->setAttribute('content', $content);
    }

    private function hydrateDirectorySection(PageSection $section, string $pageSlug): void
    {
        if (! Schema::hasTable('partners')) {
            return;
        }

        $partners = Partner::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->filter(function (Partner $partner) use ($pageSlug): bool {
                $category = strtolower((string) $partner->category);
                $isSchool = str_contains($category, 'school') || str_contains($category, 'vocational');

                if ($pageSlug === 'partner-schools') {
                    return $isSchool;
                }

                if ($pageSlug === 'partner-organisations') {
                    return ! $isSchool;
                }

                return true;
            });

        $liveEntries = $partners->map(fn (Partner $partner): array => [
            'name' => $partner->name,
            'slug' => $partner->slug,
            'group' => $partner->category ?: ($pageSlug === 'partner-schools' ? 'Primary Schools' : 'Non Government Organisations'),
            'country' => $partner->country,
            'location' => $partner->location,
            'in_partnership' => $partner->in_partnership,
            'summary' => $partner->description,
            'logo_url' => $partner->logo_image_url,
            'website_url' => $partner->website_url,
        ])->values()->all();

        $content = $section->content ?? [];
        $content['entries'] = $liveEntries;

        $defaultGroups = match ($pageSlug) {
            'partner-schools' => ['Primary Schools', 'Secondary Schools', 'Vocational Institutes'],
            'partner-organisations' => ['Non Government Organisations', 'Community Based Organisations'],
            default => [],
        };

        $liveGroups = array_values(array_unique(array_column($liveEntries, 'group')));
        $content['groups'] = array_values(array_unique(array_merge($defaultGroups, $liveGroups)));

        $section->setAttribute('content', $content);
    }

    private function hydrateGallerySection(PageSection $section): void
    {
        if (! Schema::hasTable('gallery_images')) {
            return;
        }

        $images = GalleryImage::query()
            ->visible()
            ->orderBy('sort_order')
            ->latest('id')
            ->get();

        $content = $section->content ?? [];
        $content['items'] = $images->map(fn (GalleryImage $image): array => [
            'image_url' => $image->url,
            'caption' => $image->caption,
            'album' => $image->album,
        ])->values()->all();

        $section->setAttribute('content', $content);
    }
}
