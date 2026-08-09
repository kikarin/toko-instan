export interface WalletSummary {
    balance: string;
    pending_balance: string;
}

export interface Withdrawal {
    id: number;
    amount: string;
    net_amount: string;
    fee: string;
    status: string;
    bank_name: string;
    account_number: string;
    account_name: string;
    created_at: string | null;
}

export interface AdminWithdrawal extends Withdrawal {
    store_name: string;
}

export interface WalletTransaction {
    id: number;
    type: string;
    direction: string;
    amount: string;
    balance_after: string;
    pending_after: string;
    description: string | null;
    created_at: string | null;
}

export interface Pagination {
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
}

export interface PagedList<T> {
    data: T[];
    pagination: Pagination;
}