<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import Tweet from '@/Components/Tweet.vue';
import TwitterLayout from '@/Layouts/TwitterLayout.vue';

defineProps({
    tweets: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
</script>

<template>
    <Head title="Bookmarks / Twitter" />

    <TwitterLayout>
        <div class="text-white">
            <!-- Header -->
            <div class="p-4 border-b border-gray-800 sticky top-0 bg-black/80 backdrop-blur-md z-10">
                <h1 class="text-xl font-bold text-white">Bookmarks</h1>
                <p class="text-xs text-gray-500">@{{ user?.username }}</p>
            </div>

            <!-- Empty State -->
            <div v-if="!tweets || tweets.length === 0" class="py-20 px-8 text-center max-w-sm mx-auto">
                <div class="text-2xl font-black text-white mb-2">Save posts for later</div>
                <p class="text-sm text-gray-500 leading-relaxed">
                    Don’t let the good ones fly away! Bookmark posts to easily find them again in the future.
                </p>
            </div>

            <!-- Bookmarked Tweets List -->
            <div v-else class="divide-y divide-gray-800">
                <div v-for="tweet in tweets" :key="tweet.id">
                    <Tweet :tweet="tweet" />
                </div>
            </div>
        </div>
    </TwitterLayout>
</template>
