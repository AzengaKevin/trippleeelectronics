<script setup>
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    role: {
        type: Object,
        required: true,
    },
    permissions: {
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
        title: 'Roles',
        href: route('backoffice.roles.index'),
    },
    {
        title: props.role.name,
        href: route('backoffice.roles.show', [props.role.id]),
    },
    {
        title: 'Edit',
        href: null,
    },
];

const form = useForm({
    name: props.role.name,
    permissions: props.role.permissions?.map((p) => p.id),
});

const updateRole = () => {
    form.transform((data) => ({ ...data, _method: 'patch' })).post(route('backoffice.roles.update', [props.role.id]), {
        onSuccess: () => form.reset(),
        onError: (errors) => console.error(errors),
    });
};
</script>

<template>
    <Head>
        <title>Edit Role</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Edit Role">
        <div class="space-y-4">
            <div class="card bg-base-100 shadow">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <form @submit.prevent="updateRole" class="grid grid-cols-1 gap-4" novalidate>
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

                        <!-- Permissions Section -->
                        <fieldset class="space-y-2">
                            <legend class="mb-2 text-lg font-semibold">Permissions</legend>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div v-for="({ id, name }, index) in permissions" :key="id">
                                    <label class="flex cursor-pointer items-center gap-2">
                                        <input type="checkbox" v-model="form.permissions" :value="id" class="checkbox checkbox-primary" />
                                        <span>{{ name }}</span>
                                    </label>
                                </div>
                            </div>
                        </fieldset>

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
