<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-black text-white px-4">
        <div>
            <Link href="/">
                <ApplicationLogo class="w-12 h-12 fill-current text-white hover:opacity-90 transition" />
            </Link>
        </div>

        <div
            class="w-full sm:max-w-md mt-6 px-8 py-6 bg-[#16181c] border border-gray-800 shadow-2xl overflow-hidden rounded-2xl"
        >
            <slot />
        </div>
    </div>
</template>
