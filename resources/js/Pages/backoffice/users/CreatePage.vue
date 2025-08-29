<script setup>
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    roles: {
        type: Array,
        required: true,
    },
    stores: {
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
        title: 'Users',
        href: route('backoffice.users.index'),
    },
    {
        title: 'Create',
        href: null,
    },
];

const form = useForm({
    name: '',
    email: '',
    phone: '',
    roles: [],
    stores: [],
});

const createUser = () => {
    form.post(route('backoffice.users.store'), {
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
        <title>User Details</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Create User">
        <div class="space-y-4">
            <div class="card bg-base-100 shadow">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <form @submit.prevent="createUser" class="grid grid-cols-1 gap-4" novalidate>
                        <div class="space-y-2">
                            <label for="name" class="label">
                                <span class="label-text">Name</span>
                            </label>
                            <input type="text" id="name" v-model="form.name" class="input input-bordered w-full" required />
                            <p v-if="form.errors.name" class="text-error">{{ form.errors.name }}</p>
                        </div>
                        <div class="space-y-2">
                            <label for="email" class="label">
                                <span class="label-text">Email Address</span>
                            </label>
                            <input type="email" id="email" v-model="form.email" class="input input-bordered w-full" required />
                            <p v-if="form.errors.email" class="text-error">{{ form.errors.email }}</p>
                        </div>
                        <div class="space-y-2">
                            <label for="phone" class="label">
                                <span class="label-text">Phone Number</span>
                            </label>
                            <input type="text" id="phone" v-model="form.phone" class="input input-bordered w-full" required />
                            <p v-if="form.errors.phone" class="text-error">{{ form.errors.phone }}</p>
                        </div>
                        <fieldset class="space-y-2">
                            <legend class="text-base">Roles</legend>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                                <div v-for="({ id, name }, index) in roles" :key="id">
                                    <label class="flex cursor-pointer items-center gap-2">
                                        <input type="checkbox" v-model="form.roles" :value="id" class="checkbox checkbox-primary" />
                                        <span>{{ name }}</span>
                                    </label>
                                </div>
                            </div>
                        </fieldset>
                        <fieldset class="space-y-2">
                            <legend class="text-base">Stores</legend>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                                <div v-for="({ id, name }, index) in stores" :key="id">
                                    <label class="flex cursor-pointer items-center gap-2">
                                        <input type="checkbox" v-model="form.stores" :value="id" class="checkbox checkbox-primary" />
                                        <span>{{ name }}</span>
                                    </label>
                                </div>
                            </div>
                        </fieldset>
                        <div>
                            <button type="submit" class="btn btn-lg btn-primary" :disabled="form.processing">
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
