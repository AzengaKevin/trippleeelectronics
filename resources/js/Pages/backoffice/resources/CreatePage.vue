<script setup>
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: route('backoffice.dashboard'),
    },
    {
        title: 'Resources',
        href: route('backoffice.resources.index'),
    },
    {
        title: 'Create',
        href: null,
    },
];

const form = useForm({
    name: '',
    route_name: '',
    icon: '',
    order: '',
    description: '',
    is_active: true,
    count: '',
    required_permission: '',
    morph_class: '',
});

const createResource = () => {
    form.post(route('backoffice.resources.store'), {
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
        <title>Create Resource</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Create Resource">
        <div class="space-y-4">
            <div class="card bg-base-100 shadow">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <form @submit.prevent="createResource" class="grid grid-cols-1 gap-4" novalidate>
                        <div class="space-y-2">
                            <label for="name" class="label">
                                <span class="label-text">Name</span>
                            </label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.name }"
                                required
                            />
                            <p v-if="form.errors.name" class="text-error">{{ form.errors.name }}</p>
                        </div>

                        <div class="space-y-2">
                            <label for="route_name" class="label">
                                <span class="label-text">Route Name</span>
                            </label>
                            <input
                                id="route_name"
                                v-model="form.route_name"
                                type="text"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.route_name }"
                            />
                            <p v-if="form.errors.route_name" class="text-error">{{ form.errors.route_name }}</p>
                        </div>

                        <div class="space-y-2">
                            <label for="icon" class="label">
                                <span class="label-text">Icon</span>
                            </label>
                            <input
                                id="icon"
                                v-model="form.icon"
                                type="text"
                                class="input input-bordered w-full"
                                placeholder="e.g. book, file-alt"
                                :class="{ 'input-error': form.errors.icon }"
                            />
                            <p v-if="form.errors.icon" class="text-error">{{ form.errors.icon }}</p>
                        </div>

                        <div class="space-y-2">
                            <label for="order" class="label">
                                <span class="label-text">Order</span>
                            </label>
                            <input
                                id="order"
                                v-model="form.order"
                                type="number"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.order }"
                            />
                            <p v-if="form.errors.order" class="text-error">{{ form.errors.order }}</p>
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

                        <div class="form-control">
                            <label class="label cursor-pointer justify-start gap-2">
                                <input type="checkbox" v-model="form.is_active" class="checkbox checkbox-sm" />
                                <span class="label-text">Is Active</span>
                            </label>
                        </div>

                        <div class="space-y-2">
                            <label for="count" class="label">
                                <span class="label-text">Count</span>
                            </label>
                            <input
                                id="count"
                                v-model="form.count"
                                type="number"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.count }"
                            />
                            <p v-if="form.errors.count" class="text-error">{{ form.errors.count }}</p>
                        </div>

                        <div class="space-y-2">
                            <label for="required_permission" class="label">
                                <span class="label-text">Required Permission</span>
                            </label>
                            <input
                                id="required_permission"
                                v-model="form.required_permission"
                                type="text"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.required_permission }"
                            />
                            <p v-if="form.errors.required_permission" class="text-error">{{ form.errors.required_permission }}</p>
                        </div>

                        <div class="space-y-2">
                            <label for="morph_class" class="label">
                                <span class="label-text">Morph Class</span>
                            </label>
                            <input
                                id="morph_class"
                                v-model="form.morph_class"
                                type="text"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.morph_class }"
                            />
                            <p v-if="form.errors.morph_class" class="text-error">{{ form.errors.morph_class }}</p>
                        </div>

                        <div>
                            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                <span v-if="form.processing" class="loading loading-spinner loading-md"></span>
                                <span>Submit</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
