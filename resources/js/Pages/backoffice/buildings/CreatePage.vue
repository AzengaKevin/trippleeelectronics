<script setup>
import useSwal from '@/composables/useSwal';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps({
    properties: {
        type: Array,
        required: true,
    },
    feedback: {
        type: Object,
        default: null,
    },
});

const breadcrumbs = [
    { title: 'Dashboard', href: route('backoffice.dashboard') },
    { title: 'Buildings', href: route('backoffice.buildings.index') },
    { title: 'Create', href: null },
];

const form = useForm({
    property: '',
    name: '',
    code: '',
    active: false,
});

const { showFeedbackSwal, showInertiaErrorsSwal } = useSwal();

const submitForm = () => {
    form.post(route('backoffice.buildings.store'), {
        onError: (errors) => {
            showInertiaErrorsSwal(errors);
        },
    });
};

watch(
    () => props.feedback,
    (newFeedback) => {
        if (newFeedback) {
            showFeedbackSwal(newFeedback);
        }
    },
    {
        immediate: true,
    },
);
</script>
<template>
    <Head>
        <title>Create Building</title>
    </Head>
    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Create Building">
        <div class="card bg-blue-100 shadow">
            <div class="card-body">
                <form @submit.prevent="submitForm" class="space-y-3">
                    <div class="space-y-2">
                        <label for="property" class="label">
                            <span class="label-text">Property</span>
                        </label>
                        <select v-model="form.property" id="property" class="select select-bordered w-full">
                            <option value="">Select a property</option>
                            <option v-for="property in props.properties" :key="property.id" :value="property.id">
                                {{ property.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.property" class="text-error">{{ form.errors.property }}</p>
                    </div>
                    <div class="space-y-2">
                        <label for="name" class="label">
                            <span class="label-text">Name</span>
                        </label>
                        <input v-model="form.name" type="text" id="name" class="input input-bordered w-full" />
                        <p v-if="form.errors.name" class="text-error">{{ form.errors.name }}</p>
                    </div>
                    <div class="space-y-2">
                        <label for="code" class="label">
                            <span class="label-text">Code</span>
                        </label>
                        <input v-model="form.code" type="text" id="code" class="input input-bordered w-full" />
                        <p v-if="form.errors.code" class="text-error">{{ form.errors.code }}</p>
                    </div>
                    <div class="flex gap-2">
                        <input v-model="form.active" :value="true" type="checkbox" id="active" class="checkbox checkbox-primary" />
                        <label for="active" class="label">
                            <span class="label-text">Active</span>
                        </label>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary" :disabled="form.processing">
                            <span class="loading" v-if="form.processing"></span>
                            Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </BackofficeLayout>
</template>
