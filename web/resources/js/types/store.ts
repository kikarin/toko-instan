export interface ThemeColors {
    primary: string;
    secondary: string;
    accent: string;
    strong: string;
}

export interface HeroConfig {
    about_text?: string;
    widget_title?: string;
    widget_subtitle?: string;
    widget_description?: string;
    fake_buyer_count?: string;
}

export interface ThemePayload {
    key?: string;
    colors?: Partial<ThemeColors>;
}

export interface ThemeInfo {
    key: string;
    label: string;
    colors: ThemeColors;
    font: string;
}

export interface StoreData {
    id: number;
    name: string;
    slug: string;
    category?: string;
    description?: string;
    logo?: string;
    avatar_hue?: number;
    banner_url?: string;
    banner_urls?: string[];
    highlights?: string[];
    hero_config?: HeroConfig;
    phone?: string;
    email?: string;
    address?: string;
    instagram?: string;
    tiktok?: string;
    headline?: string;
    badge?: string;
    rating?: number;
    is_active?: boolean;
    npwp?: string;
    nik?: string;
    is_pkp?: boolean;
    tax_name?: string;
    tax_address?: string;
}

export interface StorefrontInfo {
    id: number;
    name: string;
    slug: string;
    category?: string;
    description?: string;
    logo?: string;
    avatar_hue?: number;
    banner_url?: string;
    banner_urls?: string[];
    highlights?: string[];
    hero_config?: HeroConfig;
    headline?: string;
    badge?: string;
    rating?: number;
    phone?: string;
    email?: string;
    address?: string;
    instagram?: string;
    tiktok?: string;
}

export interface StorefrontTheme {
    key: string;
    label: string;
    colors: {
        primary: string;
        secondary: string;
        accent: string;
        strong?: string;
    };
    font: string;
}

export interface Testimonial {
    name?: string;
    role?: string;
    text?: string;
    rating?: number;
}

export interface StoreShowcase {
    hero?: {
        title?: string;
        subtitle?: string;
        cta_label?: string;
        image?: string | null;
    };
    about?: { title?: string; text?: string };
    testimonials?: Testimonial[];
    contact?: { show?: boolean };
}