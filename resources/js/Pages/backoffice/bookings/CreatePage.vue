<script setup>
import AppCombobox from '@/components/AppCombobox.vue';
import useAutologout from '@/composables/useAutologout';
import useClients from '@/composables/useClients';
import useLogout from '@/composables/useLogout';
import useRooms from '@/composables/useRooms';
import useSwal from '@/composables/useSwal';

import { Head, Link, useForm } from '@inertiajs/vue3';
import { Store } from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    params: {
        type: Object,
        required: false,
    },
    reservations: {
        type: Array,
        default: () => [],
    },
    reservation: {
        type: Object,
        required: false,
        default: null,
    },
    feedback: {
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
        title: 'Bookings',
        href: route('backoffice.bookings.index'),
    },
    {
        title: 'New',
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

const occupiedHeight = computed(() => navbarHeight.value + titleHeight.value + topbarHeight.value + bottomHeight.value + 48);

const appName = import.meta.env.VITE_APP_NAME;

const guestBlueprint = {
    client: null,
    name: '',
    email: '',
    phone: '',
    address: '',
    id_number: '',
};

const allocationBlueprint = {
    r: null,
    room: '',
    start: '',
    end: '',
    occupants: 1,
    price: 0,
    discount: 0,
    amount: 0,
};

const addGuest = () => {
    form.guests.push({ ...guestBlueprint });
};

const removeGuest = (index) => {
    form.guests.splice(index, 1);
};

const addAllocation = () => {
    form.allocations.push({ ...allocationBlueprint });
};

const removeAllocation = (index) => {
    form.allocations.splice(index, 1);
};

const form = useForm({
    guests: [{ ...guestBlueprint }],
    allocations: [{ ...allocationBlueprint }],
    total_price: 0,
    checkin_date: '',
    checkout_date: '',
    guests_count: 1,
    rooms_count: 1,
    tendered_amount: 0,
    balance_amount: 0,
    total_amount: 0,
});

const toggleSidebar = () => {
    showSidebar.value = !showSidebar.value;
};

const closeStayLoggedInDialog = () => {
    if (stayLoggedInDialog.value) {
        stayLoggedInDialog.value.close();
    }
};

const { logout } = useLogout();

const { showFeedbackSwal, showInertiaErrorsSwal } = useSwal();

const { loadClients } = useClients();

const { loadRooms } = useRooms();

useAutologout({
    onLogout: () => logout(false),
    onWarning: () => {
        if (stayLoggedInDialog.value) {
            stayLoggedInDialog.value.showModal();
        }
        return true;
    },
});

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

const reference = ref(props.params?.reference);

const submit = () => {
    form.transform((data) => {
        data.guests = data.guests
            .filter((g) => g.client != null)
            .map((item) => {
                return {
                    id: item.client.value,
                    email: item.email,
                    phone: item.phone,
                    address: item.address,
                    kra_pin: item.kra_pin,
                    id_number: item.id_number,
                };
            });

        data.allocations = data.allocations
            .filter((a) => a.r != null)
            .map((item) => {
                return {
                    room: item.r.value,
                    start: item.start,
                    end: item.end,
                    occupants: item.occupants,
                    price: item.price,
                    discount: item.discount,
                    amount: item.amount,
                };
            });

        return data;
    }).post(route('backoffice.reservations.store'), {
        onError: (errors) => {
            showInertiaErrorsSwal(errors);
        },
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>

    <Head>
        <title>New Booking</title>
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
                            <span v-if="auth.user.unread_messages_count > 0"
                                class="badge badge-xs badge-secondary indicator-item size-4">
                                {{ auth.user.unread_messages_count }}
                            </span>
                        </div>
                    </a>

                    <div class="dropdown dropdown-end">
                        <button tabindex="0" class="btn btn-sm btn-ghost btn-circle">
                            <span class="indicator">
                                <font-awesome-icon icon="bell" size="xl" />
                                <span v-if="auth.user.unread_notifications_count > 0"
                                    class="badge badge-xs badge-secondary indicator-item size-4">
                                    {{ auth.user.unread_notifications_count }}
                                </span>
                            </span>
                        </button>
                        <ul tabindex="0"
                            class="menu menu-sm dropdown-content bg-base-100 rounded-box z-1 mt-3 w-64 p-2 shadow">
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
                        <ul tabindex="0"
                            class="menu menu-sm dropdown-content bg-base-100 rounded-box z-1 mt-3 w-52 p-2 shadow">
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
                    <h2 class="text-xl font-semibold">New Reservation</h2>

                    <div v-if="breadcrumbs.length" class="breadcrumbs my-0 py-0 text-sm lg:hidden">
                        <ul class="m-0">
                            <li v-for="(item, index) in breadcrumbs" :key="index">
                                <Link v-if="item.href" :href="item.href">{{ item.title }}</Link>
                                <span v-else>{{ item.title }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div ref="topbarElement" class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <label class="input input-xs w-full bg-[#0000fe] text-white">
                            <span class="label font-bold text-white"><font-awesome-icon icon="search"
                                    size="sm" /></span>
                            <input type="search" v-model="reference" placeholder="Search Order By Reference" />
                        </label>
                    </div>

                    <div class="scrollbar-thin w-full overflow-x-auto whitespace-nowrap lg:grow">
                        <div class="flex gap-3">
                            <Link v-for="o in reservations" :href="route('backoffice.pos', { reference: o.reference })"
                                class="btn btn-xs btn-primary"
                                :class="{ 'btn-outline': order?.reference !== o.reference }">
                            # {{ o.reference }}
                            </Link>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button class="btn btn-xs btn-square btn-primary btn-outline">
                            <font-awesome-icon icon="building" />
                        </button>
                    </div>
                </div>

                <form novalidate @submit.prevent="submit" class="flex max-h-full grow flex-col gap-4">
                    <div ref="itemsElement" class="scrollbar-thin grow overflow-y-auto">
                        <div class="grid h-full grid-cols-12 gap-4">
                            <div class="col-span-12 h-full lg:col-span-6">
                                <div class="card h-full bg-blue-500">
                                    <div class="card-body p-1">
                                        <div class="card-title">GUESTS</div>
                                        <div class="relative overflow-x-auto pb-48">
                                            <table class="table-xs table">
                                                <thead class="uppercase">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>CLIENT</th>
                                                        <th>EMAIL</th>
                                                        <th>PHONE</th>
                                                        <th>ID NO.</th>
                                                        <th>ADDRESS</th>
                                                        <th>KRA PIN</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <template v-if="form.guests.length">
                                                        <tr v-for="(guest, index) in form.guests" :key="index">
                                                            <th>{{ index + 1 }}</th>
                                                            <td class="">
                                                                <app-combobox v-model="guest.client"
                                                                    :load-options="loadClients" size="xs" />
                                                            </td>
                                                            <td class="space-y-1">
                                                                <input type="email" v-model="guest.email"
                                                                    class="input input-xs input-bordered min-w-36 md:w-full" />
                                                            </td>
                                                            <td class="space-y-1">
                                                                <input type="tel" v-model="guest.phone"
                                                                    class="input input-xs input-bordered min-w-36 md:w-full" />
                                                            </td>
                                                            <td>
                                                                <input type="text" v-model="guest.id_number"
                                                                    class="input input-xs input-bordered min-w-36 md:w-full" />
                                                            </td>
                                                            <td>
                                                                <input type="text" v-model="guest.address"
                                                                    class="input input-xs input-bordered min-w-36 md:w-full" />
                                                            </td>
                                                            <td>
                                                                <input type="text" v-model="guest.kra_pin"
                                                                    class="input input-xs input-bordered min-w-36 md:w-full" />
                                                            </td>
                                                            <td>
                                                                <button type="button" @click="removeGuest(index)"
                                                                    class="btn btn-xs btn-square btn-error">
                                                                    <font-awesome-icon icon="trash" size="sm" />
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    </template>
                                                    <template v-else>
                                                        <tr>
                                                            <td colspan="9" class="text-center">No guests added yet</td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="9">
                                                            <button type="button" @click="addGuest"
                                                                class="btn btn-xs btn-light">Add Guest</button>
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-span-12 h-full lg:col-span-6">
                                <div class="card h-full bg-blue-500">
                                    <div class="card-body p-1">
                                        <div class="card-title">ALLOCATIONS</div>
                                        <div class="relative overflow-x-auto pb-48">
                                            <table class="table-xs table">
                                                <thead class="uppercase">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>ROOM</th>
                                                        <th>START</th>
                                                        <th>END</th>
                                                        <th>OCC.</th>
                                                        <th>AMOUNT</th>
                                                        <th>DISC.</th>
                                                        <th>STATUS</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <template v-if="form.allocations.length">
                                                        <tr v-for="(allocation, index) in form.allocations"
                                                            :key="index">
                                                            <th>{{ index + 1 }}</th>
                                                            <td>
                                                                <app-combobox v-model="allocation.r"
                                                                    :load-options="loadRooms"
                                                                    :with-custom-option="false" size="xs" />
                                                            </td>
                                                            <td class="space-y-1">
                                                                <input type="date" v-model="allocation.start"
                                                                    class="input input-xs input-bordered min-w-36 md:w-full" />
                                                            </td>
                                                            <td class="space-y-1">
                                                                <input type="date" v-model="allocation.end"
                                                                    class="input input-xs input-bordered min-w-36 md:w-full" />
                                                            </td>
                                                            <td>
                                                                <input type="number" v-model="allocation.occupants"
                                                                    class="input input-xs input-bordered min-w-36 md:w-full" />
                                                            </td>
                                                            <td>
                                                                <input type="number" v-model="allocation.discount_rate"
                                                                    class="input input-xs input-bordered min-w-24 md:w-full" />
                                                            </td>
                                                            <td>
                                                                <input type="number" v-model="allocation.total_price"
                                                                    class="input input-xs input-bordered min-w-36 md:w-full" />
                                                            </td>
                                                            <td>
                                                                <button type="button" @click="removeAllocation(index)"
                                                                    class="btn btn-xs btn-square btn-error">
                                                                    <font-awesome-icon icon="trash" size="sm" />
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    </template>
                                                    <template v-else>
                                                        <tr>
                                                            <td colspan="9" class="text-center">No allocations added yet
                                                            </td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="9">
                                                            <button type="button" @click="addAllocation"
                                                                class="btn btn-xs btn-light">
                                                                Add Allocation
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div ref="bottomElement" class="grid grid-cols-12 gap-4">
                        <div class="col-span-12 md:col-span-4">
                            <div class="card bg-[#00ff01] shadow">
                                <div class="card-body space-y-2 p-1 pb-4">
                                    <div class="overflow-x-auto">
                                        <table class="table-xs table">
                                            <tbody>
                                                <tr>
                                                    <th>CHECK IN</th>
                                                    <td>
                                                        <input v-model="form.checkin_date" type="date" step="0.50"
                                                            placeholder="Enter Check-in Date"
                                                            class="input input-bordered input-xs w-full font-semibold" />
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>CHECK OUT</th>
                                                    <td>
                                                        <input v-model="form.checkout_date" type="date" step="0.50"
                                                            placeholder="Enter Check-out Date"
                                                            class="input input-bordered input-xs w-full font-semibold" />
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>SOURCE</th>
                                                    <td>
                                                        <input v-model="form.source" type="text"
                                                            placeholder="Enter Booking Source"
                                                            class="input input-bordered input-xs w-full font-semibold" />
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>STATUS</th>
                                                    <td>
                                                        <select v-model="form.status"
                                                            class="input input-bordered input-xs w-full font-semibold">
                                                            <option value="pending">Pending</option>
                                                            <option value="confirmed">Confirmed</option>
                                                            <option value="checked_in">Checked In</option>
                                                            <option value="checked_out">Checked Out</option>
                                                            <option value="cancelled">Cancelled</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 md:col-span-4">
                            <div class="card bg-[#00ff01] shadow">
                                <div class="card-body space-y-2 p-1 pb-4">
                                    <div class="overflow-x-auto">
                                        <table class="table-xs table">
                                            <tbody>
                                                <tr>
                                                    <th>AMOUNT</th>
                                                    <td>
                                                        <input v-model="form.amount" type="number" step="0.50"
                                                            placeholder="Enter Amount"
                                                            class="input input-bordered input-xs w-full font-semibold" />
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>TENDERED</th>
                                                    <td>
                                                        <input v-model="form.tendered" type="number" step="0.50"
                                                            placeholder="Enter Tendered Amount"
                                                            class="input input-bordered input-xs w-full font-semibold" />
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>BALANCE</th>
                                                    <td>
                                                        <input v-model="form.balance" type="number" step="0.50"
                                                            placeholder="Enter Balance Amount"
                                                            class="input input-bordered input-xs w-full font-semibold" />
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>STATUS</th>
                                                    <td>
                                                        <select v-model="form.status"
                                                            class="input input-bordered input-xs w-full font-semibold">
                                                            <option value="pending">Pending</option>
                                                            <option value="confirmed">Confirmed</option>
                                                            <option value="checked_in">Checked In</option>
                                                            <option value="checked_out">Checked Out</option>
                                                            <option value="cancelled">Cancelled</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 md:col-span-4">
                            <div class="card h-full bg-[#000000] shadow">
                                <div class="card-body space-y-2 p-1">
                                    <div class="grid grid-cols-5 gap-2">
                                        <a :href="'#'"
                                            class="hover:bg-primary btn-outline flex aspect-square w-full flex-col items-center justify-center gap-3 rounded bg-white p-2 hover:text-white">
                                            <span class="font-bold uppercase">New</span>
                                        </a>
                                        <button type="submit"
                                            class="hover:bg-primary btn-outline flex aspect-square w-full flex-col items-center justify-center gap-3 rounded bg-white p-2 hover:text-white">
                                            <span class="font-bold uppercase">Save</span>
                                        </button>
                                        <button type="button"
                                            class="hover:bg-primary btn-outline flex aspect-square w-full flex-col items-center justify-center gap-3 rounded bg-white p-2 hover:text-white">
                                            <span class="font-bold uppercase">Hold</span>
                                        </button>
                                        <button type="button"
                                            class="hover:bg-primary btn-outline flex aspect-square w-full flex-col items-center justify-center gap-3 rounded bg-white p-2 hover:text-white">
                                            <span class="font-bold uppercase">Print</span>
                                        </button>
                                        <button type="button"
                                            class="hover:bg-primary btn-outline flex aspect-square w-full flex-col items-center justify-center gap-3 rounded bg-white p-2 hover:text-white">
                                            <span class="font-bold uppercase">Cancel</span>
                                        </button>
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
            <ul
                class="menu text-base-content scrollbar-thin relative h-full min-h-full w-96 flex-nowrap overflow-y-auto bg-[#a0d9ef] p-2">
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
    </teleport>
</template>
