<script setup>
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    employee: {
        type: Object,
        required: true,
    },
    auth: {
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
        title: 'Employees',
        href: route('backoffice.employees.index'),
    },
    {
        title: props.employee.name,
        href: route('backoffice.employees.show', [props.employee.id]),
    },
    {
        title: 'Edit',
        href: null,
    },
];

const form = useForm({
    name: props.employee.name,
    email: props.employee.email,
    phone: props.employee.phone,
    identification_number: props.employee.identification_number,
    kra_pin: props.employee.kra_pin,
    department: props.employee.department,
    position: props.employee.position,
    hire_date: props.employee.hire_date,
});

const submit = () => {
    form.patch(route('backoffice.employees.update', [props.employee.id]), {
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
        <title>Edit Employee</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Update Employee">
        <div class="space-y-4">
            <div class="card bg-base-100 shadow">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <form @submit.prevent="submit" class="grid grid-cols-1 gap-4" novalidate>
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
                        <div class="space-y-2">
                            <label for="identification-number" class="label">
                                <span class="label-text">Identification Number</span>
                            </label>
                            <input type="text" id="identification-number" v-model="form.identification_number" class="input input-bordered w-full" />
                            <p v-if="form.errors.identification_number" class="text-error">{{ form.errors.identification_number }}</p>
                        </div>
                        <div class="space-y-2">
                            <label for="kra-pin" class="label">
                                <span class="label-text">KRA Pin</span>
                            </label>
                            <input type="text" id="kra-pin" v-model="form.kra_pin" class="input input-bordered w-full" />
                            <p v-if="form.errors.kra_pin" class="text-error">{{ form.errors.kra_pin }}</p>
                        </div>
                        <div class="space-y-2">
                            <label for="department" class="label">
                                <span class="label-text">Department</span>
                            </label>
                            <input type="text" id="department" v-model="form.department" class="input input-bordered w-full" required />
                            <p v-if="form.errors.department" class="text-error">{{ form.errors.department }}</p>
                        </div>
                        <div class="space-y-2">
                            <label for="position" class="label">
                                <span class="label-text">Position</span>
                            </label>
                            <input type="text" id="position" v-model="form.position" class="input input-bordered w-full" required />
                            <p v-if="form.errors.position" class="text-error">{{ form.errors.position }}</p>
                        </div>
                        <div class="space-y-2">
                            <label for="hire-date" class="label">
                                <span class="label-text">Hire Date</span>
                            </label>
                            <input type="date" id="hire-date" v-model="form.hire_date" class="input input-bordered w-full" required />
                            <p v-if="form.errors.hire_date" class="text-error">{{ form.errors.hire_date }}</p>
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
