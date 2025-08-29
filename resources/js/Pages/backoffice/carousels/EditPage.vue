<script setup>
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    orientationOptions: {
        type: Array,
        required: true,
    },
    positionOptions: {
        type: Array,
        required: true,
    },
    carousel: {
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
        title: 'Carousels',
        href: route('backoffice.carousels.index'),
    },
    {
        title: props.carousel.title,
        href: route('backoffice.carousels.show', [props.carousel.id]),
    },
    {
        title: 'Edit',
        href: null,
    },
];

const form = useForm({
    title: props.carousel.title,
    image: null,
    description: props.carousel.description,
    orientation: props.carousel.orientation,
    position: props.carousel.position,
    link: props.carousel.link,
});

const updateCarousel = () => {
    form.transform((data) => ({ ...data, _method: 'patch' })).post(route('backoffice.carousels.update', [props.carousel.id]), {
        onSuccess: () => form.reset('image'),
        onError: (errors) => console.error(errors),
    });
};
</script>

<template>
    <Head>
        <title>Edit Carousel</title>
        <meta name="description" content="Edit carousel entry" />
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Edit Carousel">
        <div class="space-y-4">
            <div class="card bg-base-100 shadow">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <form @submit.prevent="updateCarousel" class="grid grid-cols-1 gap-4" novalidate>
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
                        <div class="space-y-2">
                            <label for="link" class="label">
                                <span class="label-text">Link</span>
                            </label>
                            <input
                                type="url"
                                id="link"
                                v-model="form.link"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.link }"
                            />
                            <p v-if="form.errors.link" class="text-error">{{ form.errors.link }}</p>
                        </div>
                        <div class="space-y-2">
                            <label for="description" class="label">
                                <span class="label-text">Description</span>
                            </label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                class="textarea textarea-bordered w-full"
                                :class="{ 'textarea-error': form.errors.description }"
                            ></textarea>
                            <p v-if="form.errors.description" class="text-error">{{ form.errors.description }}</p>
                        </div>

                        <div class="space-y-2">
                            <label for="orientation" class="label">
                                <span class="label-text">Orientation</span>
                            </label>
                            <select
                                id="orientation"
                                v-model="form.orientation"
                                class="select select-bordered w-full"
                                :class="{ 'input-error': form.errors.orientation }"
                            >
                                <option disabled selected value="">Select a Orientation</option>
                                <option v-for="orientation in orientationOptions" :key="orientation.value" :value="orientation.value">
                                    {{ orientation.label }}
                                </option>
                            </select>
                            <p v-if="form.errors.orientation" class="text-error">{{ form.errors.orientation }}</p>
                        </div>

                        <div class="space-y-2">
                            <label for="position" class="label">
                                <span class="label-text">Position</span>
                            </label>
                            <select
                                id="position"
                                v-model="form.position"
                                class="select select-bordered w-full"
                                :class="{ 'input-error': form.errors.position }"
                            >
                                <option disabled selected value="">Select a Position</option>
                                <option v-for="position in positionOptions" :key="position.value" :value="position.value">
                                    {{ position.label }}
                                </option>
                            </select>
                            <p v-if="form.errors.position" class="text-error">{{ form.errors.position }}</p>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-info" :disabled="form.processing">
                                <span v-if="form.processing" class="loading loading-spinner loading-md"></span>
                                Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
