import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, ArrowUpRight, ExternalLink, MapPin } from 'lucide-react';
import { RichContent, stripHtml } from '@/components/ui/rich-content';
import { PublicLayout } from '@/layouts/PublicLayout';

interface Partner {
    id: number | string;
    name: string;
    slug: string;
    category: string | null;
    country: string | null;
    location: string | null;
    logo_url: string | null;
    website_url: string | null;
    description: string | null;
    in_partnership: boolean;
}

interface RelatedPartner {
    id: number | string;
    name: string;
    slug: string;
    category: string | null;
    country: string | null;
    location: string | null;
    logo_url: string | null;
    in_partnership: boolean;
}

interface Props {
    partner: Partner;
    related: RelatedPartner[];
    backUrl?: string;
    backLabel?: string;
    editUrl?: string | null;
}

function normalizeWebsiteUrl(url: string): string {
    return /^https?:\/\//i.test(url) ? url : `https://${url}`;
}

export default function PartnerShow({
    partner,
    related,
    backUrl = '/partner-organisations',
    backLabel = 'Back to all partners',
    editUrl,
}: Props) {
    const plainSummary = stripHtml(partner.description);

    return (
        <PublicLayout editUrl={editUrl ?? undefined} hasDarkHero={false}>
            <Head title={`${partner.name} | Partners`}>
                {plainSummary && <meta name="description" content={plainSummary.slice(0, 160)} />}
            </Head>

            {/* Header */}
            <section className="pt-28 pb-8 sm:pt-36 sm:pb-10">
                <div className="mx-auto max-w-4xl border-b border-border px-5 pb-10 sm:px-8">
                    <Link
                        href={backUrl}
                        className="group mb-8 inline-flex items-center gap-2 text-sm font-semibold text-muted-foreground transition hover:text-primary"
                    >
                        <ArrowLeft className="h-4 w-4 transition-transform group-hover:-translate-x-1" />
                        {backLabel}
                    </Link>

                    <div className="flex flex-wrap items-start gap-6 sm:gap-8">
                        {partner.logo_url && (
                            <div className="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl border border-border bg-white p-3 shadow-sm sm:h-24 sm:w-24">
                                <img src={partner.logo_url} alt={partner.name} className="h-full w-full object-contain" />
                            </div>
                        )}
                        <div className="flex-1 min-w-0">
                            <div className="flex flex-wrap items-center gap-3 text-sm">
                                {partner.category && (
                                    <span className="rounded-full bg-primary/10 px-3.5 py-1 text-xs font-semibold text-primary">
                                        {partner.category}
                                    </span>
                                )}
                                <span
                                    className={`rounded-full px-3.5 py-1 text-xs font-semibold uppercase tracking-wider ${
                                        partner.in_partnership
                                            ? 'bg-primary text-white'
                                            : 'border border-border text-muted-foreground'
                                    }`}
                                >
                                    {partner.in_partnership ? 'In Partnership' : 'Seeking Partner'}
                                </span>
                            </div>
                            <h1
                                className="mt-4 text-3xl font-bold leading-tight tracking-[-0.03em] text-primary sm:text-4xl lg:text-5xl"
                                style={{ fontFamily: 'var(--font-display)' }}
                            >
                                {partner.name}
                            </h1>
                            {(partner.country || partner.location) && (
                                <p className="mt-3 flex items-center gap-2 text-base text-muted-foreground">
                                    <MapPin className="h-4 w-4 shrink-0 text-primary" />
                                    {[partner.country, partner.location].filter(Boolean).join(' — ')}
                                </p>
                            )}
                        </div>
                    </div>
                </div>
            </section>

            {/* Main content */}
            <article className="py-12 sm:py-16 lg:py-20">
                <div className="mx-auto max-w-4xl px-5 sm:px-8">
                    {partner.description && (
                        <div className="mx-auto max-w-3xl">
                            <RichContent content={partner.description} />
                        </div>
                    )}

                    <div className="mx-auto mt-14 flex max-w-3xl flex-wrap items-center justify-between gap-4 border-t border-border pt-8">
                        <Link
                            href={backUrl}
                            className="group inline-flex items-center gap-2 rounded-full border border-border px-5 py-2.5 text-sm font-semibold text-foreground transition hover:border-primary hover:text-primary"
                        >
                            <ArrowLeft className="h-4 w-4 transition-transform group-hover:-translate-x-0.5" />
                            {backLabel}
                        </Link>
                        <div className="flex flex-wrap gap-3">
                            {partner.website_url && (
                                <a
                                    href={normalizeWebsiteUrl(partner.website_url)}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="inline-flex items-center gap-2 rounded-full border border-border px-5 py-2.5 text-sm font-semibold text-foreground transition hover:border-primary hover:text-primary"
                                >
                                    Visit website <ExternalLink className="h-4 w-4" />
                                </a>
                            )}
                            <Link
                                href="/contact-us"
                                className="group inline-flex items-center gap-2 rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-dark"
                            >
                                Get in touch
                                <ArrowUpRight className="h-4 w-4 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
                            </Link>
                        </div>
                    </div>
                </div>
            </article>

            {/* Related partners */}
            {related.length > 0 && (
                <section className="border-t border-border bg-muted/30 py-16 sm:py-24">
                    <div className="mx-auto max-w-7xl px-5 sm:px-8 lg:px-10">
                        <h2
                            className="mb-10 text-2xl font-bold tracking-[-0.03em] text-primary sm:text-3xl"
                            style={{ fontFamily: 'var(--font-display)' }}
                        >
                            More <span className="title-accent">{partner.category ?? 'partners'}</span>
                        </h2>
                        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {related.map((p) => (
                                <Link
                                    key={p.id}
                                    href={`/partners/${p.slug}`}
                                    className="group flex items-start gap-4 rounded-2xl border border-border bg-card p-5 transition hover:border-primary hover:shadow-sm"
                                >
                                    <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-border bg-white p-2">
                                        {p.logo_url
                                            ? <img src={p.logo_url} alt={p.name} className="h-full w-full object-contain" />
                                            : <span className="text-xs font-bold text-muted-foreground">{p.name.slice(0, 2).toUpperCase()}</span>
                                        }
                                    </div>
                                    <div className="flex-1 min-w-0">
                                        <p className="font-semibold text-sm leading-snug text-foreground transition group-hover:text-primary">{p.name}</p>
                                        {(p.country || p.location) && (
                                            <p className="mt-1 flex items-center gap-1 text-xs text-muted-foreground">
                                                <MapPin className="h-3 w-3 shrink-0" />
                                                {[p.country, p.location].filter(Boolean).join(' — ')}
                                            </p>
                                        )}
                                    </div>
                                    <ArrowUpRight className="h-4 w-4 shrink-0 text-muted-foreground transition group-hover:text-primary" />
                                </Link>
                            ))}
                        </div>
                    </div>
                </section>
            )}
        </PublicLayout>
    );
}
