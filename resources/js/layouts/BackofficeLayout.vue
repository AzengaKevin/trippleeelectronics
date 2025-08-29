<script setup>
import useAutologout from '@/composables/useAutologout';
import useLogout from '@/composables/useLogout';
import { Link, router, usePage } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import { Store } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    title: {
        type: String,
        default: 'Dashboard',
    },
    breadcrumbs: {
        type: Array,
        default: () => [],
    },
});

const showSidebar = ref(false);

const stayLoggedInDialog = ref(null);

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

const query = ref(auth.value.params?.search_resource || '');

watch(
    query,
    debounce((newQuery) => {
        router.get(
            route('backoffice.dashboard'),
            {
                search_resource: newQuery,
            },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    }, 300),
);
</script>
<template>
    <div class="drawer bg-base-200" ref="wrapper">
        <input id="my-drawer" v-model="showSidebar" type="checkbox" class="drawer-toggle" />
        <div class="drawer-content min-h-screen bg-[#1bbe49]">
            <div class="bg-base-100 sticky inset-x-0 top-0 z-[1] flex gap-2 px-2 py-1 shadow-sm">
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
                    <Link :href="route('backoffice.messages.index')" class="btn btn-sm btn-ghost btn-circle">
                        <div class="indicator">
                            <font-awesome-icon icon="envelope" size="xl" />
                            <span v-if="auth.user.unread_messages_count > 0" class="badge badge-xs badge-secondary indicator-item size-4">
                                {{ auth.user.unread_messages_count }}
                            </span>
                        </div>
                    </Link>
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
            <div class="space-y-2 p-2">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 class="text-xl font-semibold">{{ title }}</h2>
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

                <slot />
            </div>
        </div>
        <div class="drawer-side z-[2]">
            <button type="button" @click="toggleSidebar" aria-label="close sidebar" class="drawer-overlay"></button>
            <ul class="menu text-base-content scrollbar-thin relative h-full min-h-full w-96 flex-nowrap overflow-y-auto bg-[#a0d9ef] p-2">
                <li class="sticky start-0 end-0 top-0 z-[1] w-full bg-[#a0d9ef]">
                    <Link :href="route('welcome')" class="item-center flex gap-2">
                        <Store />
                        <span class="text-2xl font-bold">{{ appName }}</span>
                    </Link>
                </li>

                <li v-for="(resource, index) in auth.resources" :key="index">
                    <Link :href="route(resource.route_name)">
                        <span class="flex h-4 w-4 items-center justify-center">
                            <font-awesome-icon :icon="resource.icon" />
                        </span>
                        <span class="font-semibold">{{ resource.name }}</span>
                    </Link>
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
