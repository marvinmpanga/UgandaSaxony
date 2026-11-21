export type ContentStatus = 'draft' | 'pending_review' | 'published' | 'archived';

export type SectionType =
    | 'hero'
    | 'rich_text'
    | 'image'
    | 'image_text'
    | 'stats'
    | 'featured_cards'
    | 'partner_logos'
    | 'testimonials'
    | 'gallery'
    | 'cta'
    | 'video_embed'
    | 'events_news_highlight'
    | 'articles'
    | 'directory'
    | 'people'
    | 'contact'
    | 'tabs';

export interface AuthUser {
    id: number;
    name: string;
    email: string;
    roles: string[];
    permissions: string[];
}

export interface NavPage {
    id: number;
    title: string;
    slug: string;
    is_homepage: boolean;
}

export interface PageSection {
    id?: number;
    page_id?: number;
    type: SectionType;
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    content: Record<string, any>;
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    settings?: Record<string, any>;
    sort_order: number;
    is_visible: boolean;
}

export interface ArticleItem {
    slug?: string;
    title: string;
    date?: string;
    tag?: string;
    location?: string;
    image_url?: string;
    excerpt?: string;
    body?: string;
}

export interface Page {
    id: number;
    title: string;
    slug: string;
    status: ContentStatus;
    is_homepage: boolean;
    show_in_nav: boolean;
    nav_order: number;
    meta_title: string | null;
    meta_description: string | null;
    published_at: string | null;
    scheduled_publish_at: string | null;
    created_at: string;
    updated_at: string;
    sections?: PageSection[];
    sections_count?: number;
    creator?: { id: number; name: string } | null;
    updater?: { id: number; name: string } | null;
    publisher?: { id: number; name: string } | null;
}

export interface MediaFolder {
    id: number;
    name: string;
    slug: string;
    parent_id: number | null;
    description: string | null;
    items_count?: number;
}

export interface MediaItem {
    id: number;
    folder_id: number | null;
    collection: string;
    disk: string;
    path: string;
    file_name: string;
    original_name: string;
    mime_type: string;
    size: number;
    width: number | null;
    height: number | null;
    alt_text: string | null;
    caption: string | null;
    url: string;
    is_image: boolean;
    created_at: string;
    folder?: { id: number; name: string } | null;
    uploader?: { id: number; name: string } | null;
}

export interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

export interface SelectOption {
    value: string;
    label: string;
}

export interface SharedProps {
    auth: {
        user: AuthUser | null;
    };
    flash: {
        success: string | null;
        error: string | null;
    };
    navigation: NavPage[];
    [key: string]: unknown;
}
