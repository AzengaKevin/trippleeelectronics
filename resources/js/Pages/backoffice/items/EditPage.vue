<script setup>
import useSwal from '@/composables/useSwal';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';

import { Head, useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
    },
    brands: {
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
        title: 'Items',
        href: route('backoffice.items.index'),
    },
    {
        title: props.item.name,
        href: route('backoffice.items.show', [props.item.id]),
    },
    {
        title: 'Edit',
        href: null,
    },
];

const form = useForm({
    name: props.item.name,
    pos_name: props.item.pos_name,
    image: null,
    images: [],
    category: props.item.item_category_id,
    brand: props.item.brand_id,
    price: props.item.price,
    selling_price: props.item.selling_price,
    cost: props.item.cost,
    description: props.item.description,
    pos_description: props.item.pos_description,
});

const { showInertiaErrorsSwal, showFeedbackSwal } = useSwal();

const updateItem = () => {
    form.transform((data) => ({ ...data, _method: 'patch' })).post(route('backoffice.items.update', [props.item.id]), {
        onSuccess: () => {
            form.reset();
        },
        onError: (errors) => {
            showInertiaErrorsSwal(errors);
        },
    });
};

watch(
    () => props.feedback,
    (newFeedback) => {
        if (newFeedback) {
            showFeedbackSwal(newFeedback);
        }
    },
);
</script>

<template>
    <Head>
        <title>Edit Item</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Edit Item">
        <div class="space-y-4">
            <div class="card bg-base-100 shadow">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <form @submit.prevent="updateItem" class="grid grid-cols-1 gap-4" novalidate>
                        <!-- Item Name -->
                        <div class="space-y-2">
                            <label for="name" class="label">
                                <span class="label-text">Item Name</span>
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

                        <!-- POS Name -->
                        <div class="space-y-2">
                            <label for="pos_name" class="label">
                                <span class="label-text">POS Name</span>
                            </label>
                            <input
                                type="text"
                                id="pos_name"
                                v-model="form.pos_name"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.pos_name }"
                                required
                            />
                            <p v-if="form.errors.pos_name" class="text-error">{{ form.errors.pos_name }}</p>
                        </div>

                        <!-- Image Upload -->
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

                        <!-- Images Upload -->
                        <div class="space-y-2">
                            <label for="images" class="label">
                                <span class="label-text">Additional Images</span>
                            </label>
                            <input
                                type="file"
                                id="images"
                                @change="(event) => (form.images = Array.from(event.target.files))"
                                class="file-input file-input-bordered w-full"
                                :class="{ 'input-error': form.errors.images }"
                                accept="image/*"
                                multiple
                            />
                            <p v-if="form.errors.images" class="text-error">{{ form.errors.images }}</p>
                        </div>

                        <!-- Category -->
                        <div class="space-y-2">
                            <label for="category" class="label">
                                <span class="label-text">Category</span>
                            </label>
                            <select
                                id="category"
                                v-model="form.category"
                                class="select select-bordered w-full"
                                :class="{ 'input-error': form.errors.category }"
                            >
                                <option disabled selected value="">Select a Category</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                            </select>
                            <p v-if="form.errors.category" class="text-error">{{ form.errors.category }}</p>
                        </div>

                        <!-- Brand -->
                        <div class="space-y-2">
                            <label for="brand" class="label">
                                <span class="label-text">Brand</span>
                            </label>
                            <select
                                id="brand"
                                v-model="form.brand"
                                class="select select-bordered w-full"
                                :class="{ 'input-error': form.errors.brand }"
                            >
                                <option disabled selected value="">Select a brand</option>
                                <option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
                            </select>
                            <p v-if="form.errors.brand" class="text-error">{{ form.errors.brand }}</p>
                        </div>

                        <!-- Cost -->
                        <div class="space-y-2">
                            <label for="cost" class="label">
                                <span class="label-text">Cost</span>
                            </label>
                            <input
                                type="number"
                                id="cost"
                                v-model="form.cost"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.cost }"
                                required
                            />
                            <p v-if="form.errors.cost" class="text-error">{{ form.errors.cost }}</p>
                        </div>

                        <!-- Price -->
                        <div class="space-y-2">
                            <label for="price" class="label">
                                <span class="label-text">Price</span>
                            </label>
                            <input
                                type="number"
                                id="price"
                                v-model="form.price"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.price }"
                                required
                            />
                            <p v-if="form.errors.price" class="text-error">{{ form.errors.price }}</p>
                        </div>

                        <!-- Selling Price -->
                        <div class="space-y-2">
                            <label for="selling_price" class="label">
                                <span class="label-text">Selling Price</span>
                            </label>
                            <input
                                type="number"
                                id="selling_price"
                                v-model="form.selling_price"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.selling_price }"
                                required
                            />
                            <p v-if="form.errors.selling_price" class="text-error">{{ form.errors.selling_price }}</p>
                        </div>

                        <!-- Description -->
                        <div class="space-y-2">
                            <label for="description" class="label">
                                <span class="label-text">Description</span>
                            </label>
                            <textarea
                                v-model="form.description"
                                class="textarea textarea-bordered w-full"
                                :class="{ 'textarea-error': form.errors.description }"
                                placeholder="Item Description"
                            ></textarea>
                            <p v-if="form.errors.description" class="text-error">{{ form.errors.description }}</p>
                        </div>

                        <!-- POS Description -->
                        <div class="space-y-2">
                            <label for="pos_description" class="label">
                                <span class="label-text">POS Description</span>
                            </label>
                            <textarea
                                v-model="form.pos_description"
                                class="textarea textarea-bordered w-full"
                                :class="{ 'textarea-error': form.errors.pos_description }"
                                placeholder="POS Description"
                            ></textarea>
                            <p v-if="form.errors.pos_description" class="text-error">{{ form.errors.pos_description }}</p>
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
