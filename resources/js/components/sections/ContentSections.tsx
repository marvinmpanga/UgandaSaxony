import { Link } from '@inertiajs/react';
import { ArrowUpRight } from 'lucide-react';
import * as React from 'react';
import { SectionHeading, SectionShell, SplitHeading, splitParagraphs } from '@/components/sections/SectionShell';
import { useParallax } from '@/hooks/useReveal';
import { cn } from '@/lib/utils';

type Content = Record<string, any>;

function isExternal(url: string): boolean {
    return (
        /^(https?:|mailto:|tel:)/.test(url) ||
        url.startsWith('/storage/') ||
        /\.(pdf|docx?|xlsx?|pptx?|odt|csv|zip)$/i.test(url)
    );
}

/**
 * Link that uses Inertia for internal URLs and a plain anchor for external or file URLs.
 */
export function SmartLink({ href, className, children }: { href: string; className?: string; children: React.ReactNode }) {
    if (isExternal(href)) {
        return (
            <a href={href} className={className} target="_blank" rel="noopener noreferrer">
                {children}
            </a>
        );
    }

    return (
        <Link href={href} className={className}>
            {children}
        </Link>
    );
}

export function RichTextSection({ content }: { content: Content }) {
    return (
        <SectionShell width="narrow">
            <SectionHeading
                index={content.index}
                label={content.label}
                title={String(content.heading ?? '')}
                accent={content.accent}
            />
            <div className="space-y-6 text-lg leading-[1.8] text-muted-foreground sm:text-xl">
                {splitParagraphs(content.body).map((paragraph, i) => (
                    <p key={i}>{paragraph}</p>
                ))}
            </div>
            {Array.isArray(content.list) && content.list.length > 0 && (
                <ul className="mt-10 grid gap-5 sm:grid-cols-2">
                    {content.list.map((item: string, i: number) => (
                        <li
                            key={i}
                            className="stagger-item flex items-start gap-3.5 border-t border-border pt-4 text-base leading-relaxed text-foreground"
                            style={{ '--stagger-index': i } as React.CSSProperties}
                        >
                            <ArrowUpRight className="mt-1 h-4 w-4 shrink-0 text-primary" />
                            {item}
                        </li>
                    ))}
                </ul>
            )}
        </SectionShell>
    );
}

export function ImageSection({ content }: { content: Content }) {
    if (!content.image_url) {
        return null;
    }

    return (
        <SectionShell width="wide" className="py-10 sm:py-14">
            <figure className="img-reveal overflow-hidden rounded-2xl border border-border">
                <img src={String(content.image_url)} alt={String(content.alt ?? '')} className="max-h-[560px] w-full object-cover" />
                {content.caption && (
                    <figcaption className="px-4 py-3 text-center text-sm text-muted-foreground">{String(content.caption)}</figcaption>
                )}
            </figure>
        </SectionShell>
    );
}

/**
 * Editorial About / Image+Text section inspired by Image 2: divided header with pill CTA, clean rounded photo, and structured copy.
 */
