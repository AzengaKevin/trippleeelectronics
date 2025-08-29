import { router } from '@inertiajs/vue3';

export default function useContacts() {
    const deleteContact = (contact) => {
        const confirmed = confirm(`Are you sure you want to delete the contact: ${contact.name}?`);

        if (confirmed) {
            router.delete(route('backoffice.contacts.destroy', [contact.id]));
        }
    };

    return {
        deleteContact,
    };
}
