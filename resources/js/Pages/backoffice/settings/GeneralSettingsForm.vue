<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps({
    settings: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    group: 'general',
    site_name: props.settings?.site_name,
    email: props.settings?.email,
    phone: props.settings?.phone,
    kra_pin: props.settings?.kra_pin,
    location: props.settings?.location,
    show_categories_banner: props.settings?.show_categories_banner,
    facebook_link: props.settings?.facebook_link,
    tiktok_link: props.settings?.tiktok_link,
    instagram_link: props.settings?.instagram_link,
    whatsapp_link: props.settings?.whatsapp_link,
});

watch(
    () => props.settings,
    (newSettings) => {
        form.site_name = newSettings.site_name;
        form.email = newSettings.email;
        form.phone = newSettings.phone;
        form.location = newSettings.location;
        form.show_categories_banner = newSettings.show_categories_banner;
        form.facebook_link = newSettings.facebook_link;
        form.tiktok_link = newSettings.tiktok_link;
        form.instagram_link = newSettings.instagram_link;
        form.whatsapp_link = newSettings.whatsapp_link;
        form.kra_pin = newSettings.kra_pin;
    },
);

const updateSettings = () => {
    form.patch(route('backoffice.settings.update'));
};
</script>
<template>
    <form @submit.prevent="updateSettings" class="card bg-base-100 shadow">
        <div class="card-body space-y-4">
            <div class="space-y-2">
                <label for="site-name" class="label">
                    <span class="label-text">Site Name</span>
                </label>

                <input
                    type="text"
                    id="site-name"
                    v-model="form.site_name"
                    placeholder="Enter site name"
                    class="input input-bordered w-full"
                    required
                />
                <span v-if="form.errors.site_name" class="text-error text-sm">
                    {{ form.errors.site_name }}
                </span>
            </div>

            <div class="space-y-2">
                <label for="email" class="label">
                    <span class="label-text">Email Address</span>
                </label>
                <input type="email" id="email" v-model="form.email" placeholder="Enter email address" class="input input-bordered w-full" required />
                <span v-if="form.errors.email" class="text-error text-sm">
                    {{ form.errors.email }}
                </span>
            </div>

            <div class="space-y-2">
                <label for="phone" class="label">
                    <span class="label-text">Phone Number</span>
                </label>
                <input type="tel" id="phone" v-model="form.phone" placeholder="Enter phone number" class="input input-bordered w-full" required />
                <span v-if="form.errors.phone" class="text-error text-sm">
                    {{ form.errors.phone }}
                </span>
            </div>
            <div class="space-y-2">
                <label for="kra-pin" class="label">
                    <span class="label-text">KRA PIN</span>
                </label>
                <input type="text" id="kra-pin" v-model="form.kra_pin" placeholder="Enter KRA PIN" class="input input-bordered w-full" required />
                <span v-if="form.errors.kra_pin" class="text-error text-sm">
                    {{ form.errors.kra_pin }}
                </span>
            </div>

            <div class="space-y-2">
                <label for="location" class="label">
                    <span class="label-text">Location</span>
                </label>

                <input type="text" id="location" v-model="form.location" placeholder="Enter location" class="input input-bordered w-full" required />
                <span v-if="form.errors.location" class="text-error text-sm">
                    {{ form.errors.location }}
                </span>
            </div>

            <div>
                <label class="flex cursor-pointer items-center gap-2">
                    <input type="checkbox" v-model="form.show_categories_banner" class="checkbox checkbox-primary" />
                    <span>Show Categories Banner</span>
                </label>
            </div>

            <div class="space-y-2">
                <label for="facebook-link" class="label">
                    <span class="label-text">Facebook Link</span>
                </label>

                <input
                    type="url"
                    id="facebook-link"
                    v-model="form.facebook_link"
                    placeholder="Enter facebook link"
                    class="input input-bordered w-full"
                    required
                />
                <span v-if="form.errors.facebook_link" class="text-error text-sm">
                    {{ form.errors.facebook_link }}
                </span>
            </div>

            <div class="space-y-2">
                <label for="tiktok-link" class="label">
                    <span class="label-text">Tiktok Link</span>
                </label>

                <input
                    type="url"
                    id="tiktok-link"
                    v-model="form.tiktok_link"
                    placeholder="Enter tiktok link"
                    class="input input-bordered w-full"
                    required
                />
                <span v-if="form.errors.tiktok_link" class="text-error text-sm">
                    {{ form.errors.tiktok_link }}
                </span>
            </div>

            <div class="space-y-2">
                <label for="instagram-link" class="label">
                    <span class="label-text">Instagram Link</span>
                </label>

                <input
                    type="url"
                    id="instagram-link"
                    v-model="form.instagram_link"
                    placeholder="Enter instagram link"
                    class="input input-bordered w-full"
                    required
                />
                <span v-if="form.errors.instagram_link" class="text-error text-sm">
                    {{ form.errors.instagram_link }}
                </span>
            </div>

            <div class="space-y-2">
                <label for="whatsapp-link" class="label">
                    <span class="label-text">WhatsApp Link</span>
                </label>

                <input
                    type="url"
                    id="whatsapp-link"
                    v-model="form.whatsapp_link"
                    placeholder="Enter whatsapp link"
                    class="input input-bordered w-full"
                    required
                />
                <span v-if="form.errors.whatsapp_link" class="text-error text-sm">
                    {{ form.errors.whatsapp_link }}
                </span>
            </div>

            <div>
                <button type="submit" class="btn btn-primary" :disabled="form.processing">
                    <span v-if="form.processing" class="loading loading-spinner loading-sm"></span>
                    Update Settings
                </button>
            </div>
        </div>
    </form>
</template>
