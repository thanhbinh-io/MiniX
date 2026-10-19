<script setup>
import { Head } from '@inertiajs/vue3';
import Tweet from '@/Components/Tweet.vue';
import TwitterLayout from '@/Layouts/TwitterLayout.vue';

defineProps({
    tweets: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head title="Home / Twitter" />

    <TwitterLayout>
        <div class="text-white divide-y divide-gray-800">
            <!-- Empty Feed State -->
            <div v-if="!tweets || tweets.length === 0" class="py-16 px-6 text-center">
                <div class="text-xl font-bold text-gray-300 mb-2">Welcome to your Twitter feed!</div>
                <p class="text-sm text-gray-500 max-w-sm mx-auto">
                    This is the best place to see what’s happening in your world. Post a tweet above to get started.
                </p>
            </div>

            <!-- Tweet List -->
            <div v-for="tweet in tweets" :key="tweet.id">
                <Tweet :tweet="tweet" />
            </div>
        </div>
    </TwitterLayout>
</template>

<style>
body {
    background-color: black;
}
</style>
