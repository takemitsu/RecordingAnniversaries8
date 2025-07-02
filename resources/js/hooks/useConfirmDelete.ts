import { Inertia } from '@inertiajs/inertia';

export function useConfirmDelete() {
    const confirmDelete = (itemName: string, message: string, deleteUrl: string) => {
        if (confirm(`remove this ${message}: ${itemName}`)) {
            Inertia.delete(deleteUrl);
        }
    };

    return { confirmDelete };
}