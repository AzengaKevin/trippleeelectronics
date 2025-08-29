import { router } from '@inertiajs/vue3';

export default function useLogout() {
    const logout = (shouldConfirm = true) => {
        const response = !shouldConfirm || confirm(`Are you sure you want to sign out?`);

        if (response) {
            router.post(route('logout'));
        }
    };

    return {
        logout,
    };
}
