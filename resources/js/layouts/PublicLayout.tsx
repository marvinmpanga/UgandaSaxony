import { Link, usePage } from '@inertiajs/react';
import { ArrowUp, ChevronDown, Eye, Menu, X } from 'lucide-react';
import * as React from 'react';
import { cn } from '@/lib/utils';
import type { NavPage, SharedProps } from '@/types';

interface PublicLayoutProps {
    children: React.ReactNode;
    isPreview?: boolean;
    editUrl?: string;
    hasDarkHero?: boolean;
}

const NAV_GROUPS: Array<{ label: string; slugs: string[] }> = [
    { label: 'Partnerships', slugs: ['partner-schools', 'partner-organisations'] },
    { label: 'Resource Centre', slugs: ['uganda-information', 'saxony-information', 'funding-opportunities'] },
];

const CTA_SLUG = 'join-the-partnership';

const FOOTER_EXCLUDED_SLUGS = new Set([
    'uganda-information',
    'saxony-information',
    'funding-opportunities',
    CTA_SLUG,
]);

type NavEntry = { type: 'link'; page: NavPage } | { type: 'group'; label: string; pages: NavPage[] };

function buildNav(pages: NavPage[]): { entries: NavEntry[]; cta?: NavPage } {
    const cta = pages.find((p) => p.slug === CTA_SLUG);
    const grouped = new Set(NAV_GROUPS.flatMap((g) => g.slugs));
    const placed = new Set<string>();
    const entries: NavEntry[] = [];

    pages.forEach((page) => {
        if (page.slug === CTA_SLUG) return;
        const group = NAV_GROUPS.find((g) => g.slugs.includes(page.slug));
        if (group) {
            if (placed.has(group.label)) return;
            placed.add(group.label);
            const members = group.slugs
                .map((slug) => pages.find((p) => p.slug === slug))
                .filter((p): p is NavPage => Boolean(p));
            if (group.label === 'Resource Centre') {
                members.unshift({
                    id: -1,
                    title: 'All Resources',
                    slug: 'resource-centre',
                    is_homepage: false,
                });
            }
            entries.push({ type: 'group', label: group.label, pages: members });
            return;
        }
        if (!grouped.has(page.slug)) entries.push({ type: 'link', page });
    });

    return { entries, cta };
}

const hrefFor = (page: NavPage) => (page.is_homepage ? '/' : `/${page.slug}`);

