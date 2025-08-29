import { router } from '@inertiajs/vue3';

export default function useOrganizations() {
    const deleteOrganization = async (organization) => {
        if (confirm('Are you sure you want to delete this organization?')) {
            router.delete(route('backoffice.organizations.destroy', organization.id));
        }
    };

    return {
        deleteOrganization,
    };
}
