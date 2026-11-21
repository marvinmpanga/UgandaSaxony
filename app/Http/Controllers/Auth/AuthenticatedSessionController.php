<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the branded Staff Sign In page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Login');
    }

    /**
     * Handle an incoming authentication request using standard Laravel session security.
     *
     * @throws ValidationException
     */
    public function store(LoginRequest $request): SymfonyResponse
    {
        $request->authenticate();

        $user = $request->user();
        $panel = Filament::getDefaultPanel();

        if (! $user || ! $user->canAccessPanel($panel)) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Your account does not have permission to access the CMS.',
            ]);
        }

        $request->session()->regenerate();

        $targetUrl = redirect()->intended('/admin')->getTargetUrl();

        return Inertia::location($targetUrl);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
