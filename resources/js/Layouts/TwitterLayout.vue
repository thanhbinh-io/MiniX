<script setup>
import { ref, computed } from 'vue';
import { router, usePage, Link } from '@inertiajs/vue3';
import Twitter from 'vue-material-design-icons/Twitter.vue';
import Magnify from 'vue-material-design-icons/Magnify.vue';
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue';
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

const switchFeedTab = (tab) => {
    router.visit(route('tweets.index', { tab }), {
        preserveScroll: true,
        preserveState: true,
    });
};

let showUserMenu = ref(false);
let createTweet = ref(false);
let tweet = ref('');
let file = ref(null);
let showUpload = ref('');
let uploadType = ref('');
let errorMessage = ref('');
let isSubmitting = ref(false);

let randImg1 = ref(`https://picsum.photos/id/${(Math.random() * 200).toFixed(0)}/100`);
let randImg2 = ref(`https://picsum.photos/id/${(Math.random() * 200).toFixed(0)}/100`);

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
    <div class="fixed w-full">
        <div class="max-w-[1400px] flex mx-auto">
            <!-- Left Sidebar Navigation -->
            <div class="lg:w-3/12 w-[60px] h-[100vh] max-w-[350px] lg:px-4 lg:mx-auto flex flex-col justify-between pb-4">
                <div>
                    <div class="p-2 px-3 mb-2">
                        <Link href="/">
                            <Twitter fillColor="#FFFFFF" :size="37" />
                        </Link>
                    </div>

                    <MenuItem iconString="Home" href="/" />
                    <MenuItem iconString="Explore" />
                    <MenuItem iconString="Notifications" />
                    <MenuItem iconString="Messages" />
                    <MenuItem iconString="Bookmarks" :href="user ? route('bookmarks.index') : route('login')" />
                    <MenuItem iconString="Profile" :href="user ? route('users.profile', user.username) : route('login')" />

                    <button
                        @click="openTweetModal"
                        class="w-full mt-6 text-white font-extrabold text-[20px] bg-[#1C9CEF] hover:bg-[#1A8CD8] p-3 px-3 rounded-full cursor-pointer transition shadow"
                    >
                        <span class="lg:block hidden">Tweet</span>
                        <span class="block lg:hidden flex justify-center"><Feather /></span>
                    </button>
                </div>

                <!-- Bottom User / Auth Action Area -->
                <div class="relative">
                    <!-- Logged In User Pill -->
                    <div v-if="user" class="relative">
                        <!-- Popover Menu -->
                        <div
                            v-if="showUserMenu"
                            class="absolute bottom-16 left-0 w-full min-w-[240px] bg-black border border-gray-700 rounded-2xl shadow-2xl p-2 z-50 mb-2"
                        >
                            <div class="flex items-center p-3 border-b border-gray-800">
                                <img
                                    :src="user.avatar_url"
                                    class="rounded-full w-10 h-10 object-cover bg-gray-700"
                                    :alt="user.name"
                                />
                                <div class="ml-3 truncate">
                                    <div class="font-bold text-white text-sm truncate">{{ user.name }}</div>
                                    <div class="text-xs text-gray-500 truncate">@{{ user.username }}</div>
                                </div>
                            </div>

                            <Link
                                :href="route('users.profile', user.username)"
                                @click="showUserMenu = false"
                                class="flex items-center px-3 py-2.5 hover:bg-gray-800 rounded-lg text-sm text-gray-200 cursor-pointer mt-1"
                            >
                                <Account :size="20" class="mr-2.5 text-gray-400" />
                                View profile
                            </Link>

                            <button
                                @click="logout"
                                class="w-full text-left flex items-center px-3 py-2.5 hover:bg-gray-800 rounded-lg text-sm font-bold text-red-500 cursor-pointer"
                            >
                                <Logout :size="20" class="mr-2.5 text-red-500" />
                                Log out @{{ user.username }}
                            </button>
                        </div>

                        <!-- Trigger Button -->
                        <button
                            type="button"
                            @click="showUserMenu = !showUserMenu"
                            class="w-full flex items-center justify-between p-2 rounded-full hover:bg-gray-900 cursor-pointer transition"
                        >
                            <div class="flex items-center truncate">
                                <img
                                    :src="user.avatar_url"
                                    class="rounded-full w-10 h-10 object-cover bg-gray-700"
                                    :alt="user.name"
                                />
                                <div class="hidden lg:block ml-3 text-left truncate">
                                    <div class="font-bold text-white text-sm truncate">{{ user.name }}</div>
                                    <div class="text-xs text-gray-500 truncate">@{{ user.username }}</div>
                                </div>
                            </div>
                            <DotsHorizontal fillColor="#ffffff" :size="20" class="hidden lg:block text-gray-500 ml-2" />
                        </button>
                    </div>

                    <!-- Guest CTA Buttons -->
                    <div v-else class="space-y-2 px-1">
                        <Link
                            :href="route('login')"
                            class="w-full block text-center font-bold text-sm text-white border border-gray-600 hover:bg-gray-900 py-2.5 rounded-full transition"
                        >
                            <span class="hidden lg:inline">Log in</span>
                            <span class="lg:hidden text-xs">Login</span>
                        </Link>
                        <Link
                            :href="route('register')"
                            class="w-full block text-center font-bold text-sm text-black bg-white hover:bg-gray-200 py-2.5 rounded-full transition shadow"
                        >
                            <span class="hidden lg:inline">Sign up</span>
                            <span class="lg:hidden text-xs">Join</span>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Center Timeline Feed Area -->
            <div class="lg:w-7/12 w-11/12 border-x border-gray-800 h-[100vh] overflow-y-auto scrollbar-hide relative">
                <!-- Top Home Bar with For You / Following tabs -->
                <div v-if="showHomeHeader" class="bg-black/80 backdrop-blur-md z-20 sticky top-0 w-full border-b border-gray-800">
                    <div class="w-full text-white text-[20px] font-bold px-4 pt-3 pb-1">
                        Home
                    </div>
                    <div class="flex">
                        <button
                            type="button"
                            @click="switchFeedTab('for-you')"
                            :class="currentTab === 'for-you' ? 'text-white' : 'text-gray-500 hover:text-gray-300'"
                            class="flex-1 flex items-center justify-center h-[52px] text-[15px] font-bold hover:bg-gray-900/60 cursor-pointer transition relative"
                        >
                            <span>For you</span>
                            <div v-if="currentTab === 'for-you'" class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-16 h-1 bg-[#1C9CEF] rounded-full"></div>
                        </button>
                        <button
                            type="button"
                            @click="switchFeedTab('following')"
                            :class="currentTab === 'following' ? 'text-white' : 'text-gray-500 hover:text-gray-300'"
                            class="flex-1 flex items-center justify-center h-[52px] text-[15px] font-bold hover:bg-gray-900/60 cursor-pointer transition relative"
                        >
                            <span>Following</span>
                            <div v-if="currentTab === 'following'" class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-16 h-1 bg-[#1C9CEF] rounded-full"></div>
                        </button>
                    </div>
                </div>

                <slot />
                <div class="pb-24"></div>
            </div>

            <!-- Right Trending / Search Area -->
            <div class="lg:block hidden lg:w-4/12 h-screen border-l border-gray-800 pl-4">
                <div class="w-full p-1 mt-2 px-4 lg:flex items-center rounded-full hidden bg-[#212327]">
                    <Magnify fillColor="#5e5c5c" :size="25"/>
                    <input
                        class="
                            appearance-none
                            w-full
                            border-0
                            py-2
                            bg-[#212327]
                            text-gray-100
                            placeholder-gray-500
                            leading-tight
                            focus:ring-0
                        "
                        type="text"
                        placeholder="Search Twitter"
                    >
                </div>

                <div class="w-full mt-4 rounded-2xl lg:block hidden bg-[#212327] p-4">
                    <div class="w-full text-white font-extrabold mb-4 text-[20px]">What's happening</div>

                    <div class="py-3 hover:bg-gray-800 rounded-xl px-2 cursor-pointer transition duration-200">
                        <div class="text-[13px] text-gray-400">Technology · Trending</div>
                        <div class="text-white font-bold text-[15px]">#TwitterClone</div>
                        <div class="text-[13px] text-gray-400">Laravel 9 + Vue 3</div>
                    </div>

                    <div class="py-3 hover:bg-gray-800 rounded-xl px-2 cursor-pointer transition duration-200">
                        <div class="text-[13px] text-gray-400">Web Development · Trending</div>
                        <div class="text-white font-bold text-[15px]">Inertia.js</div>
                        <div class="text-[13px] text-gray-400">Full-stack Monolith</div>
                    </div>

                    <div class="py-3 hover:bg-gray-800 rounded-xl px-2 cursor-pointer transition duration-200">
                        <div class="text-[13px] text-gray-400">Design · Trending</div>
                        <div class="text-white font-bold text-[15px]">TailwindCSS</div>
                        <div class="text-[13px] text-gray-400">Modern Dark Theme</div>
                    </div>

                    <div class="text-[#1C9CEF] text-sm pt-2 hover:underline cursor-pointer">
                        Show more
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tweet Creation Modal -->
    <div id="OverlaySection" v-if="createTweet" class="fixed top-0 left-0 w-full h-screen bg-black bg-opacity-70 backdrop-blur-sm z-50 flex items-start justify-center p-3">
        <div class="w-full max-w-2xl mt-10 rounded-2xl bg-black border border-gray-800 shadow-2xl p-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-800">
                <button
                    type="button"
                    @click="closeMessageBox"
                    class="hover:bg-gray-800 p-2 rounded-full cursor-pointer transition"
                >
                    <Close fillColor="#FFFFFF" :size="24" />
                </button>

                <button
                    :class="(!tweet && !file) || (tweet && tweet.length > 280) || isSubmitting ? 'bg-[#124D77] text-gray-400 cursor-not-allowed' : 'bg-[#1C9CEF] hover:bg-[#1A8CD8] text-white cursor-pointer'"
                    :disabled="(!tweet && !file) || (tweet && tweet.length > 280) || isSubmitting"
                    @click="addTweet"
                    class="font-extrabold text-[15px] py-1.5 px-5 rounded-full transition"
                >
                    <span v-if="isSubmitting">Posting...</span>
                    <span v-else>Tweet</span>
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
                        <div class="flex items-center border border-gray-700 px-3 py-0.5 rounded-full text-xs font-bold text-[#1C9CEF]">
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
                                bg-black
                                border-0
                                focus:ring-0
                                text-white
                                text-[19px]
                                placeholder-gray-500
                                min-h-[110px]
                                resize-none
                            "
                        ></textarea>
                    </div>

                    <!-- Uploaded Media Preview -->
                    <div v-if="showUpload" class="relative my-3 rounded-2xl overflow-hidden border border-gray-800 max-h-[350px]">
                        <button
                            type="button"
                            @click="clearUpload"
                            class="absolute top-2 right-2 bg-black bg-opacity-70 hover:bg-opacity-90 p-1.5 rounded-full text-white cursor-pointer z-10 transition"
                        >
                            <Close fillColor="#ffffff" :size="18" />
                        </button>
                        <video controls v-if="uploadType === 'mp4'" :src="showUpload" class="w-full rounded-2xl max-h-[350px] object-cover" />
                        <img v-else :src="showUpload" class="w-full rounded-2xl max-h-[350px] object-cover" />
                    </div>

                    <div class="flex py-2 items-center text-[#1C9CEF] text-xs font-bold">
                        <Earth class="mr-1.5" fillColor="#1C9CEF" :size="16" /> Everyone can reply
                    </div>

                    <div class="border-b border-gray-800 my-2"></div>

                    <!-- Modal Actions & Character Counter -->
                    <div class="flex items-center justify-between pt-1">
                        <div class="flex items-center space-x-1">
                            <label for="modalFileUpload" class="hover:bg-blue-900/30 p-2 rounded-full cursor-pointer text-[#1C9CEF] transition">
                                <ImageOutline fillColor="#1C9CEF" :size="22" />
                            </label>
                            <input type="file" id="modalFileUpload" class="hidden" accept="image/*,video/mp4" @change="getFile">

                            <div class="hover:bg-blue-900/30 p-2 rounded-full cursor-pointer text-[#1C9CEF] transition">
                                <FileGifBox fillColor="#1C9CEF" :size="22" />
                            </div>
                            <div class="hover:bg-blue-900/30 p-2 rounded-full cursor-pointer text-[#1C9CEF] transition">
                                <Emoticon fillColor="#1C9CEF" :size="22" />
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
                                :class="(!tweet && !file) || (tweet && tweet.length > 280) || isSubmitting ? 'bg-[#124D77] text-gray-400 cursor-not-allowed' : 'bg-[#1C9CEF] hover:bg-[#1A8CD8] text-white cursor-pointer'"
                                :disabled="(!tweet && !file) || (tweet && tweet.length > 280) || isSubmitting"
                                @click="addTweet"
                                class="font-extrabold text-[15px] py-1.5 px-5 rounded-full transition"
                            >
                                <span v-if="isSubmitting">Posting...</span>
                                <span v-else>Tweet</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
