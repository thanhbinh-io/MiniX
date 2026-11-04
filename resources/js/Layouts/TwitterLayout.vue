<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router, usePage, Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Magnify from 'vue-material-design-icons/Magnify.vue';
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue';
import DotsHorizontalCircleOutline from 'vue-material-design-icons/DotsHorizontalCircleOutline.vue';
import Feather from 'vue-material-design-icons/Feather.vue';
import Close from 'vue-material-design-icons/Close.vue';
import ChevronDown from 'vue-material-design-icons/ChevronDown.vue';
import Earth from 'vue-material-design-icons/Earth.vue';
import ImageOutline from 'vue-material-design-icons/ImageOutline.vue';
import FileGifBox from 'vue-material-design-icons/FileGifBox.vue';
import Emoticon from 'vue-material-design-icons/Emoticon.vue';
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue';
import Logout from 'vue-material-design-icons/Logout.vue';
import Account from 'vue-material-design-icons/Account.vue';
import MenuItem from '@/Components/MenuItem.vue';
import CogOutline from 'vue-material-design-icons/CogOutline.vue';
import PaletteOutline from 'vue-material-design-icons/PaletteOutline.vue';
import HelpCircleOutline from 'vue-material-design-icons/HelpCircleOutline.vue';
import WeatherNight from 'vue-material-design-icons/WeatherNight.vue';
import WeatherSunny from 'vue-material-design-icons/WeatherSunny.vue';

