# Uganda–Saxony Partnership — Developer Guide

This guide documents the architecture, conventions, workflows, and common maintenance tasks for the **Uganda–Saxony Partnership** web application so future developers can safely understand and extend the codebase.

---

## 1. Architecture & Tech Stack

The application is a monolithic **Laravel 13** app combining two interfaces backed by the same database:

1. **Public Website** — Built with **Inertia.js v3 + React 19 + TypeScript + Tailwind CSS v4**. Server-rendered routes return Inertia responses hydrated from Eloquent models.
2. **Staff CMS Admin Panel (`/admin`)** — Built with **Filament v3**, secured by **Spatie Laravel Permission** (RBAC) and a custom Inertia Staff Sign-In page (`/login`).

### Core Dependencies

| Layer | Technology | Version (from `composer.json` / `package.json`) |
| :--- | :--- | :--- |
| Backend Runtime | PHP (with GD + WebP support) | `^8.3` (dev tested on PHP 8.5) |
| Backend Framework | Laravel | `^13.17` |
| Admin CMS | Filament | `^3.3` |
| Authorization | `spatie/laravel-permission` | `^8.5` |
| Frontend Bridge | `inertiajs/inertia-laravel` & `@inertiajs/react` | `^3.7` / `^3.11` |
| Frontend UI | React + TypeScript + Tailwind CSS v4 + Radix UI + Lucide | React `^19.3`, Tailwind `^4.0`, Vite `^8.0` |
| Rich Text & Charts | TipTap (`@tiptap/*`), Recharts, Sonner (toasts) | TipTap `^3.35`, Recharts `^3.11`, Sonner `^2.0` |
| Testing & Formatting | PHPUnit 12, Laravel Pint | PHPUnit `^12.5`, Pint `^1.27` |

---

## 2. Local Development Setup

### Prerequisites
- **PHP 8.3+** with extensions: `pdo_sqlite` (or MySQL/PostgreSQL), `gd` (with WebP support for `WebpImageService`), `mbstring`, `openssl`, `xml`, `fileinfo`.
- **Composer 2**
- **Node.js 20+** and **npm**

### First-Time Setup

You can run the bundled setup script:

```bash
composer run setup
```

Or run each step manually:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan storage:link
npm install
npm run build
```

> **Storage Symlink:** Uploaded images (article covers, galleries, partner logos, media library, resource attachments) are stored on the `public` disk (`storage/app/public`). Always ensure `public/storage` is symlinked via `php artisan storage:link`.

### Default Seeded Admin Account

`Database\Seeders\RoleAndPermissionSeeder` creates a default Super Admin account for local development:

- **Login URL:** `/login` (or `/admin`, which redirects to `/login`)
- **Email:** `admin@ugandasaxony.org`
- **Password:** `password`

> Change or remove this account before deploying to production.

### Running Locally

```bash
# Run Laravel + Vite together
composer run dev

# Or run frontend Vite HMR separately
npm run dev

# Production frontend build (required whenever JS/CSS changes aren't served by Vite dev server)
npm run build
```

---

## 3. Directory Structure

```text
app/
├── Enums/                  # ContentStatus, SectionType
├── Filament/
│   ├── Resources/          # 8 Filament CMS resources + Create/Edit/List pages
│   └── Widgets/            # CmsStatsOverview, LatestArticlesWidget
├── Http/
│   ├── Controllers/
│   │   ├── Auth/           # AuthenticatedSessionController (custom Staff Sign-In)
│   │   └── Public/         # PageController, EventController, PartnerController,
│   │                       # ResourceCentreController, ContactController
│   ├── Middleware/         # HandleInertiaRequests, SecurityHeaders
│   └── Requests/           # LoginRequest, SendContactMessageRequest
├── Models/                 # Eloquent models (Article, Page, PageSection, Partner, etc.)
├── Policies/               # PagePolicy, MediaItemPolicy
├── Providers/
│   ├── AppServiceProvider.php
│   └── Filament/AdminPanelProvider.php
└── Services/
    └── WebpImageService.php # Automatic GD raster-to-WebP conversion & resizing

config/
└── partnership.php         # Custom config (contact_recipient)

database/
├── factories/              # Model factories for testing
├── migrations/             # Schema definitions
└── seeders/                # DatabaseSeeder, RoleAndPermissionSeeder, PageSeeder,
                            # SiteStatSeeder, ContentSeeder

