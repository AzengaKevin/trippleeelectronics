<script setup>
import AppAlert from '@/components/AppAlert.vue';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    payment: {
        type: Object,
        required: true,
    },
    statuses: {
        type: Array,
        required: true,
    },
    methods: {
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
        title: 'Payments',
        href: route('backoffice.payments.index'),
    },
    {
        title: `# ${props.payment.id}`,
        href: route('backoffice.payments.show', [props.payment.id]),
    },
    {
        title: 'Edit',
        href: null,
    },
];

const form = useForm({
    payment_method: props.payment.payment_method,
    status: props.payment.status,
    amount: props.payment.amount,
});

const updatePayment = () => {
    form.put(route('backoffice.payments.update', [props.payment.id]), {
        onSuccess: () => form.reset(),
        onError: (errors) => console.error(errors),
    });
};
</script>
<template>
    <Head>
        <title>Edit Payment</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Edit Payment">
        <div class="space-y-4">
            <AppAlert :feedback="feedback" />

            <div class="card bg-base-100 shadow">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <form @submit.prevent="updatePayment" class="space-y-4" novalidate>
                        <div class="space-y-2">
                            <label for="payment_method" class="label">
                                <span class="label-text">Payment Method</span>
                            </label>
                            <select
                                id="payment_method"
                                v-model="form.payment_method"
                                class="select select-bordered w-full"
                                :class="{ 'select-error': form.errors.payment_method }"
                                required
                            >
                                <option value="" disabled>Select a payment method</option>
                                <option v-for="method in methods" :key="method.value" :value="method.value">
                                    {{ method.label }}
                                </option>
                            </select>
                            <p v-if="form.errors.payment_method" class="text-error">{{ form.errors.payment_method }}</p>
                        </div>
                        <div class="space-y-2">
                            <label for="status" class="label">
                                <span class="label-text">Status</span>
                            </label>
                            <select
                                id="status"
                                v-model="form.status"
                                class="select select-bordered w-full"
                                :class="{ 'select-error': form.errors.status }"
                                required
                            >
                                <option value="" disabled>Select a status</option>
                                <option v-for="status in statuses" :key="status.value" :value="status.value">
                                    {{ status.label }}
                                </option>
                            </select>
                            <p v-if="form.errors.status" class="text-error">{{ form.errors.status }}</p>
                        </div>
                        <div class="space-y-2">
                            <label for="amount" class="label">
                                <span class="label-text">Amount</span>
                            </label>
                            <input
                                type="number"
                                id="amount"
                                v-model="form.amount"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.amount }"
                                required
                            />
                            <p v-if="form.errors.amount" class="text-error">{{ form.errors.amount }}</p>
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
