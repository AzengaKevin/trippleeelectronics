<script setup>
import AppAlert from '@/components/AppAlert.vue';
import useDate from '@/composables/useDate';
import useIndividuals from '@/composables/useIndividuals';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    individual: {
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
        title: 'Individuals',
        href: route('backoffice.individuals.index'),
    },
    {
        title: props.individual.name,
        href: null,
    },
];

const { deleteIndividual } = useIndividuals();
const { formatDate } = useDate();
</script>

<template>
    <Head>
        <title>Individual Details</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Individual Details">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <Link :href="route('backoffice.individuals.edit', [individual.id])" class="btn btn-sm btn-info btn-outline rounded-full">
                    <font-awesome-icon icon="edit" />
                    Edit
                </Link>
                <button type="button" @click="deleteIndividual(individual)" class="btn btn-sm btn-error btn-outline rounded-full">
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
                                    <td>{{ individual.author?.name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>ID</th>
                                    <td>{{ individual.id }}</td>
                                </tr>
                                <tr>
                                    <th>Name</th>
                                    <td>{{ individual.name }}</td>
                                </tr>
                                <tr>
                                    <th>Image</th>
                                    <td>
                                        <img v-if="individual.image_url" :src="individual.image_url" :alt="individual.name" width="120" />
                                        <span v-else>-</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Username</th>
                                    <td>{{ individual.username ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>{{ individual.email ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Phone</th>
                                    <td>{{ individual.phone ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Address</th>
                                    <td>{{ individual.address ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>KRA PIN</th>
                                    <td>{{ individual.kra_pin ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>ID Number</th>
                                    <td>{{ individual.id_number ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Organization</th>
                                    <td>{{ individual.organnization?.name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>User</th>
                                    <td>{{ individual.user?.name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ formatDate(individual.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ formatDate(individual.updated_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
