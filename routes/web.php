<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\EventController;
use App\Http\Controllers\Public\PageController as PublicPageController;
use App\Http\Controllers\Public\PartnerController as PublicPartnerController;
use App\Http\Controllers\Public\ResourceCentreController;
use Illuminate\Support\Facades\Route;

// Authentication (custom branded Staff Sign In page used for Filament too)
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('login.store');
});

Route::redirect('/admin/login', '/login');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Public Website routes
Route::post('/contact-us', [ContactController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');

// Legacy URLs from the previous website
foreach ([
    'about_us' => 'about-us',
    'partner_schools' => 'partner-schools',
    'partner_cso' => 'partner-organisations',
    'events' => 'events',
    'gallery' => 'gallery',
    'uganda_information' => 'uganda-information',
    'saxony_information' => 'saxony-information',
    'funding_opportunities' => 'funding-opportunities',
    'join_partnership' => 'join-the-partnership',
    'contact' => 'contact-us',
] as $legacyPath => $newPath) {
    if ($legacyPath !== $newPath) {
        Route::redirect('/'.$legacyPath, '/'.$newPath, 301);
    }
}

Route::get('/', [PublicPageController::class, 'home'])->name('home');
Route::get('/events/feed', [EventController::class, 'feed'])->name('events.feed');
Route::get('/events/{slug}', [EventController::class, 'show'])
    ->where('slug', '[a-zA-Z0-9\-_]+')
    ->name('events.show');
Route::get('/partners/{slug}', [PublicPartnerController::class, 'show'])
    ->where('slug', '[a-zA-Z0-9\-_]+')
    ->name('partners.show');
Route::get('/resource-centre', [ResourceCentreController::class, 'index'])
    ->name('resources.index');
Route::get('/resource-centre/{slug}', [ResourceCentreController::class, 'show'])
    ->where('slug', '[a-zA-Z0-9\-_]+')
    ->name('resources.show');
Route::get('/{slug}', [PublicPageController::class, 'show'])
    ->where('slug', '^(?!admin|login|logout|register|storage|up|livewire)[a-zA-Z0-9\-_]+$')
    ->name('pages.show');
