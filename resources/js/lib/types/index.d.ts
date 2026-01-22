import { Config } from 'ziggy-js';

export interface Role {
    id: number;
    name: string;
    guard_name: string;
}

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    profile_photo_url: string | undefined;
    two_factor_confirmed_at: string | null;
    disabled_at?: string | null;
    created_at?: string;
    roles?: Role[];
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: PaginationLink[];
}

export interface UserFilters {
    search?: string;
    role?: string;
    status?: 'active' | 'disabled' | '';
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
    ziggy: Config & { location: string };
};
