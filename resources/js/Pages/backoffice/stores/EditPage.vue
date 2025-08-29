<script setup>
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    store: {
        type: Object,
        required: true,
    },
    feedback: {
        type: Object,
        default: null,
    },
    methods: {
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
        title: 'Stores',
        href: route('backoffice.stores.index'),
    },
    {
        title: props.store.name,
        href: route('backoffice.stores.show', [props.store.id]),
    },
    {
        title: 'Edit',
        href: null,
    },
];

const paymentMethodBlueprint = {
    id: null,
    phone_number: null,
    paybill_number: null,
    account_number: null,
    till_number: null,
    account_name: null,
};

const form = useForm({
    name: props.store.name,
    short_name: props.store.short_name,
    email: props.store.email,
    phone: props.store.phone,
    paybill: props.store.paybill,
    account_number: props.store.account_number,
    till: props.store.till,
    kra_pin: props.store.kra_pin,
    address: props.store.address,
    location: props.store.location,
    payment_methods: props.store.payment_methods.map((method) => ({
        id: method.id,
        phone_number: method.pivot.phone_number || method.phone_number || null,
        paybill_number: method.pivot.paybill_number || method.paybill_number || null,
        account_number: method.pivot.account_number || method.account_number || null,
        till_number: method.pivot.till_number || method.till_number || null,
        account_name: method.pivot.account_name || method.account_name || null,
    })),
});

const addPaymentMethod = () => {
    form.payment_methods.push({ ...paymentMethodBlueprint });
};

const updatePaymentMethod = (index) => {
    const method = form.payment_methods[index];

    if (method.id) {
        const selectedMethod = props.methods.find((m) => m.id === method.id);
        if (selectedMethod) {
            method.phone_number = selectedMethod.phone_number || '';
            method.paybill_number = selectedMethod.paybill_number || '';
            method.account_number = selectedMethod.account_number || '';
            method.till_number = selectedMethod.till_number || '';
            method.account_name = selectedMethod.account_name || '';
        }
    } else {
        method.phone_number = '';
        method.paybill_number = '';
        method.account_number = '';
        method.till_number = '';
        method.account_name = '';
    }
};

const updateStore = () => {
    form.patch(route('backoffice.stores.update', [props.store.id]), {
        onSuccess: () => {
            form.reset();
        },
        onError: (errors) => {
            console.error(errors);
        },
    });
};

const remainingPaymentMethods = computed(() => props.methods.filter((method) => !form.payment_methods.some((pm) => pm.id === method.id)));
</script>

