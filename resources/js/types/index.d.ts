import { Config } from 'ziggy-js';

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at: string;
    google_id?: string;
}

export interface Day {
    id: number;
    name: string;
    desc: string;
    anniv_at: string;
    diff_days: number;
}

export interface Entity {
    id: number;
    name: string;
    desc: string;
    status: number;
    days: Day[];
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: {
        user: User;
    };
    ziggy: Config & { location: string };
};
