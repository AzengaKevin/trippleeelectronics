<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps({
    settings: {
        type: Object,
        required: true,
    },
    paymentMethods: {
        type: Array,
        default: () => [],
    },
});
const form = useForm({
    group: 'payment',
    mpesa_payment_method: props.settings?.mpesa_payment_method,
    cash_payment_method: props.settings?.cash_payment_method,
});

watch(
    () => props.settings,
    (newSettings) => {
        form.mpesa_payment_method = newSettings.mpesa_payment_method;
        form.cash_payment_method = newSettings.cash_payment_method;
    },
);
const updateSettings = () => {
    form.patch(route('backoffice.settings.update'));
};
</script>
<template>
    <form @submit.prevent="updateSettings" class="card bg-base-100 shadow">
        <div class="card-body space-y-4">
            <div class="space-y-2">
                <label for="receipt-footer" class="label">
                    <span class="label-text">Mpesa Payment Method</span>
                </label>
                <select id="mpesa-payment-method" v-model="form.mpesa_payment_method" class="select select-bordered w-full" required>
                    <option :value="null" disabled>Select Mpesa Payment Method</option>
                    <option v-for="method in paymentMethods" :key="method.value" :value="method.value">
                        {{ method.label }}
                    </option>
                </select>
                <span v-if="form.errors.mpesa_payment_method" class="text-error text-sm">
                    {{ form.errors.mpesa_payment_method }}
                </span>
            </div>
            <div class="space-y-2">
                <label for="cash-payment-method" class="label">
                    <span class="label-text">Cash Payment Method</span>
                </label>
                <select id="cash-payment-method" v-model="form.cash_payment_method" class="select select-bordered w-full" required>
                    <option :value="null" disabled>Select Cash Payment Method</option>
                    <option v-for="method in paymentMethods" :key="method.value" :value="method.value">
                        {{ method.label }}
                    </option>
                </select>
                <span v-if="form.errors.cash_payment_method" class="text-error text-sm">
                    {{ form.errors.cash_payment_method }}
                </span>
            </div>
            <div class="card-actions">
                <button type="submit" class="btn btn-primary">Update Settings</button>
            </div>
        </div>
    </form>
</template>
