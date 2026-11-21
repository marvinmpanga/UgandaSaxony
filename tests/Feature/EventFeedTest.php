<?php

namespace Tests\Feature;

use App\Enums\SectionType;
use App\Models\Article;
use App\Models\Page;
use App\Models\PageSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class EventFeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_events_feed_returns_published_events_latest_first_and_paginates(): void
    {
        for ($i = 1; $i <= 8; $i++) {
            Article::create([
                'title' => "Event {$i}",
                'slug' => "event-{$i}",
                'status' => 'published',
                'event_date' => Carbon::parse('2026-01-01')->addDays($i),
                'published_at' => Carbon::parse('2026-01-01')->addDays($i),
            ]);
        }

        Article::create([
            'title' => 'Draft Event',
            'slug' => 'draft-event',
            'status' => 'draft',
            'event_date' => Carbon::parse('2026-12-31'),
            'published_at' => Carbon::parse('2026-12-31'),
        ]);

        $pageOne = $this->getJson(route('events.feed', ['page' => 1, 'per_page' => 6]));

        $pageOne->assertOk()
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonPath('meta.total', 8)
            ->assertJsonPath('meta.has_more', true)
            ->assertJsonCount(6, 'data')
            ->assertJsonPath('data.0.slug', 'event-8')
            ->assertJsonPath('data.5.slug', 'event-3');

        $pageTwo = $this->getJson(route('events.feed', ['page' => 2, 'per_page' => 6]));

        $pageTwo->assertOk()
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.has_more', false)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.slug', 'event-2')
            ->assertJsonPath('data.1.slug', 'event-1');
    }

    public function test_events_page_hydrates_first_batch_with_pagination_metadata(): void
    {
        $page = Page::create([
            'title' => 'Events',
            'slug' => 'events',
            'status' => 'published',
        ]);

        PageSection::create([
            'page_id' => $page->id,
            'type' => SectionType::Articles,
            'content' => ['heading' => 'Stories from', 'accent' => 'the partnership'],
            'sort_order' => 1,
            'is_visible' => true,
        ]);

        for ($i = 1; $i <= 7; $i++) {
            Article::create([
                'title' => "Partnership Story {$i}",
                'slug' => "story-{$i}",
                'status' => 'published',
                'event_date' => Carbon::parse('2026-02-01')->addDays($i),
                'published_at' => Carbon::parse('2026-02-01')->addDays($i),
            ]);
        }

        $response = $this->get('/events');

        $response->assertOk();
        $sections = $response->original->getData()['page']['props']['page']['sections'];
        $articlesSection = $sections[0]['content'];

        $this->assertCount(6, $articlesSection['items']);
        $this->assertSame('story-7', $articlesSection['items'][0]['slug']);
        $this->assertTrue($articlesSection['pagination']['has_more']);
        $this->assertSame(7, $articlesSection['pagination']['total']);
    }
}
