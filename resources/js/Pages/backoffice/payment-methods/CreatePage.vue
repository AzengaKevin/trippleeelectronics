<script setup>
import AppAlert from '@/components/AppAlert.vue';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    feedback: {
        type: Object,
        default: null,
    },
    fields: {
        type: Array,
        required: true,
    },
    statuses: {
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
        title: 'Payment Methods',
        href: route('backoffice.payment-methods.index'),
    },
    {
        title: 'Create',
        href: null,
    },
];

const form = useForm({
    name: '',
    description: '',
    required_fields: [],
    phone_number: '',
    paybill_number: '',
    account_number: '',
    till_number: '',
    account_name: '',
    default_payment_status: '',
});

const createPaymentMethod = () => {
    form.post(route('backoffice.payment-methods.store'), {
        onSuccess: () => form.reset(),
        onError: (errors) => console.error(errors),
    });
};
</script>

<template>
    <Head>
        <title>Create Payment Methods</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Create Payment Methods">
        <div class="space-y-4">
            <AppAlert :feedback="feedback" />

            <div class="card bg-base-100 shadow">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <form @submit.prevent="createPaymentMethod" class="grid grid-cols-1 gap-4" novalidate>
                        <div class="space-y-2">
                            <label for="name" class="label">
                                <span class="label-text">Name</span>
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                id="name"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.name }"
                                placeholder="Enter payment method name"
                                required
                            />
                            <p v-if="form.errors.name" class="text-error mt-1 text-sm">
                                {{ form.errors.name }}
                            </p>
                        </div>
                        <div class="space-y-2">
                            <label for="description" class="label">
                                <span class="label-text">Description</span>
                            </label>

                            <textarea
                                v-model="form.description"
                                id="description"
                                class="textarea textarea-bordered w-full"
                                :class="{ 'textarea-error': form.errors.description }"
                                placeholder="Enter payment method description"
                                required
                            ></textarea>
                            <p v-if="form.errors.description" class="text-error mt-1 text-sm">
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <fieldset class="space-y-2">
                            <legend class="label">
                                <span class="label-text">Required Fields</span>
                            </legend>

                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                <label v-for="field in fields" :key="field.value" class="flex gap-2">
                                    <input type="checkbox" class="checkbox checkbox-primary" v-model="form.required_fields" :value="field.value" />
                                    <span class="label-text">{{ field.label }}</span>
                                </label>
                            </div>

                            <p v-if="form.errors.required_fields" class="text-error mt-1 text-sm">
                                {{ form.errors.required_fields }}
                            </p>
                        </fieldset>

                        <template v-for="field in fields">
                            <div v-if="form.required_fields.includes(field.value)" :key="field.value" class="space-y-2">
                                <label :for="field.value" class="label">
                                    <span class="label-text">Default {{ field.label }}</span>
                                </label>
                                <input type="text" :id="field.value" v-model="form[field.value]" class="input input-bordered w-full" />

                                <p v-if="form.errors?.[field.value]" class="text-error mt-1 text-sm">
                                    {{ form.errors?.[field.value] }}
                                </p>
                            </div>
                        </template>

                        <div class="space-y-2">
                            <label for="default-payment-status" class="label">
                                <span class="label-text">Default Payment Status</span>
                            </label>
                            <select v-model="form.default_payment_status" id="dfault-payment-status" class="select select-borderd w-full">
                                <option value="">Select Status</option>
                                <option v-for="status in statuses" :key="status.value" :value="status.value">{{ status.label }}</option>
                            </select>
                            <p v-if="form.errors.default_payment_status" class="text-error mt-1 text-sm">
                                {{ form.errors.default_payment_status }}
                            </p>
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
