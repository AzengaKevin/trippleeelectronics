<script setup>
import AppAlert from '@/components/AppAlert.vue';
import useContracts from '@/composables/useContracts';
import useDate from '@/composables/useDate';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    contract: {
        type: Object,
        required: true,
    },
    feedback: {
        type: Object,
        required: false,
    },
});

const breadcrumbs = [
    { title: 'Dashboard', href: route('backoffice.dashboard') },
    { title: 'Contracts', href: route('backoffice.contracts.index') },
    { title: `# ${props.contract.id}`, href: null },
];

const { deleteContract } = useContracts();
const { formatDate } = useDate();
</script>
<template>
    <Head>
        <title>Contract Details</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Contract Details">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3 lg:justify-between">
                <div class="flex flex-wrap items-center gap-3">
                    <Link
                        v-if="auth.permissions.includes('update-contracts')"
                        :href="route('backoffice.contracts.edit', [contract.id])"
                        class="btn btn-sm btn-info btn-outline rounded-full"
                    >
                        <font-awesome-icon icon="edit" />
                        Edit
                    </Link>
                    <button
                        v-if="auth.permissions.includes('delete-contracts')"
                        type="button"
                        @click="deleteContract(contract)"
                        class="btn btn-sm btn-error btn-outline rounded-full"
                    >
                        <font-awesome-icon icon="trash-alt" />
                        Delete
                    </button>
                </div>
            </div>

            <app-alert :feedback="feedback" />

            <div class="card bg-base-100 shadow">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <tbody>
                                <tr>
                                    <th>ID</th>
                                    <td>{{ contract.id }}</td>
                                </tr>
                                <tr>
                                    <th>Employee</th>
                                    <td>{{ contract.employee?.name }}</td>
                                </tr>
                                <tr>
                                    <th>Contract Type</th>
                                    <td>{{ contract.contract_type ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>{{ contract.status ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Start Date</th>
                                    <td>{{ contract.start_date ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>End Date</th>
                                    <td>{{ contract.end_date ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Responsibilities</th>
                                    <td v-if="contract.responsibilities?.length">
                                        <ul class="list">
                                            <li v-for="r in contract.responsibilities" class="list-row">{{ r }}</li>
                                        </ul>
                                    </td>
                                    <td v-else>-</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ formatDate(contract.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ formatDate(contract.updated_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
