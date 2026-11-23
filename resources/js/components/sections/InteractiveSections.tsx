import { useForm } from '@inertiajs/react';
import { ArrowLeft, ArrowRight, ArrowUpRight, ChevronLeft, ChevronRight, Mail, MapPin, Minus, Phone, Plus } from 'lucide-react';
import * as React from 'react';
import { SectionHeading, SectionShell, splitParagraphs } from '@/components/sections/SectionShell';
import { Dialog, DialogContent, DialogDescription, DialogTitle } from '@/components/ui/dialog';
import { cn } from '@/lib/utils';

type Content = Record<string, any>;

interface InfoGroup {
    heading?: string;
    body?: string;
    list?: string[];
    items?: Array<{ label: string; text: string }>;
    image_url?: string;
    image_caption?: string;
}

interface InfoTab {
    title: string;
    intro?: string;
    accordion?: boolean;
    groups?: InfoGroup[];
}

function GroupBody({ group }: { group: InfoGroup }) {
    const hasImage = Boolean(group.image_url);

    return (
        <div className={cn('gap-10', hasImage ? 'grid items-start lg:grid-cols-12' : 'space-y-5')}>
            <div className={cn('space-y-4 text-base leading-[1.8] text-muted-foreground', hasImage && 'lg:col-span-7')}>
                {splitParagraphs(group.body).map((paragraph, i) => (
                    <p key={i}>{paragraph}</p>
                ))}
                {group.items && (
                    <dl className="mt-6 divide-y divide-border/70 border-t border-border/70">
                        {group.items.map((item, i) => (
                            <div key={i} className="grid gap-2 py-4 sm:grid-cols-[210px_1fr] sm:gap-6">
                                <dt className="text-xs font-bold uppercase tracking-[0.12em] text-foreground">
                                    {item.label}
                                </dt>
                                <dd className="text-sm leading-relaxed text-muted-foreground sm:text-[15px]">
                                    {item.text}
                                </dd>
                            </div>
                        ))}
                    </dl>
                )}
                {group.list && (
                    <ul className="mt-4 divide-y divide-border/70 border-y border-border/70">
                        {group.list.map((item, i) => (
                            <li key={i} className="flex items-start gap-3 py-3 text-sm text-foreground sm:text-base">
                                <ArrowUpRight className="mt-1 h-4 w-4 shrink-0 text-primary" />
                                <span>{item}</span>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
            {group.image_url && (
                <figure className="lg:col-span-5">
                    <img
                        src={group.image_url}
                        alt={group.image_caption ?? ''}
                        className="max-h-96 w-full object-contain"
                    />
                    {group.image_caption && (
                        <figcaption className="mt-3 border-t border-border/60 pt-2.5 text-xs tracking-wide text-muted-foreground">
                            {group.image_caption}
                        </figcaption>
                    )}
                </figure>
            )}
        </div>
    );
}

/**
 * Unboxed editorial tabs & numbered hairline accordions inspired by inganjiroots.org and smco.ug.
 * - Top horizontal underline tab bar (no boxed cards or pills)
 * - Short narrative tabs render as asymmetric 1/3–2/3 numbered hairline rows
 * - Structured/long tabs (or `accordion: true`) render as numbered `+` / `−` hairline accordions
 */
export function TabsSection({ content }: { content: Content }) {
    const tabs: InfoTab[] = Array.isArray(content.tabs) ? content.tabs : [];
    const [active, setActive] = React.useState(0);
    const [open, setOpen] = React.useState<number | null>(0);
    const current = tabs[active];
    const groups = current?.groups ?? [];
    const useAccordion = Boolean(content.accordion || current?.accordion || groups.length >= 5);

    return (
        <SectionShell>
            {content.heading && (
                <SectionHeading
                    index={content.index}
                    label={content.label}
                    title={String(content.heading)}
                    accent={content.accent}
                    description={content.body}
                    divided
                />
            )}

            {/* Minimalist horizontal underline tab bar (no card wrapper) */}
            {tabs.length > 1 && (
                <div
                    className="mb-12 flex gap-8 overflow-x-auto border-b border-border no-scrollbar sm:mb-16 sm:gap-12"
                    role="tablist"
                >
                    {tabs.map((tab, i) => {
                        const isSelected = i === active;
                        return (
                            <button
                                key={tab.title}
                                type="button"
                                role="tab"
                                aria-selected={isSelected}
                                onClick={() => {
                                    setActive(i);
                                    setOpen(0);
                                }}
                                className={cn(
                                    'relative shrink-0 pb-4 text-left text-xs font-bold uppercase tracking-[0.16em] transition-colors sm:text-sm',
                                    'after:absolute after:inset-x-0 after:-bottom-px after:h-[2px] after:transition-transform after:duration-300',
                                    isSelected
                                        ? 'text-primary after:scale-x-100 after:bg-primary'
                                        : 'text-muted-foreground hover:text-foreground after:scale-x-0 after:bg-primary/40 hover:after:scale-x-100',
                                )}
                            >
                                <span className="mr-2.5 font-mono text-[11px] opacity-60">
                                    {String(i + 1).padStart(2, '0')}.
                                </span>
                                {tab.title}
                            </button>
                        );
                    })}
                </div>
            )}

            {current && (
                <div key={current.title} className="tab-panel-enter" role="tabpanel">
                    {/* Asymmetric editorial intro split (1/3 title · 2/3 lead statement) */}
                    {current.intro && (
                        <div className="mb-12 grid gap-6 lg:grid-cols-12 lg:gap-12">
                            <div className="lg:col-span-4">
                                <span className="inline-block border-b border-primary/50 pb-1 text-xs font-semibold uppercase tracking-[0.18em] text-primary">
                                    {current.title}
                                </span>
                            </div>
                            <div className="lg:col-span-8">
                                <p className="text-xl leading-[1.6] font-normal text-foreground sm:text-2xl">
                                    {current.intro}
                                </p>
                            </div>
                        </div>
                    )}

                    {useAccordion ? (
                        /* Numbered hairline `+` / `−` accordion with homepage-scale stat numbers */
                        <div className="divide-y divide-border border-y border-border">
                            {groups.map((group, i) => {
                                const isOpen = open === i;
                                return (
                                    <div key={i}>
                                        <button
                                            type="button"
                                            onClick={() => setOpen(isOpen ? null : i)}
                                            aria-expanded={isOpen}
                                            className="group grid w-full grid-cols-[4.5rem_1fr_auto] items-center gap-4 py-7 text-left transition-colors sm:grid-cols-[6.5rem_1fr_auto] sm:py-9"
                                        >
                                            <span
                                                className={cn(
                                                    'text-3xl font-bold leading-none tracking-tight tabular-nums transition-colors sm:text-4xl lg:text-5xl',
                                                    isOpen ? 'text-primary' : 'text-primary/65 group-hover:text-primary',
                                                )}
                                                style={{ fontFamily: 'var(--font-stat)' }}
                                            >
                                                {String(i + 1).padStart(2, '0')}
                                            </span>
                                            <span
                                                className={cn(
                                                    'text-lg font-bold tracking-tight transition-colors sm:text-2xl',
                                                    isOpen ? 'text-primary' : 'text-foreground group-hover:text-primary',
                                                )}
                                                style={{ fontFamily: 'var(--font-display)' }}
                                            >
                                                {group.heading}
                                            </span>
                                            <span
                                                className={cn(
                                                    'flex h-10 w-10 items-center justify-center rounded-full border border-border text-primary transition-colors',
                                                    isOpen
                                                        ? 'border-primary bg-primary text-white'
                                                        : 'text-foreground/60 group-hover:border-primary group-hover:text-primary',
                                                )}
                                                aria-hidden="true"
                                            >
                                                {isOpen ? (
                                                    <Minus className="h-4 w-4" strokeWidth={2} />
                                                ) : (
                                                    <Plus className="h-4 w-4" strokeWidth={2} />
                                                )}
                                            </span>
                                        </button>

                                        {isOpen && (
                                            <div className="tab-panel-enter pb-10 pl-0 pr-2 sm:pl-[calc(6.5rem+1rem)] sm:pr-12">
                                                <GroupBody group={group} />
                                            </div>
                                        )}
                                    </div>
                                );
                            })}
                        </div>
                    ) : (
                        /* Asymmetric 1/3 – 2/3 hairline-divided editorial rows with homepage-scale stat numbers */
                        <div className="divide-y divide-border border-t border-border">
                            {groups.map((group, i) => (
                                <div key={i} className="grid gap-6 py-12 sm:py-16 lg:grid-cols-12 lg:gap-12">
                                    <div className="lg:col-span-4">
                                        <span
                                            className="block text-4xl font-bold leading-none tracking-tight text-primary tabular-nums sm:text-5xl lg:text-6xl"
                                            style={{ fontFamily: 'var(--font-stat)' }}
                                        >
                                            {String(i + 1).padStart(2, '0')}
                                        </span>
                                        {group.heading && (
                                            <h3
                                                className="mt-4 text-2xl font-bold leading-snug tracking-tight text-foreground sm:text-[26px]"
                                                style={{ fontFamily: 'var(--font-display)' }}
                                            >
                                                {group.heading}
                                            </h3>
                                        )}
                                    </div>
                                    <div className="lg:col-span-8">
                                        <GroupBody group={group} />
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            )}
        </SectionShell>
    );
}

interface Person {
    id?: number;
    name: string;
    role?: string;
    image_url?: string;
    email?: string;
    phones?: string[];
}

/**
 * Team carousel with scroll-snap and prev/next controls.
 */
export function PeopleSection({ content }: { content: Content }) {
    const people: Person[] = Array.isArray(content.people) ? content.people : [];
    const track = React.useRef<HTMLDivElement | null>(null);
    const scrollBy = (direction: 1 | -1) => track.current?.scrollBy({ left: direction * 320, behavior: 'smooth' });

    return (
        <SectionShell>
            <div className="flex flex-wrap items-end justify-between gap-6">
                <SectionHeading
                    index={content.index}
                    label={content.label}
                    title={String(content.heading ?? '')}
                    accent={content.accent}
                    description={content.body}
                    divided
                />
                {people.length > 3 && (
                    <div className="mb-10 flex gap-2">
                        <button type="button" onClick={() => scrollBy(-1)} aria-label="Previous" className="flex h-11 w-11 items-center justify-center rounded-full border border-border transition hover:border-primary hover:text-primary">
                            <ArrowLeft className="h-4 w-4" />
                        </button>
                        <button type="button" onClick={() => scrollBy(1)} aria-label="Next" className="flex h-11 w-11 items-center justify-center rounded-full border border-border transition hover:border-primary hover:text-primary">
                            <ArrowRight className="h-4 w-4" />
                        </button>
                    </div>
                )}
            </div>
            <div ref={track} className="no-scrollbar flex snap-x gap-8 overflow-x-auto pb-2">
                {people.map((person, i) => (
                    <article
                        key={person.id ?? `${person.name}-${i}`}
                        className="stagger-item group w-80 shrink-0 snap-start border-r border-border pr-8 last:border-r-0 sm:w-88"
                        style={{ '--stagger-index': i } as React.CSSProperties}
                    >
                        <div className="img-reveal aspect-[4/5] rounded-2xl bg-secondary">
                            {person.image_url && (
                                <img src={person.image_url} alt={person.name} loading="lazy" className="h-full w-full object-cover" />
                            )}
                        </div>
                        <h3 className="mt-5 text-xl font-bold">{person.name}</h3>
                        {person.role && <p className="text-sm text-primary">{person.role}</p>}
                        <div className="mt-3 space-y-1.5 text-sm text-muted-foreground">
                            {person.email && (
                                <a href={`mailto:${person.email}`} className="flex items-center gap-2 transition hover:text-primary">
                                    <Mail className="h-4 w-4" /> {person.email}
                                </a>
                            )}
                            {(person.phones ?? []).map((phone) => (
                                <a key={phone} href={`tel:${phone.replace(/\s/g, '')}`} className="flex items-center gap-2 transition hover:text-primary">
                                    <Phone className="h-4 w-4" /> {phone}
                                </a>
                            ))}
                        </div>
                    </article>
                ))}
            </div>
        </SectionShell>
    );
}

/**
 * Contact details beside an underlined-field form, as in the reference design.
 */
export function ContactSection({ content }: { content: Content }) {
    const details: Array<{ label: string; lines: string[] }> = Array.isArray(content.details) ? content.details : [];
    const { data, setData, post, processing, errors, reset, wasSuccessful } = useForm({
        from_name: '',
        from_email: '',
        from_mobile: '',
        from_address: '',
        from_organisation: '',
        message: '',
    });

    const fieldClass =
        'peer h-12 w-full border-0 border-b border-border bg-transparent text-sm outline-none transition placeholder:text-xs placeholder:font-semibold placeholder:uppercase placeholder:tracking-wider placeholder:text-muted-foreground focus:border-primary';

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/contact-us', { preserveScroll: true, onSuccess: () => reset() });
    };

    const iconFor = (label: string) => (/mail/i.test(label) ? Mail : /contact|phone/i.test(label) ? Phone : MapPin);

    return (
        <SectionShell>
            <SectionHeading index={content.index} label={content.label} title={String(content.heading ?? 'Get in touch')} accent={content.accent} description={content.body} divided />
            <div className="grid gap-14 lg:grid-cols-[2fr_3fr] lg:gap-16">
                <div className="space-y-8">
                    {details.map((detail, i) => {
                        const Icon = iconFor(detail.label);

                        return (
                            <div
                                key={detail.label}
                                className="reveal-left flex gap-4"
                                style={{ '--stagger-index': i } as React.CSSProperties}
                            >
                                <span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-primary/15 text-primary">
                                    <Icon className="h-5 w-5" />
                                </span>
                                <div>
                                    <h3 className="text-sm font-semibold uppercase tracking-wider">{detail.label}</h3>
                                    <div className="mt-1 space-y-0.5 text-sm text-muted-foreground">
                                        {detail.lines.map((line) =>
                                            line.includes('@') ? (
                                                <a key={line} href={`mailto:${line}`} className="block transition hover:text-primary">{line}</a>
                                            ) : /^\+?[\d\s]{8,}$/.test(line) ? (
                                                <a key={line} href={`tel:${line.replace(/\s/g, '')}`} className="block transition hover:text-primary">{line}</a>
                                            ) : (
                                                <p key={line}>{line}</p>
                                            )
                                        )}
                                    </div>
                                </div>
                            </div>
                        );
                    })}
                </div>

                <form onSubmit={submit} className="lg:border-l lg:border-border lg:pl-14" noValidate>
                    <h3 className="mb-8 text-2xl font-bold tracking-tight text-foreground sm:text-3xl" style={{ fontFamily: 'var(--font-display)' }}>
                        Write to us
                    </h3>
                    <div className="grid gap-x-10 gap-y-6 sm:grid-cols-2">
                        <div>
                            <input aria-label="Your name" required className={fieldClass} placeholder="Your name" value={data.from_name} onChange={(e) => setData('from_name', e.target.value)} />
                            {errors.from_name && <p className="mt-1 text-xs text-red-400">{errors.from_name}</p>}
                        </div>
                        <div>
                            <input aria-label="Your email" type="email" required className={fieldClass} placeholder="Your email" value={data.from_email} onChange={(e) => setData('from_email', e.target.value)} />
                            {errors.from_email && <p className="mt-1 text-xs text-red-400">{errors.from_email}</p>}
                        </div>
                        <input aria-label="Phone number" className={fieldClass} placeholder="Phone number" value={data.from_mobile} onChange={(e) => setData('from_mobile', e.target.value)} />
                        <input aria-label="Location or address" className={fieldClass} placeholder="Location or address" value={data.from_address} onChange={(e) => setData('from_address', e.target.value)} />
                        <input aria-label="Organisation name" className={cn(fieldClass, 'sm:col-span-2')} placeholder="Organisation name" value={data.from_organisation} onChange={(e) => setData('from_organisation', e.target.value)} />
                        <div className="sm:col-span-2">
                            <textarea aria-label="Message" required rows={4} className={cn(fieldClass, 'h-auto resize-none py-3')} placeholder="Type your message here…" value={data.message} onChange={(e) => setData('message', e.target.value)} />
                            {errors.message && <p className="mt-1 text-xs text-red-400">{errors.message}</p>}
                        </div>
                    </div>
                    <button
                        type="submit"
                        disabled={processing}
                        className="group mt-8 inline-flex items-center gap-2 rounded-full bg-primary px-8 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-dark disabled:opacity-60"
                    >
                        {processing ? 'Sending…' : 'Send message'} <ArrowUpRight className="h-4 w-4 transition group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
                    </button>
                    {wasSuccessful && !processing && (
                        <p role="status" className="mt-4 text-sm text-primary">Thank you — your message has been received.</p>
                    )}
                </form>
            </div>
        </SectionShell>
    );
}

interface GalleryItem {
    image_url: string;
    caption?: string;
}

/**
 * Masonry-style gallery with a lightbox (arrow keys + Escape).
 */
export function GallerySection({ content }: { content: Content }) {
    const items: GalleryItem[] = Array.isArray(content.items) ? content.items : [];
    const [index, setIndex] = React.useState<number | null>(null);

    const step = React.useCallback(
        (direction: 1 | -1) => setIndex((current) => (current === null ? null : (current + direction + items.length) % items.length)),
        [items.length]
    );

    React.useEffect(() => {
        if (index === null) {
            return;
        }
        const onKey = (event: KeyboardEvent) => {
            if (event.key === 'ArrowRight') {
                step(1);
            }
            if (event.key === 'ArrowLeft') {
                step(-1);
            }
        };
        window.addEventListener('keydown', onKey);

        return () => window.removeEventListener('keydown', onKey);
    }, [index, step]);

    const current = index !== null ? items[index] : null;

    return (
        <SectionShell width="wide">
            <SectionHeading
                index={content.index}
                label={content.label}
                title={String(content.heading ?? 'Frames of')}
                accent={content.heading ? content.accent : 'Inspiration'}
                description={content.body}
                divided
            />
            <div className="columns-1 gap-4 sm:columns-2 lg:columns-3">
                {items.map((item, i) => (
                    <button
                        key={item.image_url}
                        type="button"
                        onClick={() => setIndex(i)}
                        className="img-reveal group relative mb-4 block w-full rounded-xl"
                        style={{ '--stagger-index': i % 6 } as React.CSSProperties}
                        aria-label={item.caption ? `Open ${item.caption}` : `Open photo ${i + 1}`}
                    >
                        <img src={item.image_url} alt={item.caption ?? ''} loading="lazy" className="w-full" />
                        <span className="absolute inset-0 flex items-end bg-gradient-to-t from-black/70 via-transparent opacity-0 transition group-hover:opacity-100">
                            {item.caption && <span className="p-4 text-sm font-medium text-white">{item.caption}</span>}
                        </span>
                    </button>
                ))}
            </div>

            <Dialog open={current !== null} onOpenChange={(open) => !open && setIndex(null)}>
                <DialogContent className="theme-public max-w-5xl border-0 bg-transparent p-0 shadow-none">
                    <DialogTitle className="sr-only">{current?.caption ?? 'Gallery photo'}</DialogTitle>
                    <DialogDescription className="sr-only">Photo viewer. Use the arrow keys to browse.</DialogDescription>
                    {current && (
                        <figure className="relative">
                            <img src={current.image_url} alt={current.caption ?? ''} className="max-h-[82vh] w-full rounded-xl object-contain" />
                            {current.caption && <figcaption className="mt-3 text-center text-sm text-white/80">{current.caption}</figcaption>}
                            <button type="button" onClick={() => step(-1)} aria-label="Previous photo" className="absolute left-2 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-black/60 text-white transition hover:bg-primary">
                                <ChevronLeft className="h-5 w-5" />
                            </button>
                            <button type="button" onClick={() => step(1)} aria-label="Next photo" className="absolute right-2 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-black/60 text-white transition hover:bg-primary">
                                <ChevronRight className="h-5 w-5" />
                            </button>
                        </figure>
                    )}
                </DialogContent>
            </Dialog>
        </SectionShell>
    );
}