resources/
├── css/app.css             # Tailwind v4 @theme tokens, animations, .rich-content styles
├── js/
│   ├── app.tsx             # Inertia + React entrypoint + Sonner Toaster
│   ├── components/
│   │   ├── sections/       # HeroSection, ContentSections, InteractiveSections,
│   │   │                   # ListingSections, SectionRenderer, SectionShell
│   │   └── ui/             # Reusable UI primitives (badge, button, card, dialog, etc.)
│   ├── hooks/useReveal.ts  # IntersectionObserver scroll-reveal hook
│   ├── layouts/PublicLayout.tsx # Header, nav dropdowns, mobile drawer, footer, scroll-top
│   ├── lib/utils.ts        # cn() Tailwind class merge helper
│   ├── pages/
│   │   ├── auth/Login.tsx  # Branded Staff Sign-In page
│   │   └── public/         # PageView, EventShow, PartnerShow, ResourceCentreIndex,
│   │                       # ResourceShow, ErrorPage
│   └── types/index.ts      # TypeScript interfaces matching backend Inertia props
└── views/
    ├── app.blade.php       # Root Inertia HTML template + Google Fonts
    ├── errors/             # Blade fallback error views (403, 404, 500, 503)
    └── filament/brand-logo.blade.php

routes/
└── web.php                 # All public, auth, legacy-redirect, and feed routes

tests/
└── Feature/                # EventFeedTest, EventReadPageTest, FilamentAdminTest,
                            # FilamentToWebsiteSyncTest, SecurityTest
```

---

## 4. Routing & Public Page Rendering

### Routes (`routes/web.php`)

| Method | Path | Controller / Action | Name | Notes |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/` | `Public\PageController@home` | `home` | Renders homepage (`is_homepage = true`) + hero articles |
| `GET` | `/events/feed` | `Public\EventController@feed` | `events.feed` | Paginated JSON endpoint (`per_page` 1–24, default 6) for infinite scroll |
| `GET` | `/events/{slug}` | `Public\EventController@show` | `events.show` | Single event/article read page (`public/EventShow`) |
| `GET` | `/partners/{slug}` | `Public\PartnerController@show` | `partners.show` | Single partner profile (`public/PartnerShow`); falls back to directory section entries |
| `GET` | `/resource-centre` | `Public\ResourceCentreController@index` | `resources.index` | Filterable resource catalogue (`public/ResourceCentreIndex`) |
| `GET` | `/resource-centre/{slug}` | `Public\ResourceCentreController@show` | `resources.show` | Single resource detail + attachment download (`public/ResourceShow`) |
| `POST` | `/contact-us` | `Public\ContactController@store` | `contact.store` | Rate-limited `throttle:5,1`; sends email to `config('partnership.contact_recipient')` |
| `GET` | `/{slug}` | `Public\PageController@show` | `pages.show` | Catch-all for published CMS `Page` records; excludes `admin`, `login`, `logout`, `register`, `storage`, `up`, `livewire` |
| `GET/POST` | `/login`, `/logout` | `Auth\AuthenticatedSessionController` | `login`, `login.store`, `logout` | `POST /login` is throttled (`10,1` + 5/min per email+IP); `/admin/login` redirects to `/login` |

**Legacy 301 Redirects:** `routes/web.php` defines permanent `301` redirects from old underscore URLs (e.g. `/about_us` → `/about-us`, `/partner_schools` → `/partner-schools`, `/partner_cso` → `/partner-organisations`, `/contact` → `/contact-us`, etc.).

### Single Source of Truth: Dynamic Section Hydration

Pages are stored in `pages` with ordered `page_sections` (`type` + JSON `content`). However, four section types are **hydrated dynamically at request time** in `App\Http\Controllers\Public\PageController::hydrateDynamicSections()` directly from Filament-managed tables so edits in `/admin` immediately reflect on the live website:

| `SectionType` | Source Model / Query | Behavior |
| :--- | :--- | :--- |
| `stats` | `SiteStat::ordered()` | Replaces `content.items` with live statistics |
| `articles` | `Article::published()` ordered by `COALESCE(event_date, published_at) DESC` | If `content.limit > 0`, returns fixed slice (e.g. homepage). Otherwise paginates 6/page and attaches `content.pagination` with `feed_url` (`/events/feed`) for infinite scroll |
| `directory` | `Partner::active()` ordered by `sort_order, name` | On `partner-schools`, filters categories containing `school` or `vocational`; on `partner-organisations`, includes all other categories. Auto-populates `content.groups` |
| `gallery` | `GalleryImage::visible()` ordered by `sort_order, id DESC` | Replaces `content.items` with visible gallery images |

