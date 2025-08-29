<script setup>
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    organization: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
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
        title: 'Organizations',
        href: route('backoffice.organizations.index'),
    },
    {
        title: props.organization.name,
        href: route('backoffice.organizations.show', [props.organization.id]),
    },
    {
        title: 'Edit',
        href: null,
    },
];

const form = useForm({
    category: props.organization.organization_category_id ?? '',
    name: props.organization.name,
    image: null,
    email: props.organization.email,
    phone: props.organization.phone,
    address: props.organization.address,
    kra_pin: props.organization.kra_pin,
});

const updateOrganization = () => {
    form.transform((data) => ({ ...data, _method: 'patch' })).post(route('backoffice.organizations.update', [props.organization.id]), {
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
        <title>Edit Organization</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Edit Organization">
        <div class="space-y-4">
            <div class="card bg-base-100 shadow">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <form @submit.prevent="updateOrganization" class="grid grid-cols-1 gap-4" novalidate>
                        <div class="space-y-2">
                            <label for="category" class="label">
                                <span class="label-text">Category</span>
                            </label>
                            <select
                                id="category"
                                v-model="form.category"
                                class="select select-bordered w-full"
                                :class="{ 'input-error': form.errors.category }"
                            >
                                <option disabled selected value="">Select a Category</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                            </select>
                            <p v-if="form.errors.category" class="text-error">{{ form.errors.category }}</p>
                        </div>
                        <div class="space-y-2">
                            <label for="name" class="label">
                                <span class="label-text">Name</span>
                            </label>
                            <input
                                type="text"
                                id="name"
                                v-model="form.name"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.name }"
                                required
                            />
                            <p v-if="form.errors.name" class="text-error">{{ form.errors.name }}</p>
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
                            <label for="email" class="label">
                                <span class="label-text">Email</span>
                            </label>
                            <input
                                type="email"
                                id="email"
                                v-model="form.email"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.email }"
                                required
                            />
                            <p v-if="form.errors.email" class="text-error">{{ form.errors.email }}</p>
                        </div>
                        <div class="space-y-2">
                            <label for="phone" class="label">
                                <span class="label-text">Phone</span>
                            </label>
                            <input
                                type="text"
                                id="phone"
                                v-model="form.phone"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.phone }"
                                required
                            />
                            <p v-if="form.errors.phone" class="text-error">{{ form.errors.phone }}</p>
                        </div>
                        <div class="space-y-2">
                            <label for="address" class="label">
                                <span class="label-text">Address</span>
                            </label>
                            <input
                                type="text"
                                id="address"
                                v-model="form.address"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.address }"
                                required
                            />
                            <p v-if="form.errors.address" class="text-error">{{ form.errors.address }}</p>
                        </div>
                        <div class="space-y-2">
                            <label for="kra-pin" class="label">
                                <span class="label-text">KRA PIN</span>
                            </label>
                            <input
                                type="text"
                                id="kra-pin"
                                v-model="form.kra_pin"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.kra_pin }"
                                required
                            />
                            <p v-if="form.errors.kra_pin" class="text-error">{{ form.errors.kra_pin }}</p>
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
