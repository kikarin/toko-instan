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
    low_stock?: boolean;
}

export interface ProductVariant {
    id: number;
    name: string;
    sku: string | null;
    price: number | null;
    formatted_price: string | null;
    stock: number;
    is_active: boolean;
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
}

export interface ProductDetail extends MarketplaceProduct {
    description?: string;
    sku?: string;
    brand?: string;
    weightGram?: number;
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