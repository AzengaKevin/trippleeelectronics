<script setup>
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, ref, watch } from 'vue';

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



const step = ref(1)

const form = reactive({
    guest: {
        name: "",
        email: "",
        phone: "",
    },
    stay: {
        checkIn: "",
        checkOut: "",
        rooms: 1,
    },
    rooms: [],
})

const nextStep = () => {
    if (step.value < 4) step.value++
}

const prevStep = () => {
    if (step.value > 1) step.value--
}

const generateRooms = () => {
    form.rooms = []
    for (let i = 0; i < form.stay.rooms; i++) {
        form.rooms.push({
            guestCount: 1,
            guestNames: "",
        })
    }
    nextStep()
}

</script>

<template>

    <Head>
        <title>New Booking</title>
    </Head>
    <BackofficeLayout :breadcrumbs="breadcrumbs" title="New Booking">

        <div class="card">
            <div class="card-body">

                <div class="p-6 bg-base-100 rounded-2xl shadow-xl">
                    <!-- Progress Steps -->
                    <ul class="steps w-full mb-6">
                        <li class="step" :class="{ 'step-primary': step >= 1 }">Guest Info</li>
                        <li class="step" :class="{ 'step-primary': step >= 2 }">Stay Details</li>
                        <li class="step" :class="{ 'step-primary': step >= 3 }">Rooms & Guests</li>
                        <li class="step" :class="{ 'step-primary': step >= 4 }">Confirm</li>
                    </ul>

                    <!-- Step 1 -->
                    <div v-if="step === 1" class="step-card">
                        <h2 class="text-xl font-bold mb-4">👤 Guest Information</h2>
                        <div class="form-control mb-4">
                            <label class="label">Full Name</label>
                            <input v-model="form.guest.name" type="text" placeholder="John Doe"
                                class="input input-bordered" />
                        </div>
                        <div class="form-control mb-4">
                            <label class="label">Email</label>
                            <input v-model="form.guest.email" type="email" placeholder="john@example.com"
                                class="input input-bordered" />
                        </div>
                        <div class="form-control mb-4">
                            <label class="label">Phone</label>
                            <input v-model="form.guest.phone" type="tel" placeholder="+254 712 345 678"
                                class="input input-bordered" />
                        </div>
                        <button @click="nextStep" class="btn btn-primary">Next</button>
                    </div>

                    <!-- Step 2 -->
                    <div v-if="step === 2" class="step-card">
                        <h2 class="text-xl font-bold mb-4">📅 Stay Details</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label">Check-in</label>
                                <input v-model="form.stay.checkIn" type="date" class="input input-bordered" />
                            </div>
                            <div class="form-control">
                                <label class="label">Check-out</label>
                                <input v-model="form.stay.checkOut" type="date" class="input input-bordered" />
                            </div>
                        </div>
                        <div class="form-control mt-4">
                            <label class="label">Number of Rooms</label>
                            <input v-model.number="form.stay.rooms" type="number" min="1"
                                class="input input-bordered" />
                        </div>
                        <div class="flex justify-between mt-6">
                            <button @click="prevStep" class="btn btn-ghost">Back</button>
                            <button @click="generateRooms" class="btn btn-primary">Next</button>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div v-if="step === 3" class="step-card">
                        <h2 class="text-xl font-bold mb-4">🛏️ Rooms & Guests</h2>
                        <div v-for="(room, index) in form.rooms" :key="index" class="card bg-base-100 shadow p-4 mb-4">
                            <h3 class="font-bold mb-2">Room {{ index + 1 }}</h3>
                            <div class="form-control mb-2">
                                <label class="label">Number of Guests</label>
                                <input v-model.number="room.guestCount" type="number" min="1"
                                    class="input input-bordered" />
                            </div>
                            <div class="form-control">
                                <label class="label">Guest Names</label>
                                <textarea v-model="room.guestNames" class="textarea textarea-bordered"
                                    placeholder="Enter guest names"></textarea>
                            </div>
                        </div>
                        <div class="flex justify-between mt-6">
                            <button @click="prevStep" class="btn btn-ghost">Back</button>
                            <button @click="nextStep" class="btn btn-primary">Next</button>
                        </div>
                    </div>

                    <!-- Step 4 -->
                    <div v-if="step === 4" class="step-card">
                        <h2 class="text-xl font-bold mb-4">✅ Review & Confirm</h2>
                        <div class="p-4 border rounded-lg bg-base-200 space-y-2">
                            <p><strong>Guest:</strong> {{ form.guest.name }} ({{ form.guest.email }})</p>
                            <p><strong>Phone:</strong> {{ form.guest.phone }}</p>
                            <p><strong>Stay:</strong> {{ form.stay.checkIn }} → {{ form.stay.checkOut }}</p>
                            <p><strong>Rooms:</strong> {{ form.stay.rooms }}</p>
                            <ul class="list-disc list-inside">
                                <li v-for="(room, index) in form.rooms" :key="index">
                                    Room {{ index + 1 }}: {{ room.guestCount }} guests – {{ room.guestNames }}
                                </li>
                            </ul>
                        </div>
                        <div class="flex justify-between mt-6">
                            <button @click="prevStep" class="btn btn-ghost">Back</button>
                            <button class="btn btn-success">Confirm Reservation</button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </BackofficeLayout>
</template>