<template>
    <Head>
        <title>Edit Store</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Edit Store">
        <div class="space-y-4">
            <div class="card bg-base-100 shadow">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <form @submit.prevent="updateStore" class="grid grid-cols-1 gap-4" novalidate>
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
                        <div class="space-y-2">
                            <label for="short_name" class="label">
                                <span class="label-text">Short Name</span>
                            </label>
                            <input
                                type="text"
                                id="short_name"
                                v-model="form.short_name"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.short_name }"
                                required
                            />
                            <p v-if="form.errors.short_name" class="text-error">{{ form.errors.short_name }}</p>
                        </div>
                        <div class="space-y-2">
                            <label for="address" class="label">
                                <span class="label-text">Address</span>
                            </label>
                            <input
                                type="text"
                                id="address"
                                v-model="form.address"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.address }"
                                required
                            />
                            <p v-if="form.errors.address" class="text-error">{{ form.errors.address }}</p>
                        </div>
                        <div class="space-y-2">
                            <label for="location" class="label">
                                <span class="label-text">Location</span>
                            </label>
                            <input
                                type="text"
                                id="location"
                                v-model="form.location"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.location }"
                                required
                            />
                            <p v-if="form.errors.location" class="text-error">{{ form.errors.location }}</p>
                        </div>
                        <div class="space-y-2">
                            <label for="email" class="label">
                                <span class="label-text">Email</span>
                            </label>
                            <input
                                type="email"
                                id="email"
                                v-model="form.email"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.email }"
                                required
                            />
                            <p v-if="form.errors.email" class="text-error">{{ form.errors.email }}</p>
                        </div>
                        <div class="space-y-2">
                            <label for="phone" class="label">
                                <span class="label-text">Phone</span>
                            </label>
                            <input
                                type="text"
                                id="phone"
                                v-model="form.phone"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.phone }"
                                required
                            />
                            <p v-if="form.errors.phone" class="text-error">{{ form.errors.phone }}</p>
                        </div>
                        <div class="space-y-2">
                            <label for="paybill" class="label">
                                <span class="label-text">Paybill</span>
                            </label>
                            <input
                                type="text"
                                id="paybill"
                                v-model="form.paybill"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.paybill }"
                                required
                            />
                            <p v-if="form.errors.paybill" class="text-error">{{ form.errors.paybill }}</p>
                        </div>
                        <div class="space-y-2">
                            <label for="account_number" class="label">
                                <span class="label-text">Account Number</span>
                            </label>
                            <input
                                type="text"
                                id="account_number"
                                v-model="form.account_number"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.account_number }"
                                required
                            />
                            <p v-if="form.errors.account_number" class="text-error">{{ form.errors.account_number }}</p>
                        </div>
                        <div class="space-y-2">
                            <label for="till" class="label">
                                <span class="label-text">Till</span>
                            </label>
                            <input
                                type="text"
                                id="till"
                                v-model="form.till"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.till }"
                                required
                            />
                            <p v-if="form.errors.till" class="text-error">{{ form.errors.till }}</p>
                        </div>
                        <div class="space-y-2">
                            <label for="kra_pin" class="label">
                                <span class="label-text">KRA Pin</span>
                            </label>
                            <input
                                type="text"
                                id="kra_pin"
                                v-model="form.kra_pin"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.kra_pin }"
                                required
                            />
                            <p v-if="form.errors.kra_pin" class="text-error">{{ form.errors.kra_pin }}</p>
                        </div>

                        <fieldset class="space-y-2">
                            <legend class="label">
                                <span class="label-text">Store Payment Methods</span>
                            </legend>

                            <div class="table-responsive">
                                <table class="table-sm table">
                                    <thead>
                                        <tr>
                                            <th>Payment Method</th>
                                            <th>Phone Number</th>
                                            <th>Paybill Number</th>
                                            <th>Account Number</th>
                                            <th>Till Number</th>
                                            <th>Account Name</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(method, index) in form.payment_methods" :key="index">
                                            <td>
                                                <select
                                                    v-model="method.id"
                                                    @change="updatePaymentMethod(index)"
                                                    class="select select-bordered w-full"
                                                    :class="{ 'select-error': form.errors?.[`payment_methods.${index}.id`] }"
                                                >
                                                    <option :value="null">Select Payment Method</option>
                                                    <option
                                                        v-for="m in methods"
                                                        :key="m.id"
                                                        :value="m.id"
                                                        :disabled="!remainingPaymentMethods.includes(m)"
                                                    >
                                                        {{ m.name }}
                                                    </option>
                                                </select>
                                            </td>
                                            <td>
                                                <input
                                                    type="text"
                                                    v-model="method.phone_number"
                                                    placeholder="Phone Number"
                                                    class="input input-bordered w-full"
                                                    :class="{ 'input-error': form.errors?.[`payment_methods.${index}.phone_number`] }"
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    type="text"
                                                    v-model="method.paybill_number"
                                                    placeholder="Paybill Number"
                                                    class="input input-bordered w-full"
                                                    :class="{ 'input-error': form.errors?.[`payment_methods.${index}.paybill_number`] }"
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    type="text"
                                                    v-model="method.account_number"
                                                    placeholder="Account Number"
                                                    class="input input-bordered w-full"
                                                    :class="{ 'input-error': form.errors?.[`payment_methods.${index}.account_number`] }"
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    type="text"
                                                    v-model="method.till_number"
                                                    placeholder="Till Number"
                                                    class="input input-bordered w-full"
                                                    :class="{ 'input-error': form.errors?.[`payment_methods.${index}.till_number`] }"
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    type="text"
                                                    v-model="method.account_name"
                                                    placeholder="Account Name"
                                                    class="input input-bordered w-full"
                                                    :class="{ 'input-error': form.errors?.[`payment_methods.${index}.account_name`] }"
                                                />
                                            </td>
                                            <td>
                                                <button
                                                    type="button"
                                                    @click.prevent="form.payment_methods.splice(index, 1)"
                                                    class="btn btn-sm btn-error"
                                                >
                                                    Remove
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="6">
                                                <button type="button" @click.prevent="addPaymentMethod" class="btn btn-primary btn-sm">
                                                    Add Payment Method
                                                </button>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </fieldset>
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
