import { router } from '@inertiajs/vue3';

export default function useUsers() {
    const deleteUser = (user) => {
        const response = confirm(`Are you sure you want to delete ${user.name}?`);

        if (response) {
            router.delete(route('backoffice.users.destroy', [user.id]), {
                preserveState: true,
                preserveScroll: true,
            });
        }
    };

    const resetUserPassword = (user) => {
        const response = confirm(`Are you sure you want to reset the password for ${user.name}?`);

        if (response) {
            router.patch(
                route('backoffice.users.update-password', [user.id]),
                {},
                {
                    preserveState: true,
                    preserveScroll: true,
                },
            );
        }
    };

    return {
        deleteUser,
        resetUserPassword,
    };
}
