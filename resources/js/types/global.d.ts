import type { Auth } from '@/types/auth';

declare module 'react' {
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    interface InputHTMLAttributes<T> {
        passwordrules?: string;
    }
}

export type NotificationItem = {
    id: string;
    data: { bill_id: number; message: string };
    read_at: string | null;
    created_at: string;
};

export type Notifications = {
    unread_count: number;
    items: NotificationItem[];
} | null;

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            sidebarOpen: boolean;
            roles: string[];
            permissionNames: string[];
            notifications: Notifications;
            [key: string]: unknown;
        };
    }
}
