<script setup>
import InputError from '@/components/InputError.vue';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    employees: {
        type: Array,
        required: true,
    },
});

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: route('backoffice.dashboard'),
    },
    {
        title: 'Suspensions',
        href: route('backoffice.suspensions.index'),
    },
    {
        title: 'Create',
        href: null,
    },
];

const form = useForm({
    employee: '',
    from: '',
    to: '',
    reason: '',
});
const submit = () => {
    form.post(route('backoffice.suspensions.store'), {
        onSuccess: () => form.reset(),
        onError: (errors) => console.error(errors),
    });
};
</script>

<template>
    <Head>
        <title>New Suspension</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="New Suspension">
        <div class="space-y-4">
            <div class="card bg-base-100 shadow">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <form @submit.prevent="submit" class="grid grid-cols-1 gap-4" novalidate>
                        <div class="space-y-2">
                            <label class="label"><span class="label-text">Employee</span></label>
                            <select v-model="form.employee" class="select select-bordered w-full" :class="{ 'input-error': form.errors?.employee }">
                                <option disabled value="">Select the Employee</option>
                                <option v-for="employee in employees" :key="employee.id" :value="employee.id">{{ employee.name }}</option>
                            </select>
                            <p v-if="form.errors?.employee" class="text-error">{{ form.errors?.employee }}</p>
                        </div>
                        <div class="space-y-2">
                            <label for="from" class="label"><span class="label-text">From</span></label>
                            <input v-model="form.from" type="date" id="from" class="input input-bordered w-full" />
                            <InputError :message="form.errors?.from" />
                        </div>
                        <div class="space-y-2">
                            <label for="to" class="label"><span class="label-text">To</span></label>
                            <input v-model="form.to" type="date" id="to" class="input input-bordered w-full" />
                            <InputError :message="form.errors?.to" />
                        </div>
                        <div class="space-y-2">
                            <label class="label"><span class="label-text">Reason</span></label>
                            <input v-model="form.reason" type="text" class="input input-bordered w-full" required />
                            <InputError :message="form.errors?.reason" />
                        </div>
                        <div>
                            <button type="submit" class="btn btn-lg btn-primary" :disabled="form.processing">
                                <span v-if="form.processing" class="loading loading-spinner loading-md"></span>
                                Submit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
