import { router } from '@inertiajs/vue3';
import axios from 'axios';

export default function useRooms() {
    const loadRooms = (query, setOptions) => {
        axios.get(route('api.rooms.index'), { params: { query, limit: 5, perPage: null } }).then((results) => {
            setOptions(
                results.data.data.map(({ id, name, code, status, active, price }) => ({
                    value: id,
                    label: name,
                    code,
                    status,
                    active,
                    price,
                })),
            );
        });
    };

    const deleteRoom = (room) => {
        if (confirm('Are you sure you want to delete this room?')) {
            router.delete(route('backoffice.rooms.destroy', room.id), {
                preserveState: true,
                preserveScroll: true,
            });
        }
    };

    return {
        loadRooms,
        deleteRoom,
    };
}
