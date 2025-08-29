<script setup>
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: route('backoffice.dashboard'),
    },
    {
        title: 'Brands',
        href: route('backoffice.brands.index'),
    },
    {
        title: 'Create',
        href: null,
    },
];

const form = useForm({
    name: '',
    description: '',
    image: null,
});

const createBrand = () => {
    form.post(route('backoffice.brands.store'), {
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
        <title>Create Brand</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Create Brand">
        <div class="space-y-4">
            <div class="card bg-base-100 shadow">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <form @submit.prevent="createBrand" class="grid grid-cols-1 gap-4" novalidate>
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
