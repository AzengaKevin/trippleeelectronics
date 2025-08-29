<script setup>
import AppCombobox from '@/components/AppCombobox.vue';
import InputError from '@/components/InputError.vue';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import debounce from 'lodash/debounce';
import { ref, watch } from 'vue';

const props = defineProps({
    agreement: {
        type: Object,
        required: true,
    },
    stores: {
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
        title: 'Agreements',
        href: route('backoffice.agreements.index'),
    },
    {
        title: `# ${props.agreement.id}`,
        href: route('backoffice.agreements.show', [props.agreement.id]),
    },
    {
        title: 'Edit',
        href: null,
    },
];

const form = useForm({
    client: {
        client: {
            ...props.agreement.client,
            value: props.agreement.client.id,
            label: props.agreement.client.name,
        },
    },
    deal: props.agreement.agreementable_id || null,
    store: props.agreement.store_id || '',
    content: props.agreement.content || '',
    since: props.agreement.since || '',
    until: props.agreement.until || '',
});

const updateAgreement = () => {
    form.put(route('backoffice.agreements.update', [props.agreement.id]), {
        onSuccess: () => form.reset(),
        onError: (errors) => console.error(errors),
    });
};

const loadClients = (query, setOptions) => {
    axios.get(route('api.clients.index'), { params: { query, limit: 5, perPage: null } }).then((results) => {
        setOptions(
            results.data.data.map(({ id, name, email, phone, kra_pin, type }) => ({
                value: id,
                label: name,
                email,
                phone,
                kra_pin,
                type,
            })),
        );
    });
};

watch(
    () => form.client.client,
    (newClient) => {
        form.client.id = newClient?.value || '';
        form.client.type = newClient?.type || '';
        form.client.email = newClient?.email || '';
        form.client.phone = newClient?.phone || '';
        form.client.address = newClient?.address || '';
        form.client.kra_pin = newClient?.kra_pin || '';
    },
    {
        immediate: true,
    },
);

const deals = ref([]);

watch(
    () => form.client.client,
    debounce((newClient) => {
        if (newClient) {
            axios.get(route('api.deals.index', { client: newClient.value })).then((response) => {
                deals.value = response.data.data;
            });
        } else {
            deals.value = [];
        }
    }, 300),
    {
        immediate: true,
    },
);
</script>

<template>
    <Head>
        <title>Update Agreement</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Update Agreement">
        <div class="space-y-4">
            <div class="card bg-base-100 shadow">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <form @submit.prevent="updateAgreement" class="grid grid-cols-1 gap-4" novalidate>
                        <div class="space-y-2">
                            <label class="label"><span class="label-text">Store</span></label>
                            <select v-model="form.store" class="select select-bordered w-full" :class="{ 'input-error': form.errors?.store }">
                                <option disabled value="">Select the Store</option>
                                <option v-for="store in stores" :key="store.id" :value="store.id">{{ store.name }}</option>
                            </select>
                            <p v-if="form.errors?.store" class="text-error">{{ form.errors?.store }}</p>
                        </div>
                        <div class="space-y-2">
                            <app-combobox
                                id="client"
                                v-model="form.client.client"
                                label="Client Name"
                                :load-options="loadClients"
                                placeholder="Select Client"
                                default-type="individual"
                            />
                            <InputError :message="form.errors?.['client.id']" />
                        </div>
                        <div class="space-y-2">
                            <label class="label"><span class="label-text">Client Email</span></label>
                            <input v-model="form.client.email" type="email" class="input input-bordered w-full" required />
                            <InputError :message="form.errors?.['client.email']" />
                        </div>
                        <div class="space-y-2">
                            <label class="label"><span class="label-text">Client Phone</span></label>
                            <input v-model="form.client.phone" type="text" class="input input-bordered w-full" required />
                            <InputError :message="form.errors?.['client.phone']" />
                        </div>
                        <div class="space-y-2">
                            <label class="label"><span class="label-text">Client Address</span></label>
                            <input v-model="form.client.address" type="text" class="input input-bordered w-full" />
                            <InputError :message="form.errors?.['client.address']" />
                        </div>
                        <div class="space-y-2">
                            <label class="label"><span class="label-text">Client KRA PIN</span></label>
                            <input v-model="form.client.kra_pin" type="text" class="input input-bordered w-full" />
                            <InputError :message="form.errors?.['client.kra_pin']" />
                        </div>
                        <div class="space-y-2">
                            <label for="deal" class="label"><span class="label-text">Deal</span></label>
                            <select
                                id="deal"
                                v-model="form.deal"
                                class="select select-bordered w-full"
                                :class="{ 'select-error': form.errors?.deal }"
                            >
                                <option :value="null">Select Deal</option>
                                <option v-for="deal in deals" :value="deal.id">{{ `${deal.reference}(${deal.type})` }}</option>
                            </select>
                            <InputError :message="form.errors?.deal" />
                        </div>
                        <div class="space-y-2">
                            <label for="content" class="label"><span class="label-text">Content</span></label>
                            <textarea id="content" v-model="form.content" class="textarea textarea-bordered w-full"></textarea>
                            <InputError :message="form.errors?.content" />
                        </div>
                        <div class="space-y-2">
                            <label for="since" class="label"><span class="label-text">Since</span></label>
                            <input v-model="form.since" type="date" id="since" class="input input-bordered w-full" />
                            <InputError :message="form.errors?.since" />
                        </div>
                        <div class="space-y-2">
                            <label for="until" class="label"><span class="label-text">Until</span></label>
                            <input v-model="form.until" type="date" id="until" class="input input-bordered w-full" />
                            <InputError :message="form.errors?.until" />
                        </div>
                        <div>
                            <button type="submit" class="btn btn-lg btn-info" :disabled="form.processing">
                                <span v-if="form.processing" class="loading loading-spinner loading-md"></span>
                                Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
