export interface CartItem {
    id: number;
    variant_id?: number;
    name: string;
    price: number;
    formattedPrice: string;
    img: string;
    store: string;
    qty: number;
}