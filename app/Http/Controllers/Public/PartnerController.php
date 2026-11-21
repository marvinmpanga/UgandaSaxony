<?php

namespace App\Http\Controllers\Public;

use App\Enums\SectionType;
use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Partner;
use Inertia\Inertia;
use Inertia\Response;

class PartnerController extends Controller
{
    /**
     * Render a dedicated profile page for a partner school or organisation.
     */
    public function show(string $slug): Response
    {
        $partner = Partner::active()->where('slug', $slug)->first();

        if ($partner) {
            $related = Partner::active()
                ->where('id', '!=', $partner->id)
                ->when($partner->category, fn ($q) => $q->where('category', $partner->category))
                ->orderBy('sort_order')
                ->limit(6)
                ->get()
                ->map(fn (Partner $item): array => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'slug' => $item->slug,
                    'category' => $item->category,
                    'country' => $item->country,
                    'location' => $item->location,
                    'logo_url' => $item->logo_image_url,
                    'in_partnership' => $item->in_partnership,
                ]);

            $isSchool = str_contains(strtolower((string) $partner->category), 'school')
                || str_contains(strtolower((string) $partner->category), 'vocational');

            return Inertia::render('public/PartnerShow', [
                'partner' => [
                    'id' => $partner->id,
                    'name' => $partner->name,
                    'slug' => $partner->slug,
                    'category' => $partner->category,
                    'country' => $partner->country,
                    'location' => $partner->location,
                    'logo_url' => $partner->logo_image_url,
                    'website_url' => $partner->website_url,
                    'description' => $partner->description,
                    'in_partnership' => $partner->in_partnership,
                ],
                'related' => $related,
                'backUrl' => $isSchool ? '/partner-schools' : '/partner-organisations',
                'backLabel' => $isSchool ? 'Back to partner schools' : 'Back to partner organisations',
                'editUrl' => "/admin/partners/{$partner->id}/edit",
            ]);
        }

        return $this->showFromDirectorySections($slug);
    }

    /**
     * Fallback resolver for partners stored inside published directory page sections.
     */
    private function showFromDirectorySections(string $slug): Response
    {
        $pages = Page::query()
            ->published()
            ->whereHas('sections', fn ($query) => $query
                ->where('type', SectionType::Directory->value)
                ->where('is_visible', true))
            ->with(['sections' => fn ($query) => $query
                ->where('type', SectionType::Directory->value)
                ->where('is_visible', true)
                ->orderBy('sort_order')])
            ->orderBy('id')
            ->get();

        $matchedEntry = null;
        $matchedPage = null;

        /** @var list<array<string, mixed>> $pageEntries */
        $pageEntries = [];

        foreach ($pages as $page) {
            $entriesOnPage = [];

            foreach ($page->sections as $section) {
                $entries = $section->content['entries'] ?? [];

                if (! is_array($entries)) {
                    continue;
                }

                foreach ($entries as $entry) {
                    if (! is_array($entry)) {
                        continue;
                    }

                    $entrySlug = PageSection::directoryEntrySlug($entry);

                    if ($entrySlug === '') {
                        continue;
                    }

                    $normalized = $this->normalizeDirectoryEntry($entry, $entrySlug);
                    $entriesOnPage[] = $normalized;

                    if ($matchedEntry === null && $entrySlug === $slug) {
                        $matchedEntry = $normalized;
                        $matchedPage = $page;
                    }
                }
            }

            if ($matchedPage && $matchedPage->id === $page->id) {
                $pageEntries = $entriesOnPage;
                break;
            }
        }

        if ($matchedEntry === null || $matchedPage === null) {
            abort(404);
        }

        $sameGroup = array_values(array_filter(
            $pageEntries,
            fn (array $item): bool => $item['slug'] !== $slug
                && ($matchedEntry['category'] === null || $item['category'] === $matchedEntry['category'])
        ));

        $otherEntries = array_values(array_filter(
            $pageEntries,
            fn (array $item): bool => $item['slug'] !== $slug
                && ($matchedEntry['category'] !== null && $item['category'] !== $matchedEntry['category'])
        ));

        $related = array_slice(array_merge($sameGroup, $otherEntries), 0, 6);

        return Inertia::render('public/PartnerShow', [
            'partner' => $matchedEntry,
            'related' => $related,
            'backUrl' => '/'.$matchedPage->slug,
            'backLabel' => 'Back to '.strtolower($matchedPage->title),
            'editUrl' => "/admin/pages/{$matchedPage->id}/edit",
        ]);
    }

    /**
     * Normalize a directory section entry to match the PartnerShow frontend shape.
     *
     * @param  array<string, mixed>  $entry
     * @return array<string, mixed>
     */
    private function normalizeDirectoryEntry(array $entry, string $slug): array
    {
        return [
            'id' => $slug,
            'name' => (string) ($entry['name'] ?? ''),
            'slug' => $slug,
            'category' => isset($entry['group']) ? (string) $entry['group'] : null,
            'country' => isset($entry['country']) ? (string) $entry['country'] : null,
            'location' => isset($entry['location']) ? (string) $entry['location'] : null,
            'logo_url' => isset($entry['logo_url']) ? (string) $entry['logo_url'] : null,
            'website_url' => isset($entry['website_url'])
                ? (string) $entry['website_url']
                : (isset($entry['website']) ? (string) $entry['website'] : null),
            'description' => isset($entry['body'])
                ? (string) $entry['body']
                : (isset($entry['summary']) ? (string) $entry['summary'] : null),
            'in_partnership' => (bool) ($entry['in_partnership'] ?? false),
        ];
    }
}
