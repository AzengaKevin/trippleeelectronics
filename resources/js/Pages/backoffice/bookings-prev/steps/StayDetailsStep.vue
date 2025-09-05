<script setup>
import { Field, Form } from 'vee-validate';
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

const schema = yup.object().shape({
    checkin_date: yup.string().required('Check-in date is required'),
    checkout_date: yup.string().required('Check-out date is required'),
    adults: yup.number().min(1, 'At least one adult is required').required('Number of adults is required'),
    children: yup.number().min(0).required('Number of children is required'),
    infants: yup.number().min(0).required('Number of infants is required'),
    rooms: yup.number().min(1, 'At least one room is required').required('Number of rooms is required'),
});

const initialValues = {
    checkin_date: props.state?.checkin_date ?? '',
    checkout_date: props.state?.checkout_date ?? '',
    adults: props.state?.adults ?? 1,
    children: props.state?.children ?? 0,
    infants: props.state?.infants ?? 0,
    rooms: props.state?.rooms ?? 1,
};

const emits = defineEmits(['updateState']);

const submit = (values) => {
    emits('updateState', values);

    props.nextStep();
};
</script>
<template>
    <Form @submit="submit" :validation-schema="schema" :initial-values="initialValues" class="card bg-base-100">
        <div class="card-body space-y-3">
            <div class="space-y-2">
                <label for="checkin_date" class="form-label">Check-in Date</label>
                <Field name="checkin_date" as="input" type="date" class="input input-bordered w-full" />
            </div>
            <div class="space-y-2">
                <label for="checkout_date" class="form-label">Check-out Date</label>
                <Field name="checkout_date" as="input" type="date" class="input input-bordered w-full" />
            </div>
            <div class="space-y-2">
                <label for="adults" class="form-label">Adults</label>
                <Field name="adults" as="input" type="number" class="input input-bordered w-full" />
            </div>
            <div class="space-y-2">
                <label for="children" class="form-label">Children</label>
                <Field name="children" as="input" type="number" class="input input-bordered w-full" />
            </div>
            <div class="space-y-2">
                <label for="infants" class="form-label">Infants</label>
                <Field name="infants" as="input" type="number" class="input input-bordered w-full" />
            </div>
            <div class="space-y-2">
                <label for="rooms" class="form-label">Rooms</label>
                <Field name="rooms" as="input" type="number" class="input input-bordered w-full" />
            </div>
        </div>

        <div class="card-actions justify-end p-6">
            <button type="button" @click="previousStep" class="btn btn-outline-primary">Previous</button>
            <button type="button" @click="nextStep" class="btn btn-primary">Next</button>
        </div>
    </Form>
</template>
