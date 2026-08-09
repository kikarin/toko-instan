export interface InventoryProduct {
    id: number;
    name: string;
    img: string | null;
    price: number;
    formatted_price: string;
    stock: number;
    is_active: boolean;
    sku: string | null;
    low_stock: boolean;
}

export type StockAction = 'in' | 'out' | 'adjust';

export interface StockMovement {
    id: number;
    type: 'in' | 'out' | 'adjustment';
    quantity: number;
    delta: number;
    stock_before: number;
    stock_after: number;
    reason: string | null;
    created_at: string;
}