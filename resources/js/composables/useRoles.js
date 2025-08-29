import { router } from '@inertiajs/vue3';

export default function useRoles() {
    const deleteRole = (role) => {
        const confirmed = confirm(`Are you sure you want to delete the role: ${role.name}?`);

        if (confirmed) {
            router.delete(route('backoffice.roles.destroy', [role.id]));
        }
    };

    return {
        deleteRole,
    };
}
