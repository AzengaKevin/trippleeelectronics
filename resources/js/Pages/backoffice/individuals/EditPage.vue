<script setup>
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    individual: {
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
        title: 'Individuals',
        href: route('backoffice.individuals.index'),
    },
    {
        title: props.individual.name,
        href: route('backoffice.individuals.show', [props.individual.id]),
    },
    {
        title: 'Edit',
        href: null,
    },
];

const form = useForm({
    name: props.individual.name,
    image: null,
    username: props.individual.username,
    email: props.individual.email,
    phone: props.individual.phone,
    address: props.individual.address,
    kra_pin: props.individual.kra_pin,
    id_number: props.individual.id_number,
});

const updateIndividual = () => {
    form.transform((data) => ({ ...data, _method: 'patch' })).post(route('backoffice.individuals.update', [props.individual.id]), {
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
        <title>Edit Individual</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Edit Individual">
        <div class="space-y-4">
            <div class="card bg-base-100 shadow">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <form @submit.prevent="updateIndividual" class="grid grid-cols-1 gap-4" novalidate>
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
                            <label for="username" class="label">
                                <span class="label-text">Username</span>
                            </label>
                            <input
                                type="text"
                                id="username"
                                v-model="form.username"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.username }"
                                required
                            />
                            <p v-if="form.errors.username" class="text-error">{{ form.errors.username }}</p>
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
                            <label for="kra_pin" class="label">
                                <span class="label-text">KRA PIN</span>
                            </label>
                            <input
                                type="text"
                                id="kra_pin"
                                v-model="form.kra_pin"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.kra_pin }"
                            />
                            <p v-if="form.errors.kra_pin" class="text-error">{{ form.errors.kra_pin }}</p>
                        </div>
                        <div class="space-y-2">
                            <label for="id_number" class="label">
                                <span class="label-text">ID Number</span>
                            </label>
                            <input
                                type="text"
                                id="id_number"
                                v-model="form.id_number"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.id_number }"
                            />
                            <p v-if="form.errors.id_number" class="text-error">{{ form.errors.id_number }}</p>
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
