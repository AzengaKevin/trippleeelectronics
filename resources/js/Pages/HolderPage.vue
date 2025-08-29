<script setup>
import AppCombobox from '@/components/AppCombobox.vue';
import usePrice from '@/composables/usePrice';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    quotation: {
        type: Object,
        required: true,
    },
    stores: {
        type: Array,
        required: true,
    },
    feedback: {
        type: Object,
        required: false,
    },
});

const breadcrumbs = [
    { title: 'Dashboard', href: route('backoffice.dashboard') },
    { title: 'Quotations', href: route('backoffice.quotations.index') },
    { title: `Quotation # ${props.quotation?.reference}`, href: route('backoffice.quotations.show', [props.quotation?.id]) },
    { title: 'Edit', href: null },
];

const itemBlueprint = {
    item: null,
    quantity: 1,
    price: null,
    total_price: null,
    on_hand: null,
};

const form = useForm({
    items: props.quotation.items.map((item) => {
        return {
            item: {
                ...item.item,
                type: item.item_type || '',
                value: item.item_id,
                label: item.item.name,
            },
            quantity: item.quantity,
            price: item.price,
            total_price: item.price * item.quantity,
            on_hand: item.item.quantity || 0,
        };
    }),
    customer: {
        customer: {
            ...props.quotation.customer,
            value: props.quotation.customer_id || null,
            label: props.quotation.customer?.name || '',
        },
        id: props.quotation.customer_id || '',
        type: props.quotation.customer_type || '',
        name: '',
        email: '',
        phone: '',
        address: '',
        kra_pin: '',
        id_number: '',
    },
    amount: props.quotation.amount || '',
    shipping_amount: props.quotation.shipping_amount || 0,
    total_amount: props.quotation.total_amount || '',
    store: props.quotation?.store_id || '',
});

const addItem = () => {
    form.items?.push({ ...itemBlueprint });
};

const removeItem = (index) => {
    form.items?.splice(index, 1);
};

const step = ref(1);

const nextStep = () => {
    step.value++;
};

const prevStep = () => {
    step.value--;
};

const { formatPrice } = usePrice();

const updateItem = (index, changeCost = false) => {
    const item = form.items[index];

    if (item.item) {
        if (changeCost) {
            item.price = item.item?.price || 0;
        }

        item.total_price = item.price * item.quantity;

        item.on_hand = item.item?.on_hand || 0;

        if (form.items[form.items.length - 1].item !== null) {
            addItem();
        }
    }
};

const filteredItems = computed(() => form.items.filter((item) => item.item !== null));

const itemsSetupProperly = computed(() => filteredItems.value.length > 0);

const showOrderSummaryNextButton = computed(() => step.value === 1 && itemsSetupProperly.value);

const amount = computed(() =>
    form.items.reduce((acc, item) => {
        const price = item.price || 0;
        const quantity = item.quantity || 0;
        const discount = item.discount || 0;

        return acc + (price * quantity - discount);
    }, 0),
);

const totalAmount = computed(() => parseFloat(amount.value) + parseFloat(form.shipping_amount));

const updateQuotation = () => {
    form.transform((data) => {
        data.items = filteredItems.value.map((item) => {
            return {
                product: item.item.value,
                quantity: item.quantity,
                price: item.price,
            };
        });

        data.amount = amount.value;

        data.total_amount = totalAmount.value;

        data.customer.id = data.customer?.customer?.value;

        return data;
    }).patch(route('backoffice.quotations.update', props.quotation.id), {
        onSuccess: () => form.reset(),
        onError: (errors) => console.error(errors),
    });
};

watch(
    () => form.customer.customer,
    (newCustomer) => {
        form.customer.type = newCustomer?.type || null;
        form.customer.name = newCustomer?.label || null;
        form.customer.email = newCustomer?.email || null;
        form.customer.phone = newCustomer?.phone || null;
        form.customer.address = newCustomer?.address || null;
        form.customer.kra_pin = newCustomer?.kra_pin || null;
    },
    {
        immediate: true,
    },
);

const loadProducts = (query, setOptions) => {
    axios
        .get(route('api.products.index'), { params: { query, limit: 5, perPage: null, includeVariants: true, includeCustomItems: true } })
        .then((results) => {
            setOptions(
                results.data.data.map(({ id, sku, name, slug, cost, price, type, quantity, pos_name }) => ({
                    value: id,
                    label: name,
                    sku,
                    slug,
                    cost,
                    price,
                    type,
                    quantity,
                    pos_name,
                    on_hand: quantity,
                })),
            );
        });
};

