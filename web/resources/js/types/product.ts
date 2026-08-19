export interface Product {
    id: number;
    name: string;
    category: string;
    price: number;
    formatted_price?: string;
    stock: number;
    sold: number;
    rating?: number;
    tag: string | null;
    img: string | null;
    is_active: boolean;
    sku?: string;
    description?: string | null;
    brand?: string | null;
    weight_gram?: number;
    type?: 'physical' | 'digital';
    digital_file_path?: string | null;
    digital_file_name?: string | null;
    digital_file_mime?: string | null;
    meta_title?: string | null;
    meta_description?: string | null;
    seo_tags?: string | null;
    marketing_caption?: string | null;
    low_stock?: boolean;
    variant_options?: Array<{ name: string; values: string[] }>;
    variants?: ProductVariant[];
}

export interface ProductVariant {
    id: number;
    name: string;
    sku: string | null;
    price: number | null;
    formatted_price: string | null;
    priceNum?: number;
    stock: number;
    is_active: boolean;
    img?: string | null;
}

export interface MarketplaceProduct {
    id: number;
    name: string;
    price: string;
    priceNum?: number;
    sold: number;
    rating: number;
    store: string;
    storeSlug?: string;
    img: string;
    tag: string | null;
    cat: string;
    discount?: number;
    originalPrice?: string;
    freeShipping?: boolean;
    sku?: string;
    stock?: number;
}

export interface ProductDetail extends MarketplaceProduct {
    description?: string;
    sku?: string;
    brand?: string;
    weightGram?: number;
    category?: string;
    variant_id?: number;
    variant_options?: Array<{ name: string; values: string[] }>;
    variants?: ProductVariant[];
}

export interface WishlistItem {
    id: number;
    name: string;
    price: string;
    priceNum?: number;
    sold: number;
    rating: number;
    store: string;
    storeSlug?: string;
    img: string;
    tag: string | null;
    cat: string;
    discount?: number;
}

export interface ProductOption {
    id: number;
    name: string;
    price: string;
    img: string | null;
}