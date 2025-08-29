<script setup>
import BaseLayout from '@/layouts/BaseLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    settings: {
        type: Object,
        required: true,
    },
    treeCategories: {
        type: Array,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
    },
    services: {
        type: Array,
        required: true,
    },
    title: {
        type: String,
        default: 'Account',
    },
    breadcrumbs: {
        type: Array,
        default: () => [],
    },
});
</script>
<template>
    <base-layout :auth="auth" :settings="settings" :tree-categories="treeCategories" :categories="categories" :services="services">
        <div class="container mx-auto max-w-7xl space-y-12 px-4 py-12">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h1 class="text-2xl">{{ title }}</h1>

                <div v-if="breadcrumbs.length" class="breadcrumbs text-sm">
                    <ul>
                        <li v-for="item in breadcrumbs">
                            <Link v-if="item.link" :href="item.link">
                                {{ item.title }}
                            </Link>
                            <span v-else>{{ item.title }}</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="grid min-h-[75vh] grid-cols-12 items-start gap-4">
                <div id="sidebar" class="hidden md:col-span-4 md:block lg:col-span-3">
                    <ul class="menu bg-base-100 rounded-box h-full w-full gap-y-2 py-6">
                        <li>
                            <Link :href="route('account.dashboard')">
                                <span class="inline-flex h-8 w-8 items-center justify-center"><font-awesome-icon size="xl" icon="home" /></span>
                                Account
                            </Link>
                        </li>
                        <li>
                            <Link :href="route('account.profile.show')">
                                <span class="inline-flex h-8 w-8 items-center justify-center">
                                    <font-awesome-icon size="xl" icon="user" />
                                </span>
                                Profile
                            </Link>
                        </li>
                        <li>
                            <Link :href="route('account.profile.show')">
                                <span class="inline-flex h-8 w-8 items-center justify-center">
                                    <font-awesome-icon size="xl" icon="cog" />
                                </span>
                                Settings
                            </Link>
                        </li>
                        <li>
                            <Link :href="route('account.orders.index')">
                                <span class="inline-flex h-8 w-8 items-center justify-center">
                                    <font-awesome-icon size="xl" icon="shopping-cart" />
                                </span>
                                Orders
                            </Link>
                        </li>
                    </ul>
                </div>
                <div id="content" class="col-span-12 md:col-span-8 lg:col-span-9">
                    <slot />
                </div>
            </div>
        </div>
    </base-layout>
</template>
