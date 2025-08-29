<script setup>
import AppCombobox from '@/components/AppCombobox.vue';
import useClients from '@/composables/useClients';

import * as yup from 'yup';

import { useField, useForm } from 'vee-validate';
import { watch } from 'vue';

const props = defineProps({
    client: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['clientUpdated', 'closeDialog']);

const { loadClients, processFormClient } = useClients();

const initialValues = processFormClient(props.client || {});

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

const onSubmit = handleSubmit((values) => {
    emit('clientUpdated', {
        client: values,
    });
});
</script>
<template>
    <form @submit.prevent="onSubmit" class="modal-box w-11/12 max-w-3xl space-y-4" novalidate>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h3 class="text-2xl font-bold underline underline-offset-8">Client Details</h3>

            <button class="btn btn-ghost btn-circle" type="button" @click="$emit('closeDialog')">
                <font-awesome-icon icon="times" size="lg" />
            </button>
        </div>

        <div class="grid grid-cols-1 gap-2">
            <div class="space-y-1">
                <app-combobox
                    v-model="client"
                    label="Client Name"
                    :load-options="loadClients"
                    placeholder="Select Client"
                    size="sm"
                    default-type="individual"
                />
                <span v-if="clientError" class="text-error text-xs">{{ clientError }}</span>
            </div>
            <div class="space-y-1">
                <label class="label"><span class="label-text text-xs">Client Email</span></label>
                <input v-model="email" type="email" class="input input-sm input-bordered w-full" required />
                <span v-if="emailError" class="text-error text-xs">{{ emailError }}</span>
            </div>
            <div class="space-y-1">
                <label class="label"><span class="label-text text-xs">Client Phone</span></label>
                <input v-model="phone" type="text" class="input input-sm input-bordered w-full" required />
                <span v-if="phoneError" class="text-error text-xs">{{ phoneError }}</span>
            </div>
            <div class="space-y-1">
                <label class="label"><span class="label-text text-xs">Client Address</span></label>
                <input v-model="address" type="text" class="input input-sm input-bordered w-full" />
                <span v-if="addressError" class="text-error text-xs">{{ addressError }}</span>
            </div>
            <div class="space-y-1">
                <label class="label"><span class="label-text text-xs">Client KRA PIN</span></label>
                <input v-model="kraPin" type="text" class="input input-sm input-bordered w-full" />
                <span v-if="kraPinError" class="text-error text-xs">{{ kraPinError }}</span>
            </div>
        </div>
        <div class="flew-wrap flex justify-end gap-2">
            <button class="btn btn-primary btn-outline" @click="$emit('closeDialog')" type="button">
                <font-awesome-icon icon="times" />
                Nevemind
            </button>
            <button class="btn btn-primary btn-outline" type="submit">
                <font-awesome-icon icon="save" />
                save
            </button>
        </div>
    </form>
</template>
