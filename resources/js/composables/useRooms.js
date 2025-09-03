import { router } from '@inertiajs/vue3';

export default function useRooms() {
    const deleteRoom = (room) => {
        if (confirm('Are you sure you want to delete this room?')) {
            router.delete(route('backoffice.rooms.destroy', room.id), {
                preserveState: true,
                preserveScroll: true,
            });
        }
    };

    return {
        deleteRoom,
    };
}
