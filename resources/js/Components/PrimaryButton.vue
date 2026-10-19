<script setup>
defineProps({
    type: {
        type: String,
        default: 'submit',
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center justify-center px-5 py-2.5 bg-[#1C9CEF] border border-transparent rounded-full font-bold text-sm text-white hover:bg-[#1A8CD8] active:bg-[#177CC0] focus:outline-none focus:ring-2 focus:ring-[#1C9CEF] focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 cursor-pointer"
    >
        <slot />
    </button>
</template>
