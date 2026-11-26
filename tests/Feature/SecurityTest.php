<?php

namespace Tests\Feature;

use App\Filament\Resources\ArticleResource\Pages\CreateArticle;
use App\Filament\Resources\UserResource;
use App\Filament\Widgets\LatestArticlesWidget;
use App\Models\Article;
use App\Models\Partner;
use App\Models\ResourceItem;
use App\Models\User;
use App\Services\WebpImageService;
use Database\Seeders\PageSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Livewire\Livewire;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RoleAndPermissionSeeder::class,
            PageSeeder::class,
        ]);
    }

    public function test_session_configuration_enforces_standard_laravel_security(): void
    {
        $this->assertTrue(config('session.encrypt'));
        $this->assertTrue(config('session.http_only'));
        $this->assertSame('lax', config('session.same_site'));
        $this->assertSame('json', config('session.serialization'));
    }

    public function test_login_is_rate_limited_after_five_failed_attempts_and_dispatches_lockout(): void
    {
        Event::fake([Lockout::class]);
        RateLimiter::clear('admin@ugandasaxony.org|127.0.0.1');

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'admin@ugandasaxony.org',
                'password' => 'wrong-password',
            ])->assertSessionHasErrors('email');
        }

        // 6th attempt triggers Lockout event and throttle message
        $this->post('/login', [
            'email' => 'admin@ugandasaxony.org',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        Event::assertDispatched(Lockout::class);
        $this->assertGuest();

        RateLimiter::clear('admin@ugandasaxony.org|127.0.0.1');
    }

    public function test_session_is_regenerated_on_login_and_invalidated_on_logout(): void
    {
        $this->get('/login')->assertSuccessful();
        $initialSessionId = session()->getId();

        $this->post('/login', [
            'email' => 'admin@ugandasaxony.org',
            'password' => 'password',
        ])->assertRedirect('/admin');

        $this->assertAuthenticated();
        $this->assertNotSame($initialSessionId, session()->getId());

        $authenticatedSessionId = session()->getId();

        $this->post('/logout')->assertRedirect('/login');

        $this->assertGuest();
        $this->assertNotSame($authenticatedSessionId, session()->getId());
    }

    public function test_security_headers_are_present_on_public_and_admin_responses(): void
    {
        $publicResponse = $this->get('/');
        $publicResponse->assertSuccessful();
        $publicResponse->assertHeader('X-Content-Type-Options', 'nosniff');
        $publicResponse->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $publicResponse->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $publicResponse->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $publicResponse->assertHeader('Content-Security-Policy');

        $admin = User::where('email', 'admin@ugandasaxony.org')->firstOrFail();

        $adminResponse = $this->actingAs($admin)->get('/admin');
        $adminResponse->assertSuccessful();
        $adminResponse->assertHeader('X-Content-Type-Options', 'nosniff');
        $adminResponse->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $adminResponse->assertHeader('Content-Security-Policy');
    }

    public function test_rich_text_models_sanitize_disallowed_html_on_save(): void
    {
        $dirtyHtml = '<p>Hello <strong>World</strong></p><script>window.x=1</script><a href="https://example.org" onclick="return false">Link</a>';

        $article = Article::create([
            'title' => 'Sanitized Article',
            'slug' => 'sanitized-article',
            'body' => $dirtyHtml,
            'status' => 'draft',
        ]);

        $this->assertStringContainsString('<p>Hello <strong>World</strong></p>', (string) $article->body);
        $this->assertStringNotContainsString('<script', (string) $article->body);
        $this->assertStringNotContainsString('onclick', (string) $article->body);
        $this->assertStringContainsString('rel="noopener noreferrer"', (string) $article->body);

        $partner = Partner::create([
            'name' => 'Sanitized Partner',
            'description' => '<p>Partner bio</p><iframe src="https://evil.example"></iframe>',
            'is_active' => true,
        ]);

        $this->assertStringContainsString('<p>Partner bio</p>', (string) $partner->description);
        $this->assertStringNotContainsString('<iframe', (string) $partner->description);

        $resource = ResourceItem::create([
            'title' => 'Sanitized Resource',
            'slug' => 'sanitized-resource',
            'body' => '<p>Guide text</p><svg><text>bad</text></svg>',
            'status' => 'draft',
        ]);

        $this->assertStringContainsString('<p>Guide text</p>', (string) $resource->body);
        $this->assertStringNotContainsString('<svg', (string) $resource->body);
    }

    public function test_webp_image_service_rejects_active_or_executable_uploads(): void
    {
        Storage::fake('public');
        $service = app(WebpImageService::class);

        $svgFile = UploadedFile::fake()->create('icon.svg', 1, 'image/svg+xml');
        $htmlFile = UploadedFile::fake()->create('page.html', 1, 'text/html');
        $phpFile = UploadedFile::fake()->create('shell.php', 1, 'application/x-httpd-php');

        foreach ([$svgFile, $htmlFile, $phpFile] as $file) {
            try {
                $service->storeUploadedFile($file, 'media');
                $this->fail('Expected InvalidArgumentException for '.$file->getClientOriginalName());
            } catch (InvalidArgumentException) {
                $this->assertTrue(true);
            }
        }
    }

    public function test_content_editor_cannot_publish_articles_without_publish_permission(): void
    {
        $editor = User::factory()->create();
        $editor->assignRole('Content Editor');

        $this->actingAs($editor);

        Livewire::test(CreateArticle::class)
            ->fillForm([
                'title' => 'Editor Draft Only',
                'slug' => 'editor-draft-only',
                'status' => 'published',
            ])
            ->call('create')
            ->assertHasFormErrors(['status']);
    }

    public function test_non_super_admin_with_users_manage_cannot_edit_or_delete_super_admin(): void
    {
        $superAdmin = User::where('email', 'admin@ugandasaxony.org')->firstOrFail();
        $manager = User::factory()->create();
        $manager->givePermissionTo(['dashboard.view', 'users.view', 'users.manage']);

        $this->actingAs($manager);

        $this->assertFalse(UserResource::canEdit($superAdmin));
        $this->assertFalse(UserResource::canDelete($superAdmin));
    }

    public function test_latest_articles_widget_is_hidden_from_media_manager(): void
    {
        $mediaManager = User::factory()->create();
        $mediaManager->assignRole('Media Manager');

        $this->actingAs($mediaManager);
        $this->assertFalse(LatestArticlesWidget::canView());

        $editor = User::factory()->create();
        $editor->assignRole('Content Editor');

        $this->actingAs($editor);
        $this->assertTrue(LatestArticlesWidget::canView());
    }
}
