<script setup>
import AppCombobox from '@/components/AppCombobox.vue';
import useClients from '@/composables/useClients';

import * as yup from 'yup';

import { useField, useForm } from 'vee-validate';
import { watch } from 'vue';

const props = defineProps({
    state: {
        type: Object,
        required: true,
    },
    previousStep: {
        type: Function,
        required: true,
    },
    nextStep: {
        type: Function,
        required: true,
    },
    client: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['updateState']);

const { loadClients } = useClients();

const initialValues = {
    client: props.state.primary_guest.client,
    email: props.state.primary_guest.email,
    phone: props.state.primary_guest.phone,
    address: props.state.primary_guest.address,
    kra_pin: props.state.primary_guest.kra_pin,
    type: props.state.primary_guest.type,
};

const validationSchema = yup.object({
    client: yup.object({
        value: yup.string(),
        label: yup.string(),
    }),
    email: yup.string().email('Invalid email format').nullable(),
    phone: yup.string().required(),
    address: yup.string().nullable(),
    kra_pin: yup.string().nullable(),
    type: yup.string().nullable(),
});

const { handleSubmit } = useForm({
    initialValues,
    validationSchema,
});

const { value: client, errorMessage: clientError } = useField('client');
const { value: email, errorMessage: emailError } = useField('email');
const { value: phone, errorMessage: phoneError } = useField('phone');
const { value: address, errorMessage: addressError } = useField('address');
const { value: kraPin, errorMessage: kraPinError } = useField('kra_pin');
const { value: type, errorMessage: typeError } = useField('type');

watch(
    client,
    (newClient) => {
        email.value = newClient?.email;
        phone.value = newClient?.phone;
        address.value = newClient?.address;
        kraPin.value = newClient?.kra_pin;
        type.value = newClient?.type;
    },
    {
        immediate: true,
    },
);

const submit = handleSubmit((values) => {
    emit('updateState', {
        primary_guest: values,
    });
});
</script>
<template>
    <form @submit.prevent="submit" class="card bg-base-100" novalidate>
        <div class="card-body">
            <div class="grid grid-cols-1 gap-2">
                <div class="space-y-1">
                    <app-combobox
                        v-model="client"
                        label="Client Name"
                        :load-options="loadClients"
                        placeholder="Select Client"
                        default-type="individual"
                    />
                    <span v-if="clientError" class="text-error text-xs">{{ clientError }}</span>
                </div>
                <div class="space-y-1">
                    <label class="label"><span class="label-text text-xs">Client Email</span></label>
                    <input v-model="email" type="email" class="input input-bordered w-full" required />
                    <span v-if="emailError" class="text-error text-xs">{{ emailError }}</span>
                </div>
                <div class="space-y-1">
                    <label class="label"><span class="label-text text-xs">Client Phone</span></label>
                    <input v-model="phone" type="text" class="input input-bordered w-full" required />
                    <span v-if="phoneError" class="text-error text-xs">{{ phoneError }}</span>
                </div>
                <div class="space-y-1">
                    <label class="label"><span class="label-text text-xs">Client Address</span></label>
                    <input v-model="address" type="text" class="input input-bordered w-full" />
                    <span v-if="addressError" class="text-error text-xs">{{ addressError }}</span>
                </div>
                <div class="space-y-1">
                    <label class="label"><span class="label-text text-xs">Client KRA PIN</span></label>
                    <input v-model="kraPin" type="text" class="input input-bordered w-full" />
                    <span v-if="kraPinError" class="text-error text-xs">{{ kraPinError }}</span>
                </div>
                <div class="space-y-1">
                    <label class="label">
                        <input type="checkbox" v-model="type" :value="true" class="checkbox checkbox-primary" />
                        Organization
                    </label>
                    <span v-if="typeError" class="text-error text-xs">{{ typeError }}</span>
                </div>
            </div>
            <hr />
            <div class="card-actions justify-end">
                <button class="btn btn-primary btn-outline" @click="previousStep" type="button">
                    <font-awesome-icon icon="chevron-left" />
                    Previous
                </button>
                <button class="btn btn-primary btn-outline" @click="nextStep" type="submit">
                    <font-awesome-icon icon="chevron-right" />
                    Next
                </button>
            </div>
        </div>
    </form>
</template>
