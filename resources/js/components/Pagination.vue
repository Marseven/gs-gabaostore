<script setup>
const props = defineProps({
    meta: { type: Object, default: null },
});
const emit = defineEmits(['change']);

function go(page) {
    if (page < 1 || (props.meta && page > props.meta.last_page)) return;
    emit('change', page);
}
</script>

<template>
    <div v-if="meta && meta.last_page > 1" class="flex items-center justify-between text-sm text-muted mt-4">
        <span>
            {{ meta.from ?? 0 }}–{{ meta.to ?? 0 }} sur {{ meta.total }}
        </span>
        <div class="flex items-center gap-2">
            <button class="btn-secondary !px-3 !py-1.5 text-xs" :disabled="meta.current_page <= 1" @click="go(meta.current_page - 1)">
                Précédent
            </button>
            <span class="px-1 text-ink font-medium">{{ meta.current_page }} / {{ meta.last_page }}</span>
            <button class="btn-secondary !px-3 !py-1.5 text-xs" :disabled="meta.current_page >= meta.last_page" @click="go(meta.current_page + 1)">
                Suivant
            </button>
        </div>
    </div>
</template>
