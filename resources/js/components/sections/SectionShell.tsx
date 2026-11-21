import * as React from 'react';
import { useReveal } from '@/hooks/useReveal';
import { cn } from '@/lib/utils';

interface SectionShellProps extends React.HTMLAttributes<HTMLElement> {
    children: React.ReactNode;
    width?: 'narrow' | 'default' | 'wide';
}

/**
 * Common wrapper for public sections: generous volume, consistent width, vertical rhythm and reveal-on-scroll.
 */
export function SectionShell({ children, className, width = 'default', ...props }: SectionShellProps) {
    const ref = useReveal<HTMLElement>();
    const widths = { narrow: 'max-w-4xl', default: 'max-w-7xl', wide: 'max-w-[1400px]' };

    return (
        <section ref={ref} className={cn('reveal py-20 sm:py-28 lg:py-32', className)} {...props}>
            <div className={cn('mx-auto px-5 sm:px-8 lg:px-10', widths[width])}>{children}</div>
        </section>
    );
}

interface SectionHeadingProps {
    index?: string;
    label?: string;
    title: string;
    accent?: string;
    description?: string;
    align?: 'left' | 'center';
    divided?: boolean;
    action?: React.ReactNode;
}

/**
 * Renders a heading with Vicshoot-style staggered split-word slide-in animation on scroll.
 */
export function SplitHeading({ title, accent }: { title: string; accent?: string }) {
    const titleWords = title.trim().split(/\s+/).filter(Boolean);
    const accentWords = accent ? accent.trim().split(/\s+/).filter(Boolean) : [];

    return (
        <>
            {titleWords.map((word, i) => (
                <React.Fragment key={`t-${i}`}>
                    <span className="split-word" style={{ '--word-index': i } as React.CSSProperties}>
                        {word}
                    </span>
                    {(i < titleWords.length - 1 || accentWords.length > 0) && ' '}
                </React.Fragment>
            ))}
            {accentWords.length > 0 && (
                <span className="title-accent">
                    {accentWords.map((word, i) => (
                        <React.Fragment key={`a-${i}`}>
                            <span
                                className="split-word"
                                style={{ '--word-index': titleWords.length + i } as React.CSSProperties}
                            >
                                {word}
                            </span>
                            {i < accentWords.length - 1 && ' '}
                        </React.Fragment>
                    ))}
                </span>
            )}
        </>
    );
}

/**
 * Editorial section header with clean display typography and optional animated divider/action.
 */
export function SectionHeading({
    title,
    accent,
    description,
    align = 'left',
    divided = false,
    action,
}: SectionHeadingProps) {
    return (
        <div
            className={cn(
                'relative mb-12 sm:mb-14',
                divided && 'pb-7',
                align === 'center' ? 'mx-auto max-w-3xl text-center' : 'flex flex-wrap items-end justify-between gap-6',
            )}
        >
            <div className={cn('max-w-3xl', align === 'center' && 'mx-auto')}>
                <h2
                    className="text-3xl font-bold leading-[1.12] tracking-[-0.03em] text-primary sm:text-5xl lg:text-[3.25rem]"
                    style={{ fontFamily: 'var(--font-display)' }}
                >
                    <SplitHeading title={title} accent={accent} />
                </h2>
                {description && <p className="mt-4 text-lg leading-relaxed text-muted-foreground sm:text-xl">{description}</p>}
            </div>
            {action && <div className="shrink-0">{action}</div>}
            {divided && <span aria-hidden="true" className="line-draw absolute inset-x-0 bottom-0 h-px bg-border" />}
        </div>
    );
}

export function splitParagraphs(text: unknown): string[] {
    return String(text ?? '')
        .split(/\n{2,}/)
        .map((part) => part.trim())
        .filter(Boolean);
}
