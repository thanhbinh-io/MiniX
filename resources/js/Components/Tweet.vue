<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import HeartOutline from 'vue-material-design-icons/HeartOutline.vue';
import ChartBar from 'vue-material-design-icons/ChartBar.vue';
import MessageOutline from 'vue-material-design-icons/MessageOutline.vue';
import Sync from 'vue-material-design-icons/Sync.vue';
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue';
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue';
import LinkVariant from 'vue-material-design-icons/LinkVariant.vue';

const props = defineProps({
    tweet: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const currentUser = computed(() => page.props.auth?.user);
const isOwner = computed(() => currentUser.value && currentUser.value.id === props.tweet.user_id);

const openOptions = ref(false);
const optionsRef = ref(null);
const copied = ref(false);

const toggleOptions = () => {
    openOptions.value = !openOptions.value;
};

const copyLink = () => {
    navigator.clipboard?.writeText(window.location.origin + '/tweets/' + props.tweet.id);
    copied.value = true;
    setTimeout(() => {
        copied.value = false;
        openOptions.value = false;
    }, 1200);
};

const handleClickOutside = (e) => {
    if (optionsRef.value && !optionsRef.value.contains(e.target)) {
        openOptions.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div class="flex w-full border-b border-gray-800 hover:bg-[#080808] transition duration-150 px-4 py-3">
        <!-- Author Avatar -->
        <div class="mr-3 flex-shrink-0">
            <img
                class="rounded-full w-10 h-10 object-cover bg-gray-700 hover:opacity-90 transition cursor-pointer"
                :src="tweet.user?.avatar_url || 'https://ui-avatars.com/api/?name=User&background=1C9CEF&color=fff'"
                :alt="tweet.user?.name || 'User'"
            >
        </div>

        <!-- Tweet Body -->
        <div class="w-full min-w-0">
            <!-- Header: Name, Handle, Timestamp, Options -->
            <div class="flex items-center justify-between mb-1">
                <div class="flex items-center space-x-1.5 truncate">
                    <span class="font-bold text-white text-[15px] hover:underline cursor-pointer truncate">
                        {{ tweet.user?.name || 'User' }}
                    </span>
                    <span class="text-gray-500 text-[14px] truncate">
                        @{{ tweet.user?.username || 'user' }}
                    </span>
                    <span class="text-gray-600 text-xs">·</span>
                    <span class="text-gray-500 text-[13px] whitespace-nowrap">
                        {{ tweet.created_at_human || 'just now' }}
                    </span>
                </div>

                <!-- Dropdown Menu -->
                <div class="relative" ref="optionsRef">
                    <button
                        type="button"
                        @click.stop="toggleOptions"
                        class="p-1.5 rounded-full hover:bg-gray-800 text-gray-500 hover:text-[#1C9CEF] transition cursor-pointer"
                    >
                        <DotsHorizontal :size="18" />
                    </button>

                    <div
                        v-if="openOptions"
                        class="absolute right-0 mt-1 w-52 bg-black border border-gray-700 rounded-xl shadow-2xl py-1 z-30"
                    >
                        <!-- Delete Option (Owner only) -->
                        <Link
                            v-if="isOwner"
                            as="button"
                            method="delete"
                            :href="route('tweets.destroy', { tweet: tweet.id })"
                            class="w-full flex items-center px-4 py-2.5 hover:bg-gray-900 text-red-500 text-sm font-semibold cursor-pointer transition"
                            @click="openOptions = false"
                        >
                            <TrashCanOutline class="mr-2.5" fillColor="#EF4444" :size="18"/>
                            <span>Delete tweet</span>
                        </Link>

                        <!-- Copy Link Option -->
                        <button
                            type="button"
                            @click="copyLink"
                            class="w-full flex items-center px-4 py-2.5 hover:bg-gray-900 text-gray-200 text-sm cursor-pointer transition"
                        >
                            <LinkVariant class="mr-2.5 text-gray-400" :size="18"/>
                            <span>{{ copied ? 'Copied link!' : 'Copy link to tweet' }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tweet Content Text -->
            <div v-if="tweet.tweet" class="text-white text-[15px] leading-relaxed break-words whitespace-pre-line mb-2">
                {{ tweet.tweet }}
            </div>

            <!-- Attached Media -->
            <div v-if="tweet.file" class="my-2 rounded-2xl overflow-hidden border border-gray-800 max-h-[450px]">
                <video
                    v-if="tweet.is_video"
                    controls
                    class="w-full max-h-[450px] object-cover rounded-2xl"
                    :src="tweet.file"
                ></video>
                <img
                    v-else
                    :src="tweet.file"
                    class="w-full max-h-[450px] object-cover rounded-2xl"
                    alt="Tweet attachment"
                />
            </div>

            <!-- Interaction Buttons Bar -->
            <div class="flex items-center justify-between text-gray-500 max-w-md pt-2 text-xs">
                <!-- Reply -->
                <button
                    type="button"
                    class="flex items-center space-x-2 group hover:text-[#1C9CEF] transition cursor-pointer"
                >
                    <div class="p-1.5 rounded-full group-hover:bg-blue-900/30 transition">
                        <MessageOutline :size="18" />
                    </div>
                    <span>0</span>
                </button>

                <!-- Retweet -->
                <button
                    type="button"
                    class="flex items-center space-x-2 group hover:text-green-500 transition cursor-pointer"
                >
                    <div class="p-1.5 rounded-full group-hover:bg-green-900/30 transition">
                        <Sync :size="18" />
                    </div>
                    <span>0</span>
                </button>

                <!-- Like -->
                <button
                    type="button"
                    class="flex items-center space-x-2 group hover:text-pink-500 transition cursor-pointer"
                >
                    <div class="p-1.5 rounded-full group-hover:bg-pink-900/30 transition">
                        <HeartOutline :size="18" />
                    </div>
                    <span>0</span>
                </button>

                <!-- Views / Analytics -->
                <div class="flex items-center space-x-2 text-gray-600">
                    <div class="p-1.5">
                        <ChartBar :size="18" />
                    </div>
                    <span>0</span>
                </div>
            </div>
        </div>
    </div>
</template>
