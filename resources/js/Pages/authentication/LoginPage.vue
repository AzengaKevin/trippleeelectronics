<script setup>
import AppCheckbox from '@/components/AppCheckbox.vue';
import AuthenticationCard from '@/components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/components/AuthenticationCardLogo.vue';
import InputError from '@/components/InputError.vue';
import InputLabel from '@/components/InputLabel.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import TextInput from '@/components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    username: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Log in" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-4" novalidate>
            <div class="space-y-2">
                <InputLabel for="username" value="Email or Phone Number" />
                <TextInput id="username" v-model="form.username" type="text" class="mt-1 block w-full" required autofocus autocomplete="username" />
                <InputError class="mt-2" :message="form.errors.username" />
            </div>

            <div class="space-y-2">
                <InputLabel for="password" value="Password" />
                <TextInput id="password" v-model="form.password" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="space-y-2">
                <label class="flex items-center">
                    <app-checkbox v-model:checked="form.remember" name="remember" />
                    <span class="ms-2 text-sm text-gray-600">Remember me</span>
                </label>
            </div>

            <div class="flex items-center justify-between space-y-2">
                <Link
                    :href="route('password.request')"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
                >
                    Forgot your password?
                </Link>

                <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing"> Log in </PrimaryButton>
            </div>
        </form>
    </AuthenticationCard>
</template>
