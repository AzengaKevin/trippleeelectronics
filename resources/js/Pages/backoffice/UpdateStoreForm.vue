<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    stores: {
        type: Array,
        required: true,
    },
    store: {
        type: Object,
        required: false,
    },
});

const emit = defineEmits(['closeDialog']);

const form = useForm({
    store: props.store ? props.store.id : '',
});

const submit = () => {
    form.post(route('backoffice.pos.store.update'), {
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
        <h3 class="text-lg font-bold">Select Store</h3>
        <div class="space-y-4 py-4">
            <div class="space-y-2">
                <label for="store" class="label">
                    <span class="label-text font-medium">Store</span>
                </label>
                <select id="store" v-model="form.store" class="input input-bordered w-full" required>
                    <option value="">Select Store</option>
                    <option v-for="store in stores" :value="store.id">{{ store.name }}</option>
                </select>
                <span v-if="form.errors?.store" class="text-error">
                    {{ form.errors.store }}
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
