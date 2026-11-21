import { Head } from '@inertiajs/react';
import { SectionRenderer } from '@/components/sections/SectionRenderer';
import { PublicLayout } from '@/layouts/PublicLayout';
import type { Page } from '@/types';

interface FeaturedArticle {
    title: string;
    slug: string;
    excerpt: string | null;
    cover_image: string | null;
    tag: string | null;
    event_date: string | null;
}

interface PageViewProps {
    page: Page;
    isPreview?: boolean;
    featuredArticles?: FeaturedArticle[];
}

export default function PageView({ page, isPreview = false, featuredArticles = [] }: PageViewProps) {
    let sections = page.sections ?? [];

    // Auto-inject featured articles into the hero slides if the first section is a hero
    // and there are published featured articles
    if (sections.length > 0 && sections[0].type === 'hero' && featuredArticles.length > 0) {
        const heroSection = { ...sections[0] };
        const existingSlides = Array.isArray(heroSection.content?.slides) ? heroSection.content.slides : [heroSection.content];

        const articleSlides = featuredArticles.map((a) => ({
            eyebrow: a.tag ?? 'Latest',
            heading: a.title,
            subheading: a.excerpt ?? '',
            image_url: a.cover_image ?? '',
            primary_cta_label: 'Read More',
            primary_cta_url: `/events/${a.slug}`,
        }));

        heroSection.content = {
            ...heroSection.content,
            slides: articleSlides,
        };

        sections = [heroSection, ...sections.slice(1)];
    }

    const startsWithFullHero = sections[0]?.type === 'hero' && !Boolean(sections[0]?.content?.compact);
    const hasDarkHero = startsWithFullHero;

    return (
        <PublicLayout isPreview={isPreview} editUrl="/admin" hasDarkHero={hasDarkHero}>
            <Head title={page.meta_title || page.title}>
                {page.meta_description && <meta name="description" content={page.meta_description} />}
            </Head>

            {sections.length === 0 ? (
                <div className="mx-auto max-w-4xl px-4 py-40 text-center">
                    <h1 className="text-4xl font-semibold">{page.title}</h1>
                    <p className="mt-3 text-muted-foreground">No content sections have been added to this page yet.</p>
                </div>
            ) : (
                <div className={startsWithFullHero ? '' : 'pt-20 sm:pt-24'}>
                    {sections.map((section, idx) => (
                        <SectionRenderer key={section.id ?? idx} section={section} />
                    ))}
                </div>
            )}
        </PublicLayout>
    );
}
