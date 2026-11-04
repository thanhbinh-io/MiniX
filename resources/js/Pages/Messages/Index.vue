<script setup>
import { ref, computed, nextTick, onMounted, onUnmounted, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import TwitterLayout from '@/Layouts/TwitterLayout.vue';
import Send from 'vue-material-design-icons/Send.vue';
import AccountCircle from 'vue-material-design-icons/AccountCircle.vue';

const props = defineProps({
    contacts: {
        type: Array,
        default: () => [],
    },
    activeContact: {
        type: Object,
        default: null,
    },
    messages: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const currentUser = computed(() => page.props.auth?.user);

const messageText = ref('');
const isSending = ref(false);
const chatContainer = ref(null);

const scrollToBottom = () => {
    nextTick(() => {
        if (chatContainer.value) {
            chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
        }
    });
};

let pollTimer = null;

const pollMessages = () => {
    if (document.hidden || isSending.value || !props.activeContact) return;

    router.reload({
        only: ['messages', 'contacts'],
        preserveScroll: true,
        preserveState: true,
    });
};

onMounted(() => {
    scrollToBottom();
    // Poll every 2.5 seconds for instant real-time sync across devices
    pollTimer = setInterval(pollMessages, 2500);
});

onUnmounted(() => {
    if (pollTimer) {
        clearInterval(pollTimer);
        pollTimer = null;
    }
});

watch(() => props.messages, () => {
    scrollToBottom();
}, { deep: true });

const selectContact = (contact) => {
    router.visit(route('messages.index', { contact_id: contact.id }), {
        preserveScroll: true,
        preserveState: true,
    });
};

const sendMessage = () => {
    if (!messageText.value.trim() || isSending.value || !props.activeContact) return;

    isSending.value = true;
    const textToSend = messageText.value.trim();
    messageText.value = '';

    router.post(route('messages.store'), {
        receiver_id: props.activeContact.id,
        message: textToSend,
    }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            scrollToBottom();
        },
        onError: () => {
            messageText.value = textToSend;
        },
        onFinish: () => {
            isSending.value = false;
        },
    });
};
const formatMessage = (text) => {
    if (!text) return '';
    const escaped = text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
    const urlRegex = /(https?:\/\/[^\s]+)/g;
    return escaped.replace(urlRegex, (url) => {
        const isInternal = typeof window !== 'undefined' && url.startsWith(window.location.origin);
        if (isInternal) {
            return `<a href="${url}" class="underline hover:opacity-85 font-medium break-all cursor-pointer">${url}</a>`;
        }
        return `<a href="${url}" target="_blank" rel="noopener noreferrer" class="underline hover:opacity-85 font-medium break-all">${url}</a>`;
    });
};

const handleMessageClick = (e) => {
    const anchor = e.target.closest('a');
    if (!anchor) return;

    const href = anchor.getAttribute('href');
    if (!href) return;

    const currentOrigin = window.location.origin;
    if (href.startsWith(currentOrigin) || href.startsWith('/')) {
        e.preventDefault();
        const targetPath = href.startsWith(currentOrigin) ? href.substring(currentOrigin.length) : href;
        router.visit(targetPath);
    }
};
</script>

<template>
    <Head title="Chat / Twitter" />

    <TwitterLayout>
        <div class="h-[100vh] flex text-white overflow-hidden w-full">
            <!-- Left Sub-column: Conversations List -->
            <div class="w-full md:w-[360px] lg:w-[400px] border-r border-gray-800 flex flex-col h-full bg-black flex-shrink-0">
                <!-- Header -->
                <div class="p-4 border-b border-gray-800 flex items-center justify-between">
                    <h1 class="text-xl font-bold">Chat</h1>
                </div>

                <!-- Contact List -->
                <div class="flex-1 overflow-y-auto divide-y divide-gray-800/60 scrollbar-hide">
                    <div
                        v-if="contacts.length === 0"
                        class="p-8 text-center text-gray-500 text-sm"
                    >
                        Chưa có cuộc trò chuyện nào. Hãy vào trang cá nhân của bạn bè để bắt đầu trò chuyện!
                    </div>

                    <div
                        v-for="contact in contacts"
                        :key="contact.id"
                        @click="selectContact(contact)"
                        :class="activeContact?.id === contact.id ? 'bg-gray-900/70 border-r-2 border-[#1C9CEF]' : 'hover:bg-gray-950'"
                        class="p-4 flex items-center space-x-3 cursor-pointer transition"
                    >
                        <div class="relative flex-shrink-0">
                            <img
                                :src="contact.avatar_url"
                                :alt="contact.name"
                                class="w-12 h-12 rounded-full object-cover bg-gray-800"
                            />
                            <span
                                v-if="contact.unread_count > 0"
                                class="absolute -top-1 -right-1 bg-[#1C9CEF] text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center border-2 border-black"
                            >
                                {{ contact.unread_count }}
                            </span>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-bold truncate text-white">{{ contact.name }}</p>
                                <span class="text-xs text-gray-500 ml-1 whitespace-nowrap">{{ contact.last_message_at }}</span>
                            </div>
                            <p class="text-xs text-gray-400">@{{ contact.username }}</p>
                            <p class="text-xs text-gray-500 truncate mt-1">
                                {{ contact.last_message || 'Bắt đầu cuộc trò chuyện...' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Sub-column: Active Chat Window -->
            <div class="hidden md:flex flex-1 flex-col h-full bg-black">
                <!-- If contact selected -->
                <template v-if="activeContact">
                    <!-- Chat Header -->
                    <div class="px-5 py-3 border-b border-gray-800 flex items-center justify-between bg-black/80 backdrop-blur-md">
                        <div class="flex items-center space-x-3">
                            <img
                                :src="activeContact.avatar_url"
                                class="w-10 h-10 rounded-full object-cover"
                            />
                            <div>
                                <Link
                                    :href="route('users.profile', activeContact.username)"
                                    class="text-sm font-bold text-white hover:underline"
                                >
                                    {{ activeContact.name }}
                                </Link>
                                <p class="text-xs text-gray-500">@{{ activeContact.username }}</p>
                            </div>
                        </div>

                        <Link
                            :href="route('users.profile', activeContact.username)"
                            class="text-xs text-[#1C9CEF] hover:underline"
                        >
                            Xem hồ sơ
                        </Link>
                    </div>

                    <!-- Messages Thread -->
                    <div
                        ref="chatContainer"
                        class="flex-1 overflow-y-auto p-4 space-y-3 scrollbar-hide"
                        @click="handleMessageClick"
                    >
                        <div
                            v-if="messages.length === 0"
                            class="text-center text-gray-500 py-16 text-sm"
                        >
                            <AccountCircle :size="48" class="mx-auto mb-2 text-gray-700" />
                            Gửi tin nhắn đầu tiên cho <span class="font-bold text-white">@{{ activeContact.username }}</span>!
                        </div>

                        <div
                            v-for="msg in messages"
                            :key="msg.id"
                            :class="msg.is_mine ? 'justify-end' : 'justify-start'"
                            class="flex items-end space-x-2"
                        >
                            <img
                                v-if="!msg.is_mine"
                                :src="activeContact.avatar_url"
                                class="w-7 h-7 rounded-full object-cover flex-shrink-0 mb-1"
                            />

                            <div
                                :class="msg.is_mine ? 'bg-[#1C9CEF] text-white rounded-2xl rounded-br-xs' : 'bg-gray-800 text-gray-200 rounded-2xl rounded-bl-xs'"
                                class="max-w-[75%] px-4 py-2.5 text-sm leading-relaxed break-words shadow-sm"
                            >
                                <p class="whitespace-pre-wrap" v-html="formatMessage(msg.message)"></p>
                                <span
                                    :class="msg.is_mine ? 'text-blue-100/70' : 'text-gray-400'"
                                    class="text-[10px] block text-right mt-1"
                                >
                                    {{ msg.created_at_human }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Message Input Box -->
                    <div class="p-3 border-t border-gray-800 bg-gray-950">
                        <form @submit.prevent="sendMessage" class="flex items-center space-x-2">
                            <input
                                v-model="messageText"
                                type="text"
                                maxlength="2000"
                                placeholder="Gõ tin nhắn mới..."
                                class="flex-1 bg-gray-900 border border-gray-700 rounded-full px-4 py-2.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-[#1C9CEF] transition"
                            />

                            <button
                                type="submit"
                                :disabled="!messageText.trim() || isSending"
                                :class="!messageText.trim() || isSending ? 'opacity-40 cursor-not-allowed' : 'hover:bg-[#1A8CD8] cursor-pointer'"
                                class="bg-[#1C9CEF] text-white p-2.5 rounded-full transition shadow"
                            >
                                <Send :size="18" />
                            </button>
                        </form>
                    </div>
                </template>

                <!-- If no contact selected -->
                <div v-else class="flex-1 flex flex-col items-center justify-center p-8 text-center text-gray-500">
                    <div class="text-5xl mb-4">💬</div>
                    <h2 class="text-xl font-bold text-white mb-2">Chọn một cuộc trò chuyện</h2>
                    <p class="text-sm max-w-sm">Chọn một người từ danh sách bên trái hoặc ghé thăm trang cá nhân để bắt đầu nhắn tin.</p>
                </div>
            </div>
        </div>
    </TwitterLayout>
</template>

