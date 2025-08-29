<script setup>
import AppCombobox from '@/components/AppCombobox.vue';
import OrderCustomerComponent from '@/components/OrderCustomerComponent.vue';
import OrderPaymentsComponent from '@/components/OrderPaymentsComponent.vue';
import PayLaterOrdersComponent from '@/components/PayLaterOrdersComponent.vue';
import useAutologout from '@/composables/useAutologout';
import useLogout from '@/composables/useLogout';
import useOrders from '@/composables/useOrders';
import usePOS from '@/composables/usePOS';
import usePrice from '@/composables/usePrice';
import useProducts from '@/composables/useProducts';
import useSwal from '@/composables/useSwal';
import useUpsertOrder from '@/composables/useUpsertOrder';
import UpdateStoreForm from '@/Pages/backoffice/UpdateStoreForm.vue';
import debounce from 'lodash/debounce';

import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Store } from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    stores: {
        type: Array,
        required: true,
    },
    orders: {
        type: Array,
        required: true,
    },
    order: {
        type: Object,
        required: false,
    },
    latestOrder: {
        type: Object,
        required: false,
    },
    primaryTax: {
        type: Object,
        required: true,
    },
    feedback: {
        type: Object,
        required: false,
    },
    params: {
        type: Object,
        required: false,
    },
});

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: route('backoffice.dashboard'),
    },
    {
        title: 'Point of Sale',
        href: null,
    },
];

const showSidebar = ref(false);

const stayLoggedInDialog = ref(null);

const navbarElement = ref(null);

const navbarHeight = ref(0);

const titleElement = ref(null);

const titleHeight = ref(0);

const topbarElement = ref(null);

const topbarHeight = ref(0);

const bottomElement = ref(null);

const bottomHeight = ref(0);

const itemsElement = ref(null);

const occupiedHeight = computed(() => navbarHeight.value + titleHeight.value + topbarHeight.value + bottomHeight.value + 40);

const page = usePage();

const auth = computed(() => page.props.auth);

const appName = import.meta.env.VITE_APP_NAME;

const toggleSidebar = () => {
    showSidebar.value = !showSidebar.value;
};

const closeStayLoggedInDialog = () => {
    if (stayLoggedInDialog.value) {
        stayLoggedInDialog.value.close();
    }
};

const { logout } = useLogout();

useAutologout({
    onLogout: () => logout(false),
    onWarning: () => {
        if (stayLoggedInDialog.value) {
            stayLoggedInDialog.value.showModal();
        }
        return true;
    },
});

const { formatPrice } = usePrice();

const {
    updateStoreDialog,
    orderPaymentsDialog,
    orderCustomerDialog,
    payLaterOrdersDialog,
    openUpdateStoreDialog,
    closeUpdateStoreDialog,
    openPayLaterOrdersDialog,
    closePayLaterOrdersDialog,
    openOrderPaymentsDialog,
    closeOrderPaymentsDialog,
    openOrderCustomerDialog,
    closeOrderCustomerDialog,
} = usePOS();

const {
    currentItem,
    filteredItems,
    form,
    itemsSetupProperly,
    tenderedAmount,
    totalAmount,
    balanceAmount,
    amount,
    currentItemStockLevels,
    currentItemStockLevelsFormatted,
    readyToSave,
    readyToUpdate,
    paymentsSetupCorrectly,
    addItem,
    removeItem,
    updateItem,
    itemIsInsufficientInTheCurrentStore,
    priceIsLessThanCost,
} = useUpsertOrder({ props, existingOrderItemsEditable: false });

const { loadProducts } = useProducts();

const { markComplete, printReceipt } = useOrders();

const { showInertiaErrorsSwal, showFeedbackSwal } = useSwal();

const orderReference = ref(props.params?.reference || '');

watch(
    orderReference,
    debounce((value) => {
        if (value) {
            router.get(
                route('backoffice.pos', {
                    reference: props.order?.reference,
                }),
                {
                    reference: value,
                },
                {
                    preserveState: false,
                    preserveScroll: false,
                    replace: true,
                },
            );
        }
    }, 500),
);

