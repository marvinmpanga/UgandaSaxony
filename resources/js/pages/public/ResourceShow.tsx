import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, ArrowUpRight, Calendar, FileDown } from 'lucide-react';
import { RichContent } from '@/components/ui/rich-content';
import { PublicLayout } from '@/layouts/PublicLayout';

interface ResourceDetail {
    title: string;
    slug: string;
    category: string | null;
    excerpt: string | null;
    body: string | null;
    cover_image: string | null;
    attachment_url: string | null;
    published_at: string | null;
}

interface RelatedResource {
    title: string;
    slug: string;
    category: string | null;
    excerpt: string | null;
    cover_image: string | null;
    published_at: string | null;
}

interface ResourceShowProps {
    resource: ResourceDetail;
    related: RelatedResource[];
    editUrl?: string | null;
}

export default function ResourceShow({ resource, related, editUrl }: ResourceShowProps) {
    const attachmentExt = resource.attachment_url
        ? resource.attachment_url.split('?')[0].split('.').pop()?.toUpperCase()
        : null;

    return (
        <PublicLayout editUrl={editUrl ?? undefined} hasDarkHero={false}>
            <Head title={`${resource.title} | Resource Centre`}>
                {resource.excerpt && <meta name="description" content={resource.excerpt} />}
            </Head>

            <section className="pt-28 pb-8 sm:pt-36 sm:pb-10">
                <div className="mx-auto max-w-4xl border-b border-border px-5 pb-10 sm:px-8">
                    <Link
                        href="/resource-centre"
                        className="group mb-8 inline-flex items-center gap-2 text-sm font-semibold text-muted-foreground transition hover:text-primary"
                    >
                        <ArrowLeft className="h-4 w-4 transition-transform group-hover:-translate-x-1" />
                        Back to Resource Centre
                    </Link>

                    <div className="flex flex-wrap items-center gap-3 text-sm">
                        {resource.category && (
                            <span className="rounded-full bg-primary px-3.5 py-1 text-xs font-semibold uppercase tracking-wider text-white">
                                {resource.category}
                            </span>
                        )}
                        {resource.published_at && (
                            <span className="inline-flex items-center gap-1.5 font-medium text-muted-foreground">
                                <Calendar className="h-4 w-4 text-primary" />
                                {resource.published_at}
                            </span>
                        )}
                    </div>

                    <h1
                        className="mt-6 text-3xl font-bold leading-[1.12] tracking-[-0.03em] text-primary sm:text-4xl lg:text-5xl"
                        style={{ fontFamily: 'var(--font-display)' }}
                    >
                        {resource.title}
                    </h1>

                    {resource.attachment_url && (
                        <div className="mt-6">
                            <a
                                href={resource.attachment_url}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="inline-flex items-center gap-2 rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-dark"
                            >
                                <FileDown className="h-4 w-4" />
                                {attachmentExt ? `Download ${attachmentExt}` : 'Download document'}
                            </a>
                        </div>
                    )}
                </div>
            </section>

            <article className="py-12 sm:py-16 lg:py-20">
                <div className="mx-auto max-w-4xl px-5 sm:px-8">
                    {resource.cover_image && (
                        <figure className="mb-10 overflow-hidden rounded-3xl bg-secondary shadow-sm sm:mb-12">
                            <img
                                src={resource.cover_image}
                                alt={resource.title}
                                className="max-h-[540px] w-full object-cover"
                            />
                        </figure>
                    )}

                    <div className="mx-auto max-w-3xl">
                        <RichContent content={resource.body} fallback={resource.excerpt} />
                    </div>

                    <div className="mx-auto mt-14 flex max-w-3xl flex-wrap items-center justify-between gap-4 border-t border-border pt-8">
                        <Link
                            href="/resource-centre"
                            className="group inline-flex items-center gap-2 rounded-full border border-border px-5 py-2.5 text-sm font-semibold text-foreground transition hover:border-primary hover:text-primary"
                        >
                            <ArrowLeft className="h-4 w-4 transition-transform group-hover:-translate-x-0.5" />
                            All resources
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

            {related.length > 0 && (
                <section className="border-t border-border bg-muted/30 py-16 sm:py-24">
                    <div className="mx-auto max-w-7xl px-5 sm:px-8 lg:px-10">
                        <h2
                            className="mb-10 text-2xl font-bold tracking-[-0.03em] text-primary sm:text-3xl"
                            style={{ fontFamily: 'var(--font-display)' }}
                        >
                            Related <span className="title-accent">resources</span>
                        </h2>
                        <div className="grid gap-10 md:grid-cols-2 lg:grid-cols-3">
                            {related.map((item) => (
                                <Link
                                    key={item.slug}
                                    href={`/resource-centre/${item.slug}`}
                                    className="group flex flex-col gap-4 rounded-3xl border border-border bg-card p-6 transition hover:border-primary"
                                >
                                    {item.category && (
                                        <span className="w-fit rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-primary">
                                            {item.category}
                                        </span>
                                    )}
                                    <h3 className="text-lg font-bold leading-snug text-foreground group-hover:text-primary">
                                        {item.title}
                                    </h3>
                                    {item.excerpt && (
                                        <p className="line-clamp-2 text-sm text-muted-foreground">{item.excerpt}</p>
                                    )}
                                    <span className="mt-auto inline-flex items-center gap-1.5 text-sm font-semibold text-primary">
                                        Read more <ArrowUpRight className="h-4 w-4" />
                                    </span>
                                </Link>
                            ))}
                        </div>
                    </div>
                </section>
            )}
        </PublicLayout>
    );
}
