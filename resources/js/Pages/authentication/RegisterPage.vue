<script setup>
import AppCheckbox from '@/components/AppCheckbox.vue';
import AuthenticationCard from '@/components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/components/AuthenticationCardLogo.vue';
import InputError from '@/components/InputError.vue';
import InputLabel from '@/components/InputLabel.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import TextInput from '@/components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Register" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <form @submit.prevent="submit" class="space-y-4" novalidate>
            <div class="space-y-2">
                <InputLabel for="name" value="Name" />
                <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required autofocus autocomplete="name" />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="space-y-2">
                <InputLabel for="email" value="Email" />
                <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" required autocomplete="username" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="space-y-2">
                <InputLabel for="phone-number" value="Phone Number" />
                <TextInput id="phone-number" v-model="form.phone" type="tel" class="mt-1 block w-full" required autocomplete="phone" />
                <InputError class="mt-2" :message="form.errors.phone" />
            </div>

            <div class="space-y-2">
                <InputLabel for="password" value="Password" />
                <TextInput id="password" v-model="form.password" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="space-y-2">
                <InputLabel for="password_confirmation" value="Confirm Password" />
                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div>
                <InputLabel for="terms">
                    <div class="flex items-center">
                        <app-checkbox id="terms" v-model:checked="form.terms" name="terms" required />

                        <div class="ms-2">
                            I agree to the
                            <Link
                                target="_blank"
                                :href="route('terms-of-service')"
                                class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
                                >Terms of Service</Link
                            >
                            and
                            <Link
                                target="_blank"
                                :href="route('privacy-policy')"
                                class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
                                >Privacy Policy</Link
                            >
                        </div>
                    </div>
                    <InputError class="mt-2" :message="form.errors.terms" />
                </InputLabel>
            </div>

            <div class="mt-4 flex items-center justify-between">
                <Link
                    :href="route('login')"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
                >
                    Already registered?
                </Link>

                <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing"> Register </PrimaryButton>
            </div>
        </form>
    </AuthenticationCard>
</template>