const props = defineProps({
    showHomeHeader: {
        type: Boolean,
        default: false,
    },
    currentTab: {
        type: String,
        default: 'for-you',
    },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const unreadNotificationsCount = computed(() => page.props.auth?.unread_notifications_count || 0);
const unreadMessagesCount = computed(() => page.props.auth?.unread_messages_count || 0);
const isAdmin = computed(() => page.props.auth?.is_admin || false);
const isSuspended = computed(() => page.props.auth?.is_suspended || false);
const isChatPage = computed(() => {
    return page.component === 'Messages/Index' || page.url.startsWith('/messages');
});
const isSettingsPage = computed(() => {
    return page.component.startsWith('Settings/') || page.url.startsWith('/settings');
});
const isBotPage = computed(() => {
    return page.component === 'Bot/Index' || page.url.startsWith('/bot');
});

const trendsList = computed(() => page.props.trends || []);
const whoToFollowList = computed(() => page.props.who_to_follow || []);

const switchFeedTab = (tab) => {
    router.visit(route('tweets.index', { tab }), {
        preserveScroll: true,
        preserveState: true,
    });
};

const searchQuery = ref('');

const executeSearch = () => {
    if (!searchQuery.value.trim()) return;
    router.visit(route('search', { q: searchQuery.value.trim() }));
};

const searchTag = (tag) => {
    router.visit(route('search', { q: tag }));
};

let showUserMenu = ref(false);
let showMoreMenu = ref(false);
let showDisplayModal = ref(false);
let showEmojiPicker = ref(false);
const commonEmojis = ['😀', '😂', '🔥', '❤️', '👍', '🎉', '🚀', '✨', '👏', '💯', '🙏', '👀'];
const insertEmoji = (emoji) => {
    tweet.value = (tweet.value || '') + emoji;
};
let createTweet = ref(false);
let tweet = ref('');
let file = ref(null);
let showUpload = ref('');
let uploadType = ref('');
let errorMessage = ref('');
let isSubmitting = ref(false);

// Dark / Light Mode State
const isDarkMode = ref(true);

const initTheme = () => {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'light') {
        isDarkMode.value = false;
        document.documentElement.classList.remove('dark');
    } else {
        isDarkMode.value = true;
        document.documentElement.classList.add('dark');
    }
};

const toggleTheme = () => {
    isDarkMode.value = !isDarkMode.value;
    if (isDarkMode.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
};

const handleWindowClick = () => {
    showMoreMenu.value = false;
    showUserMenu.value = false;
};

onMounted(() => {
    initTheme();
    window.addEventListener('click', handleWindowClick);
});

onUnmounted(() => {
    window.removeEventListener('click', handleWindowClick);
});

const getFile = (e) => {
    if (!e.target.files || !e.target.files[0]) return;
    file.value = e.target.files[0];
    showUpload.value = URL.createObjectURL(e.target.files[0]);
    uploadType.value = file.value.name.split('.').pop().toLowerCase();
};

const clearUpload = () => {
    file.value = null;
    showUpload.value = '';
    uploadType.value = '';
};

const closeMessageBox = () => {
    createTweet.value = false;
    tweet.value = '';
    clearUpload();
    errorMessage.value = '';
};

const openTweetModal = () => {
    if (!user.value) {
        router.visit(route('login'));
        return;
    }
    createTweet.value = true;
};

const logout = () => {
    showUserMenu.value = false;
    router.post(route('logout'));
};

const addTweet = () => {
    if (!tweet.value && !file.value) return;
    if (tweet.value && tweet.value.length > 280) {
        errorMessage.value = 'Tweet cannot exceed 280 characters.';
        return;
    }

    isSubmitting.value = true;
    errorMessage.value = '';

    let data = new FormData();
    if (tweet.value) data.append('tweet', tweet.value);
    if (file.value) data.append('file', file.value);

    router.post('/tweets', data, {
        onSuccess: () => {
            isSubmitting.value = false;
            closeMessageBox();
        },
        onError: (errors) => {
            isSubmitting.value = false;
            if (errors.tweet) {
                errorMessage.value = errors.tweet;
            } else if (errors.file) {
                errorMessage.value = errors.file;
            } else {
                errorMessage.value = 'Failed to post tweet. Please try again.';
            }
        },
        onFinish: () => {
            isSubmitting.value = false;
        }
    });
};

const textarea = ref(null);

const textareaInput = (e) => {
    textarea.value.style.height = "auto";
    textarea.value.style.height = `${e.target.scrollHeight}px`;
};
</script>

<template>
    <div class="fixed w-full h-full bg-white dark:bg-black text-gray-900 dark:text-white transition-colors duration-200">
        <div :class="isChatPage ? 'w-full flex h-full' : 'max-w-[1400px] flex mx-auto'">
            <!-- Left Sidebar Navigation -->
            <div
                :class="isChatPage
                    ? 'w-[68px] xl:w-[72px] px-1 flex-shrink-0'
                    : 'lg:w-3/12 w-[60px] max-w-[350px] lg:px-4 lg:mx-auto'"
                class="h-[100vh] flex flex-col justify-between pb-4 transition-all duration-200"
            >
                <div>
                    <div class="p-2 px-3 mb-2" :class="{ 'flex justify-center px-0': isChatPage }">
                        <Link href="/" class="inline-flex items-center justify-center p-2 hover:bg-gray-100 dark:hover:bg-gray-900 rounded-full transition w-fit" title="Trang chủ">
                            <ApplicationLogo class="w-8 h-8 text-gray-900 dark:text-white" />
                        </Link>
                    </div>

                    <MenuItem iconString="Home" href="/" :collapsed="isChatPage" />
                    <MenuItem iconString="Explore" :href="route('explore')" :collapsed="isChatPage" />
                    <MenuItem iconString="Notifications" :href="user ? route('notifications.index') : route('login')" :badgeCount="unreadNotificationsCount" :collapsed="isChatPage" />
                    <MenuItem iconString="Chat" :href="user ? route('messages.index') : route('login')" :badgeCount="unreadMessagesCount" :collapsed="isChatPage" />
                    <MenuItem iconString="Bot" :href="user ? route('bot.index') : route('login')" :collapsed="isChatPage" />
                    <MenuItem iconString="Bookmarks" :href="user ? route('bookmarks.index') : route('login')" :collapsed="isChatPage" />
                    <MenuItem iconString="Profile" :href="user ? route('users.profile', user.username) : route('login')" :collapsed="isChatPage" />

                    <!-- More Button with Circle Dots Icon & Popover -->
                    <div class="relative">
                        <button
                            type="button"
                            @click.stop="showMoreMenu = !showMoreMenu; showUserMenu = false;"
                            :class="isChatPage ? 'justify-center p-3 w-fit mx-auto' : 'w-full p-2 px-4 lg:pr-6'"
                            class="block hover:bg-gray-100 dark:hover:bg-gray-900 rounded-full cursor-pointer transition duration-200 ease-in-out text-gray-900 dark:text-white"
                            title="More"
                        >
                            <div class="flex items-center" :class="{ 'justify-center': isChatPage }">
                                <div class="relative flex items-center justify-center">
                                    <DotsHorizontalCircleOutline fillColor="currentColor" :size="30" />
                                </div>
                                <span v-if="!isChatPage" class="lg:block hidden font-extrabold text-[23px] pl-4 mt-0.5">
                                    More
                                </span>
                            </div>
                        </button>

                        <!-- More Menu Popover -->
                        <div
                            v-if="showMoreMenu"
                            @click.stop
                            :class="isChatPage ? 'bottom-14 -left-2 w-72' : 'bottom-12 left-0 w-72'"
                            class="absolute bg-white dark:bg-black border border-gray-200 dark:border-gray-800 rounded-2xl shadow-2xl p-2 z-50 mb-2 transition-all duration-150"
                        >
                            <!-- Dark / Light Theme Toggle -->
                            <div
                                @click="toggleTheme"
                                class="flex items-center justify-between p-3 hover:bg-gray-100 dark:hover:bg-gray-900 rounded-xl cursor-pointer transition select-none group"
                            >
                                <div class="flex items-center space-x-3">
                                    <component
                                        :is="isDarkMode ? WeatherNight : WeatherSunny"
                                        :size="22"
                                        class="text-gray-700 dark:text-gray-300 group-hover:text-[#1C9CEF] transition"
                                    />
                                    <div>
                                        <div class="text-sm font-bold text-gray-900 dark:text-white">
                                            Dark mode
                                        </div>
                                        <div class="text-[11px] text-gray-500">
                                            {{ isDarkMode ? 'On' : 'Off' }}
                                        </div>
                                    </div>
                                </div>
                                <!-- Toggle Switch -->
                                <div
                                    :class="isDarkMode ? 'bg-[#1C9CEF]' : 'bg-gray-300 dark:bg-gray-700'"
                                    class="w-12 h-6 flex items-center rounded-full p-1 duration-300 cursor-pointer shadow-inner"
                                >
                                    <div
                                        :class="isDarkMode ? 'translate-x-6 bg-white' : 'translate-x-0 bg-white'"
                                        class="w-4 h-4 rounded-full shadow-md transform duration-300"
                                    ></div>
                                </div>
                            </div>

                            <div class="border-t border-gray-200 dark:border-gray-800 my-1"></div>

                            <!-- 1. Settings and privacy -->
                            <Link
                                :href="user ? route('settings.account') : route('login')"
                                @click="showMoreMenu = false"
                                class="flex items-center px-3 py-2.5 hover:bg-gray-100 dark:hover:bg-gray-900 rounded-xl text-sm font-semibold text-gray-800 dark:text-gray-200 transition group"
                            >
                                <CogOutline :size="22" class="mr-3 text-gray-700 dark:text-gray-300 group-hover:text-[#1C9CEF] transition flex-shrink-0" />
                                <span>Settings and privacy</span>
                            </Link>

                            <!-- 2. Display -->
                            <div
                                @click="showMoreMenu = false; showDisplayModal = true"
                                class="flex items-center px-3 py-2.5 hover:bg-gray-100 dark:hover:bg-gray-900 rounded-xl text-sm font-semibold text-gray-800 dark:text-gray-200 cursor-pointer transition group"
                            >
                                <PaletteOutline :size="22" class="mr-3 text-gray-700 dark:text-gray-300 group-hover:text-[#1C9CEF] transition flex-shrink-0" />
                                <span>Display</span>
                            </div>

                            <!-- 3. Help Center -->
                            <a
                                href="https://help.twitter.com"
                                target="_blank"
                                rel="noopener noreferrer"
                                @click="showMoreMenu = false"
                                class="flex items-center px-3 py-2.5 hover:bg-gray-100 dark:hover:bg-gray-900 rounded-xl text-sm font-semibold text-gray-800 dark:text-gray-200 cursor-pointer transition group"
                            >
                                <HelpCircleOutline :size="22" class="mr-3 text-gray-700 dark:text-gray-300 group-hover:text-[#1C9CEF] transition flex-shrink-0" />
                                <span>Help Center</span>
                            </a>
                        </div>
                    </div>

                    <!-- Admin Panel link (only visible to admin users) -->
                    <MenuItem
                        v-if="isAdmin"
                        iconString="Admin"
                        :href="route('admin.dashboard')"
                        :collapsed="isChatPage"
                    />

                    <button
                        @click="openTweetModal"
                        :class="isChatPage
                            ? 'w-12 h-12 p-0 mx-auto flex items-center justify-center'
                            : 'w-full p-3 px-3'"
                        class="mt-6 font-extrabold text-[20px] bg-black dark:bg-white text-white dark:text-black hover:bg-gray-800 dark:hover:bg-gray-200 rounded-full cursor-pointer transition shadow"
                        title="Post"
                    >
                        <span v-if="!isChatPage" class="lg:block hidden">Post</span>
                        <span :class="isChatPage ? 'flex justify-center' : 'block lg:hidden flex justify-center'"><Feather fillColor="currentColor" :size="22" /></span>
                    </button>
                </div>

                <!-- Bottom User / Auth Action Area -->
                <div class="relative">
                    <!-- Logged In User Pill -->
                    <div v-if="user" class="relative">
                        <!-- Popover Menu -->
                        <div
                            v-if="showUserMenu"
                            @click.stop
                            :class="isChatPage ? 'bottom-16 -left-2 w-64' : 'bottom-16 left-0 w-full min-w-[240px]'"
                            class="absolute bg-white dark:bg-black border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 z-50 mb-2"
                        >
                            <div class="flex items-center p-3 border-b border-gray-100 dark:border-gray-800">
                                <img
                                    :src="user.avatar_url"
                                    class="rounded-full w-10 h-10 object-cover bg-gray-700"
                                    :alt="user.name"
                                />
                                <div class="ml-3 truncate">
                                    <div class="font-bold text-gray-900 dark:text-white text-sm truncate">{{ user.name }}</div>
                                    <div class="text-xs text-gray-500 truncate">@{{ user.username }}</div>
                                </div>
                            </div>

                            <Link
                                :href="route('users.profile', user.username)"
                                @click="showUserMenu = false"
                                class="flex items-center px-3 py-2.5 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg text-sm text-gray-700 dark:text-gray-200 cursor-pointer mt-1"
                            >
                                <Account :size="20" class="mr-2.5 text-gray-400" />
                                View profile
                            </Link>

                            <Link
                                :href="route('profile.edit')"
                                @click="showUserMenu = false"
                                class="flex items-center px-3 py-2.5 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg text-sm text-gray-700 dark:text-gray-200 cursor-pointer"
                            >
                                <span class="text-base mr-3">⚙️</span>
                                Cài đặt tài khoản
                            </Link>

                            <button
                                @click="logout"
                                class="w-full text-left flex items-center px-3 py-2.5 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg text-sm font-bold text-red-500 cursor-pointer"
                            >
                                <Logout :size="20" class="mr-2.5 text-red-500" />
                                Log out @{{ user.username }}
                            </button>
                        </div>

                        <!-- Trigger Button -->
                        <button
                            type="button"
                            @click.stop="showUserMenu = !showUserMenu; showMoreMenu = false;"
                            :class="isChatPage ? 'justify-center p-1.5 w-fit mx-auto' : 'w-full justify-between p-2'"
                            class="flex items-center rounded-full hover:bg-gray-100 dark:hover:bg-gray-900 cursor-pointer transition text-gray-900 dark:text-white"
                            :title="user.name"
                        >
                            <div class="flex items-center truncate">
                                <img
                                    :src="user.avatar_url"
                                    class="rounded-full w-10 h-10 object-cover bg-gray-700 flex-shrink-0"
                                    :alt="user.name"
                                />
                                <div v-if="!isChatPage" class="hidden lg:block ml-3 text-left truncate">
                                    <div class="font-bold text-gray-900 dark:text-white text-sm truncate">{{ user.name }}</div>
                                    <div class="text-xs text-gray-500 truncate">@{{ user.username }}</div>
                                </div>
                            </div>
                            <DotsHorizontal v-if="!isChatPage" fillColor="currentColor" :size="20" class="hidden lg:block text-gray-500 ml-2" />
                        </button>
                    </div>

                    <!-- Guest CTA Buttons -->
                    <div v-else class="space-y-2 px-1">
                        <Link
                            :href="route('login')"
                            :class="isChatPage ? 'p-2 text-center text-xs' : 'py-2.5 text-sm'"
                            class="w-full block text-center font-bold text-gray-900 dark:text-white border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-900 rounded-full transition"
                        >
                            <span :class="isChatPage ? 'hidden' : 'hidden lg:inline'">Log in</span>
                            <span :class="isChatPage ? 'inline' : 'lg:hidden'">Login</span>
                        </Link>
                        <Link
                            :href="route('register')"
                            :class="isChatPage ? 'p-2 text-center text-xs' : 'py-2.5 text-sm'"
                            class="w-full block text-center font-bold text-white dark:text-black bg-black dark:bg-white hover:bg-gray-800 dark:hover:bg-gray-200 py-2.5 rounded-full transition shadow"
                        >
                            <span :class="isChatPage ? 'hidden' : 'hidden lg:inline'">Sign up</span>
                            <span :class="isChatPage ? 'inline' : 'lg:hidden'">Join</span>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Center Timeline Feed Area -->
            <div
                :class="isChatPage || isSettingsPage || isBotPage
                    ? 'flex-1 w-full border-l border-gray-200 dark:border-gray-800'
                    : 'lg:w-7/12 w-11/12 border-x border-gray-200 dark:border-gray-800'"
                class="h-[100vh] overflow-y-auto scrollbar-hide relative bg-white dark:bg-black transition-colors duration-200"
            >
                <!-- Top Home Bar with For You / Following tabs -->
                <div v-if="showHomeHeader" class="bg-white/80 dark:bg-black/80 backdrop-blur-md z-20 sticky top-0 w-full border-b border-gray-200 dark:border-gray-800">
                    <div class="w-full text-gray-900 dark:text-white text-[20px] font-bold px-4 pt-3 pb-1">
                        Home
                    </div>
                    <div class="flex">
                        <button
                            type="button"
                            @click="switchFeedTab('for-you')"
                            :class="currentTab === 'for-you' ? 'text-gray-900 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                            class="flex-1 flex items-center justify-center h-[52px] text-[15px] font-bold hover:bg-gray-100 dark:hover:bg-gray-900/60 cursor-pointer transition relative"
                        >
                            <span>For you</span>
                            <div v-if="currentTab === 'for-you'" class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-16 h-1 bg-[#1C9CEF] rounded-full"></div>
                        </button>
                        <button
                            type="button"
                            @click="switchFeedTab('following')"
                            :class="currentTab === 'following' ? 'text-gray-900 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                            class="flex-1 flex items-center justify-center h-[52px] text-[15px] font-bold hover:bg-gray-100 dark:hover:bg-gray-900/60 cursor-pointer transition relative"
                        >
                            <span>Following</span>
                            <div v-if="currentTab === 'following'" class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-16 h-1 bg-[#1C9CEF] rounded-full"></div>
                        </button>
                    </div>
                </div>

                <slot />

                <!-- Suspended user notice banner -->
                <div
                    v-if="isSuspended"
                    class="sticky bottom-0 w-full bg-red-950 border-t border-red-800 px-4 py-3 flex items-center space-x-3 z-30"
                >
                    <span class="text-xl">🚫</span>
                    <div>
                        <p class="text-red-300 font-semibold text-sm">Tài khoản của bạn đang bị đình chỉ.</p>
                        <p class="text-red-400/70 text-xs">Bạn chỉ có thể xem nội dung. Vui lòng liên hệ quản trị viên để biết thêm thông tin.</p>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar Widgets -->
            <div
                v-if="!isChatPage && !isSettingsPage && !isBotPage"
                class="lg:w-4/12 hidden lg:block h-[100vh] border-l border-gray-200 dark:border-gray-800 px-4 pt-2 overflow-y-auto scrollbar-hide bg-white dark:bg-black transition-colors duration-200"
            >
                <!-- Search Box -->
                <form @submit.prevent="executeSearch" class="flex items-center bg-gray-100 dark:bg-[#212327] rounded-full px-4 py-1 text-gray-500 focus-within:text-[#1C9CEF] focus-within:border-[#1C9CEF] border border-transparent">
                    <Magnify :size="20" class="mr-2" />
                    <input
                        v-model="searchQuery"
                        class="
                            appearance-none
                            w-full
                            border-0
                            py-2
                            bg-transparent
                            text-gray-900
                            dark:text-gray-100
                            placeholder-gray-500
                            leading-tight
                            focus:ring-0
                        "
                        type="text"
                        placeholder="Search Twitter"
                    >
                </form>

                <!-- Real Trends Widget -->
                <div class="w-full mt-4 rounded-2xl bg-gray-50 dark:bg-[#16181c] border border-gray-200 dark:border-gray-800 p-4">
                    <div class="w-full text-gray-900 dark:text-white font-extrabold mb-3 text-[19px]">Xu hướng cho bạn</div>

                    <div v-if="trendsList && trendsList.length > 0">
                        <div
                            v-for="trend in trendsList"
                            :key="trend.tag"
                            @click="searchTag(trend.tag)"
                            class="py-2.5 hover:bg-gray-100 dark:hover:bg-gray-800/70 rounded-xl px-2 cursor-pointer transition duration-150"
                        >
                            <div class="text-[12px] text-gray-500 dark:text-gray-400">{{ trend.category }}</div>
                            <div class="text-gray-900 dark:text-white font-bold text-[15px]">{{ trend.tag }}</div>
                            <div class="text-[12px] text-gray-500 dark:text-gray-400">{{ trend.tweet_count }}</div>
                        </div>

                        <Link :href="route('explore')" class="text-[#1C9CEF] text-xs pt-2 hover:underline cursor-pointer block">
                            Xem thêm
                        </Link>
                    </div>

                    <div v-else class="py-3 text-xs text-gray-500 text-center">
                        Chưa có chủ đề xu hướng nào gần đây.
                    </div>
                </div>

                <!-- Real Who To Follow Widget -->
                <div v-if="whoToFollowList && whoToFollowList.length > 0" class="w-full mt-4 rounded-2xl bg-gray-50 dark:bg-[#16181c] border border-gray-200 dark:border-gray-800 p-4">
                    <div class="w-full text-gray-900 dark:text-white font-extrabold mb-3 text-[19px]">Gợi ý theo dõi</div>

                    <div
                        v-for="suggested in whoToFollowList"
                        :key="suggested.id"
                        class="py-2.5 flex items-center justify-between hover:bg-gray-100 dark:hover:bg-gray-800/50 rounded-xl px-2 transition"
                    >
                        <Link :href="route('users.profile', suggested.username)" class="flex items-center space-x-2.5 truncate mr-2">
                            <img :src="suggested.avatar_url" class="w-10 h-10 rounded-full object-cover bg-gray-700 flex-shrink-0" />
                            <div class="truncate">
                                <p class="text-sm font-bold text-gray-900 dark:text-white truncate hover:underline">{{ suggested.name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">@{{ suggested.username }}</p>
                            </div>
                        </Link>

                        <button
                            type="button"
                            @click="router.post(route('users.follow', suggested.id), {}, { preserveScroll: true })"
                            class="bg-black dark:bg-white hover:bg-gray-800 dark:hover:bg-gray-200 text-white dark:text-black text-xs font-bold px-3.5 py-1.5 rounded-full transition cursor-pointer flex-shrink-0 shadow-sm"
                        >
                            Theo dõi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tweet Creation Modal -->
    <div id="OverlaySection" v-if="createTweet" class="fixed top-0 left-0 w-full h-screen bg-black/60 dark:bg-black/80 backdrop-blur-sm z-50 flex items-start justify-center p-3">
        <div class="w-full max-w-2xl mt-10 rounded-2xl bg-white dark:bg-black border border-gray-200 dark:border-gray-800 shadow-2xl p-4 transition-colors">
            <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-800">
                <button
                    type="button"
                    @click="closeMessageBox"
                    class="hover:bg-gray-100 dark:hover:bg-gray-800 p-2 rounded-full cursor-pointer transition text-gray-700 dark:text-gray-300"
                >
                    <Close fillColor="currentColor" :size="24" />
                </button>
            </div>

            <!-- Error alert -->
            <div v-if="errorMessage" class="bg-red-900/50 border border-red-500 text-red-200 px-3 py-2 rounded-lg mt-3 text-sm">
                {{ errorMessage }}
            </div>

            <div class="w-full flex pt-3">
                <div class="mr-3">
                    <img
                        class="rounded-full w-11 h-11 object-cover bg-gray-700"
                        :src="user?.avatar_url || 'https://ui-avatars.com/api/?name=User&background=1C9CEF&color=fff'"
                        :alt="user?.name || 'User'"
                    />
                </div>

                <div class="w-[calc(100%-55px)]">
                    <div class="inline-block mb-1">
                        <div class="flex items-center border border-gray-300 dark:border-gray-700 px-3 py-0.5 rounded-full text-xs font-bold text-[#1C9CEF]">
                            <span>Everyone</span>
                            <ChevronDown class="ml-1" fillColor="#1C9CEF" :size="18"/>
                        </div>
                    </div>

                    <div>
                        <textarea
                            ref="textarea"
                            :oninput="textareaInput"
                            v-model="tweet"
                            placeholder="What's happening?"
                            rows="4"
                            class="
                                w-full
                                bg-transparent
                                border-0
                                focus:ring-0
                                text-gray-900
                                dark:text-white
                                text-[19px]
                                placeholder-gray-500
                                min-h-[110px]
                                resize-none
                            "
                        ></textarea>
                    </div>

                    <!-- Uploaded Media Preview -->
                    <div v-if="showUpload" class="relative my-3 rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-800 max-h-[350px]">
                        <button
                            type="button"
                            @click="clearUpload"
                            class="absolute top-2 right-2 bg-black/70 hover:bg-black/90 p-1.5 rounded-full text-white cursor-pointer z-10 transition"
                        >
                            <Close fillColor="#ffffff" :size="18" />
                        </button>
                        <video controls v-if="uploadType === 'mp4'" :src="showUpload" class="w-full rounded-2xl max-h-[350px] object-cover" />
                        <img v-else :src="showUpload" class="w-full rounded-2xl max-h-[350px] object-cover" />
                    </div>

                    <div class="flex py-2 items-center text-[#1C9CEF] text-xs font-bold">
                        <Earth class="mr-1.5" fillColor="#1C9CEF" :size="16" /> Everyone can reply
                    </div>

                    <div class="border-b border-gray-200 dark:border-gray-800 my-2"></div>

                    <!-- Modal Actions & Character Counter -->
                    <div class="flex items-center justify-between pt-1">
                        <div class="flex items-center space-x-1">
                            <label for="modalFileUpload" class="hover:bg-blue-900/30 p-2 rounded-full cursor-pointer text-[#1C9CEF] transition">
                                <ImageOutline fillColor="#1C9CEF" :size="22" />
                            </label>
                            <input type="file" id="modalFileUpload" class="hidden" accept="image/*,video/mp4" @change="getFile">

                            <!-- GIF Button with tooltip notice -->
                            <div
                                @click="insertEmoji(' [GIF] ')"
                                class="hover:bg-blue-900/30 p-2 rounded-full cursor-pointer text-[#1C9CEF] transition"
                                title="Insert GIF tag"
                            >
                                <FileGifBox fillColor="#1C9CEF" :size="22" />
                            </div>

                            <!-- Emoji Picker Button & Quick Popover -->
                            <div class="relative">
                                <div
                                    @click="showEmojiPicker = !showEmojiPicker"
                                    class="hover:bg-blue-900/30 p-2 rounded-full cursor-pointer text-[#1C9CEF] transition"
                                    title="Add emoji"
                                >
                                    <Emoticon fillColor="#1C9CEF" :size="22" />
                                </div>

                                <div
                                    v-if="showEmojiPicker"
                                    class="absolute bottom-12 left-0 bg-white dark:bg-[#16181c] border border-gray-200 dark:border-gray-800 rounded-2xl shadow-xl p-2 grid grid-cols-6 gap-1 z-30"
                                >
                                    <button
                                        v-for="e in commonEmojis"
                                        :key="e"
                                        type="button"
                                        @click="insertEmoji(e)"
                                        class="p-2 text-lg hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition"
                                    >
                                        {{ e }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- 280 Character Counter & Tweet Submit -->
                        <div class="flex items-center space-x-3">
                            <span
                                v-if="tweet"
                                :class="(280 - tweet.length) < 0 ? 'text-red-500 font-bold' : (280 - tweet.length) <= 20 ? 'text-yellow-500' : 'text-gray-500'"
                                class="text-xs"
                            >
                                {{ 280 - tweet.length }}
                            </span>

                            <button
                                :class="(!tweet && !file) || (tweet && tweet.length > 280) || isSubmitting ? 'bg-gray-300 dark:bg-white/50 text-gray-500 dark:text-black/50 cursor-not-allowed' : 'bg-black dark:bg-white hover:bg-gray-800 dark:hover:bg-gray-200 text-white dark:text-black cursor-pointer'"
                                :disabled="(!tweet && !file) || (tweet && tweet.length > 280) || isSubmitting"
                                @click="addTweet"
                                class="font-extrabold text-[15px] py-1.5 px-5 rounded-full transition shadow"
                            >
                                <span v-if="isSubmitting">Posting...</span>
                                <span v-else>Post</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Display Customization Modal -->
        <div v-if="showDisplayModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click="showDisplayModal = false">
            <div class="bg-white dark:bg-[#16181c] rounded-2xl w-full max-w-md p-6 shadow-2xl border border-gray-200 dark:border-gray-800" @click.stop>
                <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-800">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Customize your view</h3>
                    <button @click="showDisplayModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white p-1 rounded-full">
                        <Close :size="20" />
                    </button>
                </div>

                <div class="mt-4">
                    <p class="text-xs text-gray-500 mb-3">Manage your background and color theme.</p>

                    <!-- Background Theme Selection -->
                    <div class="grid grid-cols-2 gap-3 mt-3">
                        <button
                            type="button"
                            @click="if (isDarkMode) toggleTheme();"
                            :class="!isDarkMode ? 'border-[#1C9CEF] ring-2 ring-[#1C9CEF]' : 'border-gray-300 dark:border-gray-700'"
                            class="p-4 rounded-xl border bg-white text-gray-900 font-bold text-sm flex items-center justify-center space-x-2 transition"
                        >
                            <span>Default (Light)</span>
                        </button>

                        <button
                            type="button"
                            @click="if (!isDarkMode) toggleTheme();"
                            :class="isDarkMode ? 'border-[#1C9CEF] ring-2 ring-[#1C9CEF]' : 'border-gray-300 dark:border-gray-700'"
                            class="p-4 rounded-xl border bg-black text-white font-bold text-sm flex items-center justify-center space-x-2 transition"
                        >
                            <span>Lights out (Dark)</span>
                        </button>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button
                        type="button"
                        @click="showDisplayModal = false"
                        class="px-5 py-2 bg-[#1C9CEF] hover:bg-[#1A8CD8] text-white font-bold text-sm rounded-full transition shadow"
                    >
                        Done
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
