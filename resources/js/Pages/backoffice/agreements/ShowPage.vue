<script setup>
import AppAlert from '@/components/AppAlert.vue';
import useAgreements from '@/composables/useAgreements';
import useDate from '@/composables/useDate';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    agreement: {
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
    { title: 'Agreements', href: route('backoffice.agreements.index') },
    { title: `# ${props.agreement.id}`, href: null },
];

const { deleteAgreement } = useAgreements();
const { formatDate } = useDate();
</script>
<template>
    <Head>
        <title>Agreement Details</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Agreement Details">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3 lg:justify-between">
                <div class="flex flex-wrap items-center gap-3">
                    <Link
                        v-if="auth.permissions.includes('update-agreements')"
                        :href="route('backoffice.agreements.edit', [agreement.id])"
                        class="btn btn-sm btn-info btn-outline rounded-full"
                    >
                        <font-awesome-icon icon="edit" />
                        Edit
                    </Link>
                    <a
                        :href="route('backoffice.agreements.print', [agreement.id])"
                        target="_blank"
                        class="btn btn-sm btn-accent btn-outline rounded-full"
                    >
                        <font-awesome-icon icon="print" />
                        Print
                    </a>
                    <button
                        v-if="auth.permissions.includes('delete-agreements')"
                        type="button"
                        @click="deleteAgreement(agreement)"
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
                                    <th>Author</th>
                                    <td>{{ agreement.author?.name }}</td>
                                </tr>
                                <tr>
                                    <th>Client</th>
                                    <td>{{ agreement.client?.name }}</td>
                                </tr>
                                <tr>
                                    <th>Store</th>
                                    <td>{{ agreement.store?.name }}</td>
                                </tr>
                                <tr v-if="agreement.agreementable_id">
                                    <th>Deal</th>
                                    <td>
                                        {{ agreement.agreementable_id }} <span class="badge badge-primary">{{ agreement.agreementable_type }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Content</td>
                                    <td>{{ agreement.content }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>{{ agreement.status }}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ formatDate(agreement.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ formatDate(agreement.updated_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
