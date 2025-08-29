<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps({
    settings: {
        type: Object,
        required: true,
    },
});
const form = useForm({
    group: 'receipt',
    receipt_footer: props.settings?.receipt_footer,
    show_receipt_header: props.settings?.show_receipt_header,
    show_receipt_footer: props.settings?.show_receipt_footer,
});

watch(
    () => props.settings,
    (newSettings) => {
        form.receipt_footer = newSettings.receipt_footer;
        form.show_receipt_header = newSettings.show_receipt_header;
        form.show_receipt_footer = newSettings.show_receipt_footer;
    },
);
const updateSettings = () => {
    form.patch(route('backoffice.settings.update'));
};
</script>
<template>
    <form @submit.prevent="updateSettings" class="card bg-base-100 shadow">
        <div class="card-body space-y-4">
            <div class="space-y-2">
                <label for="receipt-footer" class="label">
                    <span class="label-text">Receipt Footer</span>
                </label>
                <textarea
                    id="receipt-footer"
                    v-model="form.receipt_footer"
                    placeholder="Enter receipt footer text"
                    class="textarea textarea-bordered w-full"
                    rows="3"
                    required
                ></textarea>
                <span v-if="form.errors.receipt_footer" class="text-error text-sm">
                    {{ form.errors.receipt_footer }}
                </span>
            </div>
            <div class="space-y-2">
                <div>
                    <label class="flex cursor-pointer items-center gap-2">
                        <input type="checkbox" v-model="form.show_receipt_header" class="checkbox checkbox-primary" />
                        <span class="label-text">Show Receipt Header</span>
                    </label>
                </div>
                <span v-if="form.errors.show_receipt_header" class="text-error text-sm">
                    {{ form.errors.show_receipt_header }}
                </span>
            </div>
            <div class="space-y-2">
                <div>
                    <label class="flex cursor-pointer items-center gap-2">
                        <input type="checkbox" v-model="form.show_receipt_footer" class="checkbox checkbox-primary" />
                        <span class="label-text">Show Receipt Footer</span>
                    </label>
                </div>
                <span v-if="form.errors.show_receipt_footer" class="text-error text-sm">
                    {{ form.errors.show_receipt_footer }}
                </span>
            </div>
            <div class="card-actions justify-end">
                <button type="submit" class="btn btn-primary">Update Settings</button>
            </div>
        </div>
    </form>
</template>
