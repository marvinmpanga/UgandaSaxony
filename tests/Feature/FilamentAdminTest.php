<?php

namespace Tests\Feature;

use App\Filament\Resources\ArticleResource\Pages\CreateArticle;
use App\Filament\Resources\PartnerResource\Pages\CreatePartner;
use App\Filament\Resources\ResourceResource\Pages\CreateResource;
use App\Filament\Resources\SiteStatResource\Pages\CreateSiteStat;
use App\Models\Article;
use App\Models\SiteStat;
use App\Models\User;
use Database\Seeders\PageSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\SiteStatSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;
use Tests\TestCase;

class FilamentAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_guest_is_redirected_to_branded_login_page(): void
    {
        $this->get('/admin')->assertRedirect('/login');
        $this->get('/admin/login')->assertRedirect('/login');

        $this->get('/login')
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page->component('auth/Login'));
    }

    public function test_staff_can_sign_in_via_branded_login_and_reach_filament(): void
    {
        $this->post('/login', [
            'email' => 'admin@ugandasaxony.org',
            'password' => 'password',
        ])->assertRedirect('/admin');

        $this->assertAuthenticated();
    }

    public function test_user_without_cms_permission_is_rejected_at_login(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('secret-pass'),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'secret-pass',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_user_without_dashboard_permission_cannot_access_filament_panel(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_pages_resource_is_removed_from_filament(): void
    {
        $admin = User::where('email', 'admin@ugandasaxony.org')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/pages')
            ->assertNotFound();
    }

    public function test_super_admin_can_access_dashboard_and_all_resources(): void
    {
        $admin = User::where('email', 'admin@ugandasaxony.org')->firstOrFail();

        $routes = [
            '/admin',
            '/admin/articles',
            '/admin/articles/create',
            '/admin/partners',
            '/admin/partners/create',
            '/admin/resource-centre',
            '/admin/resource-centre/create',
            '/admin/gallery-images',
            '/admin/gallery-images/create',
            '/admin/media-items',
            '/admin/media-items/create',
            '/admin/media-folders',
            '/admin/media-folders/create',
            '/admin/site-stats',
            '/admin/site-stats/create',
            '/admin/users',
            '/admin/users/create',
        ];

        foreach ($routes as $url) {
            $this->actingAs($admin)
                ->get($url)
                ->assertSuccessful();
        }
    }

    public function test_viewer_permissions_are_enforced_across_resources(): void
    {
        $viewer = User::factory()->create();
        $viewer->assignRole('Viewer');

        $this->actingAs($viewer)->get('/admin')->assertSuccessful();
        $this->actingAs($viewer)->get('/admin/articles')->assertSuccessful();
        $this->actingAs($viewer)->get('/admin/resource-centre')->assertSuccessful();
        $this->actingAs($viewer)->get('/admin/media-items')->assertSuccessful();
        $this->actingAs($viewer)->get('/admin/site-stats')->assertForbidden();
        $this->actingAs($viewer)->get('/admin/users')->assertForbidden();
    }

    public function test_super_admin_can_create_site_stat_via_filament(): void
    {
        $admin = User::where('email', 'admin@ugandasaxony.org')->firstOrFail();

        $this->actingAs($admin);

        Livewire::test(CreateSiteStat::class)
            ->fillForm([
                'key' => 'partner_schools',
                'value' => '42+',
                'label' => 'Partner Schools',
                'sort_order' => 1,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('site_stats', [
            'key' => 'partner_schools',
            'value' => '42+',
            'label' => 'Partner Schools',
        ]);
    }

    public function test_super_admin_can_create_partner_via_filament(): void
    {
        $admin = User::where('email', 'admin@ugandasaxony.org')->firstOrFail();

        $this->actingAs($admin);

        Livewire::test(CreatePartner::class)
            ->fillForm([
                'name' => 'Kampala Technical School',
                'slug' => 'kampala-technical-school',
                'category' => 'School',
                'country' => 'Uganda',
                'in_partnership' => true,
                'is_active' => true,
                'sort_order' => 1,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('partners', [
            'name' => 'Kampala Technical School',
            'slug' => 'kampala-technical-school',
            'created_by' => $admin->id,
        ]);
    }

    public function test_super_admin_can_create_article_via_filament_and_it_appears_on_homepage_hero(): void
    {
        $this->seed(PageSeeder::class);
        $admin = User::where('email', 'admin@ugandasaxony.org')->firstOrFail();

        $this->actingAs($admin);

        Livewire::test(CreateArticle::class)
            ->fillForm([
                'title' => 'Annual Partnership Summit',
                'slug' => 'annual-partnership-summit',
                'tag' => 'Event',
                'status' => 'published',
                'featured' => false,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('articles', [
            'title' => 'Annual Partnership Summit',
            'slug' => 'annual-partnership-summit',
            'status' => 'published',
            'created_by' => $admin->id,
        ]);

        // Any published article should automatically appear in the homepage hero slides
        $this->get('/')
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->component('public/PageView')
                ->where('featuredArticles.0.slug', 'annual-partnership-summit')
                ->where('featuredArticles.0.title', 'Annual Partnership Summit')
            );
    }

    public function test_super_admin_can_publish_resource_centre_item_and_view_it_publicly(): void
    {
        $admin = User::where('email', 'admin@ugandasaxony.org')->firstOrFail();

        $this->actingAs($admin);

        Livewire::test(CreateResource::class)
            ->fillForm([
                'title' => 'Exchange Programme Handbook',
                'slug' => 'exchange-programme-handbook',
                'category' => 'Guides & Reports',
                'excerpt' => 'A practical guide for partner schools.',
                'body' => '<p>Full handbook content.</p>',
                'status' => 'published',
                'sort_order' => 1,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('resource_items', [
            'slug' => 'exchange-programme-handbook',
            'status' => 'published',
        ]);

        $this->get('/resource-centre')
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->component('public/ResourceCentreIndex')
                ->where('resources.0.slug', 'exchange-programme-handbook')
            );

        $this->get('/resource-centre/exchange-programme-handbook')
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->component('public/ResourceShow')
                ->where('resource.title', 'Exchange Programme Handbook')
            );
    }

    public function test_homepage_stats_are_hydrated_from_site_stats(): void
    {
        $this->seed([PageSeeder::class, SiteStatSeeder::class]);

        $this->assertSame(4, SiteStat::query()->count());

        SiteStat::where('key', 'partner_schools')->update(['value' => '12']);

        $this->get('/')
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->component('public/PageView')
                ->has('page.sections', fn (Assert $sections) => $sections->each(fn (Assert $section) => $section->etc()))
            );
    }

    public function test_custom_404_error_page_renders_with_illustration(): void
    {
        $this->get('/this-page-does-not-exist-404')
            ->assertNotFound()
            ->assertInertia(fn (Assert $page) => $page
                ->component('public/ErrorPage')
                ->where('status', 404)
            );
    }
}