Additionally, on the **homepage** (`PageController::home()`), up to 5 published `Article` records (prioritizing `featured = true`, then `published_at DESC`) are passed as `featuredArticles` to `resources/js/pages/public/PageView.tsx`, which automatically injects them as slides into the homepage `hero` section.

### Shared Inertia Props & Navigation

`App\Http\Middleware\HandleInertiaRequests::share()` shares three keys with every React page:
- `auth.user` — `{ id, name, email, roles, permissions }` or `null`.
- `flash` — `{ success, error }` from session flash messages.
- `navigation` — Published `Page` records with `show_in_nav = true`, ordered by `nav_order`.

In `resources/js/layouts/PublicLayout.tsx`:
- `NAV_GROUPS` groups specific slugs into header dropdown menus:
  - **Partnerships:** `partner-schools`, `partner-organisations`
  - **Resource Centre:** prepends a synthetic `All Resources` (`/resource-centre`) link alongside `uganda-information`, `saxony-information`, `funding-opportunities`
- `CTA_SLUG` (`join-the-partnership`) is rendered as the highlighted header CTA button.
- `FOOTER_EXCLUDED_SLUGS` hides resource-centre sub-pages and the CTA slug from the main footer column.

---

## 5. Page Section System

Section types are defined in `app/Enums/SectionType.php` and mirrored in `resources/js/types/index.ts`.

### Currently Rendered Sections (`resources/js/components/sections/SectionRenderer.tsx`)

| Enum Value | React Component | File |
| :--- | :--- | :--- |
| `hero` | `HeroSection` | `HeroSection.tsx` |
| `rich_text` | `RichTextSection` | `ContentSections.tsx` |
| `image` | `ImageSection` | `ContentSections.tsx` |
| `image_text` | `ImageTextSection` | `ContentSections.tsx` |
| `stats` | `StatsSection` | `ContentSections.tsx` |
| `featured_cards` | `FeaturedCardsSection` | `ContentSections.tsx` |
| `partner_logos` | `PartnerLogosSection` | `ContentSections.tsx` |
| `cta` | `CtaSection` | `ContentSections.tsx` |
| `gallery` | `GallerySection` | `InteractiveSections.tsx` |
| `tabs` | `TabsSection` | `InteractiveSections.tsx` |
| `people` | `PeopleSection` | `InteractiveSections.tsx` |
| `contact` | `ContactSection` | `InteractiveSections.tsx` |
| `articles` | `ArticlesSection` | `ListingSections.tsx` |
| `directory` | `DirectorySection` | `ListingSections.tsx` |

*(Note: `testimonials`, `video_embed`, and `events_news_highlight` exist in `SectionType` as reserved types and currently return `null` in `SectionRenderer.tsx`.)*

### How to Add a New Section Type

1. **PHP Enum:** Add a new `TitleCase` case and label to `app/Enums/SectionType.php`.
2. **TypeScript Union:** Add the matching string literal to `SectionType` in `resources/js/types/index.ts`.
3. **React Component:** Create the component (wrapped in `SectionShell` from `resources/js/components/sections/SectionShell.tsx` for consistent spacing, reveal animations, and section headers).
4. **Register Renderer:** Add the `case` branch in `resources/js/components/sections/SectionRenderer.tsx`.
5. **Dynamic Data (optional):** If backed by a database table, add a hydration branch in `PageController::hydrateDynamicSections()`.
6. **Build & Test:** Run `npm run build` and add a feature test in `tests/Feature/`.

---

## 6. Filament Admin Panel (CMS)

Configured in `app/Providers/Filament/AdminPanelProvider.php`:
- **Path:** `/admin`
- **Brand:** `Uganda–Saxony CMS`, primary color `#20534f`, font `Plus Jakarta Sans`, custom logo `resources/views/filament/brand-logo.blade.php`.
- **Dashboard Widgets:** `CmsStatsOverview` and `LatestArticlesWidget`.
- **Access Gate:** `User::canAccessPanel()` requires either the `Super Admin` role or the `dashboard.view` permission.

