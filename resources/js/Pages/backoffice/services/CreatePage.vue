<script setup>
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: route('backoffice.dashboard'),
    },
    {
        title: 'Services',
        href: route('backoffice.services.index'),
    },
    {
        title: 'Create',
        href: null,
    },
];

const form = useForm({
    title: '',
    description: '',
    image: null,
});

const createService = () => {
    form.post(route('backoffice.services.store'), {
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
        <title>Create Service</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Create Service">
        <div class="space-y-4">
            <div class="card bg-base-100 shadow">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <form @submit.prevent="createService" class="grid grid-cols-1 gap-4" novalidate>
                        <div class="space-y-2">
                            <label for="title" class="label">
                                <span class="label-text">Title</span>
                            </label>
                            <input
                                type="text"
                                id="title"
                                v-model="form.title"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.title }"
                                required
                            />
                            <p v-if="form.errors.title" class="text-error">{{ form.errors.title }}</p>
                        </div>
                        <div class="space-y-2">
                            <label for="email" class="label">
                                <span class="label-text">Description</span>
                            </label>
                            <textarea
                                v-model="form.description"
                                class="textarea textarea-bordered w-full"
                                :class="{ 'textarea-error': form.errors.description }"
                                placeholder="Description"
                            ></textarea>
                            <p v-if="form.errors.description" class="text-error">{{ form.errors.description }}</p>
                        </div>
                        <div class="space-y-2">
                            <label for="image" class="label">
                                <span class="label-text">Image</span>
                            </label>
                            <input
                                type="file"
                                id="image"
                                @change="(event) => (form.image = event.target.files[0])"
                                class="file-input file-input-bordered w-full"
                                :class="{ 'input-error': form.errors.image }"
                                accept="image/*"
                            />
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
