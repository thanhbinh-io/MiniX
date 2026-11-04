<template>
    <Link
        v-if="href"
        :href="href"
        :class="collapsed ? 'justify-center p-3 w-fit mx-auto' : 'w-full p-2 px-4 lg:pr-6'"
        class="block hover:bg-gray-100 dark:hover:bg-gray-900 rounded-full cursor-pointer transition duration-200 ease-in-out text-gray-900 dark:text-white"
        :title="iconString"
    >
        <div class="flex items-center" :class="{ 'justify-center': collapsed }">
            <div class="relative flex items-center justify-center">
                <component :is="icon" fillColor="currentColor" :size="30" />
                <span
                    v-if="badgeCount > 0"
                    class="absolute -top-1 -right-1 bg-[#1C9CEF] text-white text-[11px] font-bold px-1.5 py-0.2 rounded-full min-w-[18px] text-center"
                >
                    {{ badgeCount > 99 ? '99+' : badgeCount }}
                </span>
            </div>
            <span
                v-if="!collapsed"
                class="lg:block hidden font-extrabold text-[23px] pl-4 mt-0.5"
            >
                {{ iconString }}
            </span>
        </div>
    </Link>
    <div
        v-else
        :class="collapsed ? 'justify-center p-3 w-fit mx-auto' : 'w-full lg:inline-block p-2 px-4 lg:pr-6'"
        class="hover:bg-gray-100 dark:hover:bg-gray-900 rounded-full cursor-pointer transition duration-200 ease-in-out text-gray-900 dark:text-white"
        :title="iconString"
    >
        <div class="flex items-center" :class="{ 'justify-center': collapsed }">
            <div class="relative flex items-center justify-center">
                <component :is="icon" fillColor="currentColor" :size="30" />
                <span
                    v-if="badgeCount > 0"
                    class="absolute -top-1 -right-1 bg-[#1C9CEF] text-white text-[11px] font-bold px-1.5 py-0.2 rounded-full min-w-[18px] text-center"
                >
                    {{ badgeCount > 99 ? '99+' : badgeCount }}
                </span>
            </div>
            <span
                v-if="!collapsed"
                class="lg:block hidden font-extrabold text-[23px] pl-4 mt-0.5"
            >
                {{ iconString }}
            </span>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import Home from 'vue-material-design-icons/Home.vue';
import Magnify from 'vue-material-design-icons/Magnify.vue';
import BellOutline from 'vue-material-design-icons/BellOutline.vue';
import ChatOutline from 'vue-material-design-icons/ChatOutline.vue';
import BookmarkOutline from 'vue-material-design-icons/BookmarkOutline.vue';
import AccountOutline from 'vue-material-design-icons/AccountOutline.vue';
import ShieldCrownOutline from 'vue-material-design-icons/ShieldCrownOutline.vue';
import RobotOutline from 'vue-material-design-icons/RobotOutline.vue';

const props = defineProps({
    iconString: String,
    href: {
        type: String,
        default: null,
    },
    badgeCount: {
        type: Number,
        default: 0,
    },
    collapsed: {
        type: Boolean,
        default: false,
    },
})

const icon = computed(() => {
    if (props.iconString === 'Home') return Home
    if (props.iconString === 'Explore') return Magnify
    if (props.iconString === 'Notifications') return BellOutline
    if (props.iconString === 'Messages' || props.iconString === 'Chat') return ChatOutline
    if (props.iconString === 'Bot' || props.iconString === 'Grok') return RobotOutline
    if (props.iconString === 'Bookmarks') return BookmarkOutline
    if (props.iconString === 'Profile') return AccountOutline
    if (props.iconString === 'Admin') return ShieldCrownOutline
    return Home
})
</script>
