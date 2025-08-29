<script setup>
import useAutologout from '@/composables/useAutologout';
import useDate from '@/composables/useDate';
import useLogout from '@/composables/useLogout';
import useSwal from '@/composables/useSwal';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import { useIntersectionObserver } from '@vueuse/core';
import axios from 'axios';
import debounce from 'lodash/debounce';
import { Store } from 'lucide-vue-next';
import { nextTick, onMounted, reactive, ref, watch } from 'vue';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    thread: {
        type: Object,
        required: true,
    },
    messages: {
        type: Object,
        required: true,
    },
    params: {
        type: Object,
        required: true,
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
        title: 'Messages',
        href: route('backoffice.messages.index'),
    },
    {
        title: 'Chats',
        href: null,
    },
];

const { formatDate } = useDate();

const { showNotificationSwal } = useSwal();

const filters = reactive({ ...props.params });

const localMessages = ref(props.messages.data);

const reversedMessages = ref([]);

const messagesContainer = ref(null);

const loadingOlderMessages = ref(false);

const messageRefs = new Map();

// Form for marking as read
const markAsReadForm = useForm({});

function markMessageAsRead(message) {
    if (!message.read_at && message.user.id !== props.auth.user.id) {
        markAsReadForm.put(route('backoffice.messages.read', message.id), {
            preserveScroll: true,
            preserveState: true,
        });
    }
}

function setMessageRef(el, message) {
    if (!el) return;

    messageRefs.set(message.id, el);

    useIntersectionObserver(
        el,
        ([{ isIntersecting }]) => {
            if (isIntersecting) {
                markMessageAsRead(message);
            }
        },
        { threshold: 0.75 },
    );
}

const loadOlderMessages = async () => {
    if (loadingOlderMessages.value) return;

    loadingOlderMessages.value = true;

    const oldScrollHeight = messagesContainer.value.scrollHeight;

    console.log('Loading older messages...');

    const newMessages = await axios.get(route('api.messages.index', { before: reversedMessages.value[0]?.id }));

    localMessages.value.unshift(...newMessages.data.data);

    await nextTick();

    // Maintain scroll position
    const newScrollHeight = messagesContainer.value.scrollHeight;
    messagesContainer.value.scrollTop = newScrollHeight - oldScrollHeight;

    loadingOlderMessages.value = false;
};

const handleScroll = () => {
    if (messagesContainer.value.scrollTop <= 50 && !loadingOlderMessages.value) {
        loadOlderMessages();
    }
};

watch(
    () => filters,
    debounce((newFilters) => {
        router.get(route('backoffice.messages.show', [props.thread.id]), newFilters);
    }, 300),
    { deep: true },
);

const form = useForm({
    thread_id: props.thread?.id,
    message: '',
    file: null,
});

const submit = () => {
    form.post(route('backoffice.messages.store'), {
        onSuccess: () => {
            form.reset();

            scrollMessageContainerToTheBottom();
        },
    });
};

const showSidebar = ref(false);

const stayLoggedInDialog = ref(null);

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

const addNewMessage = (message) => {
    const exists = localMessages.value.find((msg) => msg.id === message.id);

    if (!exists) {
        localMessages.value.push(message);

        scrollMessageContainerToTheBottom();
    }
};

const scrollMessageContainerToTheBottom = () => {
    nextTick(() => {
        const container = document.getElementById('messages-container');

        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    });
};

watch(
    () => props.messages,
    (newMessages) => {
        localMessages.value = [...newMessages.data];
    },
);

onMounted(() => {
    scrollMessageContainerToTheBottom();

    messagesContainer.value.addEventListener('scroll', handleScroll);
});

watch(
    localMessages,
    (messages) => {
        reversedMessages.value = messages.reverse();
    },
    {
        immediate: true,
    },
);

useEcho(`threads.${props.thread.id}`, 'NewMessageEvent', (e) => {
    if (e.message.thread_id === props.thread.id && e.message.user_id !== props.auth.user.id) {
        showNotificationSwal({
            message: 'New message received',
            type: 'info',
        });

        addNewMessage(e.message);
    }
});
</script>
<template>
    <Head>
        <title>Messages</title>
    </Head>
    <div class="drawer bg-base-200" ref="wrapper">
        <input id="my-drawer" v-model="showSidebar" type="checkbox" class="drawer-toggle" />
        <div class="drawer-content flex max-h-screen min-h-screen flex-col bg-[#1bbe49]">
            <div class="bg-base-100 sticky inset-x-0 top-0 z-[1] flex gap-2 px-2 py-1 shadow-sm">
                <div class="flex-none">
                    <button type="button" @click="toggleSidebar" class="btn btn-sm btn-square drawer-button">
                        <font-awesome-icon icon="bars" />
                    </button>
                </div>
                <div class="flex-1">
                    <div class="breadcrumbs hidden text-sm lg:block">
                        <ul>
                            <li v-for="(item, index) in breadcrumbs" :key="index">
                                <Link v-if="item.href" :href="item.href" class="text-primary">{{ item.title }}</Link>
                                <span v-else class="hover:no-underline">{{ item.title }}</span>
                            </li>
                        </ul>
                    </div>
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
            <!-- 292e2a -->
            <div class="flex h-full grow flex-col overflow-hidden bg-[#1bbe49]">
                <div ref="messagesContainer" id="messages-container" class="h-full grow overflow-y-auto p-2">
                    <div
                        v-for="message in reversedMessages"
                        :class="['chat', message.user.id === auth.user.id ? 'chat-end' : 'chat-start']"
                        :ref="(el) => setMessageRef(el, message)"
                        :key="message.id"
                    >
                        <div class="chat-image avatar">
                            <div class="w-10 rounded-full">
                                <img :src="message.user.avatar_url" />
                            </div>
                        </div>
                        <div class="chat-header font-bold text-white uppercase">
                            {{ message.user.name }}
                            <time class="ml-2 text-xs opacity-50">{{ formatDate(message.created_at, 'hh:mm A') }}</time>
                        </div>
                        <div class="chat-bubble">{{ message.message }}</div>
                    </div>
                </div>

                <div class="border-t bg-gray-50 p-2">
                    <form class="join w-full" @submit.prevent="submit">
                        <!-- File Upload Button -->
                        <label class="btn btn-primary btn-outline join-item">
                            <font-awesome-icon icon="paperclip" />
                            <span class="sr-only">Attach File</span>
                            <input type="file" class="hidden" id="file-upload" @change="(event) => (form.file = event.target.files[0])" />
                        </label>

                        <!-- Message Input -->
                        <div class="relative flex-grow">
                            <input
                                type="text"
                                placeholder="Type a message..."
                                v-model="form.message"
                                class="input input-bordered w-full pr-12"
                                :class="{ 'input-error': !!form.errors.message }"
                            />
                        </div>

                        <!-- Send Button -->
                        <button type="submit" class="btn btn-primary join-item" :disabled="form.processing">
                            <font-awesome-icon icon="paper-plane" />
                            <span class="sr-only">Send</span>
                        </button>
                    </form>
                </div>
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
