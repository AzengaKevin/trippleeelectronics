<script setup>
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    feedback: {
        type: Object,
        required: false,
        default: null,
    },
});

const breadcrumbs = [
    { title: 'Dashboard', href: route('backoffice.dashboard') },
    { title: 'Bookings', href: route('backoffice.bookings.index') },
    { title: 'New', href: null },
];

const form = reactive({
    guest: {
        name: '',
        email: '',
        phone: '',
    },
    stay: {
        checkIn: '',
        checkOut: '',
        rooms: 1,
    },
    rooms: [],
});

const generateRooms = () => {
    form.rooms = [];
    for (let i = 0; i < form.stay.rooms; i++) {
        form.rooms.push({
            guestCount: 1,
            guestNames: '',
        });
    }
};

watch(
    () => form.stay.rooms,
    (newRooms) => {
        generateRooms();
    },
    {
        immediate: true,
    },
);
</script>

<template>
    <Head>
        <title>New Booking</title>
    </Head>
    <BackofficeLayout :breadcrumbs="breadcrumbs" title="New Booking">
        <div class="grid grid-cols-4 gap-6">
            <div class="card bg-base-100">
                <div class="card-body space-y-3">
                    <h2 class="text-xl font-bold">Primary Guest Information</h2>
                    <div class="space-y-2">
                        <label class="label">Full Name</label>
                        <input v-model="form.guest.name" type="text" placeholder="John Doe" class="input input-bordered" />
                    </div>
                    <div class="space-y-2">
                        <label class="label">Email</label>
                        <input v-model="form.guest.email" type="email" placeholder="john@example.com" class="input input-bordered" />
                    </div>
                    <div class="space-y-2">
                        <label class="label">Phone</label>
                        <input v-model="form.guest.phone" type="tel" placeholder="+254 712 345 678" class="input input-bordered" />
                    </div>
                </div>
            </div>

            <div class="card bg-base-100">
                <div class="card-body space-y-3">
                    <h2 class="text-xl font-bold">Stay Details</h2>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-2">
                            <label class="label">Check-in</label>
                            <input v-model="form.stay.checkIn" type="date" class="input input-bordered" />
                        </div>
                        <div class="space-y-2">
                            <label class="label">Check-out</label>
                            <input v-model="form.stay.checkOut" type="date" class="input input-bordered" />
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="label">Number of Rooms</label>
                        <input v-model.number="form.stay.rooms" type="number" min="1" class="input input-bordered" />
                    </div>
                </div>
            </div>

            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="mb-4 text-xl font-bold">Rooms & Guests</h2>
                    <div v-for="(room, index) in form.rooms" :key="index" class="card bg-base-100 mb-4 p-4 shadow">
                        <h3 class="mb-2 font-bold">Room {{ index + 1 }}</h3>
                        <div class="form-control mb-2">
                            <label class="label">Number of Guests</label>
                            <input v-model.number="room.guestCount" type="number" min="1" class="input input-bordered" />
                        </div>
                        <div class="space-y-2">
                            <label class="label">Guest Names</label>
                            <textarea v-model="room.guestNames" class="textarea textarea-bordered" placeholder="Enter guest names"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100">
                <div class="card-body space-y-3">
                    <h2 class="mb-4 text-xl font-bold">Review & Confirm</h2>
                    <div class="bg-base-200 space-y-2 rounded-lg border p-4">
                        <p><strong>Guest:</strong> {{ form.guest.name }} ({{ form.guest.email }})</p>
                        <p><strong>Phone:</strong> {{ form.guest.phone }}</p>
                        <p><strong>Stay:</strong> {{ form.stay.checkIn }} → {{ form.stay.checkOut }}</p>
                        <p><strong>Rooms:</strong> {{ form.stay.rooms }}</p>
                        <ul class="list-inside list-disc">
                            <li v-for="(room, index) in form.rooms" :key="index">
                                Room {{ index + 1 }}: {{ room.guestCount }} guests – {{ room.guestNames }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
