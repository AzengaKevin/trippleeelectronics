import { router } from '@inertiajs/vue3';

export default function usePermissions() {
    const deletePermission = (permission) => {
        const confirmed = confirm(`Are you sure you want to delete the permission: ${permission.name}?`);

        if (confirmed) {
            router.delete(route('backoffice.permissions.destroy', [permission.id]));
        }
    };

    return {
        deletePermission,
    };
}
