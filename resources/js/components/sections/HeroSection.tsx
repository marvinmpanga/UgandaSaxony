import { Link } from '@inertiajs/react';
import { ArrowLeft, ArrowRight, ArrowUpRight } from 'lucide-react';
import * as React from 'react';
import { cn } from '@/lib/utils';

interface HeroSlide {
    eyebrow?: string;
    heading?: string;
    accent?: string;
    subheading?: string;
    image_url?: string;
    primary_cta_label?: string;
    primary_cta_url?: string;
    secondary_cta_label?: string;
    secondary_cta_url?: string;
}

interface HeroSectionProps {
    content: Record<string, any>;
}

/* Thin vertical grid lines — Vicshoot decorative treatment */
function GridLines() {
    return (
        <svg
            className="pointer-events-none absolute inset-0 h-full w-full"
            aria-hidden="true"
            preserveAspectRatio="none"
        >
            {[20, 40, 60, 80].map((x) => (
                <line
                    key={x}
                    x1={`${x}%`} y1="0"
                    x2={`${x}%`} y2="100%"
                    stroke="white"
                    strokeOpacity="0.05"
                    strokeWidth="1"
                />
            ))}
        </svg>
    );
}

export function HeroSection({ content }: HeroSectionProps) {
    const slides: HeroSlide[] =
        Array.isArray(content.slides) && content.slides.length > 0
            ? content.slides
            : [content];
    const compact = Boolean(content.compact);

    const [active, setActive] = React.useState(0);
    const [prev, setPrev] = React.useState<number | null>(null);
    const [paused, setPaused] = React.useState(false);
    const [animating, setAnimating] = React.useState(false);
    const [direction, setDirection] = React.useState<1 | -1>(1);
    const [transitionCount, setTransitionCount] = React.useState(0);
    const touchStartX = React.useRef<number | null>(null);

    const goTo = React.useCallback(
        (targetIndex: number, dir: 1 | -1) => {
            if (animating || targetIndex === active) return;
            setAnimating(true);
            setDirection(dir);
            setPrev(active);
            setActive(targetIndex);
            setTransitionCount((c) => c + 1);
            window.setTimeout(() => {
                setPrev(null);
                setAnimating(false);
            }, 1500);
        },
        [active, animating],
    );

    const go = React.useCallback(
        (dir: 1 | -1) => {
            const next = (active + dir + slides.length) % slides.length;
            goTo(next, dir);
        },
        [active, slides.length, goTo],
    );

    /* Auto-advance */
    React.useEffect(() => {
        if (slides.length < 2 || paused) return;
        const timer = window.setInterval(() => go(1), 6500);
        return () => window.clearInterval(timer);
    }, [slides.length, paused, go]);

    /* Touch swipe */
    const onTouchStart = (e: React.TouchEvent) => {
        touchStartX.current = e.touches[0].clientX;
    };
    const onTouchEnd = (e: React.TouchEvent) => {
        if (touchStartX.current === null) return;
        const delta = e.changedTouches[0].clientX - touchStartX.current;
        if (Math.abs(delta) > 50) go(delta < 0 ? 1 : -1);
        touchStartX.current = null;
    };

    /* ── Compact inner-page headers are intentionally omitted to avoid redundancy ── */
    if (compact) {
        return null;
    }

    /* ── Full-screen homepage hero ── */
    return (
        <section
            className="relative isolate overflow-hidden bg-black"
            style={{ height: '100svh', minHeight: '600px' }}
            onMouseEnter={() => setPaused(true)}
            onMouseLeave={() => setPaused(false)}
            onTouchStart={onTouchStart}
            onTouchEnd={onTouchEnd}
            aria-roledescription="carousel"
        >
            {/* ── Background images: stationary curtain-wipe reveal + dimming + delayed zoom-out ── */}
            {slides.map((slide, index) => {
                const isActive = index === active;
                const isExiting = index === prev;
                if (!isActive && !isExiting) {
                    return null;
                }
                const curtainClass =
                    isActive && transitionCount > 0
                        ? direction === 1
                            ? 'hero-curtain-next'
                            : 'hero-curtain-prev'
                        : '';

                return (
                    <div
                        key={`${index}-${isActive ? transitionCount : 'exit'}`}
                        className={cn(
                            'absolute inset-0 overflow-hidden bg-black',
                            isActive ? 'z-[2]' : 'z-[1]',
                            curtainClass,
                        )}
                        aria-hidden={!isActive}
                    >
                        {slide.image_url && (
                            <img
                                src={slide.image_url}
                                alt=""
                                className={cn(
                                    'h-full w-full object-cover object-center will-change-transform',
                                    isActive && 'hero-bg-in',
                                    isExiting && 'hero-bg-out',
                                )}
                                style={{ display: 'block' }}
                            />
                        )}
                    </div>
                );
            })}

            {/* ── Overlays ── */}
            {/* Bottom-heavy gradient so text is always readable */}
            <div className="pointer-events-none absolute inset-0 z-[5] bg-gradient-to-t from-black/90 via-black/30 to-black/15" />
            {/* Left vignette for text area */}
            <div className="pointer-events-none absolute inset-0 z-[5] bg-gradient-to-r from-black/70 via-black/10 to-transparent" />
            {/* Subtle footer-teal identity tint */}
            <div className="pointer-events-none absolute inset-0 z-[5]" style={{ background: 'rgba(32,83,79,0.08)' }} />

            {/* ── Decorative grid ── */}
            <div className="pointer-events-none absolute inset-0 z-[6]">
                <GridLines />
            </div>

            {/* ── Hero text content — bottom-left with staggered cascade ── */}
            <div
                className="absolute bottom-0 left-0 z-10 w-full"
                style={{
                    paddingLeft: 'clamp(1.5rem, 8vw, 10rem)',
                    paddingRight: 'clamp(1.5rem, 8vw, 10rem)',
                    paddingBottom: slides.length > 1 ? '7rem' : '4rem',
                }}
            >
                {/* Exiting slide text */}
                {prev !== null && (
                    <div
                        key={`exit-${prev}-${transitionCount}`}
                        className="pointer-events-none absolute bottom-0 left-0 w-full"
                        style={{
                            paddingLeft: 'clamp(1.5rem, 8vw, 10rem)',
                            paddingRight: 'clamp(1.5rem, 8vw, 10rem)',
                            paddingBottom: slides.length > 1 ? '7rem' : '4rem',
                        }}
                    >
                        <SlideContent slide={slides[prev]} exiting />
                    </div>
                )}

                {/* Entering slide text */}
                <div key={`enter-${active}-${transitionCount}`}>
                    <SlideContent slide={slides[active]} />
                </div>
            </div>

            {/* ── Bottom navigation: arrows + dots ── */}
            {slides.length > 1 && (
                <div className="absolute inset-x-0 bottom-6 z-20 flex items-center justify-center gap-5 sm:bottom-8">
                    <button
                        type="button"
                        onClick={() => go(-1)}
                        aria-label="Previous slide"
                        className="flex h-10 w-10 items-center justify-center rounded-full border border-white/25 bg-black/25 text-white/80 backdrop-blur-sm transition hover:border-white hover:bg-white/15 hover:text-white"
                    >
                        <ArrowLeft className="h-4 w-4" />
                    </button>

                    {/* Dot indicators */}
                    <div className="flex items-center gap-2.5" role="tablist">
                        {slides.map((_, i) => (
                            <button
                                key={i}
                                type="button"
                                role="tab"
                                aria-selected={i === active}
                                aria-label={`Slide ${i + 1}`}
                                onClick={() => goTo(i, i > active ? 1 : -1)}
                                className={cn(
                                    'rounded-full transition-all duration-500',
                                    i === active
                                        ? 'h-2 w-6 bg-accent-warm'
                                        : 'h-2 w-2 bg-white/35 hover:bg-white/65',
                                )}
                            />
                        ))}
                    </div>

                    <button
                        type="button"
                        onClick={() => go(1)}
                        aria-label="Next slide"
                        className="flex h-10 w-10 items-center justify-center rounded-full border border-white/25 bg-black/25 text-white/80 backdrop-blur-sm transition hover:border-white hover:bg-white/15 hover:text-white"
                    >
                        <ArrowRight className="h-4 w-4" />
                    </button>
                </div>
            )}
        </section>
    );
}

