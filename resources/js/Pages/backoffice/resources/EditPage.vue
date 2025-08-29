<script setup>
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    resource: {
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
        title: 'Resources',
        href: route('backoffice.resources.index'),
    },
    {
        title: props.resource.name,
        href: route('backoffice.resources.show', [props.resource.id]),
    },
    {
        title: 'Edit',
        href: null,
    },
];

const form = useForm({
    name: props.resource.name,
    route_name: props.resource.route_name,
    icon: props.resource.icon,
    order: props.resource.order,
    description: props.resource.description,
    is_active: props.resource.is_active,
    required_permission: props.resource.required_permission,
    morph_class: props.resource.morph_class,
});

const updateResource = () => {
    form.transform((data) => ({ ...data, _method: 'patch' })).post(route('backoffice.resources.update', [props.resource.id]), {
        onSuccess: () => {
            // optional: reset fields if needed
        },
        onError: (errors) => {
            console.error(errors);
        },
    });
};
</script>

<template>
    <Head>
        <title>Edit Resource</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Edit Resource">
        <div class="space-y-4">
            <div class="card bg-base-100 shadow">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <form @submit.prevent="updateResource" class="grid grid-cols-1 gap-4" novalidate>
                        <div class="space-y-2">
                            <label class="label"><span class="label-text">Name</span></label>
                            <input v-model="form.name" type="text" class="input input-bordered w-full" :class="{ 'input-error': form.errors.name }" />
                            <p v-if="form.errors.name" class="text-error">{{ form.errors.name }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="label"><span class="label-text">Route Name</span></label>
                            <input
                                v-model="form.route_name"
                                type="text"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.route_name }"
                            />
                            <p v-if="form.errors.route_name" class="text-error">{{ form.errors.route_name }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="label"><span class="label-text">Icon</span></label>
                            <input
                                v-model="form.icon"
                                type="text"
                                class="input input-bordered w-full"
                                placeholder="e.g., fa-folder"
                                :class="{ 'input-error': form.errors.icon }"
                            />
                            <p v-if="form.errors.icon" class="text-error">{{ form.errors.icon }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="label"><span class="label-text">Order</span></label>
                            <input
                                v-model.number="form.order"
                                type="number"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.order }"
                            />
                            <p v-if="form.errors.order" class="text-error">{{ form.errors.order }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="label"><span class="label-text">Description</span></label>
                            <textarea
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
                            <label class="label"><span class="label-text">Required Permission</span></label>
                            <input
                                v-model="form.required_permission"
                                type="text"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.required_permission }"
                            />
                            <p v-if="form.errors.required_permission" class="text-error">{{ form.errors.required_permission }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="label"><span class="label-text">Morph Class</span></label>
                            <input
                                v-model="form.morph_class"
                                type="text"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.morph_class }"
                            />
                            <p v-if="form.errors.morph_class" class="text-error">{{ form.errors.morph_class }}</p>
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
