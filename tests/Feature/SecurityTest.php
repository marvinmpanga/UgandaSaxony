<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PageSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
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

        $admin = User::where('email', 'admin@ugandasaxony.org')->firstOrFail();

        $adminResponse = $this->actingAs($admin)->get('/admin');
        $adminResponse->assertSuccessful();
        $adminResponse->assertHeader('X-Content-Type-Options', 'nosniff');
        $adminResponse->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }
}
