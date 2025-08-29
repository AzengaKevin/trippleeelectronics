<script setup>
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    items: {
        type: Array,
        required: true,
    },
    feedback: {
        type: Object,
        default: null,
    },
    item: {
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
        title: 'Item Variants',
        href: route('backoffice.item-variants.index'),
    },
    {
        title: 'Create',
        href: null,
    },
];

const form = useForm({
    item: props.item?.id ?? '',
    name: '',
    pos_name: '',
    image: null,
    attribute: '',
    value: '',
    cost: '',
    price: '',
    quantity: '',
    description: '',
    pos_description: '',
});

const createItemVariant = () => {
    form.post(route('backoffice.item-variants.store'), {
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
        <title>Create Item Variant</title>
        <meta name="description" content="Create item variant page" />
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Create Item Variant">
        <div class="space-y-4">
            <div class="card bg-base-100 shadow">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <form @submit.prevent="createItemVariant" class="grid grid-cols-1 gap-4" novalidate>
                        <!-- Item -->
                        <div class="space-y-2">
                            <label for="item" class="label">
                                <span class="label-text">Item</span>
                            </label>
                            <select id="item" v-model="form.item" class="select select-bordered w-full" :class="{ 'input-error': form.errors.item }">
                                <option disabled selected value="">Select an Item</option>
                                <option v-for="item in items" :key="item.id" :value="item.id">{{ item.name }}</option>
                            </select>
                            <p v-if="form.errors.item" class="text-error">{{ form.errors.item }}</p>
                        </div>

                        <div class="space-y-2">
                            <label for="attribute" class="label">
                                <span class="label-text">Attribute</span>
                            </label>
                            <input
                                type="text"
                                id="attribute"
                                v-model="form.attribute"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.attribute }"
                                required
                            />
                            <p v-if="form.errors.attribute" class="text-error">{{ form.errors.attribute }}</p>
                        </div>

                        <div class="space-y-2">
                            <label for="value" class="label">
                                <span class="label-text">Value</span>
                            </label>
                            <input
                                type="text"
                                id="value"
                                v-model="form.value"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.value }"
                                required
                            />
                            <p v-if="form.errors.value" class="text-error">{{ form.errors.value }}</p>
                        </div>

                        <div class="space-y-2">
                            <label for="name" class="label">
                                <span class="label-text">Variant Name</span>
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

                        <!-- Quantity -->
                        <div class="space-y-2">
                            <label for="quantity" class="label">
                                <span class="label-text">Quantity</span>
                            </label>
                            <input
                                type="number"
                                id="quantity"
                                v-model="form.quantity"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.quantity }"
                                required
                            />
                            <p v-if="form.errors.quantity" class="text-error">{{ form.errors.quantity }}</p>
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
                                placeholder="Item Variant Description"
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
                                placeholder="POS Item Variant Description"
                            ></textarea>
                            <p v-if="form.errors.pos_description" class="text-error">{{ form.errors.pos_description }}</p>
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