### Registered Filament Resources

| Resource Class | Model | Nav Group | Nav Label | URL Path | Upload Storage Path (WebP) |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `ArticleResource` | `Article` | Content | Events & News | `/admin/articles` | `articles/covers`, `articles/gallery` |
| `PartnerResource` | `Partner` | Content | Partnerships | `/admin/partners` | `partners/logos` (`maxWidth: 600`) |
| `ResourceResource` | `ResourceItem` | Content | Resource Centre | `/admin/resource-centre` | `resources/covers` |
| `GalleryImageResource` | `GalleryImage` | Media & Gallery | Gallery Images | `/admin/gallery-images` | `gallery` |
| `MediaItemResource` | `MediaItem` | Media & Gallery | Media Library | `/admin/media-items` | `media` |
| `MediaFolderResource` | `MediaFolder` | Media & Gallery | Media Folders | `/admin/media-folders` | — |
| `SiteStatResource` | `SiteStat` | Site Settings | Site Statistics | `/admin/site-stats` | — |
| `UserResource` | `User` | Administration | Users | `/admin/users` | — |

### How to Add a New Filament Resource

```bash
php artisan make:filament-resource ModelName --no-interaction
```

Then follow existing resource conventions:
1. Set `$navigationGroup` (`Content`, `Media & Gallery`, `Site Settings`, or `Administration`), `$navigationIcon`, `$navigationLabel`, and `$navigationSort`.
2. Implement `canViewAny()`, `canCreate()`, `canEdit($record)`, and `canDelete($record)` using Spatie permissions.
3. For any `FileUpload` image field, pipe uploads through `WebpImageService`:
   ```php
   ->saveUploadedFileUsing(
       fn (TemporaryUploadedFile $file): string => app(WebpImageService::class)->storeUploadedFile($file, 'your-directory')
   )
   ```

---

## 7. Database Schema, Models & Seeders

### Eloquent Models

| Model | Table | Soft Deletes | Key Relationships & Behavior |
| :--- | :--- | :--- | :--- |
| `User` | `users` | No | `HasRoles` (Spatie), implements `FilamentUser` (`canAccessPanel`) |
| `Page` | `pages` | Yes | `sections()` (`PageSection` ordered by `sort_order`), `creator()`, `updater()`, `publisher()`, `scopePublished()`, casts `status` to `ContentStatus` |
| `PageSection` | `page_sections` | No | `page()`, casts `type` to `SectionType`, `content` & `settings` to `array`, slug helpers `articleSlug()` / `directoryEntrySlug()` |
| `Article` | `articles` | Yes | `images()` (`ArticleImage`), `creator()`, `updater()`, `scopePublished()`, `scopeFeatured()`, auto-generates unique `slug` on creating, `cover_image_url` accessor |
| `ArticleImage` | `article_images` | No | `article()`, `url` accessor |
| `Partner` | `partners` | No | `creator()`, `scopeActive()`, auto-generates unique `slug` on creating, `logo_image_url` accessor |
| `ResourceItem` | `resource_items` | Yes | `creator()`, `updater()`, `scopePublished()`, auto-generates unique `slug`, `cover_image_url` & `attachment_url` accessors |
| `GalleryImage` | `gallery_images` | No | `creator()`, `scopeVisible()`, `url` accessor |
| `SiteStat` | `site_stats` | No | `SiteStat::ordered()` |
| `MediaFolder` | `media_folders` | No | Self-referential `parent()` / `children()`, `items()`, `creator()`, auto-generates unique `slug` |
| `MediaItem` | `media_items` | Yes | `folder()`, `uploader()`, appends `url` and `is_image` |

> **Laravel 13 Convention:** Models use PHP 8 attributes (`#[Fillable([...])]`, `#[Hidden([...])]`, `#[Appends([...])]`) rather than legacy `$fillable` / `$hidden` properties.

### Seeders (`database/seeders/DatabaseSeeder.php`)

