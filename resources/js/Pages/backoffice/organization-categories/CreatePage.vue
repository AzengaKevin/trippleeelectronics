<script setup>
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: route('backoffice.dashboard'),
    },
    {
        title: 'Organization Categories',
        href: route('backoffice.organization-categories.index'),
    },
    {
        title: 'Create',
        href: null,
    },
];

const form = useForm({
    name: '',
});

const createItemCategory = () => {
    form.post(route('backoffice.organization-categories.store'), {
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
        <title>Create Organization Category</title>
        <meta name="description" content="Create item category page" />
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Create Organization Category">
        <div class="space-y-4">
            <div class="card bg-base-100 shadow">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <form @submit.prevent="createItemCategory" class="grid grid-cols-1 gap-4" novalidate>
                        <div class="space-y-2">
                            <label for="name" class="label">
                                <span class="label-text">Name</span>
                            </label>
                            <input
                                type="text"
                                id="name"
                                v-model="form.name"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.name }"
                                required
                            />
                            <p v-if="form.errors.name" class="text-error">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                <span v-if="form.processing" class="loading loading-spinner loading-md"></span>
                                Submit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
