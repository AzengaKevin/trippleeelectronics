<script setup>
import BaseLayout from '@/layouts/BaseLayout.vue';
import { useCartStore } from '@/stores/cart';
import { useWishListStore } from '@/stores/wishlist';
import { Head, Link } from '@inertiajs/vue3';
import { storeToRefs } from 'pinia';
import { computed } from 'vue';

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
});

const appName = import.meta.env.VITE_APP_NAME;

const wishListStore = useWishListStore();

const cartStore = useCartStore();

const { items } = storeToRefs(wishListStore);

const { toggleItemWishListPresence } = wishListStore;
const { addItemToCart } = cartStore;

const createAPlaceholderImage = (text) => {
    const url = new URL('https://placehold.co/192x192');
    url.searchParams.append('text', text);
    return url.toString();
};

const processedItems = computed(() => {
    return items.value?.map((item) => {
        item.image_url = item.image_url ?? createAPlaceholderImage(item.name);
        return item;
    });
});
</script>
<template>
    <Head>
        <title>About Us</title>
    </Head>
    <base-layout :auth="auth" :settings="settings" :categories="categories" :services="services" :tree-categories="treeCategories">
        <main class="container mx-auto max-w-6xl space-y-12 px-4 py-12">
            <div class="space-y-3">
                <h1 class="text-primary text-4xl font-bold">About Us</h1>
                <div class="divider divider-primary w-24"></div>
                <p class="text-lg">Your trusted partner for comprehensive IT solutions since 2018</p>
            </div>

            <section class="card bg-base-100">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <div class="grid items-center gap-8 md:grid-cols-2">
                        <div>
                            <h2 class="text-3xl font-bold">Who We Are</h2>
                            <div class="mt-6 space-y-4">
                                <p>
                                    At {{ appName }}, we are dedicated to providing comprehensive IT solutions to meet the diverse needs of
                                    individuals and businesses alike. With a commitment to excellence, we specialize in offering high-quality
                                    computers, accessories, and cutting-edge technology.
                                </p>
                                <p>
                                    Our experienced team empowers clients to work smarter and stay ahead in today's fast-paced digital world. Whether
                                    you need the latest hardware or expert advice on system upgrades, we'll help you make the right choice for your
                                    unique requirements.
                                </p>
                                <p>
                                    In addition to our hardware sales, we offer a wide range of services including professional computer repairs,
                                    software installation, and system maintenance.
                                </p>
                            </div>
                        </div>
                        <div class="hidden md:block">
                            <img src="@/assets/images/about.svg" alt="Our team" class="h-auto w-full rounded-lg object-cover" />
                        </div>
                    </div>
                </div>
            </section>

            <!-- Our Services Section -->
            <section class="card bg-base-300">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <h2 class="mb-8 text-center text-3xl font-bold">Our Services</h2>
                    <div class="grid gap-6 md:grid-cols-3">
                        <div class="card bg-base-300 shadow hover:shadow-lg">
                            <div class="card-body lg-p-6 p-2 sm:p-4">
                                <div class="text-primary mb-4 text-4xl">💻</div>
                                <h3 class="card-title">Hardware Sales</h3>
                                <p>High-quality computers, accessories, and cutting-edge technology solutions</p>
                            </div>
                        </div>
                        <div class="card bg-base-300 shadow hover:shadow-lg">
                            <div class="card-body lg-p-6 p-2 sm:p-4">
                                <div class="text-secondary mb-4 text-4xl">🔧</div>
                                <h3 class="card-title">Repairs & Maintenance</h3>
                                <p>Professional computer repairs and system maintenance by certified technicians</p>
                            </div>
                        </div>
                        <div class="card bg-base-300 shadow hover:shadow-lg">
                            <div class="card-body lg-p-6 p-2 sm:p-4">
                                <div class="text-accent mb-4 text-4xl">📹</div>
                                <h3 class="card-title">CCTV Solutions</h3>
                                <p>Complete CCTV systems with professional installation for homes and businesses</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="card bg-base-100">
                <div class="card-body lg-p-6 p-2 sm:p-4">
                    <h2 class="mb-8 text-center text-3xl font-bold">Our Journey</h2>
                    <ul class="timeline timeline-snap-icon max-md:timeline-compact timeline-vertical">
                        <li>
                            <div class="timeline-middle">
                                <font-awesome-icon icon="check-circle" />
                            </div>
                            <div class="timeline-start mb-10 md:text-end">
                                <time class="font-mono italic">2018</time>
                                <div class="text-lg font-black">Founded</div>
                                Tripple e electronics was established in Kisii, aiming to provide affordable and reliable computing products and
                                services to students and local businesses.
                            </div>
                            <hr />
                        </li>

                        <li>
                            <hr />
                            <div class="timeline-middle">
                                <font-awesome-icon icon="check-circle" />
                            </div>
                            <div class="timeline-end md:mb-10">
                                <time class="font-mono italic">2019</time>
                                <div class="text-lg font-black">Expanded Services</div>
                                Started offering professional computer repair services and began stocking essential software for both personal and
                                business use.
                            </div>
                            <hr />
                        </li>

                        <li>
                            <hr />
                            <div class="timeline-middle">
                                <font-awesome-icon icon="check-circle" />
                            </div>
                            <div class="timeline-start mb-10 md:text-end">
                                <time class="font-mono italic">2021</time>
                                <div class="text-lg font-black">CCTV Systems & Installation</div>
                                Tripple e electronics began offering CCTV cameras and full installation services for homes, businesses, and
                                institutions in the Kisii region.
                            </div>
                            <hr />
                        </li>

                        <li>
                            <hr />
                            <div class="timeline-middle">
                                <font-awesome-icon icon="check-circle" />
                            </div>
                            <div class="timeline-end md:mb-10">
                                <time class="font-mono italic">2024</time>
                                <div class="text-lg font-black">Business Growth</div>
                                With growing customer trust and demand, Tripple e electronics expanded operations, increased staff, and launched an
                                online presence to better serve clients.
                            </div>
                        </li>
                    </ul>
                </div>
            </section>

            <!-- CTA Section -->
            <section class="from-primary to-accent text-primary-content rounded-xl bg-gradient-to-r p-8 text-center shadow-lg">
                <h2 class="mb-4 text-3xl font-bold">Ready to Experience Our Services?</h2>
                <p class="mb-6 text-lg">Contact us today for all your IT needs</p>
                <Link :href="route('contact-us')" class="btn btn-accent btn-lg">Get In Touch</Link>
            </section>
        </main>
    </base-layout>
</template>