onMounted(() => {
    if (navbarElement.value) {
        navbarHeight.value = navbarElement.value.offsetHeight;
    }

    if (titleElement.value) {
        titleHeight.value = titleElement.value.offsetHeight;
    }

    if (topbarElement.value) {
        topbarHeight.value = topbarElement.value.offsetHeight;
    }

    if (bottomElement.value) {
        bottomHeight.value = bottomElement.value.offsetHeight;
    }

    if (itemsElement.value) {
        if (window.innerWidth > 768) {
            itemsElement.value.style.height = window.innerHeight - occupiedHeight.value + 'px';

            itemsElement.value.style.maxHeight = window.innerHeight - occupiedHeight.value;
        }
    }
});

const handleOrderPaymentsUpdated = ({ payments, pay_later, accept_partial_payments }) => {
    form.payments = payments;
    form.pay_later = pay_later;
    form.accept_partial_payment = accept_partial_payments;

    if (payments?.length > 0) {
        tenderedAmount.value = payments.reduce((acc, payment) => acc + payment.amount, 0);
    } else {
        tenderedAmount.value = null;
    }

    closeOrderPaymentsDialog();
};

const handleOrderCustomerUpdated = ({ customer }) => {
    form.customer = customer;

    closeOrderCustomerDialog();
};

watch(
    () => props.feedback,
    (newFeedback) => {
        if (newFeedback) {
            showFeedbackSwal(newFeedback);
        }
    },
    {
        immediate: true,
    },
);

const submit = () => {
    form.transform((data) => {
        data.items = filteredItems.value.map((item) => {
            return {
                product: item.item.value,
                quantity: item.quantity,
                price: item.price,
                discount_rate: item.discount_rate,
                tax_rate: item.tax_rate,
            };
        });

        data.amount = amount.value;

        data.total_amount = totalAmount.value;

        data.balance_amount = balanceAmount.value;

        data.tendered_amount = tenderedAmount.value;

        data.pay_later = props.order ? true : data.pay_later;

        return data;
    }).post(route('backoffice.pos', { reference: props.order?.reference }), {
        onError: (errors) => {
            showInertiaErrorsSwal(errors);
        },
        preserveState: false,
        preserveScroll: false,
        replace: true,
    });
};

const submitWithPaylater = () => {
    form.transform((data) => {
        data.items = filteredItems.value.map((item) => {
            return {
                product: item.item.value,
                quantity: item.quantity,
                price: item.price,
                tax_rate: item.tax_rate,
                discount_rate: item.discount_rate,
            };
        });

        data.amount = amount.value;

        data.total_amount = totalAmount.value;

        data.balance_amount = balanceAmount.value;

        data.tendered_amount = tenderedAmount.value;

        data.pay_later = true;

        return data;
    }).post(route('backoffice.pos', { reference: props.order?.reference }), {
        onError: (errors) => {
            showInertiaErrorsSwal(errors);
        },
        preserveState: false,
        preserveScroll: false,
        replace: true,
    });
};
</script>

