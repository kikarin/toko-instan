export interface AdminUser {
    id: number;
    name: string;
    email: string;
    role: string;
    created_at: string | null;
}

export interface AdminStats {
    users: number;
    stores: number;
    products: number;
    orders: number;
    revenue: string;
}

export interface RecentOrder {
    order_number: string;
    store_name: string;
    total_amount: string;
    status: string;
    created_at: string | null;
}

export interface RecentUser {
    id: number;
    name: string;
    email: string;
    role: string;
}

export interface RecentStore {
    id: number;
    name: string;
    category: string | null;
}

export interface AdminPendingAction {
    kind: 'delete' | 'role';
    user: AdminUser;
    role?: string;
}