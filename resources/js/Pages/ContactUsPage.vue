<script setup>
import AppAlert from '@/components/AppAlert.vue';
import InputError from '@/components/InputError.vue';
import BaseLayout from '@/layouts/BaseLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    settings: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
    },
    treeCategories: {
        type: Array,
        required: true,
    },
    services: {
        type: Array,
        required: true,
    },
    feedback: {
        type: Object,
        required: false,
    },
});

const appName = import.meta.env.VITE_APP_NAME;

const form = useForm({
    name: '',
    phone: '',
    email: '',
    message: '',
});

const submit = () => {
    form.post(route('contact-us'), {
        onSuccess: () => {
            form.reset();
        },
        preserveScroll: true,
        preserveState: true,
    });
};
</script>
<template>
    <Head>
        <title>Contact Us</title>
    </Head>
    <base-layout :auth="auth" :settings="settings" :categories="categories" :services="services" :tree-categories="treeCategories">
        <main class="container mx-auto max-w-6xl space-y-12 px-4 py-12">
            <div class="space-y-3">
                <h1 class="text-primary text-4xl font-bold">Contact Us</h1>
                <div class="divider divider-primary w-24"></div>
                <p class="text-lg">Your trusted partner for comprehensive IT solutions since 2018</p>
            </div>

            <section class="mb-16">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <div class="card bg-base-100 shadow transition-shadow hover:shadow-xl">
                        <div class="card-body items-center text-center">
                            <div class="text-primary mb-4">
                                <font-awesome-icon icon="phone" size="3x" />
                            </div>
                            <h3 class="text-xl font-semibold">Phone</h3>
                            <p class="text-lg">{{ settings.phone }}</p>
                        </div>
                    </div>

                    <div class="card bg-base-100 shadow transition-shadow hover:shadow-xl">
                        <div class="card-body items-center text-center">
                            <div class="text-primary mb-4">
                                <font-awesome-icon icon="envelope" size="3x" />
                            </div>
                            <h3 class="text-xl font-semibold">Email</h3>
                            <p class="text-lg">{{ settings.email }}</p>
                        </div>
                    </div>

                    <div class="card bg-base-100 shadow transition-shadow hover:shadow-xl">
                        <div class="card-body items-center text-center">
                            <div class="text-primary mb-4">
                                <font-awesome-icon icon="location-pin" size="3x" />
                            </div>
                            <h3 class="text-xl font-semibold">Address</h3>
                            <p class="text-lg">{{ settings.location }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="card bg-base-100">
                <div class="card-body space-y-6">
                    <h2 class="text-3xl font-semibold">Leave A Message</h2>

                    <app-alert :feedback="feedback" />

                    <form @submit.prevent="submit" class="max-w-xl space-y-6" novalidate>
                        <div class="space-y-3">
                            <label class="label" for="name">
                                <span class="label-text text-lg">Your Name</span>
                            </label>
                            <input
                                type="text"
                                id="name"
                                v-model="form.name"
                                placeholder="Enter your name"
                                class="input input-bordered focus:input-primary w-full"
                                :class="{ 'input-error': form.errors.name }"
                                required
                            />
                            <input-error :message="form.errors.name" />
                        </div>

                        <div class="space-y-3">
                            <label class="label" for="phone">
                                <span class="label-text text-lg">Your Phone Number</span>
                            </label>
                            <input
                                type="tel"
                                id="phone"
                                v-model="form.phone"
                                placeholder="Enter your phone number"
                                class="input input-bordered focus:input-primary w-full"
                                :class="{ 'input-error': form.errors.phone }"
                                required
                            />
                            <input-error :message="form.errors.phone" />
                        </div>

                        <div class="space-y-3">
                            <label class="label" for="email">
                                <span class="label-text text-lg">Email Address</span>
                            </label>
                            <input
                                type="email"
                                id="email"
                                v-model="form.email"
                                placeholder="Enter your email"
                                class="input input-bordered focus:input-primary w-full"
                                :class="{ 'input-error': form.errors.email }"
                                required
                            />
                            <input-error :message="form.errors.email" />
                        </div>

                        <div class="space-y-3">
                            <label class="label" for="message">
                                <span class="label-text text-lg">Your Message</span>
                            </label>
                            <textarea
                                id="message"
                                v-model="form.message"
                                rows="5"
                                placeholder="Type your message here..."
                                class="textarea textarea-bordered focus:textarea-primary w-full"
                                :class="{ 'textarea-error': form.errors.message }"
                                required
                            ></textarea>
                            <input-error :message="form.errors.message" />
                        </div>

                        <button type="submit" class="btn btn-primary px-8 py-3 text-lg" :disabled="form.processing">
                            <span class="loading loading-spinner loading-sm" v-if="form.processing"></span>
                            Send Message
                        </button>
                    </form>
                </div>
            </section>

            <section>
                <h2 class="mb-8 text-3xl font-semibold">Visit Us</h2>

                <div class="card bg-base-100 overflow-hidden shadow">
                    <div class="card-body p-0">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3989.5429373442603!2d34.77060467483759!3d-0.6731814352630857!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x182b3be7d17d8bd7%3A0x660a0775968b5f93!2sTripple%20e%20electronics!5e0!3m2!1sen!2ske!4v1756474487721!5m2!1sen!2ske"
                            class="h-96 w-full border-0"
                            allowfullscreen=""
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                        ></iframe>
                    </div>
                </div>
            </section>
        </main>
    </base-layout>
</template>