export function ImageTextSection({ content }: { content: Content }) {
    const imageRight = content.image_position === 'right';
    const ctaLabel = String(content.cta_label ?? '');
    const paragraphs = splitParagraphs(content.body);
    const [leadParagraph, ...restParagraphs] = paragraphs;
    const parallaxRef = useParallax<HTMLDivElement>(28);

    return (
        <SectionShell>
            <SectionHeading
                index={content.index}
                label={content.label}
                title={String(content.heading ?? '')}
                accent={content.accent}
                divided
                action={
                    ctaLabel ? (
                        <SmartLink
                            href={String(content.cta_url ?? '#')}
                            className="group inline-flex items-center gap-2 rounded-full bg-primary px-7 py-3 text-sm font-semibold text-white shadow-sm transition duration-300 hover:bg-brand-dark hover:shadow-md"
                        >
                            {ctaLabel}
                            <ArrowUpRight className="h-4 w-4 transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
                        </SmartLink>
                    ) : undefined
                }
            />
            <div className="grid items-start gap-12 lg:grid-cols-12 lg:gap-16">
                {content.image_url && (
                    <div ref={parallaxRef} className={cn('will-change-transform lg:col-span-6', imageRight && 'lg:order-2')}>
                        <div className="img-reveal rounded-2xl shadow-sm">
                            <img
                                src={String(content.image_url)}
                                alt={String(content.alt ?? content.heading ?? '')}
                                className="aspect-[4/3] w-full object-cover"
                            />
                        </div>
                    </div>
                )}
                <div className={cn('space-y-6', content.image_url ? 'lg:col-span-6' : 'lg:col-span-12')}>
                    {content.kicker && (
                        <h3 className="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                            {String(content.kicker)}
                        </h3>
                    )}
                    {leadParagraph && (
                        <p className="text-xl leading-[1.6] text-foreground/90 sm:text-2xl">{leadParagraph}</p>
                    )}
                    {restParagraphs.length > 0 && (
                        <div className="space-y-5 border-t border-border pt-7 text-base leading-[1.75] text-muted-foreground sm:text-lg">
                            {restParagraphs.map((paragraph, i) => (
                                <p key={i}>{paragraph}</p>
                            ))}
                        </div>
                    )}
                    {Array.isArray(content.list) && content.list.length > 0 && (
                        <ul className="grid gap-3 border-t border-border pt-6 sm:grid-cols-2">
                            {content.list.map((item: string, i: number) => (
                                <li
                                    key={i}
                                    className="stagger-item flex items-start gap-3 text-base leading-relaxed text-foreground"
                                    style={{ '--stagger-index': i } as React.CSSProperties}
                                >
                                    <ArrowUpRight className="mt-1 h-4 w-4 shrink-0 text-primary" />
                                    {item}
                                </li>
                            ))}
                        </ul>
                    )}
                </div>
            </div>
        </SectionShell>
    );
}

function CountUp({ value }: { value: string }) {
    const match = value.match(/^(\D*)([\d,]+)(.*)$/);
    const target = match ? parseInt(match[2].replace(/,/g, ''), 10) : 0;
    const ref = React.useRef<HTMLSpanElement | null>(null);
    const [display, setDisplay] = React.useState(match ? 0 : target);

    React.useEffect(() => {
        if (!match || !ref.current) {
            return;
        }
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (reduceMotion || typeof IntersectionObserver === 'undefined') {
            setDisplay(target);
            return;
        }
        const observer = new IntersectionObserver(([entry]) => {
            if (!entry.isIntersecting) {
                return;
            }
            observer.disconnect();
            const start = performance.now();
            const tick = (now: number) => {
                const progress = Math.min((now - start) / 2000, 1);
                setDisplay(Math.round(target * (1 - Math.pow(1 - progress, 3))));
                if (progress < 1) {
                    requestAnimationFrame(tick);
                }
            };
            requestAnimationFrame(tick);
        });
        observer.observe(ref.current);

        return () => observer.disconnect();
    }, [target]);

    if (!match) {
        return <span>{value}</span>;
    }
    const shown = match[2].includes(',') ? display.toLocaleString('en-US') : String(display);

    return (
        <span ref={ref}>
            {match[1]}
            {shown}
            {match[3]}
        </span>
    );
}

export function StatsSection({ content }: { content: Content }) {
    const items: Array<{ value?: string; label?: string }> = Array.isArray(content.items) ? content.items : [];

    return (
        <SectionShell className="py-16 sm:py-24">
            {content.heading && (
                <SectionHeading
                    title={String(content.heading)}
                    accent={content.accent ? String(content.accent) : undefined}
                    align="center"
                />
            )}
            <div className="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-4 lg:gap-12">
                {items.map((item, i) => (
                    <div
                        key={i}
                        className="stagger-item border-t border-border pt-6"
                        style={{ '--stagger-index': i } as React.CSSProperties}
                    >
                        <div
                            className="text-5xl font-bold leading-none tracking-tight text-primary tabular-nums sm:text-6xl lg:text-7xl"
                            style={{ fontFamily: 'var(--font-stat)' }}
                        >
                            <CountUp value={String(item.value ?? '')} />
                        </div>
                        <div className="mt-4 text-sm font-medium leading-snug text-muted-foreground sm:text-base">{item.label}</div>
                    </div>
                ))}
            </div>
        </SectionShell>
    );
}

/**
 * Authoritative dark-surface numbered showcase inspired by Images 6 & 7.
 */