<template>
    <Head>
        <title>Point Of Sale</title>
    </Head>

    <div class="drawer bg-base-200" ref="wrapper">
        <input id="my-drawer" v-model="showSidebar" type="checkbox" class="drawer-toggle" />
        <div class="drawer-content flex min-h-screen flex-col bg-[#e7d7be] md:max-h-screen">
            <div ref="navbarElement" class="bg-base-100 sticky inset-x-0 top-0 z-[1] flex gap-2 px-2 py-1 shadow-sm">
                <div class="flex-none">
                    <button type="button" @click="toggleSidebar" class="btn btn-sm btn-square drawer-button">
                        <font-awesome-icon icon="bars" />
                    </button>
                </div>
                <div class="flex-1">
                    <div v-if="breadcrumbs.length" class="breadcrumbs hidden text-sm lg:block">
                        <ul>
                            <li v-for="(item, index) in breadcrumbs" :key="index">
                                <Link v-if="item.href" :href="item.href" class="text-primary">{{ item.title }}</Link>
                                <span v-else class="hover:no-underline">{{ item.title }}</span>
                            </li>
                        </ul>
                    </div>
                    <Link v-else :href="route('welcome')" class="btn btn-ghost text-xl">{{ appName }}</Link>
                </div>
                <div class="flex flex-none items-center gap-2">
                    <a :href="route('backoffice.messages.index')" class="btn btn-sm btn-ghost btn-circle">
                        <div class="indicator">
                            <font-awesome-icon icon="envelope" size="xl" />
                            <span v-if="auth.user.unread_messages_count > 0" class="badge badge-xs badge-secondary indicator-item size-4">
                                {{ auth.user.unread_messages_count }}
                            </span>
                        </div>
                    </a>

                    <div class="dropdown dropdown-end">
                        <button tabindex="0" class="btn btn-sm btn-ghost btn-circle">
                            <span class="indicator">
                                <font-awesome-icon icon="bell" size="xl" />
                                <span v-if="auth.user.unread_notifications_count > 0" class="badge badge-xs badge-secondary indicator-item size-4">
                                    {{ auth.user.unread_notifications_count }}
                                </span>
                            </span>
                        </button>
                        <ul tabindex="0" class="menu menu-sm dropdown-content bg-base-100 rounded-box z-1 mt-3 w-64 p-2 shadow">
                            <li v-if="auth.user.notifications.length === 0">
                                <span class="text-gray-500">No notifications</span>
                            </li>
                            <li v-for="(notification, index) in auth.user.notifications" :key="index">
                                <Link :href="notification.data.action_url">{{ notification.data.title }}</Link>
                            </li>
                            <li>
                                <Link :href="route('backoffice.notifications.index')">View all notifications</Link>
                            </li>
                        </ul>
                    </div>
                    <div class="dropdown dropdown-end">
                        <div tabindex="0" role="button" class="btn btn-sm btn-ghost avatar py-2">
                            <span class="hidden lg:inline">{{ auth.user.name }}</span>
                            <div class="mask size-6 mask-circle">
                                <img :alt="`${auth.user.name}'s avatar`" :src="auth.user.avatar_url" />
                            </div>
                        </div>
                        <ul tabindex="0" class="menu menu-sm dropdown-content bg-base-100 rounded-box z-1 mt-3 w-52 p-2 shadow">
                            <li>
                                <a role="button" href="#" @click="logout" method="post">
                                    <font-awesome-icon icon="sign-out" />
                                    Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="flex h-full flex-col gap-2 p-2">
                <div ref="titleElement" class="flex flex-wrap items-center justify-between gap-2">
                    <h2 class="text-xl font-semibold">Point Of Sale: {{ auth.store.name }}</h2>
                    <template v-if="route().current('backoffice.dashboard')">
                        <div>
                            <label class="input">
                                <span class="label"><font-awesome-icon icon="search" /></span>
                                <input type="search" v-model="query" />
                            </label>
                        </div>
                    </template>
                    <template v-else>
                        <div v-if="breadcrumbs.length" class="breadcrumbs my-0 py-0 text-sm lg:hidden">
                            <ul class="m-0">
                                <li v-for="(item, index) in breadcrumbs" :key="index">
                                    <Link v-if="item.href" :href="item.href">{{ item.title }}</Link>
                                    <span v-else>{{ item.title }}</span>
                                </li>
                            </ul>
                        </div>
                    </template>
                </div>

                <div ref="topbarElement" class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <label class="input input-xs w-full bg-[#0000fe] text-white">
                            <span class="label font-bold text-white"><font-awesome-icon icon="search" size="sm" /></span>
                            <input type="search" v-model="orderReference" required placeholder="Search Order By Reference" />
                        </label>
                    </div>

                    <div class="scrollbar-thin w-full overflow-x-auto whitespace-nowrap lg:grow">
                        <div class="flex gap-3">
                            <Link
                                v-for="o in orders"
                                :href="route('backoffice.pos', { reference: o.reference })"
                                class="btn btn-xs btn-primary"
                                :class="{ 'btn-outline': order?.reference !== o.reference }"
                            >
                                # {{ o.reference }}
                            </Link>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <a v-if="order" :href="route('backoffice.pos')" class="btn btn-xs btn-primary btn-outline">
                            <font-awesome-icon icon="sync" />
                        </a>

                        <button
                            v-if="auth.user.roles.map((r) => r.name).includes('admin')"
                            @click="openUpdateStoreDialog"
                            class="btn btn-xs btn-square btn-primary btn-outline"
                        >
                            <font-awesome-icon icon="store" />
                        </button>
                    </div>
                </div>

                <form novalidate @submit.prevent="submit" class="flex max-h-full grow flex-col gap-2">
                    <div ref="itemsElement" class="scrollbar-thin grow overflow-y-auto bg-blue-500">
                        <div class="card bg-blue-500">
                            <div class="card-body p-1">
                                <div class="relative overflow-x-auto pb-48">
                                    <table class="table-xs table">
                                        <thead class="uppercase">
                                            <tr>
                                                <th>#</th>
                                                <th>Item</th>
                                                <th>Quantity</th>
                                                <th>Price Per Item</th>
                                                <th>On Hand</th>
                                                <th>Disc %</th>
                                                <th>Tax</th>
                                                <th>Total</th>
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
                                                            size="xs"
                                                            :disabled="!item.editable"
                                                        />
                                                    </td>
                                                    <td class="space-y-1">
                                                        <input
                                                            type="number"
                                                            v-model="item.quantity"
                                                            @input="updateItem(index)"
                                                            class="input input-xs input-bordered min-w-36 md:w-full"
                                                            :class="[itemIsInsufficientInTheCurrentStore(item) ? 'input-error' : 'input-primary']"
                                                            :disabled="!item.editable"
                                                        />
                                                        <span v-if="itemIsInsufficientInTheCurrentStore(item)" class="text-error text-xs">
                                                            Insufficient Stock
                                                        </span>
                                                    </td>
                                                    <td class="space-y-1">
                                                        <input
                                                            type="number"
                                                            v-model="item.price"
                                                            @input="updateItem(index)"
                                                            class="input input-xs input-bordered min-w-36 md:w-full"
                                                            :class="[priceIsLessThanCost(item) ? 'input-error' : 'input-primary']"
                                                            :disabled="!item.editable"
                                                        />
                                                        <span v-if="priceIsLessThanCost(item)" class="text-error text-xs">
                                                            Price is too low : {{ formatPrice(item.cost) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <input
                                                            type="number"
                                                            v-model="item.on_hand"
                                                            disabled
                                                            class="input input-xs input-bordered min-w-36 md:w-full"
                                                        />
                                                    </td>
                                                    <td>
                                                        <input
                                                            type="number"
                                                            v-model="item.discount_rate"
                                                            @input="updateItem(index)"
                                                            class="input input-xs input-bordered min-w-24 md:w-full"
                                                        />
                                                    </td>
                                                    <td>
                                                        <input
                                                            type="checkbox"
                                                            @change="updateItem(index)"
                                                            v-model="item.taxable"
                                                            class="checkbox checkbox-primary"
                                                        />
                                                    </td>
                                                    <td>
                                                        <input
                                                            type="number"
                                                            v-model="item.total_price"
                                                            disabled
                                                            class="input input-xs input-bordered min-w-36 md:w-full"
                                                        />
                                                    </td>
                                                    <td>
                                                        <button
                                                            type="button"
                                                            :disabled="!item.editable"
                                                            @click="removeItem(index)"
                                                            class="btn btn-xs btn-square btn-error"
                                                        >
                                                            <font-awesome-icon icon="trash" size="sm" />
                                                        </button>
                                                    </td>
                                                </tr>
                                            </template>
                                            <template v-else>
                                                <tr>
                                                    <td colspan="9" class="text-center">No items in the order</td>
                                                </tr>
                                            </template>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="9">
                                                    <button type="button" @click="addItem" class="btn btn-xs btn-light">Add Item</button>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>

                                    <div v-if="currentItem && currentItemStockLevels.length" class="absolute right-0 bottom-0 p-4 text-end">
                                        <span>{{ currentItem.sku }}: </span>
                                        <span>{{ currentItemStockLevelsFormatted }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div ref="bottomElement" class="grid grid-cols-12 gap-2">
                        <div class="col-span-12 md:col-span-9">
                            <div class="card bg-[#00ff01] shadow">
                                <div class="card-body space-y-2 p-1 pb-4">
                                    <h2 class="text-xl font-bold">
                                        <template v-if="order">
                                            <div>
                                                Order Ref:
                                                <span class="underline underline-offset-2">{{ order.reference }}</span>
                                                Summary
                                                <span class="badge" :class="[order.order_status_badge]">{{ order.order_status }}</span>
                                            </div>
                                        </template>
                                        <template v-else> New Order Summary </template>
                                    </h2>
                                    <div class="overflow-x-auto">
                                        <table class="table-xs table">
                                            <tbody>
                                                <tr>
                                                    <th>Order Amount</th>
                                                    <td>
                                                        <div class="rounded border bg-white px-3 py-0.5">
                                                            <span class="text-xs font-semibold">{{ formatPrice(amount) }}</span>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Total Amount</th>
                                                    <td>
                                                        <div class="rounded border bg-white px-3 py-0.5">
                                                            <span class="text-xs font-semibold">{{ formatPrice(totalAmount) }}</span>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Shipping Amount</th>
                                                    <td>
                                                        <input
                                                            v-model="form.shipping_amount"
                                                            type="number"
                                                            step="0.50"
                                                            placeholder="Enter Shipping Amount"
                                                            class="input input-bordered input-xs w-full font-semibold"
                                                        />
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Tendered Amount</th>
                                                    <td>
                                                        <div class="rounded border bg-white px-3 py-0.5">
                                                            <span class="font-semibold">{{ formatPrice(tenderedAmount) }}</span>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Balance Amount</th>
                                                    <td>
                                                        <div class="rounded border bg-white px-3 py-0.5">
                                                            <span class="font-semibold">{{ formatPrice(balanceAmount) }}</span>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Customer</th>
                                                    <td>
                                                        <div class="join w-full" @click="openOrderCustomerDialog">
                                                            <div>
                                                                <button type="button" class="btn btn-xs join-item">
                                                                    <font-awesome-icon icon="user-plus" />
                                                                </button>
                                                            </div>
                                                            <input
                                                                class="input input-xs join-item w-full"
                                                                disabled
                                                                :value="form?.customer?.customer?.label"
                                                                placeholder="Enter Customer Details"
                                                            />
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 md:col-span-3">
                            <div class="card h-full bg-[#000000] shadow">
                                <div class="card-body space-y-2 p-1">
                                    <div class="grid grid-cols-3 gap-2">
                                        <button
                                            v-if="itemsSetupProperly || order"
                                            type="button"
                                            @click="openOrderPaymentsDialog"
                                            :disabled="form.processing || paymentsSetupCorrectly"
                                            :class="[
                                                'btn-outline flex aspect-square w-full flex-col items-center justify-center gap-3 rounded bg-white p-2',
                                                {
                                                    'hover:bg-primary hover:text-white': (itemsSetupProperly && !readyToSave) || order,
                                                    'ring-primary animate-bounce ring ring-offset-2': itemsSetupProperly && !readyToSave && !order,
                                                    'disabled:cursor-not-allowed disabled:bg-gray-300 disabled:text-gray-500':
                                                        form.processing || (paymentsSetupCorrectly && !order),
                                                },
                                            ]"
                                        >
                                            <font-awesome-icon icon="credit-card" size="2x" />
                                            <span class="font-bold uppercase">Payments</span>
                                        </button>
                                        <template v-if="order">
                                            <button
                                                v-if="order.can_mark_completed"
                                                type="button"
                                                @click="markComplete(order)"
                                                class="hover:bg-primary btn-outline flex aspect-square w-full flex-col items-center justify-center gap-3 rounded bg-white p-2 hover:text-white"
                                            >
                                                <font-awesome-icon icon="check" size="2x" />
                                                <span class="font-bold uppercase">Mark Complete</span>
                                            </button>
                                        </template>
                                        <button
                                            v-if="!order"
                                            :disabled="form.processing || !readyToSave"
                                            :class="[
                                                'btn-outline flex aspect-square w-full flex-col items-center justify-center gap-3 rounded bg-white p-2',
                                                {
                                                    'ring-primary hover:bg-primary animate-bounce ring ring-offset-2 hover:text-white': readyToSave,
                                                    'disabled:cursor-not-allowed disabled:bg-gray-300 disabled:text-gray-500':
                                                        form.processing || !readyToSave,
                                                },
                                            ]"
                                        >
                                            <font-awesome-icon icon="save" size="2x" />
                                            <span class="font-bold uppercase">Save</span>
                                        </button>

                                        <button
                                            v-if="order"
                                            :disabled="!readyToUpdate"
                                            :class="[
                                                'btn-outline flex aspect-square w-full flex-col items-center justify-center gap-3 rounded bg-white p-2',
                                                {
                                                    'ring-primary hover:bg-primary animate-bounce ring ring-offset-2 hover:text-white': readyToUpdate,
                                                    'disabled:cursor-not-allowed disabled:bg-gray-300 disabled:text-gray-500': !readyToUpdate,
                                                },
                                            ]"
                                        >
                                            <font-awesome-icon icon="save" size="2x" />
                                            <span class="font-bold uppercase">Save</span>
                                        </button>

                                        <button
                                            :disabled="readyToUpdate"
                                            type="button"
                                            @click="openPayLaterOrdersDialog"
                                            :class="[
                                                'btn-outline flex aspect-square w-full flex-col items-center justify-center gap-3 rounded bg-white p-2 font-bold uppercase',
                                                {
                                                    'hover:bg-primary hover:text-white': !readyToUpdate,
                                                    'disabled:cursor-not-allowed disabled:bg-gray-300 disabled:text-gray-500': readyToUpdate,
                                                },
                                            ]"
                                        >
                                            <span>Pay Later<br />Lipa Mdogo<br />Credit</span>
                                        </button>

                                        <button
                                            v-if="itemsSetupProperly && !order"
                                            type="button"
                                            @click="submitWithPaylater"
                                            :disabled="form.processing"
                                            :class="[
                                                'btn-outline hover:bg-primary flex aspect-square w-full flex-col items-center justify-center gap-3 rounded bg-white p-2 hover:text-white',
                                                {
                                                    'disabled:cursor-not-allowed disabled:bg-gray-300 disabled:text-gray-500': form.processing,
                                                },
                                            ]"
                                        >
                                            <font-awesome-icon icon="hourglass-half" size="2x" />
                                            <span class="font-bold uppercase">Pay Later</span>
                                        </button>

                                        <button
                                            type="button"
                                            @click="printReceipt(order ?? latestOrder)"
                                            class="hover:bg-primary btn-outline flex aspect-square w-full flex-col items-center justify-center gap-3 rounded bg-white p-2 hover:text-white"
                                        >
                                            <font-awesome-icon icon="print" size="2x" />
                                            <span class="font-bold uppercase">Receipt</span>
                                        </button>

                                        <a
                                            v-if="order"
                                            :href="route('backoffice.pos')"
                                            class="hover:bg-primary btn-outline flex aspect-square w-full flex-col items-center justify-center gap-3 rounded bg-white p-2 hover:text-white"
                                        >
                                            <img src="@/assets/images/pos.png" class="size-14" alt="POS Image" />
                                            <span class="font-bold uppercase">POS</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="drawer-side z-[2]">
            <button type="button" @click="toggleSidebar" aria-label="close sidebar" class="drawer-overlay"></button>
            <ul class="menu text-base-content scrollbar-thin relative h-full min-h-full w-96 flex-nowrap overflow-y-auto bg-[#a0d9ef] p-2">
                <li class="sticky start-0 end-0 top-0 z-[1] w-full bg-[#a0d9ef]">
                    <a :href="route('welcome')" class="item-center flex gap-2">
                        <Store />
                        <span class="text-2xl font-bold">{{ appName }}</span>
                    </a>
                </li>

                <li v-for="(resource, index) in auth.resources" :key="index">
                    <a :href="route(resource.route_name)">
                        <span class="flex h-4 w-4 items-center justify-center">
                            <font-awesome-icon :icon="resource.icon" />
                        </span>
                        <span class="font-semibold">{{ resource.name }}</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <teleport to="body">
        <dialog ref="stayLoggedInDialog" class="modal">
            <div class="modal-box">
                <h3 class="text-lg font-bold">Are you still there?</h3>
                <p class="py-2">Do you want to stay logged in?</p>
                <div class="modal-action">
                    <form method="dialog" class="flex gap-2">
                        <button class="btn btn-success" @click="closeStayLoggedInDialog">Stay Logged In</button>
                        <button class="btn btn-error" @click="() => logout(false)">Logout</button>
                    </form>
                </div>
            </div>
        </dialog>

        <dialog ref="updateStoreDialog" class="modal">
            <update-store-form :stores="stores" :store="auth.store" @closeDialog="closeUpdateStoreDialog" />
        </dialog>
        <dialog ref="orderPaymentsDialog" class="modal">
            <order-payments-component
                :order="order"
                :total-amount="parseFloat(totalAmount)"
                :payment-methods="auth.store.paymentMethods"
                @order-payments-updated="handleOrderPaymentsUpdated"
                @close-dialog="closeOrderPaymentsDialog"
            />
        </dialog>
        <dialog ref="orderCustomerDialog" class="modal">
            <order-customer-component :order="order" @order-customer-updated="handleOrderCustomerUpdated" @close-dialog="closeOrderCustomerDialog" />
        </dialog>
        <dialog ref="payLaterOrdersDialog" class="modal">
            <PayLaterOrdersComponent :store="auth.store" @close-dialog="closePayLaterOrdersDialog" />
        </dialog>
    </teleport>
</template>
