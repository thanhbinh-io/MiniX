<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import Heart from 'vue-material-design-icons/Heart.vue';
import HeartOutline from 'vue-material-design-icons/HeartOutline.vue';
import ChartBar from 'vue-material-design-icons/ChartBar.vue';
import MessageOutline from 'vue-material-design-icons/MessageOutline.vue';
import Sync from 'vue-material-design-icons/Sync.vue';
import Bookmark from 'vue-material-design-icons/Bookmark.vue';
import BookmarkOutline from 'vue-material-design-icons/BookmarkOutline.vue';
import ShareVariantOutline from 'vue-material-design-icons/ShareVariantOutline.vue';
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue';
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue';
import PencilOutline from 'vue-material-design-icons/PencilOutline.vue';
import Close from 'vue-material-design-icons/Close.vue';
import ImageOutline from 'vue-material-design-icons/ImageOutline.vue';

const props = defineProps({
    tweet: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const currentUser = computed(() => page.props.auth?.user);
const isOwner = computed(() => currentUser.value && currentUser.value.id === props.tweet.user_id);

// Optimistic interaction states
const isLiked = ref(Boolean(props.tweet.is_liked));
const likesCount = ref(Number(props.tweet.likes_count) || 0);

const isRetweeted = ref(Boolean(props.tweet.is_retweeted));
const retweetsCount = ref(Number(props.tweet.retweets_count) || 0);

const isBookmarked = ref(Boolean(props.tweet.is_bookmarked));
const repliesCount = ref(Number(props.tweet.replies_count) || 0);
const viewsCount = ref(Number(props.tweet.views_count) || 0);

// Menu & Modal controls
const openOptions = ref(false);
const optionsRef = ref(null);
const openRetweetMenu = ref(false);
const retweetMenuRef = ref(null);

const showReplyModal = ref(false);
const showQuoteModal = ref(false);

const replyText = ref('');
const replyFile = ref(null);
const replyPreview = ref('');
const replyType = ref('');
const isSubmittingReply = ref(false);

const quoteText = ref('');
const quoteFile = ref(null);
const quotePreview = ref('');
const quoteType = ref('');
const isSubmittingQuote = ref(false);

const toastMessage = ref('');

const showToast = (msg) => {
    toastMessage.value = msg;
    setTimeout(() => {
        toastMessage.value = '';
    }, 2200);
};

// Navigation to tweet detail
const goToStatus = (e) => {
    // If user clicked a button, link, image, or interactive element, don't navigate
    if (e.target.closest('button') || e.target.closest('a') || e.target.closest('input') || e.target.closest('label') || e.target.closest('video')) {
        return;
    }
    const username = props.tweet.user?.username || 'user';
    router.visit(`/${username}/status/${props.tweet.id}`);
};

// Toggle Like
const toggleLike = async () => {
    if (!currentUser.value) {
        router.visit(route('login'));
        return;
    }

    const prevLiked = isLiked.value;
    const prevCount = likesCount.value;

    isLiked.value = !prevLiked;
    likesCount.value = prevLiked ? Math.max(0, prevCount - 1) : prevCount + 1;

    try {
        const res = await axios.post(route('tweets.like', props.tweet.id));
        if (res.data && typeof res.data.likes_count === 'number') {
            likesCount.value = res.data.likes_count;
            isLiked.value = res.data.liked;
        }
    } catch (err) {
        isLiked.value = prevLiked;
        likesCount.value = prevCount;
        showToast('Failed to update like');
    }
};

// Toggle Retweet (Direct repost)
const toggleRetweet = async () => {
    openRetweetMenu.value = false;
    if (!currentUser.value) {
        router.visit(route('login'));
        return;
    }

    const prevRetweeted = isRetweeted.value;
    const prevCount = retweetsCount.value;

    isRetweeted.value = !prevRetweeted;
    retweetsCount.value = prevRetweeted ? Math.max(0, prevCount - 1) : prevCount + 1;

    try {
        const res = await axios.post(route('tweets.retweet', props.tweet.id));
        if (res.data && typeof res.data.retweets_count === 'number') {
            retweetsCount.value = res.data.retweets_count;
            isRetweeted.value = res.data.retweeted;
            showToast(isRetweeted.value ? 'Reposted' : 'Undo repost');
        }
    } catch (err) {
        isRetweeted.value = prevRetweeted;
        retweetsCount.value = prevCount;
        showToast('Failed to update repost');
    }
};

// Toggle Bookmark
const toggleBookmark = async () => {
    if (!currentUser.value) {
        router.visit(route('login'));
        return;
    }

    const prevBookmarked = isBookmarked.value;
    isBookmarked.value = !prevBookmarked;

    try {
        const res = await axios.post(route('tweets.bookmark', props.tweet.id));
        if (res.data) {
            isBookmarked.value = res.data.bookmarked;
            showToast(isBookmarked.value ? 'Added to your Bookmarks' : 'Removed from your Bookmarks');
        }
    } catch (err) {
        isBookmarked.value = prevBookmarked;
        showToast('Failed to update bookmark');
    }
};

// Share Link
const copyShareLink = () => {
    const username = props.tweet.user?.username || 'user';
    const url = window.location.origin + `/${username}/status/${props.tweet.id}`;
    navigator.clipboard?.writeText(url);
    showToast('Copied link to clipboard');
};

// Reply Handling
const openReply = () => {
    if (!currentUser.value) {
        router.visit(route('login'));
        return;
    }
    showReplyModal.value = true;
};

const handleReplyFile = (e) => {
    if (!e.target.files || !e.target.files[0]) return;
    replyFile.value = e.target.files[0];
    replyPreview.value = URL.createObjectURL(e.target.files[0]);
    replyType.value = replyFile.value.name.split('.').pop().toLowerCase();
};

const submitReply = () => {
    if (!replyText.value && !replyFile.value) return;

    isSubmittingReply.value = true;
    const formData = new FormData();
    if (replyText.value) formData.append('tweet', replyText.value);
    if (replyFile.value) formData.append('file', replyFile.value);
    formData.append('parent_id', props.tweet.id);

    router.post(route('tweets.store'), formData, {
        preserveScroll: true,
        onSuccess: () => {
            replyText.value = '';
            replyFile.value = null;
            replyPreview.value = '';
            isSubmittingReply.value = false;
            showReplyModal.value = false;
            repliesCount.value += 1;
            showToast('Your reply was sent');
        },
        onError: () => {
            isSubmittingReply.value = false;
            showToast('Failed to post reply');
        }
    });
};

// Quote Handling
const openQuote = () => {
    openRetweetMenu.value = false;
    if (!currentUser.value) {
        router.visit(route('login'));
        return;
    }
    showQuoteModal.value = true;
};

const handleQuoteFile = (e) => {
    if (!e.target.files || !e.target.files[0]) return;
    quoteFile.value = e.target.files[0];
    quotePreview.value = URL.createObjectURL(e.target.files[0]);
    quoteType.value = quoteFile.value.name.split('.').pop().toLowerCase();
};

const submitQuote = () => {
    if (!quoteText.value && !quoteFile.value) return;

    isSubmittingQuote.value = true;
    const formData = new FormData();
    if (quoteText.value) formData.append('tweet', quoteText.value);
    if (quoteFile.value) formData.append('file', quoteFile.value);
    formData.append('quote_of_id', props.tweet.id);

    router.post(route('tweets.store'), formData, {
        preserveScroll: true,
        onSuccess: () => {
            quoteText.value = '';
            quoteFile.value = null;
            quotePreview.value = '';
            isSubmittingQuote.value = false;
            showQuoteModal.value = false;
            retweetsCount.value += 1;
            showToast('Your quote post was sent');
        },
        onError: () => {
            isSubmittingQuote.value = false;
            showToast('Failed to post quote');
        }
    });
};

// Outside click handler
const handleClickOutside = (e) => {
    if (optionsRef.value && !optionsRef.value.contains(e.target)) {
        openOptions.value = false;
    }
    if (retweetMenuRef.value && !retweetMenuRef.value.contains(e.target)) {
        openRetweetMenu.value = false;
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
    <div
        @click="goToStatus"
        class="flex w-full border-b border-gray-800 hover:bg-[#070707] transition duration-150 px-4 py-3 cursor-pointer"
    >
        <!-- Author Avatar -->
        <div class="mr-3 flex-shrink-0">
            <img
                class="rounded-full w-10 h-10 object-cover bg-gray-700 hover:opacity-90 transition cursor-pointer"
                :src="tweet.user?.avatar_url || 'https://ui-avatars.com/api/?name=User&background=1C9CEF&color=fff'"
                :alt="tweet.user?.name || 'User'"
                @click.stop="router.visit(`/${tweet.user?.username || 'user'}`)"
            >
        </div>

        <!-- Main Body -->
        <div class="w-full min-w-0">
            <!-- Header: Author Name, Handle, Time, Options -->
            <div class="flex items-center justify-between mb-1">
                <div class="flex items-center space-x-1.5 truncate">
                    <span
                        @click.stop="router.visit(`/${tweet.user?.username || 'user'}`)"
                        class="font-bold text-white text-[15px] hover:underline cursor-pointer truncate"
                    >
                        {{ tweet.user?.name || 'User' }}
                    </span>
                    <span class="text-gray-500 text-[14px] truncate">
                        @{{ tweet.user?.username || 'user' }}
                    </span>
                    <span class="text-gray-600 text-xs">·</span>
                    <span class="text-gray-500 text-[13px] hover:underline whitespace-nowrap">
                        {{ tweet.created_at_human || 'now' }}
                    </span>
                </div>

                <!-- Three dots options dropdown -->
                <div class="relative" ref="optionsRef">
                    <button
                        type="button"
                        @click.stop="openOptions = !openOptions"
                        class="p-1.5 rounded-full hover:bg-gray-800 text-gray-500 hover:text-[#1C9CEF] transition cursor-pointer"
                    >
                        <DotsHorizontal :size="18" />
                    </button>

                    <div
                        v-if="openOptions"
                        class="absolute right-0 mt-1 w-52 bg-black border border-gray-700 rounded-2xl shadow-2xl py-1 z-30"
                    >
                        <Link
                            v-if="isOwner"
                            as="button"
                            method="delete"
                            :href="route('tweets.destroy', { tweet: tweet.id })"
                            class="w-full flex items-center px-4 py-2.5 hover:bg-gray-900 text-red-500 text-sm font-semibold cursor-pointer transition"
                            @click.stop="openOptions = false"
                        >
                            <TrashCanOutline class="mr-2.5" fillColor="#EF4444" :size="18"/>
                            <span>Delete tweet</span>
                        </Link>

                        <button
                            type="button"
                            @click.stop="copyShareLink(); openOptions = false;"
                            class="w-full flex items-center px-4 py-2.5 hover:bg-gray-900 text-gray-200 text-sm cursor-pointer transition"
                        >
                            <ShareVariantOutline class="mr-2.5 text-gray-400" :size="18"/>
                            <span>Copy link to tweet</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tweet text content -->
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
                    alt="Attachment"
                />
            </div>

            <!-- Quoted Tweet Card (Nested preview) -->
            <div
                v-if="tweet.quote_of"
                class="my-2.5 border border-gray-800 hover:border-gray-700 rounded-2xl p-3 bg-black transition"
                @click.stop="router.visit(`/${tweet.quote_of.user?.username}/status/${tweet.quote_of.id}`)"
            >
                <div class="flex items-center space-x-2 mb-1.5">
                    <img
                        :src="tweet.quote_of.user?.avatar_url || 'https://ui-avatars.com/api/?name=User'"
                        class="rounded-full w-5 h-5 object-cover"
                    />
                    <span class="font-bold text-sm text-white">{{ tweet.quote_of.user?.name }}</span>
                    <span class="text-xs text-gray-500">@{{ tweet.quote_of.user?.username }}</span>
                </div>
                <div class="text-sm text-gray-200 line-clamp-3">{{ tweet.quote_of.tweet }}</div>
                <div v-if="tweet.quote_of.file" class="mt-2 rounded-xl overflow-hidden max-h-40">
                    <img v-if="!tweet.quote_of.is_video" :src="tweet.quote_of.file" class="w-full object-cover max-h-40" />
                </div>
            </div>

            <!-- Action Buttons Row -->
            <div class="flex items-center justify-between text-gray-500 max-w-md pt-2 text-xs">
                <!-- Reply Button -->
                <button
                    type="button"
                    @click.stop="openReply"
                    class="flex items-center space-x-2 group hover:text-[#1C9CEF] transition cursor-pointer"
                >
                    <div class="p-1.5 rounded-full group-hover:bg-blue-900/30 transition">
                        <MessageOutline :size="18" />
                    </div>
                    <span>{{ repliesCount }}</span>
                </button>

                <!-- Retweet / Repost Button with Popover -->
                <div class="relative" ref="retweetMenuRef">
                    <button
                        type="button"
                        @click.stop="openRetweetMenu = !openRetweetMenu"
                        :class="isRetweeted ? 'text-green-500 font-bold' : 'group hover:text-green-500'"
                        class="flex items-center space-x-2 transition cursor-pointer"
                    >
                        <div class="p-1.5 rounded-full group-hover:bg-green-900/30 transition">
                            <Sync :size="18" />
                        </div>
                        <span>{{ retweetsCount }}</span>
                    </button>

                    <!-- Retweet / Quote Dropdown -->
                    <div
                        v-if="openRetweetMenu"
                        class="absolute bottom-8 left-0 w-44 bg-black border border-gray-700 rounded-2xl shadow-2xl py-1 z-30"
                    >
                        <button
                            type="button"
                            @click.stop="toggleRetweet"
                            class="w-full flex items-center px-4 py-2.5 hover:bg-gray-900 text-sm font-semibold cursor-pointer transition"
                            :class="isRetweeted ? 'text-red-400' : 'text-gray-200'"
                        >
                            <Sync class="mr-2" :size="18"/>
                            <span>{{ isRetweeted ? 'Undo repost' : 'Repost' }}</span>
                        </button>
                        <button
                            type="button"
                            @click.stop="openQuote"
                            class="w-full flex items-center px-4 py-2.5 hover:bg-gray-900 text-gray-200 text-sm font-semibold cursor-pointer transition"
                        >
                            <PencilOutline class="mr-2" :size="18"/>
                            <span>Quote</span>
                        </button>
                    </div>
                </div>

                <!-- Like Button -->
                <button
                    type="button"
                    @click.stop="toggleLike"
                    :class="isLiked ? 'text-[#F91880] font-bold' : 'group hover:text-[#F91880]'"
                    class="flex items-center space-x-2 transition cursor-pointer"
                >
                    <div class="p-1.5 rounded-full group-hover:bg-pink-900/30 transition">
                        <Heart v-if="isLiked" fillColor="#F91880" :size="18" />
                        <HeartOutline v-else :size="18" />
                    </div>
                    <span>{{ likesCount }}</span>
                </button>

                <!-- Views Count -->
                <div class="flex items-center space-x-1.5 text-gray-600">
                    <div class="p-1.5">
                        <ChartBar :size="18" />
                    </div>
                    <span>{{ viewsCount }}</span>
                </div>

                <!-- Bookmark Button -->
                <button
                    type="button"
                    @click.stop="toggleBookmark"
                    :class="isBookmarked ? 'text-[#1C9CEF]' : 'hover:text-[#1C9CEF]'"
                    class="p-1.5 rounded-full hover:bg-blue-900/30 transition cursor-pointer"
                    title="Bookmark"
                >
                    <Bookmark v-if="isBookmarked" fillColor="#1C9CEF" :size="18" />
                    <BookmarkOutline v-else :size="18" />
                </button>

                <!-- Share Button -->
                <button
                    type="button"
                    @click.stop="copyShareLink"
                    class="p-1.5 rounded-full hover:bg-blue-900/30 hover:text-[#1C9CEF] transition cursor-pointer"
                    title="Share"
                >
                    <ShareVariantOutline :size="18" />
                </button>
            </div>
        </div>

        <!-- Floating Toast Message -->
        <transition name="fade">
            <div
                v-if="toastMessage"
                class="fixed bottom-6 left-1/2 transform -translate-x-1/2 bg-[#1C9CEF] text-white font-semibold text-sm px-5 py-2.5 rounded-full shadow-2xl z-50 transition"
            >
                {{ toastMessage }}
            </div>
        </transition>

        <!-- Reply Modal -->
        <div
            v-if="showReplyModal"
            class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-start justify-center p-3"
            @click.self="showReplyModal = false"
        >
            <div class="w-full max-w-lg mt-14 bg-black border border-gray-800 rounded-2xl p-4 shadow-2xl">
                <div class="flex items-center justify-between pb-3 border-b border-gray-800">
                    <button
                        type="button"
                        @click="showReplyModal = false"
                        class="p-1.5 rounded-full hover:bg-gray-800 text-gray-400 hover:text-white"
                    >
                        <Close :size="20" />
                    </button>
                    <button
                        :disabled="(!replyText && !replyFile) || isSubmittingReply"
                        :class="(!replyText && !replyFile) || isSubmittingReply ? 'bg-[#124D77] text-gray-400 cursor-not-allowed' : 'bg-[#1C9CEF] hover:bg-[#1A8CD8] text-white cursor-pointer'"
                        @click="submitReply"
                        class="font-bold text-sm px-5 py-1.5 rounded-full transition"
                    >
                        <span v-if="isSubmittingReply">Replying...</span>
                        <span v-else>Reply</span>
                    </button>
                </div>

                <!-- Original Tweet in Reply Modal -->
                <div class="flex pt-3 pb-2 text-sm">
                    <img :src="tweet.user?.avatar_url" class="rounded-full w-9 h-9 object-cover mr-3" />
                    <div class="flex-1">
                        <div class="flex items-center space-x-1.5">
                            <span class="font-bold text-white">{{ tweet.user?.name }}</span>
                            <span class="text-xs text-gray-500">@{{ tweet.user?.username }}</span>
                        </div>
                        <div class="text-gray-300 line-clamp-3 mt-1">{{ tweet.tweet }}</div>
                        <div class="text-xs text-gray-500 mt-2">
                            Replying to <span class="text-[#1C9CEF]">@{{ tweet.user?.username }}</span>
                        </div>
                    </div>
                </div>

                <!-- Current User Reply Input -->
                <div class="flex pt-3 border-t border-gray-800/80">
                    <img :src="currentUser?.avatar_url" class="rounded-full w-9 h-9 object-cover mr-3" />
                    <div class="flex-1">
                        <textarea
                            v-model="replyText"
                            placeholder="Post your reply"
                            rows="3"
                            class="w-full bg-black border-0 text-white placeholder-gray-500 focus:ring-0 resize-none text-[16px] p-0"
                        ></textarea>

                        <div v-if="replyPreview" class="relative my-2 rounded-xl overflow-hidden border border-gray-800 max-h-40">
                            <button
                                type="button"
                                @click="replyFile = null; replyPreview = '';"
                                class="absolute top-1.5 right-1.5 bg-black/80 hover:bg-black p-1 rounded-full text-white cursor-pointer"
                            >
                                <Close :size="16" />
                            </button>
                            <img :src="replyPreview" class="w-full max-h-40 object-cover" />
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t border-gray-800">
                            <label for="replyModalFile" class="hover:bg-blue-900/30 p-2 rounded-full cursor-pointer text-[#1C9CEF]">
                                <ImageOutline fillColor="#1C9CEF" :size="20" />
                            </label>
                            <input type="file" id="replyModalFile" class="hidden" accept="image/*,video/mp4" @change="handleReplyFile">

                            <span class="text-xs text-gray-500">{{ 280 - (replyText ? replyText.length : 0) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quote Modal -->
        <div
            v-if="showQuoteModal"
            class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-start justify-center p-3"
            @click.self="showQuoteModal = false"
        >
            <div class="w-full max-w-lg mt-14 bg-black border border-gray-800 rounded-2xl p-4 shadow-2xl">
                <div class="flex items-center justify-between pb-3 border-b border-gray-800">
                    <button
                        type="button"
                        @click="showQuoteModal = false"
                        class="p-1.5 rounded-full hover:bg-gray-800 text-gray-400 hover:text-white"
                    >
                        <Close :size="20" />
                    </button>
                    <button
                        :disabled="(!quoteText && !quoteFile) || isSubmittingQuote"
                        :class="(!quoteText && !quoteFile) || isSubmittingQuote ? 'bg-[#124D77] text-gray-400 cursor-not-allowed' : 'bg-[#1C9CEF] hover:bg-[#1A8CD8] text-white cursor-pointer'"
                        @click="submitQuote"
                        class="font-bold text-sm px-5 py-1.5 rounded-full transition"
                    >
                        <span v-if="isSubmittingQuote">Posting...</span>
                        <span v-else>Quote</span>
                    </button>
                </div>

                <!-- Current User Input -->
                <div class="flex pt-3">
                    <img :src="currentUser?.avatar_url" class="rounded-full w-9 h-9 object-cover mr-3" />
                    <div class="flex-1">
                        <textarea
                            v-model="quoteText"
                            placeholder="Add a comment"
                            rows="3"
                            class="w-full bg-black border-0 text-white placeholder-gray-500 focus:ring-0 resize-none text-[16px] p-0"
                        ></textarea>

                        <!-- Attached file preview -->
                        <div v-if="quotePreview" class="relative my-2 rounded-xl overflow-hidden border border-gray-800 max-h-40">
                            <button
                                type="button"
                                @click="quoteFile = null; quotePreview = '';"
                                class="absolute top-1.5 right-1.5 bg-black/80 hover:bg-black p-1 rounded-full text-white cursor-pointer"
                            >
                                <Close :size="16" />
                            </button>
                            <img :src="quotePreview" class="w-full max-h-40 object-cover" />
                        </div>

                        <!-- Quoted Tweet Card Box -->
                        <div class="my-3 border border-gray-800 rounded-2xl p-3 bg-[#0d0d0d]">
                            <div class="flex items-center space-x-1.5 mb-1">
                                <img :src="tweet.user?.avatar_url" class="rounded-full w-4 h-4 object-cover" />
                                <span class="font-bold text-xs text-white">{{ tweet.user?.name }}</span>
                                <span class="text-xs text-gray-500">@{{ tweet.user?.username }}</span>
                            </div>
                            <div class="text-xs text-gray-300 line-clamp-3">{{ tweet.tweet }}</div>
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t border-gray-800">
                            <label for="quoteModalFile" class="hover:bg-blue-900/30 p-2 rounded-full cursor-pointer text-[#1C9CEF]">
                                <ImageOutline fillColor="#1C9CEF" :size="20" />
                            </label>
                            <input type="file" id="quoteModalFile" class="hidden" accept="image/*,video/mp4" @change="handleQuoteFile">

                            <span class="text-xs text-gray-500">{{ 280 - (quoteText ? quoteText.length : 0) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