/* Staggered cascade entrance (matching Vicshoot's 300ms-stepped reveal) + instant exit */
function SlideContent({ slide, exiting = false }: { slide: HeroSlide; exiting?: boolean }) {
    const hasSubbar = Boolean(slide.subheading || slide.primary_cta_label || slide.secondary_cta_label);

    return (
        <div className={cn('max-w-4xl', exiting && 'hero-cascade-out')}>
            <h1
                className={cn(
                    'line-clamp-2 font-bold leading-[1.1] tracking-[-0.03em] text-white',
                    !exiting && 'hero-cascade-y',
                )}
                style={{
                    fontFamily: 'var(--font-display)',
                    fontSize: 'clamp(2rem, 4.2vw, 3.5rem)',
                    animationDelay: exiting ? '0ms' : '350ms',
                }}
            >
                {slide.heading}
                {slide.accent && (
                    <>
                        {' '}
                        <span className="title-accent">{slide.accent}</span>
                    </>
                )}
            </h1>
            {hasSubbar && (
                <div className="mt-7 flex flex-col gap-5 border-t border-white/20 pt-5 sm:flex-row sm:items-center sm:justify-between">
                    {(slide.primary_cta_label || slide.secondary_cta_label) && (
                        <div className="flex flex-wrap items-center gap-3">
                            {slide.primary_cta_label && (
                                <Link
                                    href={slide.primary_cta_url ?? '#'}
                                    className={cn(
                                        'group inline-flex items-center gap-2 rounded-full bg-primary px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-dark',
                                        !exiting && 'hero-cascade-x',
                                    )}
                                    style={{ animationDelay: exiting ? '0ms' : '900ms' }}
                                >
                                    {slide.primary_cta_label}
                                    <ArrowUpRight className="h-4 w-4 transition group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
                                </Link>
                            )}
                            {slide.secondary_cta_label && (
                                <Link
                                    href={slide.secondary_cta_url ?? '#'}
                                    className={cn(
                                        'inline-flex items-center gap-2 rounded-full border border-white/30 px-6 py-2.5 text-sm font-medium text-white/85 transition hover:border-white hover:text-white',
                                        !exiting && 'hero-cascade-x',
                                    )}
                                    style={{ animationDelay: exiting ? '0ms' : '1150ms' }}
                                >
                                    {slide.secondary_cta_label}
                                </Link>
                            )}
                        </div>
                    )}
                    {slide.subheading && (
                        <p
                            className={cn(
                                'max-w-md text-sm leading-relaxed text-white/75 sm:text-right sm:text-base',
                                !exiting && 'hero-cascade-y',
                            )}
                            style={{ animationDelay: exiting ? '0ms' : '650ms' }}
                        >
                            {slide.subheading}
                        </p>
                    )}
                </div>
            )}
        </div>
    );
}
