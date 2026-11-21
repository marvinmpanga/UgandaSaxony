<?php

namespace Tests\Feature;

use App\Filament\Resources\ArticleResource\Pages\CreateArticle;
use App\Filament\Resources\GalleryImageResource\Pages\CreateGalleryImage;
use App\Filament\Resources\PartnerResource\Pages\CreatePartner;
use App\Filament\Resources\ResourceResource\Pages\CreateResource;
use App\Models\Article;
use App\Models\GalleryImage;
use App\Models\Partner;
use App\Models\SiteStat;
use App\Models\User;
use App\Services\WebpImageService;
use Database\Seeders\PageSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\SiteStatSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;
use Tests\TestCase;

class FilamentToWebsiteSyncTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RoleAndPermissionSeeder::class,
            PageSeeder::class,
            SiteStatSeeder::class,
        ]);

        $this->admin = User::where('email', 'admin@ugandasaxony.org')->firstOrFail();
    }

    public function test_webp_service_converts_and_resizes_raster_images_and_keeps_pdfs_intact(): void
    {
        Storage::fake('public');

        $service = app(WebpImageService::class);

        $png = UploadedFile::fake()->image('oversized-banner.png', 2400, 1200);
        $webpPath = $service->storeUploadedFile($png, 'articles/covers', 'public', quality: 80, maxWidth: 1920);

        $this->assertStringStartsWith('articles/covers/', $webpPath);
        $this->assertStringEndsWith('.webp', $webpPath);
        Storage::disk('public')->assertExists($webpPath);

        $dimensions = getimagesize(Storage::disk('public')->path($webpPath));
        $this->assertIsArray($dimensions);
        $this->assertSame(1920, $dimensions[0]);
        $this->assertSame(960, $dimensions[1]);
        $this->assertSame('image/webp', $dimensions['mime']);

        $pdf = UploadedFile::fake()->create('guide.pdf', 120, 'application/pdf');
        $pdfPath = $service->storeUploadedFile($pdf, 'resources/documents', 'public');

        $this->assertStringEndsWith('.pdf', $pdfPath);
        Storage::disk('public')->assertExists($pdfPath);
    }

    public function test_dashboard_renders_logo_quick_actions_and_recent_articles(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin')
            ->assertSuccessful()
            ->assertSee('/images/legacy/logo.png')
            ->assertSee('Uganda–Saxony')
            ->assertSee('Recent Events &amp; News', false);
    }

    public function test_article_published_in_filament_converts_cover_to_webp_and_reaches_website(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin);

        $cover = UploadedFile::fake()->image('summit-photo.jpg', 1200, 800);

        Livewire::test(CreateArticle::class)
            ->fillForm([
                'title' => 'Kampala Dresden Youth Exchange',
                'slug' => 'kampala-dresden-youth-exchange',
                'tag' => 'Exchange',
                'location' => 'Kampala, Uganda',
                'excerpt' => 'Youth leaders from Uganda and Saxony meet in Kampala.',
                'body' => '<p>Detailed report from the exchange week.</p>',
                'cover_image' => $cover,
                'status' => 'published',
                'featured' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $article = Article::where('slug', 'kampala-dresden-youth-exchange')->firstOrFail();
        $this->assertStringEndsWith('.webp', (string) $article->cover_image);
        Storage::disk('public')->assertExists((string) $article->cover_image);

        // Renders on Homepage Hero
        $this->get('/')
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->component('public/PageView')
                ->where('featuredArticles.0.slug', 'kampala-dresden-youth-exchange')
                ->where('featuredArticles.0.cover_image', fn (string $url) => str_ends_with($url, '.webp'))
            );

        // Renders on /events listing
        $this->get('/events')
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->component('public/PageView')
                ->where('page.sections.1.content.items.0.slug', 'kampala-dresden-youth-exchange')
            );

        // Renders on dedicated /events/{slug} page
        $this->get('/events/kampala-dresden-youth-exchange')
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->component('public/EventShow')
                ->where('event.title', 'Kampala Dresden Youth Exchange')
                ->where('event.image_url', fn (string $url) => str_ends_with($url, '.webp'))
            );

        // Unpublishing immediately removes it from public view
        $article->update(['status' => 'draft']);

        $this->get('/events/kampala-dresden-youth-exchange')->assertNotFound();
    }

    public function test_partners_created_in_filament_render_on_schools_and_cso_pages(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin);

        Livewire::test(CreatePartner::class)
            ->fillForm([
                'name' => 'Jinja Vocational Academy',
                'slug' => 'jinja-vocational-academy',
                'category' => 'Vocational Institutes',
                'country' => 'Uganda',
                'location' => 'Jinja City',
                'logo_url' => UploadedFile::fake()->image('logo.png', 800, 800),
                'description' => 'Practical technical training institute in Jinja.',
                'in_partnership' => true,
                'is_active' => true,
                'sort_order' => 1,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        Livewire::test(CreatePartner::class)
            ->fillForm([
                'name' => 'Mukono Green Initiative',
                'slug' => 'mukono-green-initiative',
                'category' => 'Non Government Organisations',
                'country' => 'Uganda',
                'location' => 'Mukono',
                'description' => 'Environmental conservation NGO.',
                'in_partnership' => false,
                'is_active' => true,
                'sort_order' => 1,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $school = Partner::where('slug', 'jinja-vocational-academy')->firstOrFail();
        $this->assertStringEndsWith('.webp', (string) $school->logo_url);

        // School appears on /partner-schools
        $this->get('/partner-schools')
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->where('page.sections.1.content.entries.0.slug', 'jinja-vocational-academy')
            );

        // CSO appears on /partner-organisations
        $this->get('/partner-organisations')
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->where('page.sections.1.content.entries.0.slug', 'mukono-green-initiative')
            );

        // Dedicated profile page renders with WebP logo
        $this->get('/partners/jinja-vocational-academy')
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->component('public/PartnerShow')
                ->where('partner.name', 'Jinja Vocational Academy')
                ->where('partner.logo_url', fn (string $url) => str_ends_with($url, '.webp'))
            );

        // Deactivating hides it from /partner-schools
        $school->update(['is_active' => false]);

        $this->get('/partner-schools')
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->where('page.sections.1.content.entries', [])
            );
    }

    public function test_gallery_photo_uploaded_in_filament_converts_to_webp_and_renders_on_gallery(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin);

        Livewire::test(CreateGalleryImage::class)
            ->fillForm([
                'path' => UploadedFile::fake()->image('workshop.jpg', 1600, 1000),
                'caption' => 'Dresden Partner Workshop 2026',
                'album' => 'Workshops',
                'is_visible' => true,
                'sort_order' => 1,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $photo = GalleryImage::where('caption', 'Dresden Partner Workshop 2026')->firstOrFail();
        $this->assertStringEndsWith('.webp', $photo->path);

        $this->get('/gallery')
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->where('page.sections.1.content.items.0.caption', 'Dresden Partner Workshop 2026')
                ->where('page.sections.1.content.items.0.image_url', fn (string $url) => str_ends_with($url, '.webp'))
            );

        $photo->update(['is_visible' => false]);

        $this->get('/gallery')
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->where('page.sections.1.content.items', [])
            );
    }

    public function test_resource_centre_item_and_site_stat_updates_render_live_on_website(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin);

        Livewire::test(CreateResource::class)
            ->fillForm([
                'title' => '2026 Partnership Toolkit',
                'slug' => '2026-partnership-toolkit',
                'category' => 'Guides & Reports',
                'excerpt' => 'Step-by-step toolkit for new partners.',
                'body' => '<p>Toolkit body text.</p>',
                'cover_image' => UploadedFile::fake()->image('toolkit.png', 1000, 700),
                'status' => 'published',
                'sort_order' => 1,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->get('/resource-centre')
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->component('public/ResourceCentreIndex')
                ->where('resources.0.slug', '2026-partnership-toolkit')
                ->where('resources.0.cover_image', fn (string $url) => str_ends_with($url, '.webp'))
            );

        $this->get('/resource-centre/2026-partnership-toolkit')
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->component('public/ResourceShow')
                ->where('resource.title', '2026 Partnership Toolkit')
            );

        // Update a SiteStat and verify Homepage reflects it live
        SiteStat::where('key', 'partner_schools')->update([
            'value' => '18',
            'label' => 'Active partner schools',
        ]);

        $this->get('/')
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->where('page.sections.2.content.items.1.value', '18')
                ->where('page.sections.2.content.items.1.label', 'Active partner schools')
            );
    }

    public function test_slugs_auto_generate_when_left_blank_in_filament_forms(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreateArticle::class)
            ->fillForm([
                'title' => 'Auto Slug Event Story',
                'slug' => '',
                'body' => '<h2>Highlights</h2><ul><li>Point one</li></ul>',
                'status' => 'published',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        Livewire::test(CreatePartner::class)
            ->fillForm([
                'name' => 'Auto Slug Partner School',
                'slug' => '',
                'category' => 'Primary Schools',
                'description' => '<p>Rich <strong>formatted</strong> partner overview.</p>',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        Livewire::test(CreateResource::class)
            ->fillForm([
                'title' => 'Auto Slug Guide',
                'slug' => '',
                'body' => '<blockquote>Important note</blockquote>',
                'status' => 'published',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('articles', ['slug' => 'auto-slug-event-story']);
        $this->assertDatabaseHas('partners', ['slug' => 'auto-slug-partner-school']);
        $this->assertDatabaseHas('resource_items', ['slug' => 'auto-slug-guide']);

        $this->get('/events/auto-slug-event-story')->assertSuccessful();
        $this->get('/partners/auto-slug-partner-school')->assertSuccessful();
        $this->get('/resource-centre/auto-slug-guide')->assertSuccessful();
    }
}
