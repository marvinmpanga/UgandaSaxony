import { Link } from '@inertiajs/react';
import { ArrowUpRight, Calendar, Loader2, MapPin, Search } from 'lucide-react';
import * as React from 'react';
import { SmartLink } from '@/components/sections/ContentSections';
import { SectionHeading, SectionShell } from '@/components/sections/SectionShell';
import { stripHtml } from '@/components/ui/rich-content';
import { cn } from '@/lib/utils';
import type { ArticleItem } from '@/types';

type Content = Record<string, any>;

function fallbackSlug(title: string): string {
    return title
        .toLowerCase()
        .normalize('NFKD')
        .replace(/[''`]/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

interface ArticlesPagination {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    has_more: boolean;
    feed_url?: string;
}

/**
 * Events / news cards with staggered editorial layout and infinite-scroll lazy loading.
 * Selecting a card navigates to its dedicated event read page.
 */
export function ArticlesSection({ content }: { content: Content }) {
    const initialItems: ArticleItem[] = React.useMemo(() => {
        const all: ArticleItem[] = Array.isArray(content.items) ? content.items : [];
        return content.limit ? all.slice(0, Number(content.limit)) : all;
    }, [content.items, content.limit]);

    const initialPagination: ArticlesPagination | null =
        !content.limit && content.pagination && typeof content.pagination === 'object'
            ? (content.pagination as ArticlesPagination)
            : null;

    const [items, setItems] = React.useState<ArticleItem[]>(initialItems);
    const [pagination, setPagination] = React.useState<ArticlesPagination | null>(initialPagination);
    const [loadingMore, setLoadingMore] = React.useState(false);
    const [loadError, setLoadError] = React.useState(false);
    const sentinelRef = React.useRef<HTMLDivElement | null>(null);

    React.useEffect(() => {
        setItems(initialItems);
        setPagination(initialPagination);
        setLoadError(false);
    }, [initialItems, initialPagination]);

    const loadMore = React.useCallback(async () => {
        if (!pagination || !pagination.has_more || loadingMore) {
            return;
        }

        const feedUrl = pagination.feed_url || '/events/feed';
        const nextPage = pagination.current_page + 1;
        setLoadingMore(true);
        setLoadError(false);

        try {
            const response = await fetch(`${feedUrl}?page=${nextPage}&per_page=${pagination.per_page}`, {
                headers: { Accept: 'application/json' },
            });
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const payload = (await response.json()) as {
                data?: ArticleItem[];
                meta?: Omit<ArticlesPagination, 'feed_url'>;
            };

            const nextItems = Array.isArray(payload.data) ? payload.data : [];
            setItems((prev) => {
                const seen = new Set(prev.map((entry) => entry.slug || fallbackSlug(entry.title)));
                const unique = nextItems.filter((entry) => !seen.has(entry.slug || fallbackSlug(entry.title)));
                return [...prev, ...unique];
            });

            if (payload.meta) {
                setPagination({ ...payload.meta, feed_url: feedUrl });
            } else {
                setPagination((prev) => (prev ? { ...prev, has_more: false } : null));
            }
        } catch {
            setLoadError(true);
        } finally {
            setLoadingMore(false);
        }
    }, [pagination, loadingMore]);

    React.useEffect(() => {
        const sentinel = sentinelRef.current;
        if (!sentinel || !pagination?.has_more || loadingMore || loadError || typeof IntersectionObserver === 'undefined') {
            return;
        }

        const observer = new IntersectionObserver(
            (entries) => {
                if (entries.some((entry) => entry.isIntersecting)) {
                    void loadMore();
                }
            },
            { rootMargin: '250px 0px' },
        );

        observer.observe(sentinel);

        return () => observer.disconnect();
    }, [pagination?.has_more, loadingMore, loadError, loadMore]);

    return (
        <SectionShell>
            <SectionHeading
                index={content.index}
                label={content.label}
                title={String(content.heading ?? '')}
                accent={content.accent}
                description={content.body}
                divided
                action={
                    content.view_all_url ? (
                        <SmartLink
                            href={String(content.view_all_url)}
                            className="group inline-flex items-center gap-2 rounded-full border border-border px-5 py-2.5 text-sm font-semibold text-foreground transition hover:border-primary hover:text-primary"
                        >
                            {String(content.view_all_label ?? 'View all')}
                            <ArrowUpRight className="h-4 w-4 transition group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
                        </SmartLink>
                    ) : undefined
                }
            />

            <div className="grid gap-10 md:grid-cols-2 lg:grid-cols-3 lg:gap-12">
                {items.map((item, i) => {
                    const slug = item.slug || fallbackSlug(item.title);
                    const textFirstOnDesktop = i % 3 === 1;
                    const mediaBlock = (
                        <div className="img-reveal relative aspect-[4/3] rounded-3xl bg-secondary shadow-sm">
                            {item.image_url && (
                                <img
                                    src={item.image_url}
                                    alt=""
                                    loading="lazy"
                                    className="h-full w-full object-cover"
                                />
                            )}
                            {item.tag && (
                                <span className="absolute left-4 top-4 rounded-full bg-primary px-3.5 py-1.5 text-xs font-semibold uppercase tracking-wider text-white shadow-sm">
                                    {item.tag}
                                </span>
                            )}
                        </div>
                    );
                    const textBlock = (
                        <div className="flex flex-1 flex-col gap-3.5">
                            {item.date && (
                                <span className="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                    <Calendar className="h-4 w-4 text-primary" /> {item.date}
                                </span>
                            )}
                            <h3 className="text-xl font-bold leading-snug tracking-tight text-foreground transition-colors duration-300 group-hover:text-primary sm:text-2xl">
                                {item.title}
                            </h3>
                            {item.excerpt && <p className="line-clamp-3 text-base leading-relaxed text-muted-foreground">{item.excerpt}</p>}
                            <span className="mt-2 inline-flex w-fit items-center gap-2 rounded-full bg-primary/10 px-5 py-2 text-sm font-semibold text-primary transition-all duration-300 group-hover:bg-primary group-hover:text-white">
                                Read more <ArrowUpRight className="h-4 w-4 transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
                            </span>
                        </div>
                    );

                    return (
                        <Link
                            key={slug || i}
                            href={`/events/${slug}`}
                            className="stagger-item group flex flex-col gap-6 text-left"
                            style={{ '--stagger-index': i % 6 } as React.CSSProperties}
                        >
                            {textFirstOnDesktop ? (
                                <>
                                    <div className="order-2 lg:order-1">{textBlock}</div>
                                    <div className="order-1 lg:order-2">{mediaBlock}</div>
                                </>
                            ) : (
                                <>
                                    {mediaBlock}
                                    {textBlock}
                                </>
                            )}
                        </Link>
                    );
                })}
            </div>

            {pagination && (
                <div ref={sentinelRef} className="mt-14 flex flex-col items-center justify-center gap-4">
                    {loadingMore && (
                        <div className="inline-flex items-center gap-2.5 text-sm font-semibold text-primary">
                            <Loader2 className="h-4 w-4 animate-spin" />
                            Loading more events…
                        </div>
                    )}

                    {!loadingMore && pagination.has_more && (
                        <button
                            type="button"
                            onClick={() => void loadMore()}
                            className="inline-flex items-center gap-2 rounded-full border border-border px-6 py-2.5 text-sm font-semibold text-primary transition hover:border-primary hover:bg-primary hover:text-white"
                        >
                            {loadError ? 'Retry loading events' : 'Load more events'}
                        </button>
                    )}

                    {!pagination.has_more && pagination.total > pagination.per_page && (
                        <p className="text-xs font-medium uppercase tracking-[0.14em] text-muted-foreground">
                            Showing all {pagination.total} events
                        </p>
                    )}
                </div>
            )}
        </SectionShell>
    );
}

interface DirectoryEntry {
    name: string;
    slug?: string;
    group: string;
    country?: string;
    location?: string;
    in_partnership?: boolean;
    summary?: string;
}

function entrySlug(entry: DirectoryEntry): string {
    return entry.slug || entry.name
        .toLowerCase()
        .normalize('NFKD')
        .replace(/['’`]/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

/**
 * Partner schools / organisations directory: category tabs, partnership filter, search.
 * Each card links to its dedicated partner page.
 */
export function DirectorySection({ content }: { content: Content }) {
    const entries: DirectoryEntry[] = Array.isArray(content.entries) ? content.entries : [];
    const groups: string[] = Array.isArray(content.groups) ? content.groups : Array.from(new Set(entries.map((e) => e.group)));
    const [group, setGroup] = React.useState(groups[0] ?? '');
    const [status, setStatus] = React.useState<'all' | 'yes' | 'no'>('all');
    const [query, setQuery] = React.useState('');

    const visible = entries.filter((entry) => {
        if (entry.group !== group) { return false; }
        if (status === 'yes' && !entry.in_partnership) { return false; }
        if (status === 'no' && entry.in_partnership) { return false; }
        return `${entry.name} ${entry.location ?? ''}`.toLowerCase().includes(query.trim().toLowerCase());
    });

    const countFor = (name: string) => entries.filter((e) => e.group === name).length;

    return (
        <SectionShell>
            <SectionHeading index={content.index} label={content.label} title={String(content.heading ?? '')} accent={content.accent} description={content.body} divided />

            <div className="grid gap-10 lg:grid-cols-[260px_1fr] lg:gap-14">
                <div className="flex gap-2 overflow-x-auto border-b border-border pb-3 no-scrollbar lg:flex-col lg:gap-1 lg:border-b-0 lg:border-r lg:pb-0 lg:pr-6" role="tablist">
                    {groups.map((name) => (
                        <button
                            key={name}
                            type="button"
                            role="tab"
                            aria-selected={name === group}
                            onClick={() => setGroup(name)}
                            className={cn(
                                'flex shrink-0 items-center justify-between gap-4 rounded-xl px-4 py-3 text-left text-sm font-semibold transition',
                                name === group
                                    ? 'bg-primary text-white'
                                    : 'text-foreground/75 hover:bg-muted hover:text-primary',
                            )}
                        >
                            <span>{name}</span>
                            <span
                                className={cn(
                                    'rounded-full px-2 py-0.5 text-xs tabular-nums',
                                    name === group ? 'bg-white/20 text-white' : 'bg-muted text-muted-foreground',
                                )}
                            >
                                {countFor(name)}
                            </span>
                        </button>
                    ))}
                </div>

                <div>
                    <div className="mb-8 flex flex-wrap items-center justify-between gap-4">
                        <div className="relative min-w-[220px] flex-1">
                            <Search className="absolute left-3.5 top-3 h-4 w-4 text-muted-foreground" />
                            <input
                                value={query}
                                onChange={(e) => setQuery(e.target.value)}
                                placeholder="Search by name or location…"
                                className="h-10 w-full rounded-full border border-border bg-transparent pl-10 pr-4 text-sm outline-none transition focus:border-primary"
                                aria-label="Search directory"
                            />
                        </div>
                        <div className="flex items-center gap-1.5">
                            {(
                                [
                                    ['all', 'All'],
                                    ['yes', 'In Partnership'],
                                    ['no', 'Not In Partnership'],
                                ] as const
                            ).map(([value, label]) => (
                                <button
                                    key={value}
                                    type="button"
                                    onClick={() => setStatus(value)}
                                    className={cn(
                                        'rounded-full px-4 py-2 text-xs font-semibold transition',
                                        status === value
                                            ? 'bg-primary text-white'
                                            : 'border border-border text-muted-foreground hover:border-primary hover:text-primary',
                                    )}
                                >
                                    {label}
                                </button>
                            ))}
                        </div>
                    </div>

                    {visible.length === 0 ? (
                        <p className="border-t border-border py-12 text-center text-muted-foreground">
                            No data to display.
                        </p>
                    ) : (
                        <div className="divide-y divide-border border-y border-border">
                            {visible.map((entry, i) => (
                                <Link
                                    key={entry.name}
                                    href={`/partners/${entrySlug(entry)}`}
                                    className="reveal-left group -mx-4 grid gap-4 rounded-2xl px-4 py-7 transition-colors duration-200 hover:bg-muted/50 sm:grid-cols-[4rem_1fr_auto] sm:items-start sm:gap-6 sm:py-8"
                                    style={{ '--stagger-index': i } as React.CSSProperties}
                                >
                                    <span
                                        className="hidden pt-0.5 text-2xl font-bold leading-none tracking-tight tabular-nums text-primary/50 transition-colors group-hover:text-primary sm:block sm:text-3xl"
                                        style={{ fontFamily: 'var(--font-stat)' }}
                                    >
                                        {String(i + 1).padStart(2, '0')}
                                    </span>

                                    <div className="min-w-0 space-y-2.5">
                                        <div className="flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs">
                                            <span
                                                className={cn(
                                                    'inline-flex items-center gap-1.5 font-semibold uppercase tracking-wider',
                                                    entry.in_partnership ? 'text-primary' : 'text-accent-warm',
                                                )}
                                            >
                                                <span
                                                    className={cn(
                                                        'h-1.5 w-1.5 rounded-full',
                                                        entry.in_partnership ? 'bg-primary' : 'bg-accent-warm',
                                                    )}
                                                />
                                                {entry.in_partnership ? 'In partnership' : 'Seeking partner'}
                                            </span>
                                            {entry.location && (
                                                <span className="inline-flex items-center gap-1.5 text-muted-foreground">
                                                    <MapPin className="h-3.5 w-3.5 shrink-0 text-primary/70" />
                                                    {entry.country ? `${entry.country} — ` : ''}
                                                    {entry.location}
                                                </span>
                                            )}
                                        </div>

                                        <h3 className="text-xl font-bold leading-snug tracking-tight text-primary transition-colors group-hover:text-brand-dark sm:text-2xl">
                                            {entry.name}
                                        </h3>

                                        {entry.summary && (
                                            <p className="line-clamp-2 max-w-2xl text-sm leading-relaxed text-muted-foreground sm:text-base">
                                                {stripHtml(entry.summary)}
                                            </p>
                                        )}
                                    </div>

                                    <span className="inline-flex shrink-0 items-center gap-1.5 text-sm font-semibold text-primary/80 transition group-hover:text-primary sm:self-center">
                                        View profile
                                        <ArrowUpRight className="h-4 w-4 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
                                    </span>
                                </Link>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </SectionShell>
    );
}
