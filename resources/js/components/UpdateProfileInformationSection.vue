<script setup>
import InputError from '@/components/InputError.vue';
import InputLabel from '@/components/InputLabel.vue';
import TextInput from '@/components/TextInput.vue';
import useDate from '@/composables/useDate';
import { useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

const auth = computed(() => page.props.auth);

const status = computed(() => page.props.status);
const { formatDate } = useDate();

const form = useForm({
    _method: 'put',
    name: auth.value.user?.name || '',
    email: auth.value.user?.email || '',
    phone: auth.value.user?.phone || '',
    dob: auth.value.user?.dob ? formatDate(auth.value.user.dob, 'YYYY-MM-DD') : '',
    address: auth.value.user?.address || '',
    avatar: null,
});

const submit = () => {
    form.post(route('user-profile-information.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>
<template>
    <form @submit.prevent="submit" novalidate>
        <div class="card bg-base-100">
            <div class="card-body space-y-4">
                <div class="space-y-2">
                    <h5 class="text-xl">Profile Information</h5>
                    <span class="text-muted">Update your account's profile information and email address.</span>
                </div>

                <div class="space-y-2">
                    <InputLabel for="name" value="Name" />
                    <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required autofocus autocomplete="name" />
                    <InputError :message="form.errors.name" />
                </div>
                <div class="space-y-2">
                    <InputLabel for="email" value="Email" />
                    <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" required autocomplete="username" />
                    <InputError :message="form.errors.email" />
                </div>
                <div class="space-y-2">
                    <InputLabel for="phone-number" value="Phone Number" />
                    <TextInput id="phone-number" v-model="form.phone" type="tel" class="mt-1 block w-full" required autocomplete="phone" />
                    <InputError :message="form.errors.phone" />
                </div>
                <div class="space-y-2">
                    <InputLabel for="dob" value="Date of Birth" />
                    <TextInput id="dob" v-model="form.dob" type="date" class="mt-1 block w-full" required autocomplete="bday" />
                    <InputError :message="form.errors.dob" />
                </div>
                <div class="space-y-2">
                    <InputLabel for="address" value="Address" />
                    <TextInput id="address" v-model="form.address" type="text" class="mt-1 block w-full" required autocomplete="address" />
                    <InputError :message="form.errors.address" />
                </div>

                <div class="flex items-center gap-4">
                    <img :src="auth.user.avatar_url" :alt="auth.user.name" class="h-24 w-24 rounded-full object-cover" />
                    <div class="space-y-2">
                        <label for="avatar" class="label">
                            <span class="label-text">Avatar</span>
                        </label>
                        <input
                            id="avatar"
                            type="file"
                            accept="image/*"
                            class="file-input file-input-bordered w-full"
                            @change="(e) => (form.avatar = e.target.files[0])"
                        />
                        <InputError :message="form.errors.avatar" />
                    </div>
                </div>

                <div class="flex flex-col items-start gap-3">
                    <div v-if="status === 'profile-information-updated'" class="text-success space-x-3 text-sm">
                        <font-awesome-icon icon="check-circle" />
                        <span>You've successfully update your profile</span>
                    </div>
                    <button type="submit" :disabled="form.processing" class="btn btn-primary">
                        <span v-if="form.processing" class="loading loading-spinner loading-lg"></span>
                        <font-awesome-icon v-else icon="save" />
                        <span>Save</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</template>
