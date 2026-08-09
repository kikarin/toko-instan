export interface Address {
    id: number;
    label: string | null;
    recipient_name: string;
    phone: string;
    address: string;
    district: string;
    city: string;
    province: string;
    postal_code: string;
    is_default: boolean;
}