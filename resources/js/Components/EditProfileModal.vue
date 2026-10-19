<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Close from 'vue-material-design-icons/Close.vue';
import CameraOutline from 'vue-material-design-icons/CameraOutline.vue';

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    isOpen: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

const avatarPreview = ref(props.user.avatar_url);
const bannerPreview = ref(props.user.banner_url);

const form = useForm({
    name: props.user.name || '',
    bio: props.user.bio || '',
    location: props.user.location || '',
    website: props.user.website || '',
    birthdate: props.user.birthdate || '',
    avatar: null,
    banner: null,
});

const onAvatarChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.avatar = file;
        avatarPreview.value = URL.createObjectURL(file);
    }
};

const onBannerChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.banner = file;
        bannerPreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.post(route('profile.custom_update'), {
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
        },
    });
};
</script>

<template>
    <div
        v-if="isOpen"
        class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto"
        @click.self="emit('close')"
    >
        <div class="bg-black border border-gray-800 rounded-2xl w-full max-w-xl shadow-2xl overflow-hidden my-6">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-800">
                <div class="flex items-center space-x-4">
                    <button
                        type="button"
                        @click="emit('close')"
                        class="p-1.5 rounded-full hover:bg-gray-800 text-white cursor-pointer transition"
                    >
                        <Close :size="20" />
                    </button>
                    <h2 class="text-lg font-bold text-white">Edit profile</h2>
                </div>

                <button
                    type="button"
                    :disabled="form.processing"
                    @click="submit"
                    class="bg-white hover:bg-gray-200 text-black font-bold text-sm px-5 py-1.5 rounded-full transition disabled:opacity-50 cursor-pointer"
                >
                    <span v-if="form.processing">Saving...</span>
                    <span v-else>Save</span>
                </button>
            </div>

            <!-- Form Content -->
            <form @submit.prevent="submit" class="max-h-[80vh] overflow-y-auto">
                <!-- Banner Image Field -->
                <div class="relative h-44 bg-gray-900 overflow-hidden">
                    <img
                        :src="bannerPreview"
                        class="w-full h-full object-cover opacity-80"
                        alt="Profile Banner"
                    />
                    <div class="absolute inset-0 bg-black/30 flex items-center justify-center">
                        <label
                            for="bannerUpload"
                            class="p-3 bg-black/60 hover:bg-black/80 rounded-full cursor-pointer text-white transition"
                            title="Add header photo"
                        >
                            <CameraOutline :size="22" />
                        </label>
                        <input
                            type="file"
                            id="bannerUpload"
                            class="hidden"
                            accept="image/*"
                            @change="onBannerChange"
                        />
                    </div>
                </div>

                <!-- Avatar Image Field -->
                <div class="px-4 -mt-14 mb-4 relative flex justify-between items-end">
                    <div class="relative rounded-full ring-4 ring-black overflow-hidden w-24 h-24 bg-gray-800">
                        <img
                            :src="avatarPreview"
                            class="w-full h-full object-cover"
                            alt="Avatar"
                        />
                        <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                            <label
                                for="avatarUpload"
                                class="p-2 bg-black/60 hover:bg-black/80 rounded-full cursor-pointer text-white transition"
                                title="Change avatar"
                            >
                                <CameraOutline :size="20" />
                            </label>
                            <input
                                type="file"
                                id="avatarUpload"
                                class="hidden"
                                accept="image/*"
                                @change="onAvatarChange"
                            />
                        </div>
                    </div>
                </div>

                <!-- Input Fields -->
                <div class="px-4 pb-6 space-y-4">
                    <!-- Name Input -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Name</label>
                        <input
                            type="text"
                            v-model="form.name"
                            required
                            maxlength="50"
                            class="w-full bg-black border border-gray-700 rounded-lg px-3 py-2 text-white focus:border-[#1C9CEF] focus:ring-0 text-sm"
                            placeholder="Your display name"
                        />
                        <div v-if="form.errors.name" class="text-red-400 text-xs mt-1">{{ form.errors.name }}</div>
                    </div>

                    <!-- Bio Input -->
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label class="block text-xs font-semibold text-gray-500">Bio</label>
                            <span class="text-xs text-gray-600">{{ 160 - (form.bio ? form.bio.length : 0) }}</span>
                        </div>
                        <textarea
                            v-model="form.bio"
                            rows="3"
                            maxlength="160"
                            class="w-full bg-black border border-gray-700 rounded-lg px-3 py-2 text-white focus:border-[#1C9CEF] focus:ring-0 text-sm resize-none"
                            placeholder="Tell the world about yourself"
                        ></textarea>
                        <div v-if="form.errors.bio" class="text-red-400 text-xs mt-1">{{ form.errors.bio }}</div>
                    </div>

                    <!-- Location Input -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Location</label>
                        <input
                            type="text"
                            v-model="form.location"
                            maxlength="100"
                            class="w-full bg-black border border-gray-700 rounded-lg px-3 py-2 text-white focus:border-[#1C9CEF] focus:ring-0 text-sm"
                            placeholder="e.g. San Francisco, CA"
                        />
                        <div v-if="form.errors.location" class="text-red-400 text-xs mt-1">{{ form.errors.location }}</div>
                    </div>

                    <!-- Website Input -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Website</label>
                        <input
                            type="url"
                            v-model="form.website"
                            maxlength="100"
                            class="w-full bg-black border border-gray-700 rounded-lg px-3 py-2 text-white focus:border-[#1C9CEF] focus:ring-0 text-sm"
                            placeholder="https://yourwebsite.com"
                        />
                        <div v-if="form.errors.website" class="text-red-400 text-xs mt-1">{{ form.errors.website }}</div>
                    </div>

                    <!-- Birthdate Input -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Birth date</label>
                        <input
                            type="date"
                            v-model="form.birthdate"
                            class="w-full bg-black border border-gray-700 rounded-lg px-3 py-2 text-white focus:border-[#1C9CEF] focus:ring-0 text-sm"
                        />
                        <div v-if="form.errors.birthdate" class="text-red-400 text-xs mt-1">{{ form.errors.birthdate }}</div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>