export function FeaturedCardsSection({ content }: { content: Content }) {
    const cards: Array<{ title?: string; description?: string; link_label?: string; link_url?: string; image_url?: string }> =
        Array.isArray(content.cards) ? content.cards : [];

    return (
        <SectionShell className="bg-primary text-white">
            <div className="relative mb-12 pb-8">
                <div className="flex items-end justify-between gap-6">
                    <h2
                        className="max-w-2xl text-3xl font-bold leading-[1.15] tracking-[-0.025em] text-white sm:text-4xl lg:text-5xl"
                        style={{ fontFamily: 'var(--font-display)' }}
                    >
                        <SplitHeading
                            title={String(content.heading ?? '')}
                            accent={content.accent ? String(content.accent) : undefined}
                        />
                    </h2>
                    <ArrowUpRight className="hidden h-7 w-7 shrink-0 text-white/70 sm:block" />
                </div>
                <span aria-hidden="true" className="line-draw absolute inset-x-0 bottom-0 h-px bg-white/20" />
            </div>

            <div className="divide-y divide-white/15">
                {cards.map((card, i) => (
                    <div
                        key={i}
                        className="reveal-left group grid gap-5 py-12 transition-colors duration-300 sm:grid-cols-[6rem_1fr] sm:gap-10 sm:py-16 lg:grid-cols-[8rem_1fr]"
                        style={{ '--stagger-index': i } as React.CSSProperties}
                    >
                        <span className="text-2xl font-semibold tracking-tight text-white/55 transition-colors duration-300 group-hover:text-emerald-300 sm:text-3xl">
                            {String(i + 1).padStart(2, '0')}.
                        </span>
                        <div className="space-y-5 transition-transform duration-300 ease-out group-hover:translate-x-1">
                            <div className="flex flex-wrap items-baseline justify-between gap-4">
                                <h3 className="text-2xl font-bold tracking-tight text-white sm:text-3xl">{card.title}</h3>
                                {card.link_label && (
                                    <SmartLink
                                        href={card.link_url ?? '#'}
                                        className="inline-flex items-center gap-2 rounded-full border border-white/25 px-5 py-2 text-sm font-semibold text-white transition hover:border-white hover:bg-white hover:text-primary"
                                    >
                                        {card.link_label}
                                        <ArrowUpRight className="h-4 w-4 transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
                                    </SmartLink>
                                )}
                            </div>
                            {card.description && (
                                <p className="max-w-3xl text-base leading-[1.75] text-white/75 sm:text-lg">{card.description}</p>
                            )}
                        </div>
                    </div>
                ))}
            </div>
        </SectionShell>
    );
}

