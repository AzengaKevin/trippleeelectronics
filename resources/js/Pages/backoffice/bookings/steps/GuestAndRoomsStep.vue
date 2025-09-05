<script setup>
import AppCombobox from '@/components/AppCombobox.vue';
import { ErrorMessage, Field, FieldArray, Form } from 'vee-validate';
import * as yup from 'yup';

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
});

const emit = defineEmits(['updateState']);

const guestRoomBlueprint = {
    client: null,
    room: null,
    email: '',
    phone: '',
    address: '',
    kra_pin: '',
    id_number: '',
    name: '',
};

const initialValues = {
    guest_rooms: [guestRoomBlueprint],
};

const schema = yup.object().shape({
    guest_rooms: yup
        .array()
        .min(1, 'At least one guest room is required')
        .of(
            yup.object().shape({
                client: yup.object().nullable().required('Client is required'),
                room: yup.object().nullable().required('Room is required'),
                email: yup.string().email('Invalid email format').required('Email is required'),
                phone: yup.string().required('Phone is required'),
                address: yup.string().required('Address is required'),
                kra_pin: yup.string(),
                id_number: yup.string().required('ID Number is required'),
                name: yup.string().required('Name is required'),
            }),
        ),
});

const submit = (values) => {
    emit('updateState', values);

    props.nextStep();
};
</script>
<template>
    <Form @submit="submit" v-slot="{ values, errors }" :validation-schema="schema" :initial-values="initialValues">
        <div class="card bg-base-100">
            <div class="card-body">
                <table class="table table-xs">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Client</th>
                            <th>Room</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>KRA PIN</th>
                            <th>ID Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        <FieldArray name="guest_rooms" v-slot="{ fields, push, remove }">
                            <tr v-for="(field, index) in fields" :key="index">
                                <td>{{ index + 1 }}</td>
                                <td>
                                    <Field :name="`guest_rooms[${index}].client`" v-slot="{ field, meta }">
                                        <AppCombobox v-bind="field" size="xs"
                                            :class="['form-select', 'form-select-sm', { 'is-invalid': meta.touched && meta.invalid }]" />
                                    </Field>
                                    <ErrorMessage class="invalid-feedback" :name="`guest_rooms[${index}].client`" />
                                </td>
                                <td>
                                    <Field type="text" :id="`guest_rooms-${index}-name`"
                                        :name="`guest_rooms[${index}].name`"
                                        class="input input-xs input-bordered w-full"
                                        :class="{ 'is-invalid': errors[`guest_rooms[${index}].name`] }" />
                                    <ErrorMessage class="invalid-feedback" :name="`guest_rooms[${index}].name`" />
                                </td>
                                <td>
                                    <Field type="email" :id="`guest_rooms-${index}-email`"
                                        :name="`guest_rooms[${index}].email`"
                                        class="input input-xs input-bordered w-full" :class="{
                                            'is-invalid': errors[`guest_rooms[${index}].email`],
                                        }" />
                                    <ErrorMessage class="invalid-feedback" :name="`guest_rooms[${index}
].email`" />
                                </td>
                                <td>
                                    <Field type="text" :id="`guest_rooms-${index}-phone`"
                                        :name="`guest_rooms[${index}].phone`"
                                        class="input input-xs input-bordered w-full"
                                        :class="{ 'is-invalid': errors[`guest_rooms[${index}].phone`] }" />
                                    <ErrorMessage class="invalid-feedback" :name="`guest_rooms[${index}].phone`" />
                                </td>
                                <td>
                                    <Field type="text" :id="`guest_rooms-${index}-address`"
                                        :name="`guest_rooms[${index}].address`"
                                        class="input input-xs input-bordered w-full"
                                        :class="{ 'is-invalid': errors[`guest_rooms[${index}].address`] }" />
                                    <ErrorMessage class="invalid-feedback" :name="`guest_rooms[${index}].address`" />
                                </td>
                                <td>
                                    <Field type="text" :id="`guest_rooms-${index}-kra_pin`"
                                        :name="`guest_rooms[${index}].kra_pin`"
                                        class="input input-xs input-bordered w-full"
                                        :class="{ 'is-invalid': errors[`guest_rooms[${index}].kra_pin`] }" />
                                    <ErrorMessage class="invalid-feedback" :name="`guest_rooms[${index}].kra_pin`" />
                                </td>
                                <td>
                                    <Field type="text" :id="`guest_rooms-${index}-id_number`"
                                        :name="`guest_rooms[${index}].id_number`"
                                        class="input input-xs input-bordered w-full"
                                        :class="{ 'is-invalid': errors[`guest_rooms[${index}].id_number`] }" />
                                    <ErrorMessage class="invalid-feedback" :name="`guest_rooms[${index}].id_number`" />
                                </td>
                                <td>
                                    <button type="button" class="btn btn-xs btn-danger"
                                        @click="values.guest_rooms.length > 1 ? values.guest_rooms.splice(index, 1) : null"
                                        :disabled="values.guest_rooms.length <= 1">
                                        <font-awesome-icon icon="trash" />
                                    </button>
                                </td>
                            </tr>
                        </FieldArray>
                    </tbody>
                </table>
            </div>

            <div class="card-actions justify-end p-6">
                <div class="d-flex gap-3">
                    <button type="button" @click="previousStep" class="btn btn-outline-primary">Previous</button>
                    <button type="submit" class="btn btn-primary">Next</button>
                </div>
            </div>
        </div>
    </Form>
</template>
