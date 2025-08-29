import { router } from '@inertiajs/vue3';

export default function useMedia() {
    const deleteMedia = (media) => {
        const confirmed = confirm(`Are you sure you want to delete the media: ${media.name}?`);

        if (confirmed) {
            router.delete(route('backoffice.media.destroy', [media.id]));
        }
    };

    return {
        deleteMedia,
    };
}
