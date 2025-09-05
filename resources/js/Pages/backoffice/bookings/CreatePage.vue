<script setup>
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import GuestAndRoomsStep from '@/Pages/backoffice/bookings/steps/GuestAndRoomsStep.vue';
import PrimaryGuestStep from '@/Pages/backoffice/bookings/steps/PrimaryGuestStep.vue';
import ReviewStep from '@/Pages/backoffice/bookings/steps/ReviewStep.vue';
import StayDetailsStep from '@/Pages/backoffice/bookings/steps/StayDetailsStep.vue';
import { Head, router } from '@inertiajs/vue3';
import { useLocalStorage } from '@vueuse/core';
import { computed, markRaw, ref } from 'vue';

const NEW_BOOKING_DETAILS_KEY = 'new-booking-details';

const props = defineProps({
    feedback: {
        type: Object,
        default: null,
    },
});

const breadcrumbs = [
    { title: 'Dashboard', href: route('backoffice.dashboard') },
    { title: 'Bookings', href: route('backoffice.bookings.index') },
    { title: 'New Booking', href: null },
];

const steps = [
    {
        id: 1,
        label: 'Primary Guest Information',
        icon: 'fa-solid fa-user',
        component: markRaw(PrimaryGuestStep),
    },
    {
        id: 2,
        label: 'Stay Details',
        icon: 'fa-solid fa-bed',
        component: markRaw(StayDetailsStep),
    },
    {
        id: 3,
        label: 'Guests & Rooms',
        icon: 'fa-solid fa-user-group',
        component: markRaw(GuestAndRoomsStep),
    },
    {
        id: 4,
        label: 'Review',
        icon: 'fa-solid fa-square-check',
        component: markRaw(ReviewStep),
    },
];

const currentStep = ref(steps[0]);

const state = useLocalStorage(
    NEW_BOOKING_DETAILS_KEY,
    {
        multiple: false,
        primary_guest: {
            client: null,
            email: '',
            phone: '',
            address: '',
            kra_pin: '',
            type: '',
        },
        checkin_date: '',
        checkout_date: '',
        adults: 1,
        children: 0,
        infants: 0,
        rooms: 1,
        guest_rooms: [
            {
                client: null,
                email: '',
                phone: '',
                address: '',
                kra_pin: '',
                room: null,
            },
        ],
    },
    { mergeDefaults: true },
);

const percentageProgress = computed(() => {
    return Math.round((currentStep.value.id / steps.length) * 100);
});

const nextStep = () => {
    const currentStepId = currentStep.value.id;

    const nextStepId = currentStepId + 1;

    const maybeNextStep = steps.find((step) => step.id === nextStepId);

    if (maybeNextStep) currentStep.value = maybeNextStep;
};

const previousStep = () => {
    const currentStepId = currentStep.value.id;

    const previousStepId = currentStepId - 1;

    const maybePreviousStep = steps.find((step) => step.id === previousStepId);

    if (maybePreviousStep) currentStep.value = maybePreviousStep;
};

const updateState = (value) => {
    state.value = {
        ...state.value,
        ...value,
    };
};

const submitProperty = () => {
    router.post(
        route('properties.store'),
        {
            ...state.value,
            location_name: state.value.location_name?.label,
        },
        {
            preserveScroll: true,
            preserveState: true,
            onError: (errors) => {
                console.log(errors);

                // Set the error on a global property that can be user
            },
        },
    );
};
</script>
<template>
    <Head>
        <title>New Booking</title>
    </Head>

    <BackofficeLayout title="New Booking" :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-7xl">
            <div class="flex flex-col gap-4">
                <ul class="steps">
                    <li v-for="step in steps" :key="step.id" :class="['step', { 'step-primary': step.id <= currentStep.id }]">
                        <div class="text-primary flex flex-col gap-2 p-3">
                            <font-awesome-icon :icon="step.icon" size="xl" />
                            <span class="font-semibold uppercase">{{ step.label }}</span>
                        </div>
                    </li>
                </ul>

                <progress class="progress-bar progress-accent w-full" :value="percentageProgress" max="100"></progress>

                <div>
                    <component
                        v-if="currentStep"
                        :is="currentStep.component"
                        :state="state"
                        :previousStep="previousStep"
                        :nextStep="nextStep"
                        @update-state="updateState"
                        :submitProperty="submitProperty"
                    />
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
