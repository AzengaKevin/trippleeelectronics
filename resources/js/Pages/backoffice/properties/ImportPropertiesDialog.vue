<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    file: null,
});

const submit = () => {
    form.post(route('backoffice.properties.import'), {
        onSuccess: () => {
            form.reset();
            importPropertiesDialog.value.close();
        },
        onError: (errors) => {
            console.error(errors);
        },
    });
};

const importPropertiesDialog = ref(null);
</script>
<template>
    <dialog ref="importPropertiesDialog" id="importPropertiesDialog" class="modal">
        <form @submit.prevent="submit" class="modal-box">
            <h3 class="text-lg font-bold">Import Properties</h3>

            <div class="space-y-2">
                <label for="file" class="label">
                    <span class="label-text">File</span>
                </label>
                <input type="file" id="file" class="file-input file-input-bordered w-full" @input="form.file = $event.target.files[0]" />
                <div class="text-sm">
                    Download the sample import file
                    <a :href="route('backoffice.properties.export', { limit: 1 })" download class="text-primary underline"> here </a>
                </div>
                <p v-if="form.errors.file" class="text-error text-sm">
                    {{ form.errors.file }}
                </p>
            </div>

            <div class="modal-action">
                <button type="button" @click="importPropertiesDialog.close()" class="btn">Nevermind</button>
                <button type="submit" class="btn btn-primary">Import</button>
            </div>
        </form>
    </dialog>
</template>
