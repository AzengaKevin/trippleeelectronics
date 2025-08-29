<script setup>
import UpdatePasswordSection from '@/components/UpdatePasswordSection.vue';
import UpdateProfileInformationSection from '@/components/UpdateProfileInformationSection.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

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

const breadcrumbs = [
    {
        title: 'Account',
        link: route('account.dashboard'),
    },
    {
        title: 'Profile',
        link: null,
    },
];
</script>
<template>
    <Head>
        <title>Profile</title>
    </Head>
    <app-layout
        :auth="auth"
        :settings="settings"
        :tree-categories="treeCategories"
        :categories="categories"
        :services="services"
        :breadcrumbs="breadcrumbs"
        title="Profile"
    >
        <div class="space-y-4">
            <update-profile-information-section />

            <update-password-section />
        </div>
    </app-layout>
</template>
