<script setup>
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    category: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
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
        title: 'Item Categories',
        href: route('backoffice.item-categories.index'),
    },
    {
        title: props.category.name,
        href: route('backoffice.item-categories.show', [props.category.id]),
    },
    {
        title: 'Edit',
        href: null,
    },
];

const form = useForm({
    name: props.category.name,
    image: null,
    description: props.category.description,
    featured: props.category.featured,
});

const updateItemCategory = () => {
    form.transform((data) => ({ ...data, _method: 'patch' })).post(route('backoffice.item-categories.update', [props.category.id]), {
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
        <title>Edit Item Category</title>
        <meta name="description" content="Users edit page" />
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Edit Item Category">
        <div class="space-y-4">
            <div class="card bg-base-100 shadow">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <form @submit.prevent="updateItemCategory" class="grid grid-cols-1 gap-4" novalidate>
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

                        <div>
                            <label class="flex cursor-pointer items-center gap-2">
                                <input type="checkbox" v-model="form.featured" class="checkbox checkbox-primary" />
                                <span>Featured</span>
                            </label>
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
                            <button type="submit" class="btn btn-info" :disabled="form.processing">
                                <span v-if="form.processing" class="loading loading-spinner loading-md"></span>
                                <span>Update</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
