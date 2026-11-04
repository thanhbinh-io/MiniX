<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    username: '',
    email: '',
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
    <GuestLayout>
        <Head title="Create your account" />

        <div class="mb-6">
            <h1 class="text-2xl font-black text-white">Create your account</h1>
            <p class="text-sm text-gray-400 mt-1">Join the Twitter clone community</p>
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="name" value="Display Name" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="e.g. John Doe"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="username" value="Username (@handle)" />

                <div class="relative mt-1">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 font-bold">@</span>
                    <TextInput
                        id="username"
                        type="text"
                        class="block w-full pl-8"
                        v-model="form.username"
                        required
                        autocomplete="username"
                        placeholder="username"
                    />
                </div>
                <p class="text-xs text-gray-500 mt-1">3-15 characters, letters, numbers, and underscores only</p>

                <InputError class="mt-2" :message="form.errors.username" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="email"
                    placeholder="you@example.com"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
                <p class="text-xs text-gray-500 mt-1">8-16 characters, at least 1 uppercase, 1 lowercase, and 1 symbol (@, #, $, ...)</p>
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Confirm Password" />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="flex items-center justify-between mt-6">
                <Link
                    :href="route('login')"
                    class="text-sm text-[#1C9CEF] hover:underline"
                >
                    Already have an account?
                </Link>

                <PrimaryButton class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Sign up
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
