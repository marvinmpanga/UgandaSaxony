import { cn } from '@/lib/utils';

export type SectionPatternVariant = 'halftone-dual';

interface SectionPatternProps {
    variant: SectionPatternVariant;
    className?: string;
}

/**
 * Static, vertically-centered radial halftone dot bursts flanking the left and right edges
 * of card sections on desktop. Hidden on mobile to keep narrow layouts uncluttered.
 */
export function SectionPattern({ variant, className }: SectionPatternProps) {
    if (variant !== 'halftone-dual') {
        return null;
    }

    return (
        <div
            aria-hidden="true"
            className={cn('pointer-events-none absolute inset-0 z-0 hidden select-none overflow-hidden md:block', className)}
        >
            <img
                src="/images/patterns/halftone-dots.svg"
                alt=""
                loading="lazy"
                decoding="async"
                className="pattern-halftone-mask absolute -left-40 top-1/2 h-[460px] w-[460px] max-w-none -translate-y-1/2 opacity-[0.18] lg:-left-44 lg:h-[560px] lg:w-[560px]"
            />
            <img
                src="/images/patterns/halftone-dots.svg"
                alt=""
                loading="lazy"
                decoding="async"
                className="pattern-halftone-mask absolute -right-40 top-1/2 h-[460px] w-[460px] max-w-none -translate-y-1/2 rotate-180 opacity-[0.18] lg:-right-44 lg:h-[560px] lg:w-[560px]"
            />
        </div>
    );
}
