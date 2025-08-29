<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    settings: {
        type: Object,
        default: () => ({}),
    },
    services: {
        type: Array,
        default: () => [],
    },
});

const appName = import.meta.env.VITE_APP_NAME;
</script>
<template>
    <footer class="bg-base-100 border-t border-slate-300 px-4 pt-8 pb-16">
        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
            <div>
                <h3 class="text-xl font-bold">{{ appName }}</h3>
                <p class="mt-6">We offer all round computer solutions in Kisii and around the Nyanza region since 2018.</p>
            </div>

            <div>
                <h3 class="text-lg font-semibold">Services</h3>
                <ul class="mt-6 space-y-3">
                    <li v-for="service in services" :key="service.id" class="">
                        {{ service.title }}
                    </li>
                </ul>
            </div>

            <div>
                <h3 class="text-lg font-semibold">Company</h3>
                <ul class="mt-6 space-y-3">
                    <li>
                        <Link :href="route('about-us')" class="hover:underline">About Us</Link>
                    </li>
                    <li>
                        <Link :href="route('contact-us')" class="hover:underline">Contact Us</Link>
                    </li>
                    <li>
                        <Link :href="route('services')" class="hover:underline">Services</Link>
                    </li>
                    <li>
                        <Link :href="route('terms-of-service')" class="hover:underline">Terms of Service</Link>
                    </li>
                </ul>
            </div>

            <div>
                <h3 class="text-lg font-semibold">Contact Us</h3>

                <ul class="mt-6 flex flex-col gap-y-3">
                    <li class="inline-flex gap-2">
                        <span class="inline-flex h-6 w-6 items-center justify-center"><font-awesome-icon icon="location-pin" /></span>
                        <span>{{ settings?.location }}</span>
                    </li>
                    <li class="inline-flex gap-2">
                        <span class="inline-flex h-6 w-6 items-center justify-center"><font-awesome-icon icon="envelope" /></span>
                        <span>{{ settings?.email }}</span>
                    </li>
                    <li class="inline-flex gap-2">
                        <span class="inline-flex h-6 w-6 items-center justify-center"><font-awesome-icon icon="phone" /></span>
                        <span>{{ settings?.phone }}</span>
                    </li>
                    <li>
                        <h4 class="text-md font-semibold">Follow Us</h4>
                        <ul class="mt-4 flex items-center gap-4">
                            <li>
                                <a
                                    :href="settings?.facebook_link ?? '#'"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-primary hover:text-[#1877F2]"
                                >
                                    <font-awesome-icon size="lg" :icon="['brands', 'facebook']" />
                                </a>
                            </li>
                            <li>
                                <a
                                    :href="settings?.x_link ?? '#'"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-primary hover:text-[#000000]"
                                >
                                    <font-awesome-icon size="lg" :icon="['brands', 'x-twitter']" />
                                </a>
                            </li>
                            <li>
                                <a
                                    :href="settings?.youtube_link ?? '#'"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-primary hover:text-[#FF0000]"
                                >
                                    <font-awesome-icon size="lg" :icon="['brands', 'youtube']" />
                                </a>
                            </li>
                            <li>
                                <a
                                    :href="settings?.instagram_link ?? '#'"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-primary hover:text-[#1877F2]"
                                >
                                    <font-awesome-icon size="lg" :icon="['brands', 'instagram']" />
                                </a>
                            </li>
                            <li>
                                <a
                                    :href="settings?.whatsapp_link ?? '#'"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-primary hover:text-[#25D366]"
                                >
                                    <font-awesome-icon size="lg" :icon="['brands', 'whatsapp']" />
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>

        <div class="mt-8 border-t border-gray-300 py-12 text-center text-sm text-gray-400">
            &copy; {{ new Date().getFullYear() }} Tripple e electronics. All rights reserved.
        </div>
    </footer>
</template>
