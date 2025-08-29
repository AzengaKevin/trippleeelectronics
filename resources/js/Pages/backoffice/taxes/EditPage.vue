<script setup>
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    jurisdictions: {
        type: Array,
        required: true,
    },
    tax: {
        type: Object,
        required: true,
    },
});

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: route('backoffice.dashboard'),
    },
    {
        title: 'Taxes',
        href: route('backoffice.taxes.index'),
    },
    {
        title: props.tax.name,
        href: route('backoffice.taxes.show', props.tax.id),
    },
    {
        title: 'Edit',
        href: null,
    },
];

const form = useForm({
    name: props.tax.name || '',
    description: props.tax.description || '',
    jurisdiction: props.tax.jurisdiction_id || '',
    rate: props.tax.rate || '',
    type: props.tax.type || '',
    is_compound: props.tax.is_compound || true,
    is_inclusive: props.tax.is_inclusive || true,
});

const createTax = () => {
    form.put(route('backoffice.taxes.update', [props.tax.id]), {
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
        <title>Edit Tax</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Edit Tax">
        <div class="space-y-4">
            <div class="card bg-base-100 shadow">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <form @submit.prevent="createTax" class="grid grid-cols-1 gap-4" novalidate>
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
                            <label for="rate" class="label">
                                <span class="label-text">Percentage Rate</span>
                            </label>
                            <input
                                id="rate"
                                v-model="form.rate"
                                type="number"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.rate }"
                            />
                            <p v-if="form.errors.rate" class="text-error">{{ form.errors.rate }}</p>
                        </div>

                        <div class="space-y-2">
                            <label for="jurisdiction" class="label">
                                <span class="label-text">Jurisdiction</span>
                            </label>
                            <select
                                id="jurisdiction"
                                v-model="form.jurisdiction"
                                class="select select-bordered w-full"
                                :class="{ 'select-error': form.errors.jurisdiction }"
                            >
                                <option value="" disabled>Select a jurisdiction</option>
                                <option v-for="jurisdiction in props.jurisdictions" :key="jurisdiction.id" :value="jurisdiction.id">
                                    {{ jurisdiction.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.jurisdiction" class="text-error">{{ form.errors.jurisdiction }}</p>
                        </div>

                        <div class="form-control">
                            <label class="label cursor-pointer justify-start gap-2">
                                <input type="checkbox" v-model="form.is_compound" class="checkbox checkbox-sm" />
                                <span class="label-text">Is Compound</span>
                            </label>
                        </div>

                        <div class="form-control">
                            <label class="label cursor-pointer justify-start gap-2">
                                <input type="checkbox" v-model="form.is_inclusive" class="checkbox checkbox-sm" />
                                <span class="label-text">Is Inclusive</span>
                            </label>
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
