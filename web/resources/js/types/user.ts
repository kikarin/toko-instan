export interface UserProfile {
    id?: number;
    userId?: string;
    name: string;
    username?: string;
    bio?: string;
    email?: string;
    phone?: string;
    phoneVerified?: boolean;
    gender?: string;
    birthDate?: string;
    role: string;
    avatar?: string | null;
    created_at?: string;
}

export interface ActiveUser {
    name: string | null;
    email: string | null;
    displayName: string | null;
    photoURL: string | null;
}