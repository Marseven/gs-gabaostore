<script setup>
defineProps({
    title: { type: String, default: '' },
});
const emit = defineEmits(['close']);
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-ink/40 backdrop-blur-sm" @click="emit('close')"></div>
        <div class="relative card shadow-soft-lg w-full max-w-lg max-h-[90vh] overflow-y-auto animate-rise">
            <div class="flex items-center justify-between border-b border-black/5 px-5 py-4">
                <h3 class="font-semibold text-ink">{{ title }}</h3>
                <button class="icon-btn !w-8 !h-8" @click="emit('close')">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-5">
                <slot />
            </div>
        </div>
    </div>
</template>
