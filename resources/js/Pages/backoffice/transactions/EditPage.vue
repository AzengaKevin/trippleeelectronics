<script setup>
import AppAlert from '@/components/AppAlert.vue';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    statuses: {
        type: Array,
        required: true,
    },
    methods: {
        type: Array,
        required: true,
    },
    transaction: {
        type: Object,
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
        title: 'Transactions',
        href: route('backoffice.transactions.index'),
    },
    {
        title: `# ${props.transaction.local_reference}`,
        href: route('backoffice.transactions.show', props.transaction.id),
    },
    {
        title: 'Edit',
        href: null,
    },
];

const form = useForm({
    payment: props.payment?.reference,
    transaction_method: props.transaction.transaction_method,
    till: props.transaction.till,
    paybill: props.transaction.paybill,
    account_number: props.transaction.account_number,
    phone_number: props.transaction.phone_number,
    reference: props.transaction.reference,
    status: props.transaction.status,
    amount: props.transaction.amount,
    fee: props.transaction.fee,
});

const updateTransaction = () => {
    form.put(route('backoffice.transactions.update', [props.transaction.id]), {
        onSuccess: () => form.reset(),
        onError: (errors) => console.error(errors),
    });
};
</script>
<template>
    <Head>
        <title>Update Transaction</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Update Transaction">
        <div class="space-y-4">
            <AppAlert :feedback="feedback" />

            <div class="card bg-base-100 shadow">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <form @submit.prevent="updateTransaction" class="space-y-4" novalidate>
                        <div class="space-y-2">
                            <label for="payment" class="label">
                                <span class="label-text">Transaction Method</span>
                            </label>
                            <select
                                v-model="form.transaction_method"
                                id="transaction_method"
                                class="select select-bordered w-full"
                                required
                                :class="{ 'select-error': form.errors.transaction_method }"
                            >
                                <option value="" disabled>Select Transaction Method</option>
                                <option v-for="method in methods" :key="method.value" :value="method.value">
                                    {{ method.label }}
                                </option>
                            </select>
                            <p v-if="form.errors.transaction_method" class="text-error">{{ form.errors.transaction_method }}</p>
                        </div>

                        <div class="space-y-2">
                            <label for="till" class="label">
                                <span class="label-text">Amount</span>
                            </label>
                            <input
                                v-model="form.amount"
                                type="number"
                                id="amount"
                                class="input input-bordered w-full"
                                required
                                :class="{ 'input-error': form.errors.amount }"
                                placeholder="Enter amount"
                            />
                            <p v-if="form.errors.amount" class="text-error">{{ form.errors.amount }}</p>
                        </div>

                        <div class="space-y-2">
                            <label for="fee" class="label">
                                <span class="label-text">Fee</span>
                            </label>
                            <input
                                v-model="form.fee"
                                type="number"
                                id="fee"
                                class="input input-bordered w-full"
                                required
                                :class="{ 'input-error': form.errors.fee }"
                                placeholder="Enter fee"
                            />
                            <p v-if="form.errors.fee" class="text-error">{{ form.errors.fee }}</p>
                        </div>
                        <div class="space-y-2">
                            <label for="status" class="label">
                                <span class="label-text">Status</span>
                            </label>
                            <select
                                v-model="form.status"
                                id="status"
                                class="select select-bordered w-full"
                                required
                                :class="{ 'select-error': form.errors.status }"
                            >
                                <option value="" disabled>Select Status</option>
                                <option v-for="status in statuses" :key="status.value" :value="status.value">
                                    {{ status.label }}
                                </option>
                            </select>
                            <p v-if="form.errors.status" class="text-error">{{ form.errors.status }}</p>
                        </div>
                        <div class="space-y-2">
                            <label for="reference" class="label">
                                <span class="label-text">Reference</span>
                            </label>
                            <input
                                v-model="form.reference"
                                type="text"
                                id="reference"
                                class="input input-bordered w-full"
                                required
                                :class="{ 'input-error': form.errors.reference }"
                                placeholder="Enter reference"
                            />
                            <p v-if="form.errors.reference" class="text-error">{{ form.errors.reference }}</p>
                        </div>

                        <div class="space-y-2">
                            <label for="phone_number" class="label">
                                <span class="label-text">Phone Number</span>
                            </label>
                            <input
                                v-model="form.phone_number"
                                type="text"
                                id="phone_number"
                                class="input input-bordered w-full"
                                required
                                :class="{ 'input-error': form.errors.phone_number }"
                                placeholder="Enter phone number"
                            />
                            <p v-if="form.errors.phone_number" class="text-error">{{ form.errors.phone_number }}</p>
                        </div>
                        <div class="space-y-2">
                            <label for="account_number" class="label">
                                <span class="label-text">Account Number</span>
                            </label>
                            <input
                                v-model="form.account_number"
                                type="text"
                                id="account_number"
                                class="input input-bordered w-full"
                                required
                                :class="{ 'input-error': form.errors.account_number }"
                                placeholder="Enter account number"
                            />
                            <p v-if="form.errors.account_number" class="text-error">{{ form.errors.account_number }}</p>
                        </div>
                        <div class="space-y-2">
                            <label for="paybill" class="label">
                                <span class="label-text">Paybill</span>
                            </label>
                            <input
                                v-model="form.paybill"
                                type="text"
                                id="paybill"
                                class="input input-bordered w-full"
                                required
                                :class="{ 'input-error': form.errors.paybill }"
                                placeholder="Enter paybill"
                            />
                            <p v-if="form.errors.paybill" class="text-error">{{ form.errors.paybill }}</p>
                        </div>
                        <div class="space-y-2">
                            <label for="till" class="label">
                                <span class="label-text">Till</span>
                            </label>
                            <input
                                v-model="form.till"
                                type="text"
                                id="till"
                                class="input input-bordered w-full"
                                required
                                :class="{ 'input-error': form.errors.till }"
                                placeholder="Enter till"
                            />
                            <p v-if="form.errors.till" class="text-error">{{ form.errors.till }}</p>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-info" :disabled="form.processing">
                                <span v-if="form.processing" class="loading loading-spinner loading-md"></span>
                                Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
