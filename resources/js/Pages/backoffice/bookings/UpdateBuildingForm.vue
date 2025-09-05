<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    buildings: {
        type: Array,
        required: true,
    },
    building: {
        type: Object,
        required: false,
    },
});

const emit = defineEmits(['closeDialog']);

const form = useForm({
    building: props.building ? props.building.id : '',
});

const submit = () => {
    form.post(route('backoffice.booking.building.update'), {
        onSuccess: () => {
            emit('closeDialog');
        },
        onError: (errors) => {
            console.error(errors);
        },
    });
};
</script>
<template>
    <form @submit.prevent="submit" class="modal-box">
        <h3 class="text-lg font-bold">Select Building</h3>
        <div class="space-y-4 py-4">
            <div class="space-y-2">
                <label for="building" class="label">
                    <span class="label-text font-medium">Building</span>
                </label>
                <select id="building" v-model="form.building" class="input input-bordered w-full" required>
                    <option value="">Select Building</option>
                    <option v-for="building in buildings" :value="building.id">{{ building.name }}</option>
                </select>
                <span v-if="form.errors?.building" class="text-error">
                    {{ form.errors.building }}
                </span>
            </div>
        </div>
        <div class="modal-action">
            <button type="button" @click="$emit('closeDialog')" class="btn btn-outline rounded-full">Close</button>
            <button type="submit" class="btn btn-primary rounded-full" :disabled="form.processing">
                <span v-if="form.processing" class="loading loading-spinner"></span>
                Submit
            </button>
        </div>
    </form>
</template>