export function PartnerLogosSection({ content }: { content: Content }) {
    const logos: Array<{
        name?: string;
        image_url?: string;
        url?: string;
        description?: string;
        region?: string;
    }> = Array.isArray(content.logos) ? content.logos : [];

    const featureImage =
        content.image_url !== undefined
            ? String(content.image_url)
            : '/images/legacy/hero-5.jpg';
    const parallaxRef = useParallax<HTMLDivElement>(26);

    return (
        <SectionShell className="bg-muted/40">
            <div className="relative grid items-end gap-6 pb-10 lg:grid-cols-12 lg:gap-12">
                <div className="lg:col-span-6">
                    <h2
                        className="text-3xl font-bold leading-[1.12] tracking-[-0.03em] text-primary sm:text-5xl lg:text-[3.25rem]"
                        style={{ fontFamily: 'var(--font-display)' }}
                    >
                        <SplitHeading
                            title={String(content.heading ?? '')}
                            accent={content.accent ? String(content.accent) : undefined}
                        />
                    </h2>
                    {(content.tagline || content.label) && (
                        <p
                            className="mt-3 text-lg text-primary/80 sm:text-xl"
                            style={{ fontFamily: 'var(--font-script)' }}
                        >
                            {String(content.tagline ?? content.label)}
                        </p>
                    )}
                </div>

                {content.body && (
                    <div className="lg:col-span-6 lg:text-right">
                        <p className="text-base leading-[1.75] text-muted-foreground sm:text-lg lg:ml-auto lg:max-w-xl">
                            {String(content.body)}
                        </p>
                    </div>
                )}
                <span aria-hidden="true" className="line-draw absolute inset-x-0 bottom-0 h-px bg-border" />
            </div>

            <div className={cn('mt-12 grid items-center gap-12 lg:gap-16', featureImage && 'lg:grid-cols-12')}>
                <div
                    className={cn(
                        'grid gap-10 sm:grid-cols-2 sm:gap-12',
                        featureImage ? 'lg:col-span-7' : 'lg:grid-cols-3',
                    )}
                >
                    {logos.map((logo, i) => {
                        const itemBody = (
                            <div
                                className="stagger-item group flex flex-col items-start text-left"
                                style={{ '--stagger-index': i } as React.CSSProperties}
                            >
                                {logo.image_url && (
                                    <div className="flex h-28 items-center">
                                        <img
                                            src={logo.image_url}
                                            alt={logo.name ?? ''}
                                            className="max-h-28 w-auto object-contain mix-blend-multiply transition-transform duration-300 ease-out group-hover:scale-105"
                                        />
                                    </div>
                                )}
                                {logo.region && (
                                    <span className="mt-4 text-xs font-semibold uppercase tracking-[0.14em] text-accent-warm">
                                        {logo.region}
                                    </span>
                                )}
                                <h3 className="mt-4 text-xl font-bold leading-snug tracking-tight text-primary transition-colors group-hover:text-brand-dark sm:text-2xl">
                                    {logo.name}
                                </h3>
                                {logo.description && (
                                    <p className="mt-2.5 text-sm leading-relaxed text-muted-foreground sm:text-base">
                                        {logo.description}
                                    </p>
                                )}
                                {logo.url && (
                                    <span className="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-primary/80 transition group-hover:text-primary">
                                        Visit website
                                        <ArrowUpRight className="h-4 w-4 transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
                                    </span>
                                )}
                            </div>
                        );

                        return logo.url ? (
                            <SmartLink key={i} href={logo.url} className="block">
                                {itemBody}
                            </SmartLink>
                        ) : (
                            <div key={i}>{itemBody}</div>
                        );
                    })}
                </div>

                {featureImage && (
                    <div ref={parallaxRef} className="will-change-transform lg:col-span-5">
                        <div className="img-reveal rounded-3xl shadow-sm">
                            <img
                                src={featureImage}
                                alt={String(content.image_alt ?? content.heading ?? 'Uganda–Saxony partnership')}
                                loading="lazy"
                                className="aspect-[4/3] w-full object-cover"
                            />
                        </div>
                    </div>
                )}
            </div>
        </SectionShell>
    );
}

/**
 * Full-bleed rounded photo CTA banner with dark gradient overlay and pill button, inspired by Images 4 & 5.
 */
export function CtaSection({ content }: { content: Content }) {
    const bgImage = String(content.image_url || '/images/legacy/hero-1.jpg');

    return (
        <SectionShell>
            <div className="img-reveal relative isolate overflow-hidden rounded-3xl bg-black px-8 py-24 text-center text-white shadow-xl sm:px-20 sm:py-32">
                <img
                    src={bgImage}
                    alt=""
                    loading="lazy"
                    className="absolute inset-0 -z-20 h-full w-full object-cover object-center"
                />
                <div className="absolute inset-0 -z-10 bg-black/55" />
                <div className="relative mx-auto max-w-3xl">
                    <h2
                        className="text-4xl font-bold leading-[1.12] tracking-[-0.03em] text-white sm:text-5xl lg:text-6xl"
                        style={{ fontFamily: 'var(--font-display)' }}
                    >
                        <SplitHeading
                            title={String(content.heading ?? '')}
                            accent={content.accent ? String(content.accent) : undefined}
                        />
                    </h2>
                    {content.body && (
                        <p className="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-white/85 sm:text-xl">
                            {String(content.body)}
                        </p>
                    )}
                    {content.button_label && (
                        <SmartLink
                            href={String(content.button_url ?? '#')}
                            className="group mt-10 inline-flex items-center gap-2.5 rounded-full bg-white px-9 py-4 text-base font-semibold text-primary shadow-lg transition duration-300 hover:bg-white/90 hover:shadow-xl"
                        >
                            {String(content.button_label)}
                            <ArrowUpRight className="h-5 w-5 transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
                        </SmartLink>
                    )}
                </div>
            </div>
        </SectionShell>
    );
}
