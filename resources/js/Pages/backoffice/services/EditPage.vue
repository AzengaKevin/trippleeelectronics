<script setup>
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    service: {
        type: Object,
        required: true,
    },
    feedback: {
        type: Object,
        default: null,
    },
});

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: route('backoffice.dashboard'),
    },
    {
        title: 'Services',
        href: route('backoffice.services.index'),
    },
    {
        title: props.service.title,
        href: route('backoffice.services.show', [props.service.id]),
    },
    {
        title: 'Edit',
        href: null,
    },
];

const form = useForm({
    title: props.service.title,
    description: props.service.description,
    image: null,
});

const updateService = () => {
    form.transform((data) => ({ ...data, _method: 'patch' })).post(route('backoffice.services.update', [props.service.id]), {
        onSuccess: () => {
            form.reset();
        },
        onError: (errors) => {
            console.error(errors);
        },
    });
};
</script>

<template>
    <Head>
        <title>Edit Service</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Edit Service">
        <div class="space-y-4">
            <div class="card bg-base-100 shadow">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <form @submit.prevent="updateService" class="grid grid-cols-1 gap-4" novalidate>
                        <div class="space-y-2">
                            <label for="title" class="label">
                                <span class="label-text">Title</span>
                            </label>
                            <input
                                type="text"
                                id="title"
                                v-model="form.title"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.title }"
                                required
                            />
                            <p v-if="form.errors.title" class="text-error">{{ form.errors.title }}</p>
                        </div>
                        <div class="space-y-2">
                            <label for="desription" class="label">
                                <span class="label-text">Description</span>
                            </label>
                            <textarea
                                id="desription"
                                v-model="form.description"
                                class="textarea textarea-bordered w-full"
                                :class="{ 'textarea-error': form.errors.description }"
                                placeholder="Description"
                            ></textarea>
                            <p v-if="form.errors.description" class="text-error">{{ form.errors.description }}</p>
                        </div>
                        <div class="space-y-2">
                            <label for="image" class="label">
                                <span class="label-text">Image</span>
                            </label>
                            <input
                                type="file"
                                id="image"
                                @change="(event) => (form.image = event.target.files[0])"
                                class="file-input file-input-bordered w-full"
                                :class="{ 'input-error': form.errors.image }"
                                accept="image/*"
                            />
                        </div>
                        <div>
                            <button type="submit" class="btn btn-info" :disabled="form.processing">
                                <span v-if="form.processing" class="loading loading-spinner loading-md"></span>
                                <span>Update</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
