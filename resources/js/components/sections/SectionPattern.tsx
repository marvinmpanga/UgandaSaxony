import { cn } from '@/lib/utils';

export type SectionPatternVariant = 'halftone-dual';

interface SectionPatternProps {
    variant: SectionPatternVariant;
    className?: string;
}

/**
 * Static, vertically-centered radial halftone dot bursts anchored to the max-w-7xl content
 * bounds so they sit partially behind the left and right cards on desktop. Hidden on mobile.
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
            <div className="relative mx-auto h-full max-w-7xl">
                <img
                    src="/images/patterns/halftone-dots.svg"
                    alt=""
                    loading="lazy"
                    decoding="async"
                    className="pattern-halftone-mask absolute -left-20 top-1/2 h-[460px] w-[460px] max-w-none -translate-y-1/2 opacity-[0.18] lg:-left-28 lg:h-[560px] lg:w-[560px]"
                />
                <img
                    src="/images/patterns/halftone-dots.svg"
                    alt=""
                    loading="lazy"
                    decoding="async"
                    className="pattern-halftone-mask absolute -right-20 top-1/2 h-[460px] w-[460px] max-w-none -translate-y-1/2 rotate-180 opacity-[0.18] lg:-right-28 lg:h-[560px] lg:w-[560px]"
                />
            </div>
        </div>
    );
}
