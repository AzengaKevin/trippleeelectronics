<script setup>
import AppAlert from '@/components/AppAlert.vue';
import useDate from '@/composables/useDate';
import useTaxes from '@/composables/useTaxes';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    tax: {
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
        title: 'Taxes',
        href: route('backoffice.taxes.index'),
    },
    {
        title: props.tax.name,
        href: null,
    },
];

const { deleteTax } = useTaxes();
const { formatDate } = useDate();
</script>

<template>
    <Head>
        <title>Tax Details</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Tax Details">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="auth.permissions?.includes('update-taxes')"
                    :href="route('backoffice.taxes.edit', [tax.id])"
                    class="btn btn-sm btn-info btn-outline rounded-full"
                >
                    <font-awesome-icon icon="edit" />
                    Edit
                </Link>
                <button
                    v-if="auth.permissions?.includes('delete-taxes')"
                    type="button"
                    @click="deleteTax(tax)"
                    class="btn btn-sm btn-error btn-outline rounded-full"
                >
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
                                    <th>ID</th>
                                    <td>{{ tax.id }}</td>
                                </tr>
                                <tr>
                                    <th>Name</th>
                                    <td>{{ tax.name }}</td>
                                </tr>
                                <tr>
                                    <th>Rate</th>
                                    <td>{{ tax.rate ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Type</th>
                                    <td>{{ tax.type ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Description</th>
                                    <td>{{ tax.description ?? '-' }}</td>
                                </tr>

                                <tr>
                                    <th>Is Compound</th>
                                    <td>{{ tax.is_compound ? 'Yes' : 'No' }}</td>
                                </tr>
                                <tr>
                                    <th>Is Inclusive</th>
                                    <td>{{ tax.is_inclusive ? 'Yes' : 'No' }}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ formatDate(tax.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ formatDate(tax.updated_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