export function PublicLayout({ children, isPreview = false, editUrl, hasDarkHero = false }: PublicLayoutProps) {
    const { navigation, auth } = usePage<SharedProps>().props;
    const { url } = usePage();
    const [mobileOpen, setMobileOpen] = React.useState(false);
    const [scrolled, setScrolled] = React.useState(false);
    const [showScrollTop, setShowScrollTop] = React.useState(false);
    const [openGroup, setOpenGroup] = React.useState<string | null>(null);
    const [mobileOpenGroup, setMobileOpenGroup] = React.useState<string | null>(null);
    const { entries, cta } = React.useMemo(() => buildNav(navigation), [navigation]);
    const currentPath = url.split('?')[0];

    React.useEffect(() => {
        const onScroll = () => {
            setScrolled(window.scrollY > 40);
            setShowScrollTop(window.scrollY > window.innerHeight * 0.7);
        };
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
        return () => window.removeEventListener('scroll', onScroll);
    }, []);

    React.useEffect(() => {
        setMobileOpen(false);
        setOpenGroup(null);
        setMobileOpenGroup(null);
    }, [currentPath]);

    React.useEffect(() => {
        document.body.style.overflow = mobileOpen ? 'hidden' : '';
        return () => { document.body.style.overflow = ''; };
    }, [mobileOpen]);

    const isActive = (page: NavPage) => {
        const href = hrefFor(page);
        return currentPath === href || (!page.is_homepage && currentPath.startsWith(`${href}/`));
    };
    const lightHeader = !hasDarkHero;

    /* Nav link — matches footer's bold, normal-case typography */
    const navLinkClass = (active: boolean) =>
        cn(
            'relative py-1 text-[15px] font-bold tracking-[-0.01em] transition-colors',
            'after:absolute after:inset-x-0 after:-bottom-0.5 after:h-[2px] after:origin-left after:rounded-full after:transition-transform after:duration-300',
            lightHeader
                ? cn(
                      'text-primary hover:text-brand-dark',
                      active ? 'after:scale-x-100 after:bg-accent-warm' : 'after:scale-x-0 after:bg-accent-warm hover:after:scale-x-100',
                  )
                : cn(
                      'text-white hover:text-white/85',
                      active ? 'after:scale-x-100 after:bg-accent-warm' : 'after:scale-x-0 after:bg-accent-warm hover:after:scale-x-100',
                  ),
        );

    return (
        <div className="theme-public flex min-h-screen flex-col">

            {/* ── Preview banner ── */}
            {isPreview && (
                <div className="fixed inset-x-0 top-0 z-[60] flex items-center justify-between bg-amber-400 px-4 py-2 text-sm font-medium text-amber-950">
                    <div className="flex items-center gap-2">
                        <Eye className="h-4 w-4" />
                        <span>Preview mode — this is how the page will look when published.</span>
                    </div>
                    {editUrl && (
                        <a href={editUrl} className="underline hover:no-underline">
                            Back to editor
                        </a>
                    )}
                </div>
            )}

            {/* ── Header: transparent over dark hero, clean white bar on inner pages ── */}
            <header
                className={cn(
                    'fixed inset-x-0 z-50 transition-all duration-500',
                    isPreview ? 'top-9' : 'top-0',
                    lightHeader
                        ? 'border-b border-border bg-white/95 py-3.5 backdrop-blur-md'
                        : scrolled
                          ? 'bg-primary/95 py-3 backdrop-blur-md'
                          : 'bg-transparent py-6',
                )}
            >
                <div className="mx-auto flex max-w-[1400px] items-center justify-between px-6 sm:px-10 lg:px-16">

                    {/* Left: logo */}
                    <Link href="/" aria-label="Uganda–Saxony Partnership home" className="shrink-0">
                        <img
                            src="/images/legacy/logo.png"
                            alt="Uganda–Saxony Partnership"
                            className="h-11 w-11 rounded-full bg-white object-contain p-0.5 sm:h-12 sm:w-12"
                        />
                    </Link>

                    {/* Centre: nav links (desktop) */}
                    <nav className="hidden items-center gap-8 lg:flex xl:gap-10" aria-label="Main navigation">
                        {entries.map((entry) =>
                            entry.type === 'link' ? (
                                <Link key={entry.page.id} href={hrefFor(entry.page)} className={navLinkClass(isActive(entry.page))}>
                                    {entry.page.title}
                                </Link>
                            ) : (
                                <div
                                    key={entry.label}
                                    className="relative"
                                    onMouseEnter={() => setOpenGroup(entry.label)}
                                    onMouseLeave={() => setOpenGroup(null)}
                                >
                                    <button
                                        type="button"
                                        aria-expanded={openGroup === entry.label}
                                        onClick={() => setOpenGroup(openGroup === entry.label ? null : entry.label)}
                                        className={cn(navLinkClass(entry.pages.some(isActive)), 'flex items-center gap-1.5')}
                                    >
                                        {entry.label}
                                        <ChevronDown
                                            className={cn(
                                                'h-3.5 w-3.5 transition-transform duration-200',
                                                openGroup === entry.label && 'rotate-180',
                                            )}
                                        />
                                    </button>
                                    <div
                                        className={cn(
                                            'absolute left-1/2 top-full min-w-56 -translate-x-1/2 pt-3.5 transition-all duration-200 ease-out origin-top',
                                            openGroup === entry.label
                                                ? 'visible translate-y-0 opacity-100'
                                                : 'pointer-events-none invisible translate-y-1.5 opacity-0',
                                        )}
                                    >
                                        <div className="relative rounded-none border border-border/80 bg-white py-1.5 shadow-[0_20px_40px_-12px_rgba(15,38,36,0.22)]">
                                            {/* Top arrow pointing to the nav item */}
                                            <span
                                                aria-hidden="true"
                                                className="absolute -top-[6px] left-1/2 h-2.5 w-2.5 -translate-x-1/2 rotate-45 border-l border-t border-border/80 bg-white"
                                            />
                                            <ul className="relative divide-y divide-border/50">
                                                {entry.pages.map((page) => {
                                                    const active = isActive(page);

                                                    return (
                                                        <li key={page.id}>
                                                            <Link
                                                                href={hrefFor(page)}
                                                                className={cn(
                                                                    'block whitespace-nowrap px-5 py-3 text-[13px] font-semibold tracking-wide transition-colors',
                                                                    active
                                                                        ? 'bg-primary/8 text-primary'
                                                                        : 'text-foreground hover:bg-muted/70 hover:text-primary',
                                                                )}
                                                            >
                                                                {page.title}
                                                            </Link>
                                                        </li>
                                                    );
                                                })}
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            ),
                        )}
                    </nav>

                    {/* Right: menu button */}
                    <div className="flex items-center">
                        <button
                            type="button"
                            onClick={() => setMobileOpen(true)}
                            aria-label="Open menu"
                            className={cn(
                                'flex h-10 w-10 items-center justify-center transition',
                                lightHeader ? 'text-foreground hover:text-primary' : 'text-white hover:text-white/70',
                            )}
                        >
                            <Menu className="h-5 w-5" strokeWidth={1.5} />
                        </button>
                    </div>
                </div>
            </header>

            {/* ── Slide-in menu panel (right side, Vicshoot style) ── */}
            {/* Backdrop */}
            <div
                className={cn(
                    'fixed inset-0 z-[69] bg-black/50 transition-opacity duration-300',
                    mobileOpen ? 'opacity-100' : 'pointer-events-none opacity-0',
                )}
                onClick={() => setMobileOpen(false)}
                aria-hidden="true"
            />

            {/* Panel */}
            <div
                style={{ backgroundColor: '#20534F' }}
                className={cn(
                    'fixed right-0 top-0 z-[70] flex h-full w-[460px] max-w-[92vw] flex-col overflow-y-auto shadow-2xl transition-transform duration-500 ease-in-out',
                    mobileOpen ? 'translate-x-0' : 'translate-x-full',
                )}
                role="dialog"
                aria-modal="true"
                aria-label="Menu"
            >
                {/* Close */}
                <div className="flex items-center justify-end px-8 pt-8 sm:px-12 sm:pt-10">
                    <button
                        type="button"
                        onClick={() => setMobileOpen(false)}
                        className="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-white/70 transition hover:text-white"
                        aria-label="Close menu"
                    >
                        Close <X className="h-5 w-5" strokeWidth={1.75} />
                    </button>
                </div>

                {/* Logo */}
                <div className="my-8 flex justify-center px-8 sm:my-10 sm:px-12 lg:my-12">
                    <img
                        src="/images/legacy/logo.png"
                        alt="Uganda–Saxony Partnership"
                        className="h-24 w-24 rounded-full bg-white object-contain p-2 sm:h-28 sm:w-28 lg:h-36 lg:w-36"
                    />
                </div>

                {/* Mobile navigation (hidden on desktop where top navbar is visible) */}
                <nav
                    aria-label="Mobile navigation"
                    className="border-t border-white/20 px-8 py-8 sm:px-12 lg:hidden"
                >
                    <p className="mb-5 text-xs font-semibold uppercase tracking-[0.2em] text-white/50">
                        Menu
                    </p>
                    <ul className="space-y-4">
                        {entries.map((entry) =>
                            entry.type === 'link' ? (
                                <li key={entry.page.id}>
                                    <Link
                                        href={hrefFor(entry.page)}
                                        className={cn(
                                            'flex items-center gap-2.5 text-xl font-bold tracking-[-0.01em] transition',
                                            isActive(entry.page)
                                                ? 'text-accent-warm'
                                                : 'text-white hover:text-white/75',
                                        )}
                                    >
                                        {isActive(entry.page) && (
                                            <span className="h-1.5 w-1.5 shrink-0 rounded-full bg-accent-warm" />
                                        )}
                                        {entry.page.title}
                                    </Link>
                                </li>
                            ) : (
                                <li key={entry.label}>
                                    <button
                                        type="button"
                                        aria-expanded={mobileOpenGroup === entry.label}
                                        onClick={() =>
                                            setMobileOpenGroup(mobileOpenGroup === entry.label ? null : entry.label)
                                        }
                                        className={cn(
                                            'flex w-full items-center justify-between text-left text-xl font-bold tracking-[-0.01em] transition',
                                            entry.pages.some(isActive)
                                                ? 'text-accent-warm'
                                                : 'text-white hover:text-white/75',
                                        )}
                                    >
                                        <span>{entry.label}</span>
                                        <ChevronDown
                                            className={cn(
                                                'h-5 w-5 text-white/70 transition-transform duration-200',
                                                mobileOpenGroup === entry.label && 'rotate-180 text-accent-warm',
                                            )}
                                        />
                                    </button>
                                    {mobileOpenGroup === entry.label && (
                                        <ul className="mt-3 space-y-2.5 border-l border-white/20 pl-4">
                                            {entry.pages.map((page) => (
                                                <li key={page.id}>
                                                    <Link
                                                        href={hrefFor(page)}
                                                        className={cn(
                                                            'block py-0.5 text-base font-semibold transition',
                                                            isActive(page)
                                                                ? 'text-accent-warm'
                                                                : 'text-white/80 hover:text-white',
                                                        )}
                                                    >
                                                        {page.title}
                                                    </Link>
                                                </li>
                                            ))}
                                        </ul>
                                    )}
                                </li>
                            ),
                        )}
                    </ul>
                    {cta && (
                        <Link
                            href={hrefFor(cta)}
                            className="mt-7 inline-flex w-full items-center justify-center rounded-full bg-accent-warm px-6 py-3 text-sm font-bold text-ink transition hover:brightness-105"
                        >
                            {cta.title}
                        </Link>
                    )}
                </nav>

                {/* Contact info */}
                <div className="border-t border-white/20 px-8 py-10 sm:px-12 sm:py-12">
                    <p className="mb-6 text-xs font-semibold uppercase tracking-[0.2em] text-white/50">
                        Contact
                    </p>
                    <div className="space-y-6">
                        <a
                            href="mailto:info@uganda-saxonypartnership.org"
                            className="flex items-center gap-4 text-lg font-bold text-white transition hover:text-white/75 sm:text-xl"
                        >
                            <svg className="h-5 w-5 shrink-0 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75">
                                <rect x="2" y="4" width="20" height="16" rx="2" />
                                <path d="m2 7 10 7 10-7" />
                            </svg>
                            <span className="break-all">info@uganda-saxonypartnership.org</span>
                        </a>
                        <div className="flex items-center gap-4 text-lg font-bold text-white sm:text-xl">
                            <svg className="h-5 w-5 shrink-0 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75">
                                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" />
                                <circle cx="12" cy="9" r="2.5" />
                            </svg>
                            <span>Kampala, Uganda</span>
                        </div>
                    </div>
                </div>

                {/* Follow Us */}
                <div className="border-t border-white/20 px-8 py-10 sm:px-12 sm:py-12">
                    <p className="mb-6 text-xs font-semibold uppercase tracking-[0.2em] text-white/50">
                        Follow Us
                    </p>
                    <div className="flex items-center gap-4">
                        {/* Facebook */}
                        <a href="https://www.facebook.com/profile.php?id=100094606894335" target="_blank" rel="noopener noreferrer" aria-label="Facebook"
                            className="flex h-12 w-12 items-center justify-center border border-white/25 text-white/75 transition hover:border-white hover:text-white">
                            <svg className="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" /></svg>
                        </a>
                        {/* Twitter/X */}
                        <a href="https://twitter.com/uganda_saxony" target="_blank" rel="noopener noreferrer" aria-label="X / Twitter"
                            className="flex h-12 w-12 items-center justify-center border border-white/25 text-white/75 transition hover:border-white hover:text-white">
                            <svg className="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.253 5.622 5.911-5.622Zm-1.161 17.52h1.833L7.084 4.126H5.117z" /></svg>
                        </a>
                        {/* Instagram */}
                        <a href="https://www.instagram.com/uganda_saxony_partnership/" target="_blank" rel="noopener noreferrer" aria-label="Instagram"
                            className="flex h-12 w-12 items-center justify-center border border-white/25 text-white/75 transition hover:border-white hover:text-white">
                            <svg className="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z" /></svg>
                        </a>
                    </div>
                </div>

                {/* Staff login */}
                <div className="mt-auto border-t border-white/20 px-8 py-8 sm:px-12 sm:py-10">
                    <a
                        href={auth.user ? '/admin' : '/login'}
                        className="text-lg font-bold text-white transition hover:text-white/70 sm:text-xl"
                    >
                        Staff login
                    </a>
                </div>
            </div>

            <main className="flex-1">{children}</main>

            {/* ── Footer ── */}
            <footer style={{ backgroundColor: '#20534F' }}>
                <div className="mx-auto max-w-7xl px-6 py-16 sm:px-10 lg:px-16">

                    {/* Upper: logo left · nav right */}
                    <div className="flex flex-col gap-12 lg:flex-row lg:items-start lg:justify-between">
                        <div className="shrink-0">
                            <img
                                src="/images/legacy/logo.png"
                                alt="Uganda–Saxony Partnership"
                                className="w-40 rounded-full bg-white object-contain p-2 sm:w-52 lg:w-64"
                            />
                        </div>

                        <nav aria-label="Footer navigation" className="flex flex-col gap-5 sm:gap-6 lg:items-end">
                            {navigation
                                .filter((page) => !FOOTER_EXCLUDED_SLUGS.has(page.slug))
                                .map((page) => (
                                    <Link
                                        key={page.id}
                                        href={hrefFor(page)}
                                        className="text-lg font-bold text-white transition hover:text-white/70 sm:text-xl lg:text-[22px]"
                                    >
                                        {page.title}
                                    </Link>
                                ))}
                        </nav>
                    </div>

                    <hr className="mt-14 border-white/20" />

                    {/* Middle: tagline + funding */}
                    <div className="mt-10 flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">
                        <p className="max-w-2xl text-[22px] font-normal leading-relaxed text-white sm:text-2xl">
                            Building partnerships for sustainable development between Uganda and the Free State of Saxony.
                        </p>

                        <img
                            src="/images/legacy/saxony-coat-of-arms.jpeg"
                            alt="This project is financed from tax funds based on the budget passed by the members of the Parliament of the Free State of Saxony."
                            className="h-28 w-auto shrink-0 self-start rounded-lg object-contain sm:h-32 lg:h-36 lg:self-auto"
                        />
                    </div>

                    <hr className="mt-12 border-white/20" />

                    {/* Bottom: copyright left · coordinator + socials right */}
                    <div className="mt-6 flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
                        <p className="text-sm text-white/40">
                            © {new Date().getFullYear()} Uganda–Saxony Partnership. All rights reserved.
                        </p>

                        <div className="flex flex-col items-start gap-3 sm:items-end">
                            <p className="text-sm text-white/50">Coordinated by KWDT &amp; ENS · Kampala, Uganda</p>

                            <div className="flex flex-wrap items-center gap-6">
                                <div className="flex items-center gap-3">
                                    <span className="text-[11px] font-semibold uppercase tracking-wider text-white/40">Uganda</span>
                                    <a href="https://twitter.com/uganda_saxony" target="_blank" rel="noopener noreferrer" aria-label="Uganda–Saxony X / Twitter" className="text-white/50 transition hover:text-white">
                                        <svg className="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.253 5.622 5.911-5.622Zm-1.161 17.52h1.833L7.084 4.126H5.117z" /></svg>
                                    </a>
                                    <a href="https://www.facebook.com/profile.php?id=100094606894335" target="_blank" rel="noopener noreferrer" aria-label="Uganda–Saxony Facebook" className="text-white/50 transition hover:text-white">
                                        <svg className="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" /></svg>
                                    </a>
                                    <a href="https://www.instagram.com/uganda_saxony_partnership/" target="_blank" rel="noopener noreferrer" aria-label="Uganda–Saxony Instagram" className="text-white/50 transition hover:text-white">
                                        <svg className="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z" /></svg>
                                    </a>
                                </div>
                                <div className="flex items-center gap-3">
                                    <span className="text-[11px] font-semibold uppercase tracking-wider text-white/40">Saxony</span>
                                    <a href="https://www.facebook.com/profile.php?id=100094409740783" target="_blank" rel="noopener noreferrer" aria-label="Saxony Germany Facebook" className="text-white/50 transition hover:text-white">
                                        <svg className="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" /></svg>
                                    </a>
                                    <a href="https://www.instagram.com/ugandasachsenpartnerschaft/" target="_blank" rel="noopener noreferrer" aria-label="Saxony Germany Instagram" className="text-white/50 transition hover:text-white">
                                        <svg className="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z" /></svg>
                                    </a>
                                </div>
                            </div>

                            <a
                                href={auth.user ? '/admin' : '/login'}
                                className="text-sm font-semibold text-white/60 transition hover:text-white"
                            >
                                Staff login
                            </a>
                        </div>
                    </div>

                </div>
            </footer>

            {/* ── Floating scroll-to-top button (Vicshoot style with vertical accent line) ── */}
            <button
                type="button"
                onClick={() => window.scrollTo({ top: 0, behavior: 'smooth' })}
                aria-label="Scroll to top"
                className={cn(
                    'scroll-top-btn fixed bottom-7 right-7 z-40 flex h-11 w-11 items-center justify-center rounded-full border border-primary/20 bg-white/95 text-primary shadow-lg backdrop-blur-sm transition-all duration-500 hover:border-accent-warm hover:bg-primary hover:text-white',
                    showScrollTop
                        ? 'is-shown translate-y-0 opacity-100'
                        : 'pointer-events-none translate-y-4 opacity-0',
                )}
            >
                <ArrowUp className="h-4 w-4" />
            </button>
        </div>
    );
}
