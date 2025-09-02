<script setup>
import useSwal from '@/composables/useSwal';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps({
    property: {
        type: Object,
        required: true,
    },
    feedback: {
        type: Object,
        default: null,
    },
});

const breadcrumbs = [
    { title: 'Dashboard', href: route('backoffice.dashboard') },
    { title: 'Properties', href: route('backoffice.properties.index') },
    { title: props.property.name, href: route('backoffice.properties.show', props.property.id) },
    { title: 'Edit', href: null },
];

const form = useForm({
    name: props.property.name,
    code: props.property.code,
    address: props.property.address,
    active: props.property.active,
});

const { showFeedbackSwal, showInertiaErrorsSwal } = useSwal();

const submitForm = () => {
    form.put(route('backoffice.properties.update', props.property.id), {
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
        <title>Edit Property</title>
    </Head>
    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Edit Property">
        <div class="card bg-blue-100 shadow">
            <div class="card-body">
                <form @submit.prevent="submitForm" class="space-y-3">
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
                    <div class="space-y-2">
                        <label for="address" class="label">
                            <span class="label-text">Address</span>
                        </label>
                        <input v-model="form.address" type="text" id="address" class="input input-bordered w-full" />
                        <p v-if="form.errors.address" class="text-error">{{ form.errors.address }}</p>
                    </div>
                    <div class="flex gap-2">
                        <input v-model="form.active" :value="true" type="checkbox" id="active" class="checkbox checkbox-primary" />
                        <label for="active" class="label">
                            <span class="label-text">Active</span>
                        </label>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-info" :disabled="form.processing">
                            <span class="loading" v-if="form.processing"></span>
                            Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </BackofficeLayout>
</template>
