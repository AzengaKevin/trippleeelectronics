<script setup>
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    categories: {
        type: Array,
        required: true,
    },
});

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: route('backoffice.dashboard'),
    },
    {
        title: 'Item Categories',
        href: route('backoffice.item-categories.index'),
    },
    {
        title: 'Create',
        href: null,
    },
];

const form = useForm({
    category: '',
    name: '',
    description: '',
    image: null,
    featured: false,
});

const createItemCategory = () => {
    form.post(route('backoffice.item-categories.store'), {
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
        <title>Create Item Category</title>
        <meta name="description" content="Create item category page" />
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Create Item Category">
        <div class="space-y-4">
            <div class="card bg-base-100 shadow">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <form @submit.prevent="createItemCategory" class="grid grid-cols-1 gap-4" novalidate>
                        <div class="space-y-2">
                            <label for="parent" class="label">
                                <span class="label-text">Parent</span>
                            </label>
                            <select
                                id="parent"
                                v-model="form.category"
                                class="select select-bordered w-full"
                                :class="{ 'input-error': form.errors.category }"
                            >
                                <option disabled selected value="">Select a Category</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                            </select>
                            <p v-if="form.errors.category" class="text-error">{{ form.errors.category }}</p>
                        </div>
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
                        <div>
                            <label class="flex cursor-pointer items-center gap-2">
                                <input type="checkbox" v-model="form.featured" class="checkbox checkbox-primary" />
                                <span>Featured</span>
                            </label>
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
