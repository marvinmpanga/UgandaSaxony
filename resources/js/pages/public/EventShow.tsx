import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, ArrowUpRight, Calendar, MapPin } from 'lucide-react';
import { RichContent } from '@/components/ui/rich-content';
import { PublicLayout } from '@/layouts/PublicLayout';
import type { ArticleItem } from '@/types';

interface EventShowProps {
    event: ArticleItem & {
        slug: string;
        gallery?: Array<{ url: string; caption?: string | null }>;
    };
    relatedEvents: Array<ArticleItem & { slug: string }>;
    editUrl?: string | null;
}

export default function EventShow({ event, relatedEvents, editUrl }: EventShowProps) {
    return (
        <PublicLayout editUrl={editUrl ?? undefined} hasDarkHero={false}>
            <Head title={`${event.title} | Events`}>
                {event.excerpt && <meta name="description" content={event.excerpt} />}
            </Head>

            {/* ── Editorial article header ── */}
            <section className="pt-28 pb-8 sm:pt-36 sm:pb-10">
                <div className="mx-auto max-w-4xl border-b border-border px-5 pb-10 sm:px-8">
                    <Link
                        href="/events"
                        className="group mb-8 inline-flex items-center gap-2 text-sm font-semibold text-muted-foreground transition hover:text-primary"
                    >
                        <ArrowLeft className="h-4 w-4 transition-transform group-hover:-translate-x-1" />
                        Back to all events
                    </Link>

                    <div className="flex flex-wrap items-center gap-3 text-sm">
                        {event.tag && (
                            <span className="rounded-full bg-primary px-3.5 py-1 text-xs font-semibold uppercase tracking-wider text-white">
                                {event.tag}
                            </span>
                        )}
                        {event.date && (
                            <span className="inline-flex items-center gap-1.5 font-medium text-muted-foreground">
                                <Calendar className="h-4 w-4 text-primary" />
                                {event.date}
                            </span>
                        )}
                        {event.location && (
                            <span className="inline-flex items-center gap-1.5 font-medium text-muted-foreground">
                                <MapPin className="h-4 w-4 text-primary" />
                                {event.location}
                            </span>
                        )}
                    </div>

                    <h1
                        className="mt-6 text-3xl font-bold leading-[1.12] tracking-[-0.03em] text-primary sm:text-4xl lg:text-5xl"
                        style={{ fontFamily: 'var(--font-display)' }}
                    >
                        {event.title}
                    </h1>
                </div>
            </section>

            {/* ── Main article content ── */}
            <article className="py-12 sm:py-16 lg:py-20">
                <div className="mx-auto max-w-4xl px-5 sm:px-8">
                    {event.image_url && (
                        <figure className="mb-10 overflow-hidden rounded-3xl bg-secondary shadow-sm sm:mb-12">
                            <img
                                src={event.image_url}
                                alt={event.title}
                                className="max-h-[540px] w-full object-cover"
                            />
                        </figure>
                    )}

                    <div className="mx-auto max-w-3xl">
                        <RichContent content={event.body} fallback={event.excerpt} />
                    </div>

                    {Array.isArray(event.gallery) && event.gallery.length > 0 && (
                        <div className="mx-auto mt-12 max-w-3xl">
                            <h2 className="mb-6 text-xl font-bold tracking-tight text-foreground">Photo Gallery</h2>
                            <div className="grid gap-4 sm:grid-cols-2">
                                {event.gallery.map((photo, idx) => (
                                    <figure key={idx} className="overflow-hidden rounded-2xl bg-secondary">
                                        <img
                                            src={photo.url}
                                            alt={photo.caption ?? event.title}
                                            loading="lazy"
                                            className="aspect-[4/3] w-full object-cover"
                                        />
                                        {photo.caption && (
                                            <figcaption className="px-4 py-2.5 text-xs text-muted-foreground">
                                                {photo.caption}
                                            </figcaption>
                                        )}
                                    </figure>
                                ))}
                            </div>
                        </div>
                    )}

                    <div className="mx-auto mt-14 flex max-w-3xl flex-wrap items-center justify-between gap-4 border-t border-border pt-8">
                        <Link
                            href="/events"
                            className="group inline-flex items-center gap-2 rounded-full border border-border px-5 py-2.5 text-sm font-semibold text-foreground transition hover:border-primary hover:text-primary"
                        >
                            <ArrowLeft className="h-4 w-4 transition-transform group-hover:-translate-x-0.5" />
                            All events &amp; news
                        </Link>
                        <Link
                            href="/contact-us"
                            className="group inline-flex items-center gap-2 rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-dark"
                        >
                            Get in touch
                            <ArrowUpRight className="h-4 w-4 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
                        </Link>
                    </div>
                </div>
            </article>

            {/* ── Related events ── */}
            {relatedEvents.length > 0 && (
                <section className="border-t border-border bg-muted/30 py-16 sm:py-24">
                    <div className="mx-auto max-w-7xl px-5 sm:px-8 lg:px-10">
                        <div className="mb-10 flex flex-wrap items-end justify-between gap-4">
                            <h2
                                className="text-2xl font-bold tracking-[-0.03em] text-primary sm:text-3xl"
                                style={{ fontFamily: 'var(--font-display)' }}
                            >
                                More <span className="title-accent">events &amp; news</span>
                            </h2>
                            <Link
                                href="/events"
                                className="group inline-flex items-center gap-2 rounded-full border border-border px-5 py-2 text-sm font-semibold text-foreground transition hover:border-primary hover:text-primary"
                            >
                                View all events
                                <ArrowUpRight className="h-4 w-4 transition group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
                            </Link>
                        </div>

                        <div className="grid gap-10 md:grid-cols-2 lg:grid-cols-3">
                            {relatedEvents.map((item) => (
                                <Link
                                    key={item.slug}
                                    href={`/events/${item.slug}`}
                                    className="group flex flex-col gap-5 text-left"
                                >
                                    <div className="relative aspect-[4/3] overflow-hidden rounded-3xl bg-secondary shadow-sm">
                                        {item.image_url && (
                                            <img
                                                src={item.image_url}
                                                alt=""
                                                loading="lazy"
                                                className="h-full w-full object-cover transition-transform duration-700 ease-out group-hover:scale-105"
                                            />
                                        )}
                                        {item.tag && (
                                            <span className="absolute left-4 top-4 rounded-full bg-primary px-3.5 py-1.5 text-xs font-semibold uppercase tracking-wider text-white shadow-sm">
                                                {item.tag}
                                            </span>
                                        )}
                                    </div>
                                    <div className="flex flex-1 flex-col gap-3">
                                        {item.date && (
                                            <span className="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                                <Calendar className="h-4 w-4 text-primary" /> {item.date}
                                            </span>
                                        )}
                                        <h3 className="text-lg font-bold leading-snug tracking-tight text-foreground transition-colors duration-300 group-hover:text-primary sm:text-xl">
                                            {item.title}
                                        </h3>
                                        {item.excerpt && (
                                            <p className="line-clamp-2 text-sm leading-relaxed text-muted-foreground">
                                                {item.excerpt}
                                            </p>
                                        )}
                                        <span className="mt-1 inline-flex w-fit items-center gap-2 rounded-full bg-primary/10 px-4 py-1.5 text-sm font-semibold text-primary transition-all duration-300 group-hover:bg-primary group-hover:text-white">
                                            Read more
                                            <ArrowUpRight className="h-4 w-4 transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
                                        </span>
                                    </div>
                                </Link>
                            ))}
                        </div>
                    </div>
                </section>
            )}
        </PublicLayout>
    );
}
