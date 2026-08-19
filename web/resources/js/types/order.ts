export interface BuyerOrderItem {
    id?: number;
    product_name: string;
    sku?: string | null;
    qty: number;
    price?: number;
    subtotal?: number;
    product_type?: 'physical' | 'digital';
    can_review?: boolean;
    product_slug?: string | null;
    download_url?: string | null;
}

export interface BuyerOrder {
    order_number: string;
    status: string;
    total_amount: string;
    store_name: string;
    items: BuyerOrderItem[];
    tracking_number?: string | null;
    shipping_courier?: string | null;
    created_at: string | null;
}

export interface SellerOrderItem {
    id: number;
    product_name: string;
    sku?: string | null;
    quantity: number;
    price: number;
    subtotal: number;
}

export interface SellerOrder {
    id: number;
    order_number: string;
    customer_name: string;
    customer_email: string;
    customer_phone: string;
    shipping_address: string;
    store_name: string;
    total_amount: string;
    total_num: number;
    status: string;
    payment_method?: string | null;
    created_at: string;
    items?: SellerOrderItem[];
    notes?: string;
    tracking_number?: string;
    payment?: {
        id: number;
        provider: string;
        method: string;
        status: string;
        proof_name?: string | null;
        can_confirm?: boolean;
    } | null;
}

export interface OrderInvoice {
    id: number;
    order_number: string;
    customer_name: string;
    customer_email: string;
    customer_phone?: string | null;
    shipping_address?: string | null;
    notes?: string | null;
    subtotal: number;
    subtotal_formatted: string;
    shipping_fee: number;
    shipping_fee_formatted: string;
    discount?: number;
    discount_formatted?: string;
    voucher_code?: string | null;
    tax?: number;
    tax_formatted?: string;
    total_amount: string;
    total_num: number;
    status: string;
    store_name: string;
    created_at: string;
    items?: OrderInvoiceItem[];
}

export interface OrderInvoiceItem {
    id: number;
    product_name: string;
    sku?: string | null;
    qty: number;
    price: number;
    subtotal: number;
    price_formatted?: string;
    subtotal_formatted?: string;
}