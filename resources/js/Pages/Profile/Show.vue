<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import TwitterLayout from '@/Layouts/TwitterLayout.vue';
import Tweet from '@/Components/Tweet.vue';
import EditProfileModal from '@/Components/EditProfileModal.vue';
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue';
import CalendarBlankOutline from 'vue-material-design-icons/CalendarBlankOutline.vue';
import MapMarkerOutline from 'vue-material-design-icons/MapMarkerOutline.vue';
import LinkVariant from 'vue-material-design-icons/LinkVariant.vue';
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue';
import VolumeMute from 'vue-material-design-icons/VolumeMute.vue';
import AccountCancelOutline from 'vue-material-design-icons/AccountCancelOutline.vue';

const props = defineProps({
    profileUser: {
        type: Object,
        required: true,
    },
    currentTab: {
        type: String,
        default: 'tweets',
    },
    tweets: {
        type: Array,
        default: () => [],
    },
    isFollowing: {
        type: Boolean,
        default: false,
    },
    isBlocked: {
        type: Boolean,
        default: false,
    },
    isMuted: {
        type: Boolean,
        default: false,
    },
    isBlockedBy: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();
const currentUser = computed(() => page.props.auth?.user);
const isOwner = computed(() => currentUser.value && currentUser.value.id === props.profileUser.id);

// Follow state
const localIsFollowing = ref(props.isFollowing);
const localFollowersCount = ref(Number(props.profileUser.followers_count) || 0);

// Block & Mute state
const localIsBlocked = ref(props.isBlocked);
const localIsMuted = ref(props.isMuted);

const isEditModalOpen = ref(false);
const showOptionsMenu = ref(false);
const optionsRef = ref(null);
const isHoveringFollowing = ref(false);

const toggleFollow = async () => {
    if (!currentUser.value) {
        router.visit(route('login'));
        return;
    }

    const prevFollowing = localIsFollowing.value;
    const prevCount = localFollowersCount.value;

    localIsFollowing.value = !prevFollowing;
    localFollowersCount.value = prevFollowing ? Math.max(0, prevCount - 1) : prevCount + 1;

    try {
        const res = await axios.post(route('users.follow', props.profileUser.id));
        if (res.data && typeof res.data.followers_count === 'number') {
            localFollowersCount.value = res.data.followers_count;
            localIsFollowing.value = res.data.is_following;
        }
    } catch (err) {
        localIsFollowing.value = prevFollowing;
        localFollowersCount.value = prevCount;
    }
};

const toggleBlock = async () => {
    showOptionsMenu.value = false;
    if (!currentUser.value) return;

    try {
        const res = await axios.post(route('users.block', props.profileUser.id));
        if (res.data) {
            localIsBlocked.value = res.data.is_blocked;
            if (localIsBlocked.value) {
                localIsFollowing.value = false;
            }
        }
    } catch (err) {
        // error
    }
};

const toggleMute = async () => {
    showOptionsMenu.value = false;
    if (!currentUser.value) return;

    try {
        const res = await axios.post(route('users.mute', props.profileUser.id));
        if (res.data) {
            localIsMuted.value = res.data.is_muted;
        }
    } catch (err) {
        // error
    }
};

const switchTab = (tabName) => {
    router.visit(route('users.profile', { username: props.profileUser.username, tab: tabName }), {
        preserveScroll: true,
        preserveState: true,
    });
};
</script>

<template>
    <Head :title="`${profileUser.name} (@${profileUser.username}) / Twitter`" />

    <TwitterLayout>
        <div class="text-white min-h-screen border-b border-gray-800">
            <!-- Header bar with Back button and post count -->
            <div class="sticky top-0 bg-black/80 backdrop-blur-md z-20 flex items-center px-4 py-2 border-b border-gray-800 space-x-6">
                <Link href="/" class="p-2 rounded-full hover:bg-gray-800 transition cursor-pointer">
                    <ArrowLeft fillColor="#ffffff" :size="20" />
                </Link>
                <div>
                    <h1 class="text-xl font-bold leading-tight flex items-center">
                        {{ profileUser.name }}
                    </h1>
                    <p class="text-xs text-gray-500">
                        {{ profileUser.tweets_count }} posts
                    </p>
                </div>
            </div>

            <!-- Banner Header -->
            <div class="relative h-48 bg-gray-900 overflow-hidden">
                <img
                    :src="profileUser.banner_url"
                    class="w-full h-full object-cover"
                    alt="Header Banner"
                />
            </div>

            <!-- Profile Info Area -->
            <div class="px-4 pb-4 border-b border-gray-800">
                <!-- Avatar & Action Buttons Row -->
                <div class="relative flex justify-between items-end -mt-16 mb-3">
                    <!-- Big Avatar with border -->
                    <div class="rounded-full ring-4 ring-black overflow-hidden w-32 h-32 bg-gray-800">
                        <img
                            :src="profileUser.avatar_url"
                            class="w-full h-full object-cover"
                            :alt="profileUser.name"
                        />
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center space-x-2">
                        <!-- Owner: Edit Profile Button -->
                        <button
                            v-if="isOwner"
                            @click="isEditModalOpen = true"
                            type="button"
                            class="border border-gray-600 hover:bg-gray-900 text-white font-bold text-sm px-4 py-1.5 rounded-full transition cursor-pointer"
                        >
                            Edit profile
                        </button>

                        <!-- Viewing other user: Follow / Options -->
                        <div v-else class="flex items-center space-x-2">
                            <!-- Three dots options dropdown -->
                            <div class="relative" ref="optionsRef">
                                <button
                                    type="button"
                                    @click="showOptionsMenu = !showOptionsMenu"
                                    class="border border-gray-600 hover:bg-gray-900 p-1.5 rounded-full text-white cursor-pointer transition"
                                >
                                    <DotsHorizontal :size="18" />
                                </button>

                                <div
                                    v-if="showOptionsMenu"
                                    class="absolute right-0 mt-2 w-48 bg-black border border-gray-700 rounded-xl shadow-2xl py-1 z-30"
                                >
                                    <button
                                        type="button"
                                        @click="toggleMute"
                                        class="w-full flex items-center px-4 py-2.5 hover:bg-gray-900 text-gray-200 text-sm font-semibold cursor-pointer"
                                    >
                                        <VolumeMute class="mr-2" :size="18" />
                                        <span>{{ localIsMuted ? `Unmute @${profileUser.username}` : `Mute @${profileUser.username}` }}</span>
                                    </button>
                                    <button
                                        type="button"
                                        @click="toggleBlock"
                                        class="w-full flex items-center px-4 py-2.5 hover:bg-gray-900 text-red-500 text-sm font-semibold cursor-pointer"
                                    >
                                        <AccountCancelOutline class="mr-2" :size="18" />
                                        <span>{{ localIsBlocked ? `Unblock @${profileUser.username}` : `Block @${profileUser.username}` }}</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Follow Button -->
                            <button
                                v-if="!localIsBlocked"
                                type="button"
                                @click="toggleFollow"
                                @mouseenter="isHoveringFollowing = true"
                                @mouseleave="isHoveringFollowing = false"
                                :class="localIsFollowing
                                    ? (isHoveringFollowing ? 'bg-red-950/40 border-red-600 text-red-500' : 'bg-black border-gray-600 text-white')
                                    : 'bg-white hover:bg-gray-200 text-black border-transparent'"
                                class="border font-bold text-sm px-5 py-1.5 rounded-full transition cursor-pointer min-w-[105px]"
                            >
                                <span v-if="localIsFollowing">{{ isHoveringFollowing ? 'Unfollow' : 'Following' }}</span>
                                <span v-else>Follow</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Block Alert Banner if blocked -->
                <div v-if="localIsBlocked" class="bg-red-950/40 border border-red-800 rounded-xl p-3 my-3 text-sm text-red-300">
                    You have blocked @{{ profileUser.username }}. You cannot see their posts or follow them.
                </div>

                <!-- Blocked By Alert -->
                <div v-else-if="isBlockedBy" class="bg-gray-900 border border-gray-800 rounded-xl p-3 my-3 text-sm text-gray-400">
                    @{{ profileUser.username }} has blocked you. You are blocked from following @{{ profileUser.username }} and viewing their posts.
                </div>

                <!-- Profile details -->
                <div class="mt-2">
                    <h2 class="text-xl font-black text-white leading-tight">
                        {{ profileUser.name }}
                    </h2>
                    <p class="text-sm text-gray-500">
                        @{{ profileUser.username }}
                    </p>

                    <!-- Bio -->
                    <p v-if="profileUser.bio" class="text-[15px] text-gray-200 mt-3 whitespace-pre-line leading-relaxed">
                        {{ profileUser.bio }}
                    </p>

                    <!-- Metadata details (Location, Website, Joined Date) -->
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 text-sm text-gray-500 mt-3">
                        <div v-if="profileUser.location" class="flex items-center space-x-1">
                            <MapMarkerOutline :size="16" class="text-gray-500" />
                            <span>{{ profileUser.location }}</span>
                        </div>
                        <div v-if="profileUser.website" class="flex items-center space-x-1">
                            <LinkVariant :size="16" class="text-gray-500" />
                            <a :href="profileUser.website" target="_blank" rel="noopener noreferrer" class="text-[#1C9CEF] hover:underline">
                                {{ profileUser.website.replace(/^https?:\/\//, '') }}
                            </a>
                        </div>
                        <div v-if="profileUser.joined_date_human" class="flex items-center space-x-1">
                            <CalendarBlankOutline :size="16" class="text-gray-500" />
                            <span>{{ profileUser.joined_date_human }}</span>
                        </div>
                    </div>

                    <!-- Follower & Following Counts -->
                    <div class="flex items-center space-x-5 text-sm mt-3.5">
                        <Link
                            :href="route('users.following', profileUser.username)"
                            class="hover:underline flex items-center space-x-1"
                        >
                            <span class="font-bold text-white">{{ profileUser.following_count }}</span>
                            <span class="text-gray-500">Following</span>
                        </Link>
                        <Link
                            :href="route('users.followers', profileUser.username)"
                            class="hover:underline flex items-center space-x-1"
                        >
                            <span class="font-bold text-white">{{ localFollowersCount }}</span>
                            <span class="text-gray-500">Followers</span>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Profile Filter Tabs Row -->
            <div class="flex border-b border-gray-800 text-sm font-bold text-gray-500">
                <button
                    type="button"
                    @click="switchTab('tweets')"
                    :class="currentTab === 'tweets' ? 'text-white' : 'hover:bg-gray-900/50 hover:text-gray-300'"
                    class="flex-1 py-3 text-center transition cursor-pointer relative"
                >
                    <span>Posts</span>
                    <div v-if="currentTab === 'tweets'" class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-16 h-1 bg-[#1C9CEF] rounded-full"></div>
                </button>

                <button
                    type="button"
                    @click="switchTab('replies')"
                    :class="currentTab === 'replies' ? 'text-white' : 'hover:bg-gray-900/50 hover:text-gray-300'"
                    class="flex-1 py-3 text-center transition cursor-pointer relative"
                >
                    <span>Replies</span>
                    <div v-if="currentTab === 'replies'" class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-16 h-1 bg-[#1C9CEF] rounded-full"></div>
                </button>

                <button
                    type="button"
                    @click="switchTab('media')"
                    :class="currentTab === 'media' ? 'text-white' : 'hover:bg-gray-900/50 hover:text-gray-300'"
                    class="flex-1 py-3 text-center transition cursor-pointer relative"
                >
                    <span>Media</span>
                    <div v-if="currentTab === 'media'" class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-16 h-1 bg-[#1C9CEF] rounded-full"></div>
                </button>

                <button
                    type="button"
                    @click="switchTab('likes')"
                    :class="currentTab === 'likes' ? 'text-white' : 'hover:bg-gray-900/50 hover:text-gray-300'"
                    class="flex-1 py-3 text-center transition cursor-pointer relative"
                >
                    <span>Likes</span>
                    <div v-if="currentTab === 'likes'" class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-16 h-1 bg-[#1C9CEF] rounded-full"></div>
                </button>
            </div>

            <!-- Filtered Tweets List -->
            <div v-if="!localIsBlocked && !isBlockedBy" class="divide-y divide-gray-800">
                <div v-if="tweets && tweets.length > 0">
                    <div v-for="tweet in tweets" :key="tweet.id">
                        <Tweet :tweet="tweet" />
                    </div>
                </div>
                <div v-else class="py-16 text-center text-gray-500 text-sm max-w-sm mx-auto px-4">
                    <div class="text-xl font-bold text-gray-300 mb-1">
                        {{ currentTab === 'likes' ? 'No likes yet' : currentTab === 'media' ? 'No media yet' : currentTab === 'replies' ? 'No replies yet' : 'No posts yet' }}
                    </div>
                    <p class="text-xs text-gray-500">
                        When @{{ profileUser.username }} shares posts, they’ll show up here.
                    </p>
                </div>
            </div>
        </div>

        <!-- Edit Profile Modal -->
        <EditProfileModal
            v-if="isOwner"
            :user="profileUser"
            :isOpen="isEditModalOpen"
            @close="isEditModalOpen = false"
        />
    </TwitterLayout>
</template>
