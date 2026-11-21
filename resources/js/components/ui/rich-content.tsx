import { splitParagraphs } from '@/components/sections/SectionShell';
import { cn } from '@/lib/utils';

interface RichContentProps {
    content?: string | null;
    fallback?: string | null;
    className?: string;
}

const HTML_TAG_RE = /<[a-z][\s\S]*>/i;

/**
 * Strip HTML tags for plain-text excerpts, summaries, and meta descriptions.
 */
export function stripHtml(value?: string | null): string {
    if (!value) {
        return '';
    }

    return value
        .replace(/<[^>]+>/g, ' ')
        .replace(/&nbsp;/gi, ' ')
        .replace(/&amp;/gi, '&')
        .replace(/&quot;/gi, '"')
        .replace(/&#039;|&apos;/gi, "'")
        .replace(/&lt;/gi, '<')
        .replace(/&gt;/gi, '>')
        .replace(/\s+/g, ' ')
        .trim();
}

/**
 * Renders both Filament RichEditor HTML (headings, lists, blockquotes, links,
 * inline uploaded images, and file attachments) and plain multi-paragraph text.
 */
export function RichContent({ content, fallback, className }: RichContentProps) {
    const raw = (content ?? '').trim() || (fallback ?? '').trim();

    if (!raw) {
        return null;
    }

    if (HTML_TAG_RE.test(raw)) {
        return (
            <div
                className={cn('rich-content', className)}
                dangerouslySetInnerHTML={{ __html: raw }}
            />
        );
    }

    const paragraphs = splitParagraphs(raw);

    return (
        <div className={cn('space-y-6 text-base leading-relaxed text-foreground/85 sm:text-lg sm:leading-8', className)}>
            {paragraphs.map((paragraph, index) => (
                <p key={index}>{paragraph}</p>
            ))}
        </div>
    );
}
