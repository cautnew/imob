export type Company = {
    id: number;
    name: string;
    document: string | null;
    phone: string | null;
    address: string | null;
    onboarded_at: string | null;
};

export type User = {
    id: number;
    company_id: number;
    is_owner: boolean;
    company: Company;
    roles?: string[];
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
};

/* @chisel-passkeys */
export type Passkey = {
    id: number;
    name: string;
    authenticator: string | null;
    created_at_diff: string;
    last_used_at_diff: string | null;
};
/* @end-chisel-passkeys */
