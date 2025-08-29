<script setup>
import InputError from '@/components/InputError.vue';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    employees: {
        type: Array,
        required: true,
    },
    types: {
        type: Array,
        required: true,
    },
    statuses: {
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
        title: 'Contracts',
        href: route('backoffice.contracts.index'),
    },
    {
        title: 'Create',
        href: null,
    },
];

const form = useForm({
    employee: '',
    contract_type: '',
    start_date: '',
    end_date: '',
    salary: '',
    status: '',
    responsibilities: [],
});
const submit = () => {
    form.post(route('backoffice.contracts.store'), {
        onSuccess: () => form.reset(),
        onError: (errors) => console.error(errors),
    });
};

const addResponsibility = () => {
    form.responsibilities.push(null);
};

const removeResponsibility = (index) => {
    form.responsibilities.splice(index, 1);
};
</script>

<template>
    <Head>
        <title>New Contract</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="New Contract">
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
                            <label class="label"><span class="label-text">Contract Type</span></label>
                            <select
                                v-model="form.contract_type"
                                class="select select-bordered w-full"
                                :class="{ 'input-error': form.errors?.contract_type }"
                            >
                                <option disabled value="">Contract Type</option>
                                <option v-for="t in props.types" :key="t.value" :value="t.value">{{ t.label }}</option>
                            </select>
                            <InputError :message="form.errors?.contract_type" />
                        </div>
                        <div class="space-y-2">
                            <label for="start-date" class="label"><span class="label-text">Start Date</span></label>
                            <input v-model="form.start_date" type="date" id="start-date" class="input input-bordered w-full" />
                            <InputError :message="form.errors?.start_date" />
                        </div>
                        <div class="space-y-2">
                            <label for="end-date" class="label"><span class="label-text">End Date</span></label>
                            <input v-model="form.end_date" type="date" id="end-date" class="input input-bordered w-full" />
                            <InputError :message="form.errors?.end_date" />
                        </div>
                        <div class="space-y-2">
                            <label class="label"><span class="label-text">Salary</span></label>
                            <input v-model="form.salary" type="number" step="0.01" class="input input-bordered w-full" required />
                            <InputError :message="form.errors?.salary" />
                        </div>
                        <div class="space-y-2">
                            <label class="label"><span class="label-text">Contract Status</span></label>
                            <select v-model="form.status" class="select select-bordered w-full" :class="{ 'input-error': form.errors?.status }">
                                <option disabled value="">Contract Status</option>
                                <option v-for="status in props.statuses" :key="status.value" :value="status.value">{{ status.label }}</option>
                            </select>
                            <InputError :message="form.errors?.status" />
                        </div>
                        <div class="space-y-2">
                            <label class="label"><span class="label-text">Responsibilities</span></label>
                            <fieldset class="space-y-2">
                                <div v-for="(responsibility, index) in form.responsibilities" class="join w-full">
                                    <button type="button" class="btn btn-error btn-outline join-item" @click="removeResponsibility(index)">
                                        <font-awesome-icon icon="trash" />
                                    </button>
                                    <input
                                        class="input join-item w-full"
                                        v-model="form.responsibilities[index]"
                                        placeholder="Responsibility Details"
                                    />
                                </div>
                                <div>
                                    <button type="button" class="btn btn-primary btn-outline" @click="addResponsibility">
                                        <font-awesome-icon icon="plus" />
                                        <span>Responsibility</span>
                                    </button>
                                </div>
                            </fieldset>
                            <InputError :message="form.errors?.status" />
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
