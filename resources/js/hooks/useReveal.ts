import * as React from 'react';

/**
 * Adds the `is-visible` class once the element scrolls into view, driving scroll-triggered animations.
 */
export function useReveal<T extends HTMLElement>(threshold = 0.08): React.RefObject<T | null> {
    const ref = React.useRef<T | null>(null);

    React.useEffect(() => {
        const element = ref.current;
        if (!element) {
            return;
        }

        if (
            typeof IntersectionObserver === 'undefined' ||
            window.matchMedia('(prefers-reduced-motion: reduce)').matches
        ) {
            element.classList.add('is-visible');
            return;
        }

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            },
            { threshold, rootMargin: '0px 0px -48px 0px' },
        );

        observer.observe(element);

        return () => observer.disconnect();
    }, [threshold]);

    return ref;
}

/**
 * Applies subtle scroll-linked vertical parallax (`translate3d`) to an element using rAF.
 * Inspired by Vicshoot's `[data-parallax]` vertical scroll motion.
 */
export function useParallax<T extends HTMLElement>(distance = 32): React.RefObject<T | null> {
    const ref = React.useRef<T | null>(null);

    React.useEffect(() => {
        const element = ref.current;
        if (!element || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        let rafId = 0;

        const update = () => {
            rafId = 0;
            const rect = element.getBoundingClientRect();
            const vh = window.innerHeight || 1;
            if (rect.bottom < -100 || rect.top > vh + 100) {
                return;
            }
            const progress = (rect.top + rect.height / 2 - vh / 2) / (vh + rect.height);
            const y = Math.max(-distance, Math.min(distance, progress * distance * 1.6));
            element.style.transform = `translate3d(0, ${y.toFixed(2)}px, 0)`;
        };

        const onScroll = () => {
            if (!rafId) {
                rafId = window.requestAnimationFrame(update);
            }
        };

        update();
        window.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('resize', onScroll, { passive: true });

        return () => {
            if (rafId) {
                window.cancelAnimationFrame(rafId);
            }
            window.removeEventListener('scroll', onScroll);
            window.removeEventListener('resize', onScroll);
        };
    }, [distance]);

    return ref;
}
