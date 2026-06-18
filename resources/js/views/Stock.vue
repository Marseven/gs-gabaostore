<script setup>
import { ref, onMounted, computed } from 'vue';
import api, { getToken } from '../lib/api';
import { useCategoriesStore } from '../stores/categories';

const categories = useCategoriesStore();
const items = ref([]);
const loading = ref(false);
const filters = ref({ categorie_id: '', search: '' });

async function load() {
    loading.value = true;
    try {
        const params = {};
        if (filters.value.categorie_id) params.categorie_id = filters.value.categorie_id;
        if (filters.value.search) params.search = filters.value.search;
        const { data } = await api.get('/stock', { params });
        items.value = data.data;
    } finally {
        loading.value = false;
    }
}

function exportStock() {
    const token = getToken();
    // Téléchargement via fetch pour porter le header Authorization.
    fetch('/api/v1/export/stock', { headers: { Authorization: `Bearer ${token}` } })
        .then((r) => r.blob())
        .then((blob) => {
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'stock.xlsx';
            a.click();
            URL.revokeObjectURL(url);
        });
}

const total = computed(() => items.value.reduce((s, a) => s + a.stock_actuel, 0));

onMounted(() => {
    categories.fetch();
    load();
});
</script>

<template>
    <div>
        <div class="flex flex-wrap items-end justify-between gap-3 mb-5 animate-rise">
            <div>
                <span class="badge-soft mb-2">Niveaux actuels</span>
                <h1 class="text-heading font-semibold text-ink">État du stock</h1>
            </div>
            <button class="btn-secondary" @click="exportStock">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                Export Excel
            </button>
        </div>

        <div class="card p-4 mb-4 flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="label">Recherche</label>
                <input v-model="filters.search" type="text" class="input" placeholder="Référence ou désignation" @keyup.enter="load" />
            </div>
            <div class="min-w-[180px]">
                <label class="label">Catégorie</label>
                <select v-model="filters.categorie_id" class="input">
                    <option value="">Toutes</option>
                    <option v-for="c in categories.items" :key="c.id" :value="c.id">{{ c.nom }}</option>
                </select>
            </div>
            <button class="btn-primary" @click="load">Filtrer</button>
        </div>

        <div class="card overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-black/5">
                        <th class="th">Référence</th>
                        <th class="th">Désignation</th>
                        <th class="th">Catégorie</th>
                        <th class="th">Stock actuel</th>
                        <th class="th">Seuil</th>
                        <th class="th">État</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="a in items" :key="a.id" class="border-b border-black/5 last:border-0 hover:bg-black/[0.02]">
                        <td class="td font-semibold">{{ a.reference }}</td>
                        <td class="td">{{ a.designation }}</td>
                        <td class="td text-muted">{{ a.categorie?.nom || '—' }}</td>
                        <td class="td font-semibold">{{ a.stock_actuel }} {{ a.unite }}</td>
                        <td class="td text-muted">{{ a.seuil_alerte ?? '—' }}</td>
                        <td class="td">
                            <span v-if="a.en_alerte" class="badge bg-red-100 text-red-700">Alerte</span>
                            <span v-else class="badge-lime">OK</span>
                        </td>
                    </tr>
                    <tr v-if="!items.length && !loading"><td class="td text-muted text-center py-8" colspan="6">Aucun article suivi.</td></tr>
                    <tr v-if="loading"><td class="td text-muted text-center py-8" colspan="6">Chargement…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
