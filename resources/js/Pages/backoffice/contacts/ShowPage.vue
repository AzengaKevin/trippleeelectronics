<script setup>
import AppAlert from '@/components/AppAlert.vue';
import useContacts from '@/composables/useContacts';
import useDate from '@/composables/useDate';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    contact: {
        type: Object,
        required: true,
    },
    feedback: {
        type: Object,
        default: null,
    },
});

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: route('backoffice.dashboard'),
    },
    {
        title: 'Contacts',
        href: route('backoffice.contacts.index'),
    },
    {
        title: props.contact.name,
        href: null,
    },
];

const { deleteContact } = useContacts();
const { formatDate } = useDate();
</script>

<template>
    <Head>
        <title>Contact Details</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Contact Details">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <button type="button" @click="deleteContact(contact)" class="btn btn-sm btn-error btn-outline rounded-full">
                    <font-awesome-icon icon="trash-alt" />
                    Delete
                </button>
            </div>

            <app-alert :feedback="feedback" />

            <div class="card bg-base-100 shadow">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <tbody>
                                <tr>
                                    <th>Author</th>
                                    <td>{{ contact.author?.name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>ID</th>
                                    <td>{{ contact.id }}</td>
                                </tr>
                                <tr>
                                    <th>Name</th>
                                    <td>{{ contact.name }}</td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>{{ contact.email ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Phone</th>
                                    <td>{{ contact.phone ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Message</th>
                                    <td>{{ contact.message ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ formatDate(contact.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ formatDate(contact.updated_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
