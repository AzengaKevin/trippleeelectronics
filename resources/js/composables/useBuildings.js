import { router } from '@inertiajs/vue3';

export default function useBuildings() {
    const deleteBuilding = (building) => {
        if (confirm('Are you sure you want to delete this building?')) {
            router.delete(route('backoffice.buildings.destroy', building.id));
        }
    };

    return {
        deleteBuilding,
    };
}
