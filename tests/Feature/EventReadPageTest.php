<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Enums\SectionType;
use App\Models\Page;
use App\Models\PageSection;
use Database\Seeders\ContentSeeder;
use Database\Seeders\PageSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EventReadPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed([RoleAndPermissionSeeder::class, PageSeeder::class, ContentSeeder::class]);
    }

    public function test_events_page_enriches_article_items_with_canonical_slugs(): void
    {
        $this->get('/events')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('public/PageView')
                ->where('page.slug', 'events')
                ->has('page.sections.1.content.items', 5)
                ->where(
                    'page.sections.1.content.items.1.slug',
                    '2024-bsw-training-at-st-simon-vocational-training-institute-hoima'
                )
            );
    }

    public function test_each_event_renders_its_own_dedicated_read_page(): void
    {
        $eventsPage = Page::where('slug', 'events')->with('sections')->firstOrFail();
        $articlesSection = $eventsPage->sections->firstWhere('type', SectionType::Articles);
        $items = $articlesSection->content['items'] ?? [];

        $this->assertNotEmpty($items);

        foreach ($items as $item) {
            $slug = PageSection::articleSlug($item);

            $this->get("/events/{$slug}")
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page
                    ->component('public/EventShow')
                    ->where('event.slug', $slug)
                    ->where('event.title', $item['title'])
                    ->has('relatedEvents', 3)
                );
        }
    }

    public function test_unknown_event_slug_returns_not_found(): void
    {
        $this->get('/events/this-event-does-not-exist')->assertNotFound();
    }

    public function test_event_on_draft_page_is_not_accessible_publicly(): void
    {
        $draft = Page::factory()->create([
            'title' => 'Draft Events',
            'slug' => 'draft-events',
            'status' => ContentStatus::Draft,
        ]);

        $draft->sections()->create([
            'type' => SectionType::Articles,
            'content' => [
                'heading' => 'Hidden Events',
                'items' => [
                    [
                        'title' => 'Secret Draft Event',
                        'body' => 'Should not be visible publicly.',
                    ],
                ],
            ],
            'sort_order' => 0,
            'is_visible' => true,
        ]);

        $this->get('/events/secret-draft-event')->assertNotFound();
    }
}
