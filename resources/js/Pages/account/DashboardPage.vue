<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

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
});
</script>
<template>
    <Head>
        <title>Account Dashboard</title>
    </Head>
    <app-layout :auth="auth" :settings="settings" :tree-categories="treeCategories" :categories="categories" :services="services">
        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
            <!-- Profile Card -->
            <Link :href="route('account.dashboard')" class="card bg-base-100 shadow transition hover:shadow-md">
                <div class="card-body flex flex-row items-start">
                    <font-awesome-icon icon="home" class="text-primary text-4xl" />
                    <div class="ml-4">
                        <h2 class="card-title">Account</h2>
                        <p>Your account dashboard.</p>
                    </div>
                </div>
            </Link>

            <Link :href="route('account.profile.show')" class="card bg-base-100 shadow transition hover:shadow-md">
                <div class="card-body flex flex-row items-start">
                    <font-awesome-icon icon="user" class="text-primary text-4xl" />
                    <div class="ml-4">
                        <h2 class="card-title">Profile</h2>
                        <p>Update your profile.</p>
                    </div>
                </div>
            </Link>

            <Link :href="route('account.profile.show')" class="card bg-base-100 shadow transition hover:shadow-md">
                <div class="card-body flex flex-row items-start">
                    <font-awesome-icon icon="cog" class="text-primary text-4xl" />
                    <div class="ml-4">
                        <h2 class="card-title">User Settings</h2>
                        <p>Manage your settings.</p>
                    </div>
                </div>
            </Link>

            <Link :href="route('account.orders.index')" class="card bg-base-100 shadow transition hover:shadow-md">
                <div class="card-body flex flex-row items-start">
                    <font-awesome-icon icon="shopping-cart" class="text-primary text-4xl" />
                    <div class="ml-4">
                        <h2 class="card-title">Orders</h2>
                        <p>Your order history.</p>
                    </div>
                </div>
            </Link>

            <Link
                v-if="auth.permissions.includes('access-pos')"
                :href="route('backoffice.pos')"
                class="card bg-base-100 shadow transition hover:shadow-md"
            >
                <div class="card-body flex flex-row items-start">
                    <font-awesome-icon icon="cash-register" class="text-primary text-4xl" />
                    <div class="ml-4">
                        <h2 class="card-title">POS</h2>
                        <p>Point of Sale system.</p>
                    </div>
                </div>
            </Link>

            <Link
                v-if="auth.permissions.includes('access-backoffice')"
                :href="route('backoffice.dashboard')"
                class="card bg-base-100 shadow transition hover:shadow-md"
            >
                <div class="card-body flex flex-row items-start">
                    <font-awesome-icon icon="tachometer-alt" class="text-primary text-4xl" />
                    <div class="ml-4">
                        <h2 class="card-title">Backoffice</h2>
                        <p>Access the backoffice.</p>
                    </div>
                </div>
            </Link>
        </div>
    </app-layout>
</template>