const loadCustomers = (query, setOptions) => {
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
</script>

<template>
    <Head>
        <title>Edit Quotation</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Edit Quotation">
        <form @submit.prevent="updateQuotation" class="grid grid-cols-12 gap-4" novalidate>
            <div class="col-span-12 lg:col-span-9">
                <div class="card bg-base-100 shadow">
                    <div class="card-body lg-p-6 p-2 sm:p-4">
                        <legend class="label">Items</legend>
                        <p v-if="form.errors.items" class="text-error">{{ form.errors.items }}</p>
                        <div class="overflow-x-auto pb-48">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Item</th>
                                        <th>Quantity</th>
                                        <th>Price Per Item</th>
                                        <th>Total</th>
                                        <th>On Hand</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template v-if="form.items.length">
                                        <tr v-for="(item, index) in form.items" :key="index">
                                            <th>{{ index + 1 }}</th>
                                            <td class="w-2/5 min-w-[32rem]">
                                                <app-combobox
                                                    v-model="item.item"
                                                    @handle-change="updateItem(index, true)"
                                                    :load-options="loadProducts"
                                                />
                                            </td>
                                            <td class="space-y-1">
                                                <input
                                                    type="number"
                                                    v-model="item.quantity"
                                                    @input="updateItem(index)"
                                                    class="input input-bordered input-primary w-fit md:w-full"
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    type="number"
                                                    v-model="item.price"
                                                    @input="updateItem(index)"
                                                    class="input input-bordered min-w-48 md:w-full"
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    type="number"
                                                    v-model="item.total_price"
                                                    disabled
                                                    class="input input-bordered min-w-48 md:w-full"
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    type="number"
                                                    v-model="item.on_hand"
                                                    disabled
                                                    class="input input-bordered min-w-48 md:w-full"
                                                />
                                            </td>
                                            <td>
                                                <button type="button" @click="removeItem(index)" class="btn btn-sm btn-square btn-outline btn-error">
                                                    <font-awesome-icon icon="trash" />
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                    <template v-else>
                                        <tr>
                                            <td colspan="7" class="text-center">No items in the order</td>
                                        </tr>
                                    </template>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="7">
                                            <button type="button" @click="addItem" class="btn btn-primary">Add Item</button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-span-12 lg:col-span-3">
                <div class="card bg-base-100 shadow">
                    <div class="card-body space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-bold">Quotation Summary</h2>
                            </div>
                            <div></div>
                        </div>
                        <hr class="border-slate-300" />

                        <template v-if="step === 1">
                            <div class="grid grid-cols-1 gap-4">
                                <div class="space-y-2">
                                    <label class="label"><span class="label-text">Quotation Amount</span></label>
                                    <div>
                                        <span class="text-xl font-bold">{{ formatPrice(amount) }}</span>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label class="label"><span class="label-text">Shipping Amount</span></label>
                                    <input v-model="form.shipping_amount" type="number" step="0.50" class="input input-bordered w-full" />
                                </div>
                                <div class="space-y-2">
                                    <label class="label"><span class="label-text">Total Amount</span></label>
                                    <div>
                                        <span class="text-xl font-bold">{{ formatPrice(totalAmount) }}</span>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template v-if="step === 2">
                            <div class="grid grid-cols-1 gap-4">
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
                                        id="customer"
                                        v-model="form.customer.customer"
                                        label="Customer Name"
                                        :load-options="loadCustomers"
                                        placeholder="Select Customer"
                                        default-type="individual"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <label class="label"><span class="label-text">Customer Email</span></label>
                                    <input v-model="form.customer.email" type="email" class="input input-bordered w-full" required />
                                </div>
                                <div class="space-y-2">
                                    <label class="label"><span class="label-text">Customer Phone</span></label>
                                    <input v-model="form.customer.phone" type="text" class="input input-bordered w-full" required />
                                </div>
                                <div class="space-y-2">
                                    <label class="label"><span class="label-text">Customer Address</span></label>
                                    <input v-model="form.customer.address" type="text" class="input input-bordered w-full" />
                                </div>
                                <div class="space-y-2">
                                    <label class="label"><span class="label-text">Customer KRA PIN</span></label>
                                    <input v-model="form.customer.kra_pin" type="text" class="input input-bordered w-full" />
                                </div>
                            </div>
                        </template>

                        <div class="flex flex-col justify-between gap-3 pt-4">
                            <button type="button" @click="prevStep" :disabled="step === 1" class="btn btn-lg rounded-full">Back</button>

                            <button v-if="showOrderSummaryNextButton" type="button" @click="nextStep" class="btn btn-lg btn-primary rounded-full">
                                Next
                            </button>

                            <button v-if="step === 2" type="submit" class="btn btn-lg btn-success rounded-full" :disabled="form.processing">
                                <span v-if="form.processing" class="loading loading-spinner loading-md"></span>
                                Update Quotation
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </BackofficeLayout>
</template>
