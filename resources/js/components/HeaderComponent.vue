<script setup>
import BrowseCategoriesComponent from '@/components/BrowseCategoriesComponent.vue';
import CategoriesMarquee from '@/components/CategoriesMarquee.vue';
import ProductSearchAutocomplete from '@/components/ProductSearchAutocomplete.vue';
import useLogout from '@/composables/useLogout';
import { useCartStore } from '@/stores/cart';
import { useWishListStore } from '@/stores/wishlist';
import { Link } from '@inertiajs/vue3';
import { storeToRefs } from 'pinia';
import { computed } from 'vue';

import { usePage } from '@inertiajs/vue3';

const props = defineProps({
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
});

const appName = import.meta.env.VITE_APP_NAME;

const cartStore = useCartStore();

const wishListStore = useWishListStore();

const page = usePage();

const auth = computed(() => page.props.auth);

const { cartItemsCount } = storeToRefs(cartStore);

const { wishListItemsCount } = storeToRefs(wishListStore);

const { logout } = useLogout();
</script>
<template>
    <div class="bg-base-100 text-base-content sticky top-0 right-0 left-0 z-20 border-t shadow-sm">
        <div class="from-primary to-accent text-primary-content scrollbar-none overflow-x-auto bg-gradient-to-r whitespace-nowrap">
            <div class="w-full px-4 py-2 text-sm font-bold">
                <div class="flex items-center gap-4 lg:grid lg:grid-cols-3 lg:justify-between">
                    <div class="flex justify-start">
                        <a :href="`tel:${settings.phone}`" class="inline-flex items-center gap-2">
                            <span><font-awesome-icon icon="phone" /></span>
                            <span class="border-l pl-2">{{ settings.phone }}</span>
                        </a>
                    </div>
                    <div class="flex justify-center">
                        <a :href="`mailto:${settings.email}`" class="inline-flex items-center gap-2">
                            <span><font-awesome-icon icon="envelope" /></span>
                            <span class="border-l pl-2">{{ settings.email }}</span>
                        </a>
                    </div>
                    <div class="flex justify-end">
                        <div class="inline-flex items-center gap-2">
                            <font-awesome-icon icon="location-pin" />
                            <span class="border-l-2 pl-2">{{ settings.location }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="px-4 py-2">
            <div class="grid grid-cols-2 items-center justify-between gap-2 xl:grid-cols-3">
                <div class="space-2 flex items-center justify-start sm:space-y-0 sm:space-x-6">
                    <Link :href="route('welcome')" class="text-primary text-xl font-bold sm:text-2xl">{{ appName }} </Link>
                </div>

                <div class="hidden justify-center xl:flex">
                    <product-search-autocomplete />
                </div>

                <div class="flex items-center justify-end gap-2">
                    <div class="hidden items-center gap-2 lg:flex">
                        <template v-if="auth.user">
                            <span class="text-sm font-light">Hi {{ auth.user.name.split(' ')[0] }}!</span>
                            <span class="inline-block border-l">&nbsp;</span>
                            <button class="hover:text-primary text-sm font-light hover:underline" @click="logout">Logout</button>
                            <span class="inline-block border-l">&nbsp;</span>
                            <Link class="hover:text-primary text-sm font-light hover:underline" :href="'#'">Orders </Link>
                            <span class="inline-block border-l">&nbsp;</span>
                            <Link class="hover:text-primary text-sm font-light hover:underline" :href="route('account.dashboard')">My Account</Link>
                        </template>
                        <template v-else>
                            <Link class="text-sm font-light" :href="route('login')">Login</Link>
                            <span class="inline-block border-l">&nbsp;</span>
                            <Link class="text-sm font-light" :href="route('register')">Register</Link>
                        </template>
                    </div>

                    <Link :href="route('wishlist')" class="btn btn-sm btn-secondary rounded-full">
                        <font-awesome-icon icon="heart" />
                        <span class="sr-only">Favourites</span>
                        <span>{{ wishListItemsCount }}</span>
                    </Link>
                    <Link :href="route('cart')" class="btn btn-sm btn-success rounded-full">
                        <font-awesome-icon icon="shopping-cart" />
                        <div class="sr-only">Shopping Cart</div>
                        <span>{{ cartItemsCount }}</span>
                    </Link>
                    <div class="dropdown dropdown-end lg:hidden">
                        <div tabindex="0" role="button" class="btn btn-ghost btn-circle m-0 p-0">
                            <img v-if="auth.user" :src="auth.user.avatar_url" alt="User Avatar" class="rounded-full" />
                            <font-awesome-icon v-else icon="user" />
                            <span class="sr-only">User Menu</span>
                        </div>
                        <ul tabindex="0" class="menu dropdown-content bg-base-100 rounded-box z-1 w-48 p-2 shadow-sm">
                            <template v-if="auth.user">
                                <li>
                                    <Link :href="route('account.dashboard')">My Account</Link>
                                </li>
                                <li>
                                    <Link href="#">Orders</Link>
                                </li>
                                <li><button @click="logout">Logout</button></li>
                            </template>
                            <template v-else>
                                <li>
                                    <Link :href="route('login')">Login</Link>
                                </li>
                                <li>
                                    <Link :href="route('register')">Register</Link>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="block border-t px-4 py-2 lg:hidden">
            <product-search-autocomplete />
        </div>

        <div class="border-t px-4 py-2">
            <div class="flex items-center justify-between gap-2 lg:items-center">
                <div>
                    <browse-categories-component :categories="treeCategories" key="sidebar-browsing-categories" />
                </div>
                <div class="scrollbar-none overflow-x-auto whitespace-nowrap">
                    <ul class="animate-marquee-disabled flex gap-3 text-sm font-bold">
                        <li>
                            <Link :href="route('about-us')" class="text-primary hover:text-accent">About Us</Link>
                        </li>
                        <li>
                            <Link :href="route('contact-us')" class="text-primary hover:text-accent">Contact Us</Link>
                        </li>
                        <li>
                            <Link :href="route('track-your-order')" class="text-primary hover:text-accent">Track Your Order </Link>
                        </li>
                        <li>
                            <Link :href="route('terms-of-service')" class="text-primary hover:text-accent">Terms of Service </Link>
                        </li>
                        <li>
                            <Link :href="route('return-policy')" class="text-primary hover:text-accent">Return Policy </Link>
                        </li>
                        <li>
                            <Link :href="route('refund-policy')" class="text-primary hover:text-accent">Refund Policy </Link>
                        </li>
                        <li>
                            <Link :href="route('placing-order')" class="text-primary hover:text-accent">Placing Order </Link>
                        </li>
                        <li>
                            <Link :href="route('claiming-warranty')" class="text-primary hover:text-accent">Claiming Warranty </Link>
                        </li>
                        <li>
                            <Link :href="route('services')" class="text-primary hover:text-accent">Services</Link>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div v-if="settings.show_categories_banner" class="border-t px-4 py-2">
            <categories-marquee :categories="categories" key="custom-marquee-categories" />
        </div>
    </div>
</template>