Seeders execute in this order and are idempotent (`firstOrCreate` / `updateOrCreate`):
1. `RoleAndPermissionSeeder` — Creates 13 permissions, 6 roles, and the default Super Admin user.
2. `PageSeeder` — Seeds the 10 core public pages (`home`, `about-us`, `partner-schools`, `partner-organisations`, `events`, `gallery`, `uganda-information`, `saxony-information`, `funding-opportunities`, `join-the-partnership`, `contact-us`) and their initial `PageSection` structures.
3. `SiteStatSeeder` — Seeds the 4 default homepage statistics (`countries`, `partner_schools`, `women_organised`, `sdg`).
4. `ContentSeeder` — Seeds initial articles, partners, gallery images, and resource items.

---

## 8. Authentication, Roles & Permissions

Authorization is powered by `spatie/laravel-permission`. In `app/Providers/AppServiceProvider.php`, `Gate::before` grants all abilities automatically to any user with the `Super Admin` role.

### Seeded Roles & Permissions

| Role | Granted Permissions |
| :--- | :--- |
| **Super Admin** | All permissions (plus `Gate::before` bypass) |
| **Website Administrator** | `dashboard.view`, `pages.*`, `media.*`, `users.view`, `settings.manage` |
| **Content Editor** | `dashboard.view`, `pages.view`, `pages.create`, `pages.edit`, `media.view`, `media.upload`, `media.edit` |
| **Content Publisher** | `dashboard.view`, `pages.view`, `pages.edit`, `pages.publish`, `media.view` |
| **Media Manager** | `dashboard.view`, `media.view`, `media.upload`, `media.edit`, `media.delete` |
| **Viewer** | `dashboard.view`, `pages.view`, `media.view` |

### Login Flow
- Visiting `/admin` while unauthenticated redirects to `/login` (`resources/js/pages/auth/Login.tsx`).
- `AuthenticatedSessionController::store()` validates credentials via `LoginRequest` (rate-limited), verifies `$user->canAccessPanel()`, regenerates the session, and issues an `Inertia::location('/admin')` full-page redirect so Filament's Livewire shell loads cleanly.

---

## 9. Media & Automatic WebP Image Pipeline

`App\Services\WebpImageService` handles image optimization for all Filament uploads:
- **Convertible MIME types:** `image/jpeg`, `image/png`, `image/gif`, `image/bmp`, `image/webp`.
- **Non-raster / SVG files:** Stored as-is without conversion.
- **Resizing & Transparency:** Scales down proportionally if wider than `$maxWidth` (default `1920px`; `600px` for partner logos), preserves alpha transparency, encodes to WebP (`quality: 82`), and saves to the `public` disk under a lowercase ULID filename (`{directory}/{ulid}.webp`).
- **Graceful Fallback:** If PHP GD's `imagewebp()` is unavailable or decoding fails, it falls back to storing the original uploaded file.

All image accessors (`cover_image_url`, `logo_image_url`, `url`) support three formats transparently:
- Full external URLs (`http://...` / `https://...`)
- Root-relative public assets (`/images/legacy/...`)
- Relative paths on the `public` storage disk (resolved via `Storage::disk('public')->url(...)`)

---

## 10. Frontend Styling & Conventions

- **Tailwind CSS v4:** Configured via `@tailwindcss/vite` in `vite.config.js` and `@theme` in `resources/css/app.css`.
- **Brand Tokens:**
  - Primary Teal: `#20534f` (`--color-brand`, `--primary`)
  - Dark Teal: `#183f3c` (`--color-brand-dark`)
  - Warm Amber Accent: `#f5a623` (`--color-accent-warm`)
  - Deep Ink: `#0f2624` (`--color-ink`, `--foreground`)
- **Typography:** Loaded in `resources/views/app.blade.php`:
  - Body & Headings: `Plus Jakarta Sans`
  - Stats: `Outfit`
  - Decorative Script Accents (`.title-accent`): `Kaushan Script`
- **Scroll Animations:** `resources/js/hooks/useReveal.ts` toggles `.is-visible` via `IntersectionObserver`, driving `.reveal`, `.stagger-item`, `.reveal-left`, `.split-word`, `.line-draw`, and `.img-reveal` classes in `resources/css/app.css`. All animations respect `@media (prefers-reduced-motion: reduce)`.
- **Rich HTML Rendering:** Content created with Filament's `RichEditor` (Trix) is rendered inside `.rich-content` (via `resources/js/components/ui/rich-content.tsx`), styled in `resources/css/app.css`.
- **Import Alias:** `@/*` resolves to `resources/js/*` (configured in both `tsconfig.json` and `vite.config.js`).

---

## 11. Security & Error Handling

