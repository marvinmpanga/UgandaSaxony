import {
    CtaSection,
    FeaturedCardsSection,
    ImageSection,
    ImageTextSection,
    PartnerLogosSection,
    RichTextSection,
    StatsSection,
} from '@/components/sections/ContentSections';
import { HeroSection } from '@/components/sections/HeroSection';
import {
    ContactSection,
    GallerySection,
    PeopleSection,
    TabsSection,
} from '@/components/sections/InteractiveSections';
import { ArticlesSection, DirectorySection } from '@/components/sections/ListingSections';
import type { PageSection } from '@/types';

interface SectionRendererProps {
    section: PageSection;
}

/**
 * Maps a stored section type to its reusable React component.
 */
export function SectionRenderer({ section }: SectionRendererProps) {
    const content = section.content ?? {};

    switch (section.type) {
        case 'hero':
            return <HeroSection content={content} />;
        case 'rich_text':
            return <RichTextSection content={content} />;
        case 'image':
            return <ImageSection content={content} />;
        case 'image_text':
            return <ImageTextSection content={content} />;
        case 'stats':
            return <StatsSection content={content} />;
        case 'featured_cards':
            return <FeaturedCardsSection content={content} />;
        case 'partner_logos':
            return <PartnerLogosSection content={content} />;
        case 'cta':
            return <CtaSection content={content} />;
        case 'gallery':
            return <GallerySection content={content} />;
        case 'tabs':
            return <TabsSection content={content} />;
        case 'articles':
            return <ArticlesSection content={content} />;
        case 'directory':
            return <DirectorySection content={content} />;
        case 'people':
            return <PeopleSection content={content} />;
        case 'contact':
            return <ContactSection content={content} />;
        default:
            return null;
    }
}
