<?php

namespace App\Enums;

enum SectionType: string
{
    case Hero = 'hero';
    case RichText = 'rich_text';
    case Image = 'image';
    case ImageText = 'image_text';
    case Stats = 'stats';
    case FeaturedCards = 'featured_cards';
    case PartnerLogos = 'partner_logos';
    case Testimonials = 'testimonials';
    case Gallery = 'gallery';
    case Cta = 'cta';
    case VideoEmbed = 'video_embed';
    case EventsNewsHighlight = 'events_news_highlight';
    case Articles = 'articles';
    case Directory = 'directory';
    case People = 'people';
    case Contact = 'contact';
    case Tabs = 'tabs';

    public function label(): string
    {
        return match ($this) {
            self::Hero => 'Hero Banner',
            self::RichText => 'Rich Text Content',
            self::Image => 'Image',
            self::ImageText => 'Image & Text',
            self::Stats => 'Statistics',
            self::FeaturedCards => 'Featured Cards',
            self::PartnerLogos => 'Partner Logos',
            self::Testimonials => 'Testimonials',
            self::Gallery => 'Image Gallery',
            self::Cta => 'Call to Action',
            self::VideoEmbed => 'Video Embed',
            self::EventsNewsHighlight => 'Events & News Highlights',
            self::Articles => 'Articles / Events',
            self::Directory => 'Directory',
            self::People => 'People / Team',
            self::Contact => 'Contact Form',
            self::Tabs => 'Tabbed Content',
        };
    }
}
