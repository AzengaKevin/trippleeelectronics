<script setup>
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    stockLevel: {
        type: Object,
        required: true,
    },
    stores: {
        type: Array,
        required: true,
    },
    items: {
        type: Array,
        required: true,
    },
    stockableTypes: {
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
        title: 'Stock Levels',
        href: route('backoffice.stock-levels.index'),
    },
    {
        title: `Details`,
        href: route('backoffice.stock-levels.show', [props.stockLevel.id]),
    },
    {
        title: 'Edit',
        href: null,
    },
];

const form = useForm({
    item_id: props.stockLevel.stockable_type === 'item' ? props.stockLevel.stockable_id : props.stockLevel.stockable?.item_id,
    item_variant_id: props.stockLevel.stockable_type === 'item-variant' ? props.stockLevel.stockable_id : '',
    stockable_type: props.stockLevel.stockable_type,
    quantity: props.stockLevel.quantity,
    store: props.stockLevel.store_id,
});

const variants = computed(() => {
    return props.items.find((item) => item.id === form.item_id)?.variants || [];
});

const updateStockLevel = () => {
    form.transform((data) => {
        if (data.item_variant_id) {
            data.stockable_type = 'item-variant';
            data.stockable_id = data.item_variant_id;
        } else {
            data.stockable_type = 'item';
            data.stockable_id = data.item_id;
        }

        return data;
    }).patch(route('backoffice.stock-levels.update', [props.stockLevel.id]), {
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
        <title>Edit Stock Level</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Edit Stock Level">
        <div class="space-y-4">
            <div class="card bg-base-100 shadow">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <form @submit.prevent="updateStockLevel" class="grid grid-cols-1 gap-4" novalidate>
                        <!-- Item -->
                        <div class="space-y-2">
                            <label for="item" class="label">
                                <span class="label-text">Item</span>
                            </label>
                            <select
                                id="item"
                                v-model="form.item_id"
                                class="select select-bordered w-full"
                                :class="{ 'input-error': form.errors.item_id }"
                            >
                                <option disabled selected value="">Select an Item</option>
                                <option v-for="item in items" :key="item.id" :value="item.id">{{ item.name }}</option>
                            </select>
                            <p v-if="form.errors.item_id" class="text-error">{{ form.errors.item_id }}</p>
                        </div>

                        <div class="hidden">
                            <input type="text" id="stockable_type" v-model="form.stockable_type" />
                        </div>

                        <!-- Item Variant -->
                        <div v-if="variants?.length" class="space-y-2">
                            <label for="variant" class="label">
                                <span class="label-text">Variant</span>
                            </label>

                            <select
                                id="variant"
                                v-model="form.item_variant_id"
                                class="select select-bordered w-full"
                                :class="{ 'input-error': form.errors.item_variant_id }"
                            >
                                <option disabled selected value="">Select a Variant</option>
                                <option v-for="variant in variants" :key="variant.id" :value="variant.id">{{ variant.name }}</option>
                            </select>

                            <p v-if="form.errors.item_variant_id" class="text-error">{{ form.errors.item_variant_id }}</p>
                        </div>

                        <!-- Store -->
                        <div class="space-y-2">
                            <label for="store" class="label">
                                <span class="label-text">Store</span>
                            </label>
                            <select
                                id="store"
                                v-model="form.store"
                                class="select select-bordered w-full"
                                :class="{ 'input-error': form.errors.store }"
                            >
                                <option disabled selected value="">Select a Store</option>
                                <option v-for="store in stores" :key="store.id" :value="store.id">{{ store.name }}</option>
                            </select>
                            <p v-if="form.errors.store" class="text-error">{{ form.errors.store }}</p>
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

                        <div>
                            <button type="submit" class="btn btn-info" :disabled="form.processing">
                                <span v-if="form.processing" class="loading loading-spinner loading-md"></span>
                                <span>Update Stock Level</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
