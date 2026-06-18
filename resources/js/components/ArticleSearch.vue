<script setup>
import { ref, watch } from 'vue';
import { useArticlesStore } from '../stores/articles';

const props = defineProps({
    modelValue: { type: Object, default: null },
});
const emit = defineEmits(['update:modelValue']);

const articles = useArticlesStore();
const term = ref(props.modelValue ? `${props.modelValue.reference} — ${props.modelValue.designation}` : '');
const results = ref([]);
const open = ref(false);
const loading = ref(false);
let timer = null;
// Vrai quand on remplit `term` nous-mêmes (sélection) : évite que le watch
// ne déselectionne l'article qu'on vient de choisir.
let isSelecting = false;

watch(term, (val) => {
    if (isSelecting) {
        return; // changement programmatique dû à select() : on ne touche à rien.
    }
    if (props.modelValue) {
        // L'utilisateur retape : on déselectionne.
        emit('update:modelValue', null);
    }
    clearTimeout(timer);
    if (!val || val.length < 1) {
        results.value = [];
        open.value = false;
        return;
    }
    timer = setTimeout(async () => {
        loading.value = true;
        try {
            results.value = await articles.search(val);
            open.value = true;
        } finally {
            loading.value = false;
        }
    }, 250);
});

function select(article) {
    isSelecting = true;
    emit('update:modelValue', article);
    term.value = `${article.reference} — ${article.designation}`;
    open.value = false;
    results.value = [];
    // Réactive le watch après que la mise à jour de `term` ait été traitée.
    setTimeout(() => { isSelecting = false; }, 0);
}
</script>

<template>
    <div class="relative">
        <input
            v-model="term"
            type="text"
            class="input"
            placeholder="Rechercher un article (référence ou désignation)…"
            autocomplete="off"
            @focus="results.length && (open = true)"
        />
        <div
            v-if="open && results.length"
            class="absolute z-20 mt-2 w-full bg-surface rounded-2xl shadow-soft-lg ring-1 ring-black/5 max-h-60 overflow-y-auto p-1.5"
        >
            <button
                v-for="a in results"
                :key="a.id"
                type="button"
                class="w-full text-left px-3 py-2 text-sm rounded-xl hover:bg-lime-pale flex justify-between gap-2 transition"
                @click="select(a)"
            >
                <span><span class="font-semibold">{{ a.reference }}</span> — {{ a.designation }}
                    <span v-if="a.prix != null" class="text-muted">· {{ a.prix }}</span>
                </span>
                <span v-if="a.suivi_stock" class="text-muted shrink-0">stock : {{ a.stock_actuel }}</span>
                <span v-else class="text-muted/70 shrink-0">non suivi</span>
            </button>
        </div>
        <p v-if="open && !results.length && !loading" class="absolute z-20 mt-2 w-full bg-surface rounded-2xl shadow-soft ring-1 ring-black/5 px-4 py-3 text-sm text-muted">
            Aucun article trouvé.
        </p>
    </div>
</template>
