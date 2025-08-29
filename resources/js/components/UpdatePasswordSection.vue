<script setup>
import InputError from '@/components/InputError.vue';
import InputLabel from '@/components/InputLabel.vue';
import TextInput from '@/components/TextInput.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const passwordInput = ref(null);

const currentPasswordInput = ref(null);

const page = usePage();

const status = computed(() => page.props.status || null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.put(route('user-password.update'), {
        errorBag: 'updatePassword',
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }

            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>
<template>
    <form @submit.prevent="submit" class="card bg-base-100" novalidate>
        <div class="card-body space-y-4">
            <div class="space-y-2">
                <h4 class="text-xl">Update Password</h4>
                <span>Ensure your account is using a long, random password to stay secure.</span>
            </div>

            <div class="space-y-3">
                <div class="space-y-2">
                    <input-label for="current-password" value="Current Password" />
                    <text-input
                        id="current-password"
                        v-model="form.current_password"
                        type="password"
                        required
                        autocomplete="current-password"
                        ref="currentPasswordInput"
                    />
                    <input-error :message="form.errors.current_password" />
                </div>
                <div class="space-y-2">
                    <input-label for="password" value="New Password" />
                    <text-input id="password" v-model="form.password" type="password" required />
                    <input-error :message="form.errors.password" />
                </div>
                <div class="space-y-2">
                    <input-label for="password-confirmation" value="Confirm Password" />
                    <text-input id="password-confirmation" v-model="form.password_confirmation" type="password" required ref="passwordInput" />
                    <span v-if="form.errors.password_confirmation" class="invalid-feedback">{{ form.errors.password_confirmation }}</span>
                </div>

                <div class="flex flex-col items-start gap-3">
                    <div v-if="status === 'password-updated'" class="text-success space-x-3 text-sm">
                        <font-awesome-icon icon="info-circle" />
                        <span>You've successfully updated your password.</span>
                    </div>
                    <button type="submit" class="btn btn-primary" :disabled="form.processing">
                        <span v-if="form.processing" class="loading loading-spinner loading-lg"></span>
                        <font-awesome-icon v-else icon="save" />
                        <span>Save</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</template>
