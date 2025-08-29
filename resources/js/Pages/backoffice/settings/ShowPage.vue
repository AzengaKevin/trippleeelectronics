<script setup>
import AppAlert from '@/components/AppAlert.vue';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import GeneralSettingsForm from '@/Pages/backoffice/settings/GeneralSettingsForm.vue';
import PaymentSettingsForm from '@/Pages/backoffice/settings/PaymentSettingsForm.vue';
import ReceiptSettingsForm from '@/Pages/backoffice/settings/ReceiptSettingsForm.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    feedback: {
        type: Object,
        default: null,
    },
    settings: {
        type: Object,
        required: true,
    },
    groups: {
        type: Array,
        default: () => [],
    },
    paymentMethods: {
        type: Array,
        default: () => [],
    },
    params: {
        type: Object,
        default: () => ({}),
    },
});

const breadcrumbItems = [
    {
        title: 'Dashboard',
        href: route('backoffice.dashboard'),
    },
    {
        title: 'Settings',
        href: null,
    },
];

const currentGroup = computed(() => props.groups.find((group) => group.value === props.params.group));
</script>
<template>
    <Head>
        <title>Settings</title>
    </Head>

    <backoffice-layout :breadcrumbs="breadcrumbItems" :title="currentGroup ? currentGroup.label : 'Settings'">
        <div class="space-y-4">
            <app-alert :feedback="feedback" />

            <div class="items-starts grid grid-cols-12 gap-4">
                <div class="col-span-12 md:col-span-8 lg:col-span-9">
                    <template v-if="params.group === 'general'">
                        <GeneralSettingsForm :settings="settings" />
                    </template>
                    <template v-else-if="params.group === 'receipt'">
                        <ReceiptSettingsForm :settings="settings" />
                    </template>
                    <template v-else-if="params.group === 'payment'">
                        <PaymentSettingsForm :settings="settings" :payment-methods="paymentMethods" />
                    </template>
                    <template v-else>
                        <div class="card bg-base-100 shadow">
                            <div class="card-body lg-p-6 p-2 sm:p-4">
                                <p class="text-gray-500">No specific settings available for this group yet.</p>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="col-span-12 md:col-span-4 lg:col-span-3">
                    <div class="card bg-base-100 shadow">
                        <div class="card-body lg-p-6 p-2 sm:p-4">
                            <ul class="menu menu-primary bg-base-200 rounded-box w-full gap-4">
                                <li v-for="group in groups" :key="group.value">
                                    <Link
                                        :href="route('backoffice.settings.show', { group: group.value })"
                                        :class="{ 'bg-primary text-primary-content': group.value === params.group }"
                                        >{{ group.label }}
                                    </Link>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </backoffice-layout>
</template>