- **HTTP Security Headers (`App\Http\Middleware\SecurityHeaders`):** Attached to both public web routes (`bootstrap/app.php`) and Filament (`AdminPanelProvider.php`). Sets `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy`, and `Strict-Transport-Security` over HTTPS.
- **Rate Limiting:**
  - `POST /login`: `throttle:10,1` at route level + 5 attempts/minute per `email|ip` in `LoginRequest`.
  - `POST /contact-us`: `throttle:5,1`.
- **Password Policy (`AppServiceProvider`):** Minimum 8 characters locally; requires mixed case, numbers, and `uncompromised()` check in production.
- **Error Pages (`bootstrap/app.php`):** Statuses `403`, `404`, `500` (when `APP_DEBUG=false`), and `503` render the branded Inertia page `resources/js/pages/public/ErrorPage.tsx` for browser requests, JSON for API/JSON requests, and Blade fallbacks in `resources/views/errors/`.

---

## 12. Common Maintenance Recipes

### Update Contact Form Recipient
Set `CONTACT_RECIPIENT` in `.env` (read by `config/partnership.php` and used in `App\Http\Controllers\Public\ContactController`).

### Edit Static Page Content (e.g., About Us, Join the Partnership, Contact Details)
Static page copy is initially seeded in `database/seeders/PageSeeder.php`. If you update copy in `PageSeeder.php`, re-run:
```bash
php artisan db:seed --class=PageSeeder
```
*(Dynamic sections like Events, Partners, Stats, and Gallery should be edited via `/admin` instead.)*

### Add a New Public Page with a Custom Controller
1. Create a controller in `app/Http/Controllers/Public/` (`php artisan make:controller Public/ExampleController --no-interaction`).
2. Register the route in `routes/web.php` **above** the catch-all `/{slug}` route.
3. Create the React page in `resources/js/pages/public/ExamplePage.tsx` wrapped in `<PublicLayout>` with an Inertia `<Head title="..." />`.
4. Run `npm run build` and add a feature test in `tests/Feature/`.

### Modify the Database Schema
```bash
php artisan make:migration add_field_to_table --no-interaction
php artisan migrate
```
Update the model's `#[Fillable([...])]` attribute, `casts()`, Filament resource form/table schema, and TypeScript interfaces in `resources/js/types/index.ts`.

---

## 13. Testing, Code Style & Deployment

### Running Tests (PHPUnit)

Tests run against an in-memory SQLite database (`phpunit.xml`):

```bash
# Run full test suite
php artisan test --compact

# Run a specific test file
php artisan test --compact tests/Feature/FilamentToWebsiteSyncTest.php

# Filter a specific test method
php artisan test --compact --filter=testName
```

**Existing Feature Test Suites:**
- `tests/Feature/EventFeedTest.php` — `/events/feed` pagination, ordering, and `per_page` clamping.
- `tests/Feature/EventReadPageTest.php` — `/events/{slug}` rendering, gallery images, related events, 404 on drafts.
- `tests/Feature/FilamentAdminTest.php` — Panel authentication, RBAC access checks, Filament resource pages.
- `tests/Feature/FilamentToWebsiteSyncTest.php` — End-to-end verification that Filament-managed `SiteStat`, `Article`, `Partner`, and `GalleryImage` records hydrate live public pages.
- `tests/Feature/SecurityTest.php` — Security headers, login throttling, contact form rate limiting, error pages.

### Code Formatting (Laravel Pint)

Before committing PHP changes, always run:

```bash
vendor/bin/pint --dirty --format agent
```

TypeScript type-checking:

```bash
npx tsc --noEmit
```

### Production Deployment Checklist
1. Set `.env` values:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_URL=https://your-domain.org`
   - `SESSION_SECURE_COOKIE=true`
   - Configure real `MAIL_*` SMTP credentials and `CONTACT_RECIPIENT`.
   - Configure production database (`DB_CONNECTION=mysql` / `pgsql` or persistent `sqlite`).
2. Install dependencies & build assets:
   ```bash
   composer install --no-dev --optimize-autoloader
   npm ci && npm run build
   ```
3. Run migrations, symlink storage, and warm caches:
   ```bash
   php artisan migrate --force
   php artisan storage:link
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan filament:optimize
   ```
4. Change or remove the default seeded `admin@ugandasaxony.org` password.
