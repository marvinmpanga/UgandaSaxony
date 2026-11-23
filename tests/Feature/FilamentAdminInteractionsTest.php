<?php

namespace Tests\Feature;

use App\Filament\Resources\ArticleResource\Pages\EditArticle;
use App\Filament\Resources\ArticleResource\Pages\ListArticles;
use App\Filament\Resources\GalleryImageResource\Pages\EditGalleryImage;
use App\Filament\Resources\MediaFolderResource\Pages\CreateMediaFolder;
use App\Filament\Resources\MediaItemResource\Pages\CreateMediaItem;
use App\Filament\Resources\PartnerResource\Pages\EditPartner;
use App\Filament\Resources\SiteStatResource\Pages\EditSiteStat;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Models\Article;
use App\Models\GalleryImage;
use App\Models\MediaFolder;
use App\Models\MediaItem;
use App\Models\Partner;
use App\Models\SiteStat;
use App\Models\User;
use Database\Seeders\PageSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\SiteStatSeeder;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FilamentAdminInteractionsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleAndPermissionSeeder::class, PageSeeder::class, SiteStatSeeder::class]);

        $this->admin = User::where('email', 'admin@ugandasaxony.org')->firstOrFail();
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function publishedArticle(array $attributes = []): Article
    {
        return Article::create([
            'title' => 'Original Event Title',
            'slug' => 'original-event-title',
            'status' => 'published',
            'published_at' => now(),
            ...$attributes,
        ]);
    }

    /**
     * Find a page section's content by its type from an Inertia page response.
     *
     * @return array<string, mixed>
     */
    private function sectionContent(string $url, string $type): array
    {
        $sections = $this->get($url)->assertSuccessful()->inertiaProps('page.sections');

        $section = collect($sections)->firstWhere('type', $type);
        $this->assertNotNull($section, "No [{$type}] section found on {$url}.");

        return $section['content'];
    }

    public function test_editing_an_article_updates_the_public_event_page(): void
    {
        $article = $this->publishedArticle();

        $this->actingAs($this->admin);

        Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
            ->fillForm(['title' => 'Updated Event Title', 'location' => 'Kampala'])
            ->call('save')
            ->assertHasNoFormErrors();

        $article->refresh();
        $this->assertSame('Updated Event Title', $article->title);
        $this->assertSame($this->admin->id, $article->updated_by);

        $this->get('/events/original-event-title')
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->where('event.title', 'Updated Event Title')
                ->where('event.location', 'Kampala'));
    }

    public function test_publishing_a_draft_article_sets_published_at_and_makes_it_public(): void
    {
        $article = Article::create(['title' => 'Draft Story', 'slug' => 'draft-story', 'status' => 'draft']);

        $this->get('/events/draft-story')->assertNotFound();

        $this->actingAs($this->admin);

        Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
            ->fillForm(['status' => 'published'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertNotNull($article->refresh()->published_at);
        $this->get('/events/draft-story')->assertSuccessful();
    }

    public function test_article_slug_must_be_unique_when_editing(): void
    {
        $this->publishedArticle(['title' => 'Taken', 'slug' => 'taken-slug']);
        $article = $this->publishedArticle();

        $this->actingAs($this->admin);

        Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
            ->fillForm(['slug' => 'taken-slug'])
            ->call('save')
            ->assertHasFormErrors(['slug' => 'unique']);
    }

    public function test_deleting_an_article_soft_deletes_it_and_restore_brings_it_back(): void
    {
        $article = $this->publishedArticle();

        $this->actingAs($this->admin);

        Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
            ->callAction(DeleteAction::class);

        $this->assertSoftDeleted($article);
        $this->get('/events/original-event-title')->assertNotFound();

        Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
            ->callAction(RestoreAction::class);

        $this->assertNotSoftDeleted($article);
        $this->get('/events/original-event-title')->assertSuccessful();
    }

    public function test_content_editor_can_edit_but_cannot_delete_articles(): void
    {
        $article = $this->publishedArticle();
        $editor = $this->userWithRole('Content Editor');

        $this->actingAs($editor);

        Livewire::test(ListArticles::class)
            ->assertCanSeeTableRecords([$article])
            ->assertTableActionVisible('edit', $article)
            ->assertTableActionHidden('delete', $article);

        Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
            ->assertActionHidden(DeleteAction::class);
    }

    public function test_content_publisher_cannot_create_articles(): void
    {
        $this->actingAs($this->userWithRole('Content Publisher'))
            ->get('/admin/articles/create')
            ->assertForbidden();
    }

    public function test_media_manager_cannot_access_content_resources(): void
    {
        $manager = $this->userWithRole('Media Manager');

        $this->actingAs($manager)->get('/admin/articles')->assertForbidden();
        $this->actingAs($manager)->get('/admin/partners')->assertForbidden();
        $this->actingAs($manager)->get('/admin/gallery-images')->assertSuccessful();
        $this->actingAs($manager)->get('/admin/media-items/create')->assertSuccessful();
    }

    public function test_editing_a_partner_updates_its_public_profile(): void
    {
        $partner = Partner::create([
            'name' => 'Lake Victoria Primary',
            'category' => 'Primary Schools',
            'country' => 'Uganda',
            'is_active' => true,
        ]);

        $this->actingAs($this->admin);

        Livewire::test(EditPartner::class, ['record' => $partner->getRouteKey()])
            ->fillForm(['description' => 'A thriving partner school.'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->get('/partners/'.$partner->slug)
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('public/PartnerShow')
                ->where('partner.description', 'A thriving partner school.'));
    }

    public function test_deactivating_a_partner_removes_it_from_the_public_site(): void
    {
        $partner = Partner::create([
            'name' => 'Inactive Org',
            'category' => 'Non Government Organisations',
            'is_active' => true,
        ]);

        $this->actingAs($this->admin);

        Livewire::test(EditPartner::class, ['record' => $partner->getRouteKey()])
            ->fillForm(['is_active' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->get('/partners/'.$partner->slug)->assertNotFound();

        $entries = $this->sectionContent('/partner-organisations', 'directory')['entries'];
        $this->assertNotContains('Inactive Org', array_column($entries, 'name'));
    }

    public function test_editing_a_site_stat_updates_the_homepage_stats_bar(): void
    {
        $stat = SiteStat::where('key', 'partner_schools')->firstOrFail();

        $this->actingAs($this->admin);

        Livewire::test(EditSiteStat::class, ['record' => $stat->getRouteKey()])
            ->fillForm(['value' => '99'])
            ->call('save')
            ->assertHasNoFormErrors();

        $items = $this->sectionContent('/', 'stats')['items'];
        $this->assertContains('99', array_column($items, 'value'));
    }

    public function test_hiding_a_gallery_image_removes_it_from_the_public_gallery(): void
    {
        Storage::fake('public');
        $storedPath = UploadedFile::fake()->image('visit.jpg')->store('gallery', 'public');

        $image = GalleryImage::create([
            'path' => $storedPath,
            'caption' => 'Exchange visit',
            'is_visible' => true,
        ]);

        $captions = array_column($this->sectionContent('/gallery', 'gallery')['items'], 'caption');
        $this->assertContains('Exchange visit', $captions);

        $this->actingAs($this->admin);

        Livewire::test(EditGalleryImage::class, ['record' => $image->getRouteKey()])
            ->fillForm(['is_visible' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $captions = array_column($this->sectionContent('/gallery', 'gallery')['items'], 'caption');
        $this->assertNotContains('Exchange visit', $captions);
    }

    public function test_media_folder_is_created_with_generated_slug_and_creator(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreateMediaFolder::class)
            ->fillForm(['name' => 'School Visits 2026', 'slug' => null])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('media_folders', [
            'name' => 'School Visits 2026',
            'slug' => 'school-visits-2026',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_uploading_media_item_converts_image_to_webp_and_records_metadata(): void
    {
        Storage::fake('public');
        $folder = MediaFolder::create(['name' => 'Events']);

        $this->actingAs($this->admin);

        Livewire::test(CreateMediaItem::class)
            ->fillForm([
                'path' => UploadedFile::fake()->image('workshop.jpg', 2400, 1600),
                'folder_id' => $folder->id,
                'collection' => 'general',
                'alt_text' => 'Workshop participants',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $item = MediaItem::firstOrFail();

        $this->assertStringStartsWith('media/', $item->path);
        $this->assertStringEndsWith('.webp', $item->path);
        Storage::disk('public')->assertExists($item->path);
        $this->assertSame('image/webp', $item->mime_type);
        $this->assertSame(1920, $item->width);
        $this->assertSame($this->admin->id, $item->uploaded_by);
        $this->assertSame($folder->id, $item->folder_id);
        $this->assertTrue($item->is_image);
    }

    public function test_super_admin_can_create_user_with_role_and_new_user_can_sign_in(): void
    {
        $this->actingAs($this->admin);

        $editorRole = Role::findByName('Content Editor');

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'New Editor',
                'email' => 'editor@ugandasaxony.org',
                'password' => 'Str0ngPassw0rd!',
                'roles' => [$editorRole->id],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $user = User::where('email', 'editor@ugandasaxony.org')->firstOrFail();
        $this->assertTrue($user->hasRole('Content Editor'));
        $this->assertTrue(Hash::check('Str0ngPassw0rd!', $user->password));

        auth()->logout();

        $this->post('/login', ['email' => 'editor@ugandasaxony.org', 'password' => 'Str0ngPassw0rd!'])
            ->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_creation_requires_unique_email_and_password(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Duplicate',
                'email' => 'admin@ugandasaxony.org',
                'password' => '',
                'roles' => [],
            ])
            ->call('create')
            ->assertHasFormErrors(['email' => 'unique', 'password' => 'required', 'roles' => 'required']);
    }

    public function test_editing_user_without_password_keeps_existing_password(): void
    {
        $user = $this->userWithRole('Viewer');
        $originalHash = $user->password;

        $this->actingAs($this->admin);

        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->fillForm(['name' => 'Renamed Viewer', 'password' => ''])
            ->call('save')
            ->assertHasNoFormErrors();

        $user->refresh();
        $this->assertSame('Renamed Viewer', $user->name);
        $this->assertSame($originalHash, $user->password);
    }

    public function test_admin_cannot_delete_their_own_account_but_can_delete_others(): void
    {
        $other = $this->userWithRole('Viewer');

        $this->actingAs($this->admin);

        Livewire::test(EditUser::class, ['record' => $this->admin->getRouteKey()])
            ->assertActionHidden(DeleteAction::class);

        Livewire::test(EditUser::class, ['record' => $other->getRouteKey()])
            ->callAction(DeleteAction::class);

        $this->assertModelMissing($other);
    }

    public function test_website_administrator_can_view_but_not_manage_users(): void
    {
        $webAdmin = $this->userWithRole('Website Administrator');

        $this->actingAs($webAdmin)->get('/admin/users')->assertSuccessful();
        $this->actingAs($webAdmin)->get('/admin/users/create')->assertForbidden();
        $this->actingAs($webAdmin)->get('/admin/users/'.$this->admin->id.'/edit')->assertForbidden();
    }

    public function test_staff_can_log_out(): void
    {
        $this->actingAs($this->admin)
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
        $this->get('/admin')->assertRedirect('/login');
    }
}
