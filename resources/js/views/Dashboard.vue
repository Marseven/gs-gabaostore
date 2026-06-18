<script setup>
import { ref, onMounted, computed } from 'vue';
import api from '../lib/api';

const loading = ref(true);
const stats = ref({ articles_suivis: 0, en_alerte: 0, mouvements_du_jour: 0 });
const alertes = ref([]);
const derniers = ref([]);

function fmtDate(d) {
    if (!d) return '';
    const [y, m, j] = d.split('-');
    return `${j}/${m}/${y}`;
}

const today = computed(() =>
    new Date().toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' })
);

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/dashboard');
        stats.value = data.stats;
        alertes.value = data.alertes.data ?? data.alertes;
        derniers.value = data.derniers_mouvements.data ?? data.derniers_mouvements;
    } finally {
        loading.value = false;
    }
}

onMounted(load);
</script>

<template>
    <div>
        <!-- Hero -->
        <div class="flex flex-wrap items-end justify-between gap-4 mb-6 animate-rise">
            <div>
                <div class="flex items-center gap-2 text-xs text-muted mb-2">
                    <span class="badge-soft">Vue d'ensemble</span>
                    <span>·</span>
                    <span class="capitalize">{{ today }}</span>
                </div>
                <h1 class="text-heading font-semibold text-ink">Tableau de bord</h1>
            </div>
            <div class="flex items-center gap-2">
                <router-link :to="{ name: 'saisie' }" class="btn-accent">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                    Nouveau mouvement
                </router-link>
                <button class="icon-btn" title="Rafraîchir" @click="load">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/></svg>
                </button>
            </div>
        </div>

        <!-- Cartes statistiques -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
            <div class="card p-6 animate-rise" style="animation-delay: 60ms">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-muted">Articles suivis</p>
                    <span class="icon-btn !w-8 !h-8 !shadow-none">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10m0-10L4 7"/></svg>
                    </span>
                </div>
                <p class="text-5xl font-bold tracking-tight mt-3">{{ stats.articles_suivis }}</p>
                <p class="text-xs text-muted mt-1">références avec niveau de stock</p>
            </div>

            <!-- Carte accent (alertes) -->
            <div class="rounded-card p-6 animate-rise" :class="stats.en_alerte > 0 ? 'bg-lime' : 'card'" style="animation-delay: 120ms">
                <div class="flex items-center justify-between">
                    <p class="text-sm" :class="stats.en_alerte > 0 ? 'text-ink/70' : 'text-muted'">En alerte stock bas</p>
                    <span class="w-8 h-8 rounded-full grid place-items-center" :class="stats.en_alerte > 0 ? 'bg-ink text-lime' : 'bg-black/5 text-ink'">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4M12 17h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                    </span>
                </div>
                <p class="text-5xl font-bold tracking-tight mt-3 text-ink">{{ stats.en_alerte }}</p>
                <p class="text-xs mt-1" :class="stats.en_alerte > 0 ? 'text-ink/60' : 'text-muted'">article(s) sous le seuil</p>
            </div>

            <div class="card p-6 animate-rise" style="animation-delay: 180ms">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-muted">Mouvements du jour</p>
                    <span class="icon-btn !w-8 !h-8 !shadow-none">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 17l6-6 4 4 8-8M21 7v6M21 7h-6"/></svg>
                    </span>
                </div>
                <p class="text-5xl font-bold tracking-tight mt-3">{{ stats.mouvements_du_jour }}</p>
                <p class="text-xs text-muted mt-1">entrées et sorties enregistrées</p>
            </div>
        </div>

        <!-- Panneaux -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="card overflow-hidden animate-rise" style="animation-delay: 240ms">
                <div class="px-6 pt-5 pb-3 flex items-center justify-between">
                    <h2 class="font-semibold text-ink">Articles en alerte</h2>
                    <router-link :to="{ name: 'stock' }" class="icon-btn">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M7 17L17 7M7 7h10v10"/></svg>
                    </router-link>
                </div>
                <table class="w-full">
                    <thead><tr class="border-y border-black/5"><th class="th">Référence</th><th class="th">Désignation</th><th class="th">Stock</th><th class="th">Seuil</th></tr></thead>
                    <tbody>
                        <tr v-for="a in alertes" :key="a.id" class="border-b border-black/5 last:border-0 hover:bg-black/[0.02]">
                            <td class="td font-semibold">{{ a.reference }}</td>
                            <td class="td">{{ a.designation }}</td>
                            <td class="td"><span class="badge bg-red-100 text-red-700">{{ a.stock_actuel }} {{ a.unite }}</span></td>
                            <td class="td text-muted">{{ a.seuil_alerte }}</td>
                        </tr>
                        <tr v-if="!alertes.length"><td class="td text-muted py-8 text-center" colspan="4">
                            <span class="inline-flex items-center gap-2">
                                <svg class="w-4 h-4 text-lime-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg>
                                Aucune alerte — tout est au vert
                            </span>
                        </td></tr>
                    </tbody>
                </table>
            </div>

            <div class="card overflow-hidden animate-rise" style="animation-delay: 300ms">
                <div class="px-6 pt-5 pb-3 flex items-center justify-between">
                    <h2 class="font-semibold text-ink">Derniers mouvements</h2>
                    <router-link :to="{ name: 'historique' }" class="icon-btn">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M7 17L17 7M7 7h10v10"/></svg>
                    </router-link>
                </div>
                <table class="w-full">
                    <thead><tr class="border-y border-black/5"><th class="th">Date</th><th class="th">Type</th><th class="th">Article</th><th class="th">Qté</th></tr></thead>
                    <tbody>
                        <tr v-for="m in derniers" :key="m.id" class="border-b border-black/5 last:border-0 hover:bg-black/[0.02]">
                            <td class="td">{{ fmtDate(m.date_mouvement) }}</td>
                            <td class="td">
                                <span class="badge" :class="m.type === 'entree' ? 'badge-lime' : 'badge-ink'">
                                    {{ m.type === 'entree' ? 'Entrée' : 'Sortie' }}
                                </span>
                            </td>
                            <td class="td truncate max-w-[180px]">{{ m.article?.designation }}</td>
                            <td class="td font-semibold">{{ m.quantite }}</td>
                        </tr>
                        <tr v-if="!derniers.length"><td class="td text-muted py-8 text-center" colspan="4">Aucun mouvement.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
