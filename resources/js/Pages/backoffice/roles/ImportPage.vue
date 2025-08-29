<script setup>
import AppAlert from '@/components/AppAlert.vue';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    feedback: Object,
});

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: route('backoffice.dashboard'),
    },
    {
        title: 'Roles',
        href: route('backoffice.roles.index'),
    },
    {
        title: 'Import',
        href: null,
    },
];

const form = useForm({
    file: null,
});

const importRoles = () => {
    form.post(route('backoffice.roles.import'), {
        onSuccess: () => form.reset(),
        onError: (errors) => console.error(errors),
    });
};
</script>

<template>
    <Head>
        <title>Import Roles</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Import Roles">
        <div class="space-y-4">
            <AppAlert :feedback="feedback" />
            <div class="card bg-base-100 shadow">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <form @submit.prevent="importRoles" class="grid grid-cols-1 gap-4" novalidate>
                        <div class="space-y-2">
                            <label for="file" class="label">
                                <span class="label-text">File</span>
                            </label>
                            <input type="file" id="file" class="file-input file-input-bordered w-full" @input="form.file = $event.target.files[0]" />
                            <div class="text-sm">
                                Download the sample import file
                                <a :href="route('backoffice.roles.export', { limit: 1 })" download class="text-primary underline"> here </a>
                            </div>
                            <p v-if="form.errors.file" class="text-error text-sm">
                                {{ form.errors.file }}
                            </p>
                        </div>

                        <div>
                            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                <span v-if="form.processing" class="loading loading-spinner loading-md"></span>
                                <span>Import</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
