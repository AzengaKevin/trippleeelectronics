<script setup>
import AppAlert from '@/components/AppAlert.vue';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
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
        title: 'Payments',
        href: route('backoffice.payments.index'),
    },
    {
        title: 'Import',
        href: null,
    },
];

const form = useForm({
    file: null,
});

const importPayments = () => {
    form.post(route('backoffice.payments.import'), {
        onSuccess: () => {
            form.reset();
        },
        onError: (errors) => {
            console.error(errors);
        },
    });
};
</script>
<template>
    <Head>
        <title>Import Payments</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Import Payments">
        <div class="space-y-4">
            <AppAlert :feedback="feedback" />
            <div class="card bg-base-100 shadow">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <form @submit.prevent="importPayments" class="grid grid-cols-1 gap-4" novalidate>
                        <div class="space-y-2">
                            <label for="file" class="label">
                                <span class="label-text">File</span>
                            </label>
                            <input type="file" id="file" class="file-input file-input-bordered w-full" @change="form.file = $event.target.files[0]" />

                            <div class="text-sm">
                                Download the sample import file
                                <a :href="route('backoffice.payments.export', { limit: 1 })" download class="text-primary underline"> here </a>
                            </div>
                            <p v-if="form.errors.file" class="text-error text-sm">
                                {{ form.errors.file }}
                            </p>
                        </div>
                        <div class="form-control">
                            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                <span v-if="form.processing">Importing...</span>
                                <span v-else>Import</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
