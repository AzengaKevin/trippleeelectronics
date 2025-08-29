<script setup>
import AppCombobox from '@/components/AppCombobox.vue';
import InputError from '@/components/InputError.vue';
import useProducts from '@/composables/useProducts';
import useStockLevels from '@/composables/useStockLevels';
import useSwal from '@/composables/useSwal';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';

import { Head, useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps({
    stores: {
        type: Array,
        required: true,
    },
    stockMovementTypes: {
        type: Array,
        required: true,
    },
    feedback: {
        type: Object,
        default: null,
    },
});

const breadcrumbs = [
    { title: 'Dashboard', href: route('backoffice.dashboard') },
    { title: 'Stock Movements', href: route('backoffice.stock-movements.index') },
    { title: 'Create', href: null },
];

const form = useForm({
    product: null,
    item: null,
    store: '',
    type: '',
    quantity: '',
    description: '',
    cost_implication: '',
});

const createStockMovement = () => {
    form.transform((data) => {
        data.product = data.item?.value;
        return data;
    }).post(route('backoffice.stock-movements.store'), {
        onSuccess: () => form.reset(),
        onError: (errors) => console.error(errors),
    });
};

const { stockLevels, stockLevelsFormatted, fetchProductStockLevels } = useStockLevels();

const { loadProducts } = useProducts();

const { showFeedbackSwal } = useSwal();

watch(
    () => props.feedback,
    (newFeedback) => {
        if (newFeedback) {
            showFeedbackSwal(newFeedback);
        }
    },
    {
        immediate: true,
    },
);

watch(
    () => form.item,
    (newItem) => {
        if (newItem) {
            fetchProductStockLevels(newItem.value);
        }
    },
);
</script>

<template>
    <Head>
        <title>Create Stock Movement</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Create Stock Movement">
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <form @submit.prevent="createStockMovement" class="grid grid-cols-1 gap-2" novalidate>
                    <!-- Item -->
                    <div class="space-y-2">
                        <AppCombobox
                            v-model="form.item"
                            :label="'Product'"
                            :placeholder="'Select an item...'"
                            :load-options="loadProducts"
                            :with-custom-option="false"
                        />
                        <InputError :message="form.errors.product" />
                        <output v-if="stockLevels.length && form.item">{{ stockLevelsFormatted }}</output>
                    </div>

                    <!-- Store -->
                    <div class="space-y-2">
                        <label class="label"><span class="label-text">Store</span></label>
                        <select v-model="form.store" class="select select-bordered w-full" :class="{ 'input-error': form.errors.store }">
                            <option disabled value="">Select a Store</option>
                            <option v-for="store in stores" :key="store.id" :value="store.id">{{ store.name }}</option>
                        </select>
                        <p v-if="form.errors.store" class="text-error">{{ form.errors.store }}</p>
                    </div>

                    <!-- Type -->
                    <div class="space-y-2">
                        <label class="label"><span class="label-text">Movement Type</span></label>
                        <select v-model="form.type" class="select select-bordered w-full">
                            <option disabled value="">Select Movement Type</option>
                            <option v-for="type in stockMovementTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
                        </select>
                        <p v-if="form.errors.type" class="text-error">{{ form.errors.type }}</p>
                    </div>

                    <!-- Quantity -->
                    <div class="space-y-2">
                        <label class="label"><span class="label-text">Quantity</span></label>
                        <input
                            v-model="form.quantity"
                            type="number"
                            class="input input-bordered w-full"
                            :class="{ 'input-error': form.errors.quantity }"
                        />
                        <p v-if="form.errors.quantity" class="text-error">{{ form.errors.quantity }}</p>
                    </div>

                    <!-- Description -->
                    <div class="space-y-2">
                        <label class="label"><span class="label-text">Description</span></label>
                        <textarea
                            v-model="form.description"
                            class="textarea textarea-bordered w-full"
                            rows="3"
                            :class="{ 'input-error': form.errors.description }"
                        ></textarea>
                        <p v-if="form.errors.description" class="text-error">{{ form.errors.description }}</p>
                    </div>

                    <!-- Cost Implication -->
                    <div class="space-y-2">
                        <label class="label"><span class="label-text">Cost Implication</span></label>
                        <input
                            v-model="form.cost_implication"
                            type="number"
                            step="0.01"
                            class="input input-bordered w-full"
                            :class="{ 'input-error': form.errors.cost_implication }"
                        />
                        <p v-if="form.errors.cost_implication" class="text-error">{{ form.errors.cost_implication }}</p>
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
    </BackofficeLayout>
</template>
