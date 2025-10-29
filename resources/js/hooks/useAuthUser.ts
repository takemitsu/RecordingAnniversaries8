import { usePage } from '@inertiajs/react';
import type { PageProps } from '@/types';

export function useAuthUser() {
    return usePage<PageProps>().props.auth.user;
}
