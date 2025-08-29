<script setup>
import BaseLayout from '@/layouts/BaseLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    settings: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
    },
    treeCategories: {
        type: Array,
        required: true,
    },
    services: {
        type: Array,
        required: true,
    },
    order: {
        type: Object,
        required: false,
    },
    params: {
        type: Object,
        required: false,
    },
});

const orderResultDialog = ref(null);

const openOrderResultDialog = () => {
    if (orderResultDialog.value) {
        orderResultDialog.value.showModal();
    }
};

const closeOrderResultDialog = () => {
    if (orderResultDialog.value) {
        orderResultDialog.value.close();
    }
};

const form = useForm({
    query: props.params.query || '',
});

const submit = () => {
    form.get(route('track-your-order'), {
        onSuccess: () => {},
        onError: () => {},
    });
};

onMounted(() => {
    if (props.params.query && props.order) {
        openOrderResultDialog();
    }
});
</script>
<template>
    <Head>
        <title>Track Your Order</title>
    </Head>
    <base-layout :auth="auth" :settings="settings" :categories="categories" :services="services" :tree-categories="treeCategories">
        <main class="container mx-auto max-w-6xl space-y-12 px-4 py-12">
            <div class="space-y-3">
                <h1 class="text-primary text-4xl font-bold">Track Your Order</h1>
                <div class="divider divider-primary w-24"></div>
                <p class="text-lg">Your trusted partner for comprehensive IT solutions since 2018</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <label class="input input-lg input-bordered w-full">
                    <span class="label"><font-awesome-icon icon="search" /></span>
                    <input required type="search" v-model="form.query" placeholder="Enter Order Reference Number" />
                </label>

                <template v-if="params.query && !order">
                    <p class="text-error">No order results match "{{ params.query }}"</p>
                </template>

                <button type="submit" class="btn btn-lg btn-primary" :disabled="form.processing">
                    <span class="loading loading-spinner" v-if="form.processing"></span>
                    Search
                </button>
            </form>
        </main>

        <teleport to="body">
            <dialog id="orderResultDialog" ref="orderResultDialog" class="modal">
                <div class="modal-box w-11/12 max-w-5xl space-y-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold">Order Details</h3>
                    </div>

                    <table class="table w-full">
                        <tbody>
                            <tr>
                                <th>Order Status</th>
                                <td>{{ order?.order_status ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Fulfillment Status</th>
                                <td>{{ order?.fulfillment_status ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Payment Status</th>
                                <td>{{ order?.payment_status ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Shipping Status</th>
                                <td>{{ order?.shipping_status ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="modal-action">
                        <form method="dialog">
                            <button class="btn">Close</button>
                        </form>
                    </div>
                </div>
            </dialog>
        </teleport>
    </base-layout>
</template>
