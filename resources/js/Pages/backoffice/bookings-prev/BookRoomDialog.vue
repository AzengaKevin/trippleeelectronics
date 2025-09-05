<script setup>
import AppCombobox from '@/components/AppCombobox.vue';
import useClients from '@/composables/useClients';
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    currentBooking: {
        type: Object,
        required: false,
    },
});

const form = useForm({
    room: null,
    file: null,
    client: null,
});

const room = ref(null);

const { loadClients } = useClients();

const submit = () => {};

const newBookingDialog = ref(null);

watch(
    () => props.currentBooking,
    (newBooking) => {
        if (newBooking) {
            form.room = newBooking.id;
        }
    },
);
</script>
<template>
    <dialog ref="newBookingDialog" id="newBookingDialog" class="modal">
        <form @submit.prevent="submit" class="modal-box max-h-[90vh] w-11/12 max-w-5xl space-y-3">
            <h3 class="text-lg font-bold">Book {{ currentBooking?.name }}</h3>

            <div class="space-y-1">
                <app-combobox
                    v-model="form.client"
                    label="Client Name"
                    :load-options="loadClients"
                    placeholder="Select Client"
                    default-type="individual"
                />
            </div>

            <div class="space-y-2">
                <label for="date" class="label">
                    <span class="label-text">Date</span>
                </label>
                <input type="date" id="date" class="input input-bordered w-full" v-model="form.date" />
                <p v-if="form.errors.date" class="text-error text-sm">
                    {{ form.errors.date }}
                </p>
            </div>

            <div class="modal-action">
                <button type="button" @click="newBookingDialog.close()" class="btn">Nevermind</button>
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </dialog>
</template>
