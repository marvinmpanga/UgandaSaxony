<?php

namespace Tests\Feature;

use App\Filament\Resources\ProfileTemplateResource\Pages\CreateProfileTemplate;
use App\Filament\Resources\ProfileTemplateResource\Pages\EditProfileTemplate;
use App\Filament\Resources\TeamMemberResource\Pages\CreateTeamMember;
use App\Filament\Resources\TeamMemberResource\Pages\EditTeamMember;
use App\Models\ProfileTemplate;
use App\Models\TeamMember;
use App\Models\User;
use Database\Seeders\PageSeeder;
use Database\Seeders\ProfileTemplateSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\TeamMemberSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileTemplateAndTeamMemberTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RoleAndPermissionSeeder::class,
            PageSeeder::class,
        ]);

        $this->admin = User::where('email', 'admin@ugandasaxony.org')->firstOrFail();
    }

    public function test_seeded_organisation_profile_docx_exists_and_renders_on_join_partnership(): void
    {
        $this->seed(ProfileTemplateSeeder::class);

        $this->assertFileExists(storage_path('app/public/templates/UPDATED-TEMPLATE-FOR-ORGANISATION-PROFILE.docx'));

        $this->get('/join-the-partnership')
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->component('public/PageView')
                ->where('page.sections.2.content.cards.0.title', 'Template for Uganda-Saxony Organisation Profile')
                ->where('page.sections.2.content.cards.0.link_label', 'Download (DOCX)')
                ->where('page.sections.2.content.cards.0.link_url', fn (string $url): bool => str_ends_with($url, '/storage/templates/UPDATED-TEMPLATE-FOR-ORGANISATION-PROFILE.docx'))
                ->where('page.sections.2.content.cards.1.title', 'Template for School Partnership Profile')
            );
    }

    public function test_admin_can_upload_edit_and_toggle_profile_template_in_filament(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin);

        $docx = UploadedFile::fake()->create(
            'NEW-PARTNERSHIP-FORM.docx',
            120,
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        );

        Livewire::test(CreateProfileTemplate::class)
            ->fillForm([
                'title' => 'Municipal Partnership Profile',
                'description' => 'For municipalities and districts seeking partners in Saxony.',
                'link_label' => '',
                'file_path' => $docx,
                'is_active' => true,
                'sort_order' => 1,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $template = ProfileTemplate::where('title', 'Municipal Partnership Profile')->firstOrFail();
        $this->assertNotNull($template->file_path);
        Storage::disk('public')->assertExists($template->file_path);
        $this->assertSame('Download (DOCX)', $template->resolved_link_label);

        $this->get('/join-the-partnership')
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->where('page.sections.2.content.cards.0.title', 'Municipal Partnership Profile')
                ->where('page.sections.2.content.cards.0.link_label', 'Download (DOCX)')
                ->where('page.sections.2.content.cards.0.link_url', fn (string $url): bool => str_contains($url, 'NEW-PARTNERSHIP-FORM.docx'))
            );

        Livewire::test(EditProfileTemplate::class, ['record' => $template->getRouteKey()])
            ->fillForm([
                'title' => 'Updated Municipal Profile',
                'link_label' => 'Download Form (DOCX)',
                'is_active' => false,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('profile_templates', [
            'id' => $template->id,
            'title' => 'Updated Municipal Profile',
            'link_label' => 'Download Form (DOCX)',
            'is_active' => false,
        ]);

        $this->get('/join-the-partnership')
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->where('page.sections.2.content.cards', [])
            );
    }

    public function test_seeded_team_members_render_on_contact_us_page(): void
    {
        $this->seed(TeamMemberSeeder::class);

        $this->get('/contact-us')
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->component('public/PageView')
                ->where('page.sections.1.content.people.0.name', 'Nakato Margaret')
                ->where('page.sections.1.content.people.0.role', 'Project Coordinator')
                ->where('page.sections.1.content.people.1.name', 'Fiona Mugisha Okwere')
                ->where('page.sections.1.content.people.1.role', 'Project Officer')
            );
    }

    public function test_admin_can_create_edit_and_hide_team_members_in_filament(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin);

        $portrait = UploadedFile::fake()->image('coordinator.png', 800, 1000);

        Livewire::test(CreateTeamMember::class)
            ->fillForm([
                'name' => 'Samuel Kato',
                'role' => 'Partnerships Liaison',
                'email' => 'samuel.kato@ugandasaxony.org',
                'phones' => [
                    ['number' => '+256 700 111 222'],
                    ['number' => '+256 772 333 444'],
                ],
                'photo_path' => $portrait,
                'is_active' => true,
                'sort_order' => 1,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $member = TeamMember::where('name', 'Samuel Kato')->firstOrFail();
        $this->assertStringEndsWith('.webp', (string) $member->photo_path);
        Storage::disk('public')->assertExists((string) $member->photo_path);

        $this->get('/contact-us')
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->where('page.sections.1.content.people.0.name', 'Samuel Kato')
                ->where('page.sections.1.content.people.0.role', 'Partnerships Liaison')
                ->where('page.sections.1.content.people.0.email', 'samuel.kato@ugandasaxony.org')
                ->where('page.sections.1.content.people.0.phones.0', '+256 700 111 222')
                ->where('page.sections.1.content.people.0.phones.1', '+256 772 333 444')
                ->where('page.sections.1.content.people.0.image_url', fn (string $url): bool => str_ends_with($url, '.webp'))
            );

        Livewire::test(EditTeamMember::class, ['record' => $member->getRouteKey()])
            ->fillForm([
                'name' => 'Samuel Kato',
                'role' => 'Senior Partnerships Liaison',
                'phones' => [
                    ['number' => '+256 700 999 888'],
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->get('/contact-us')
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->where('page.sections.1.content.people.0.role', 'Senior Partnerships Liaison')
                ->where('page.sections.1.content.people.0.phones', ['+256 700 999 888'])
            );

        $member->update(['is_active' => false]);

        $this->get('/contact-us')
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->where('page.sections.1.content.people', [])
            );
    }
}
