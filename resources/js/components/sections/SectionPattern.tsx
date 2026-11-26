import { cn } from '@/lib/utils';

export type SectionPatternVariant = 'halftone-dual';

interface SectionPatternProps {
    variant: SectionPatternVariant;
    className?: string;
}

/**
 * Large, vertically-centered radial halftone dot bursts flanking the left and right edges
 * of card sections, with scroll-reveal scale-in and a slow, continuous orbital drift.
 */
export function SectionPattern({ variant, className }: SectionPatternProps) {
    if (variant !== 'halftone-dual') {
        return null;
    }

    return (
        <div aria-hidden="true" className={cn('pointer-events-none absolute inset-0 z-0 select-none overflow-hidden', className)}>
            <div className="pattern-halftone-wrap absolute -left-48 top-1/2 h-[540px] w-[540px] -translate-y-1/2 sm:-left-60 sm:h-[760px] sm:w-[760px] lg:-left-72 lg:h-[940px] lg:w-[940px]">
                <img
                    src="/images/patterns/halftone-dots.svg"
                    alt=""
                    loading="lazy"
                    decoding="async"
                    className="pattern-halftone-mask pattern-halftone-spin-cw h-full w-full max-w-none opacity-[0.2]"
                />
            </div>

            <div
                className="pattern-halftone-wrap absolute -right-48 top-1/2 h-[540px] w-[540px] -translate-y-1/2 sm:-right-60 sm:h-[760px] sm:w-[760px] lg:-right-72 lg:h-[940px] lg:w-[940px]"
                style={{ transitionDelay: '160ms' }}
            >
                <img
                    src="/images/patterns/halftone-dots.svg"
                    alt=""
                    loading="lazy"
                    decoding="async"
                    className="pattern-halftone-mask pattern-halftone-spin-ccw h-full w-full max-w-none opacity-[0.2]"
                />
            </div>
        </div>
    );
}
