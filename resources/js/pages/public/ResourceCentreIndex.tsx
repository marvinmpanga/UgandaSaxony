import { Head, Link } from '@inertiajs/react';
import { ArrowUpRight, BookOpen, Calendar, FileDown, Search } from 'lucide-react';
import * as React from 'react';
import { PublicLayout } from '@/layouts/PublicLayout';
import { cn } from '@/lib/utils';

interface ResourceSummary {
    id: number;
    title: string;
    slug: string;
    category: string | null;
    excerpt: string | null;
    cover_image: string | null;
    has_attachment: boolean;
    published_at: string | null;
}

interface ResourceCentreIndexProps {
    resources: ResourceSummary[];
    categories: string[];
}

const CORE_GUIDES = [
    {
        title: 'Uganda Information',
        href: '/uganda-information',
        description: 'Key facts, education context, and partnership background for Uganda.',
    },
    {
        title: 'Saxony Information',
        href: '/saxony-information',
        description: 'Overview of the Free State of Saxony and institutional cooperation.',
    },
    {
        title: 'Funding Opportunities',
        href: '/funding-opportunities',
        description: 'Grants, programmes, and financial support available to partnership projects.',
    },
];

export default function ResourceCentreIndex({ resources, categories }: ResourceCentreIndexProps) {
    const [activeCategory, setActiveCategory] = React.useState<string>('all');
    const [query, setQuery] = React.useState('');

    const filtered = resources.filter((item) => {
        if (activeCategory !== 'all' && item.category !== activeCategory) {
            return false;
        }
        const q = query.trim().toLowerCase();
        if (!q) {
            return true;
        }
        return `${item.title} ${item.excerpt ?? ''} ${item.category ?? ''}`.toLowerCase().includes(q);
    });

    return (
        <PublicLayout hasDarkHero={false}>
            <Head title="Resource Centre | Uganda–Saxony Partnership">
                <meta
                    name="description"
                    content="Guides, reports, funding opportunities, and general information for the Uganda–Saxony Partnership."
                />
            </Head>

            {/* Core Reference Guides (clean unboxed hairline rows matching homepage rhythm) */}
            <section className="pt-28 pb-12 sm:pt-36 sm:pb-16">
                <div className="mx-auto max-w-7xl px-5 sm:px-8 lg:px-10">
                    <div className="mb-12 border-b border-border pb-7">
                        <h1
                            className="text-3xl font-bold leading-[1.12] tracking-[-0.03em] text-primary sm:text-5xl lg:text-[3.25rem]"
                            style={{ fontFamily: 'var(--font-display)' }}
                        >
                            Resource <span className="title-accent">Centre</span>
                        </h1>
                        <p className="mt-4 max-w-2xl text-lg leading-relaxed text-muted-foreground sm:text-xl">
                            Reference materials, country guides, funding information, and published resources supporting Uganda–Saxony cooperation.
                        </p>
                    </div>

                    <div className="divide-y divide-border border-b border-border">
                        {CORE_GUIDES.map((guide, i) => (
                            <Link
                                key={guide.href}
                                href={guide.href}
                                className="group grid items-center gap-4 py-7 transition-colors sm:grid-cols-[5.5rem_1fr_auto] sm:gap-8 sm:py-9"
                            >
                                <span
                                    className="text-3xl font-bold leading-none tracking-tight text-primary tabular-nums sm:text-4xl lg:text-5xl"
                                    style={{ fontFamily: 'var(--font-stat)' }}
                                >
                                    {String(i + 1).padStart(2, '0')}
                                </span>
                                <div>
                                    <h2
                                        className="text-xl font-bold tracking-tight text-foreground transition group-hover:text-primary sm:text-2xl"
                                        style={{ fontFamily: 'var(--font-display)' }}
                                    >
                                        {guide.title}
                                    </h2>
                                    <p className="mt-1.5 text-sm leading-relaxed text-muted-foreground sm:text-base">
                                        {guide.description}
                                    </p>
                                </div>
                                <span className="inline-flex w-fit items-center gap-2 rounded-full border border-border px-5 py-2 text-sm font-semibold text-foreground transition group-hover:border-primary group-hover:bg-primary group-hover:text-white">
                                    Explore <ArrowUpRight className="h-4 w-4 transition group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
                                </span>
                            </Link>
                        ))}
                    </div>
                </div>
            </section>

            {/* Published Resources */}
            <section className="py-12 sm:py-20">
                <div className="mx-auto max-w-7xl px-5 sm:px-8 lg:px-10">
                    <div className="mb-10 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div className="flex flex-wrap items-center gap-2">
                            <button
                                type="button"
                                onClick={() => setActiveCategory('all')}
                                className={cn(
                                    'rounded-full px-4 py-2 text-xs font-semibold uppercase tracking-wider transition',
                                    activeCategory === 'all'
                                        ? 'bg-primary text-white'
                                        : 'border border-border text-muted-foreground hover:border-primary hover:text-foreground',
                                )}
                            >
                                All ({resources.length})
                            </button>
                            {categories.map((category) => (
                                <button
                                    key={category}
                                    type="button"
                                    onClick={() => setActiveCategory(category)}
                                    className={cn(
                                        'rounded-full px-4 py-2 text-xs font-semibold uppercase tracking-wider transition',
                                        activeCategory === category
                                            ? 'bg-primary text-white'
                                            : 'border border-border text-muted-foreground hover:border-primary hover:text-foreground',
                                    )}
                                >
                                    {category}
                                </button>
                            ))}
                        </div>

                        <div className="relative w-full sm:w-72">
                            <Search className="absolute left-3.5 top-3 h-4 w-4 text-muted-foreground" />
                            <input
                                value={query}
                                onChange={(e) => setQuery(e.target.value)}
                                placeholder="Search resources…"
                                className="h-10 w-full rounded-full border border-border bg-transparent pl-10 pr-4 text-sm outline-none focus:border-primary"
                                aria-label="Search resources"
                            />
                        </div>
                    </div>

                    {filtered.length === 0 ? (
                        <div className="rounded-3xl border border-dashed border-border p-12 text-center">
                            <BookOpen className="mx-auto h-8 w-8 text-muted-foreground/50" />
                            <p className="mt-3 text-base font-medium text-foreground">No published resources yet</p>
                            <p className="mt-1 text-sm text-muted-foreground">
                                New guides, reports, and general information articles published in the Resource Centre will appear here.
                            </p>
                        </div>
                    ) : (
                        <div className="grid gap-10 md:grid-cols-2 lg:grid-cols-3">
                            {filtered.map((item) => (
                                <Link
                                    key={item.id}
                                    href={`/resource-centre/${item.slug}`}
                                    className="group flex flex-col gap-5 text-left"
                                >
                                    <div className="relative aspect-[4/3] overflow-hidden rounded-3xl bg-secondary shadow-sm">
                                        {item.cover_image ? (
                                            <img
                                                src={item.cover_image}
                                                alt={item.title}
                                                loading="lazy"
                                                className="h-full w-full object-cover transition-transform duration-700 ease-out group-hover:scale-105"
                                            />
                                        ) : (
                                            <div className="flex h-full w-full items-center justify-center bg-primary/5">
                                                <BookOpen className="h-10 w-10 text-primary/35" />
                                            </div>
                                        )}
                                        {item.category && (
                                            <span className="absolute left-4 top-4 rounded-full bg-primary px-3.5 py-1.5 text-xs font-semibold uppercase tracking-wider text-white shadow-sm">
                                                {item.category}
                                            </span>
                                        )}
                                    </div>

                                    <div className="flex flex-1 flex-col gap-3">
                                        <div className="flex flex-wrap items-center gap-3 text-xs font-medium text-muted-foreground">
                                            {item.published_at && (
                                                <span className="inline-flex items-center gap-1.5">
                                                    <Calendar className="h-3.5 w-3.5 text-primary" /> {item.published_at}
                                                </span>
                                            )}
                                            {item.has_attachment && (
                                                <span className="inline-flex items-center gap-1 text-primary">
                                                    <FileDown className="h-3.5 w-3.5" /> Attachment
                                                </span>
                                            )}
                                        </div>

                                        <h3 className="text-xl font-bold leading-snug tracking-tight text-foreground transition-colors group-hover:text-primary">
                                            {item.title}
                                        </h3>

                                        {item.excerpt && (
                                            <p className="line-clamp-3 text-sm leading-relaxed text-muted-foreground">
                                                {item.excerpt}
                                            </p>
                                        )}

                                        <span className="mt-auto inline-flex w-fit items-center gap-2 rounded-full bg-primary/10 px-4 py-1.5 text-sm font-semibold text-primary transition group-hover:bg-primary group-hover:text-white">
                                            Read resource <ArrowUpRight className="h-4 w-4" />
                                        </span>
                                    </div>
                                </Link>
                            ))}
                        </div>
                    )}
                </div>
            </section>
        </PublicLayout>
    );
}
