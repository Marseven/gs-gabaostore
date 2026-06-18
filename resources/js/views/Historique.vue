<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useMouvementsStore } from '../stores/mouvements';
import { useAuthStore } from '../stores/auth';
import { getToken } from '../lib/api';
import Pagination from '../components/Pagination.vue';
import Modal from '../components/Modal.vue';

const mouvements = useMouvementsStore();
const auth = useAuthStore();

const filters = reactive({
    type: '',
    livreur: '',
    date_from: '',
    date_to: '',
    search: '',
});
const page = ref(1);

function fmtDate(d) {
    if (!d) return '';
    const [y, m, j] = d.split('-');
    return `${j}/${m}/${y}`;
}

async function load(p = 1) {
    page.value = p;
    const params = { page: p };
    Object.entries(filters).forEach(([k, v]) => {
        if (v) params[k] = v;
    });
    await mouvements.fetch(params);
}

function reset() {
    Object.keys(filters).forEach((k) => (filters[k] = ''));
    load(1);
}

function exportExcel() {
    const token = getToken();
    const params = new URLSearchParams();
    Object.entries(filters).forEach(([k, v]) => v && params.append(k, v));
    fetch(`/api/v1/export/mouvements?${params.toString()}`, { headers: { Authorization: `Bearer ${token}` } })
        .then((r) => r.blob())
        .then((blob) => {
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'mouvements.xlsx';
            a.click();
            URL.revokeObjectURL(url);
        });
}

// --- Édition / suppression (admin) ---
const editing = ref(null);
const editForm = reactive({ quantite: 0, date_mouvement: '', livreur: '', destination: '', source: '', note: '' });
const editErrors = ref({});

function openEdit(m) {
    editing.value = m;
    editForm.quantite = m.quantite;
    editForm.date_mouvement = m.date_mouvement;
    editForm.livreur = m.livreur || '';
    editForm.destination = m.destination || '';
    editForm.source = m.source || '';
    editForm.note = m.note || '';
    editErrors.value = {};
}

async function saveEdit() {
    editErrors.value = {};
    try {
        await mouvements.update(editing.value.id, {
            quantite: Number(editForm.quantite),
            date_mouvement: editForm.date_mouvement,
            livreur: editForm.livreur || null,
            destination: editForm.destination || null,
            source: editForm.source || null,
            note: editForm.note || null,
        });
        editing.value = null;
        load(page.value);
    } catch (e) {
        editErrors.value = e.response?.data?.errors || { _global: ['Erreur lors de la mise à jour.'] };
    }
}

async function remove(m) {
    if (!confirm(`Supprimer ce mouvement ? Le stock de « ${m.article?.designation} » sera recalculé.`)) return;
    await mouvements.remove(m.id);
    load(page.value);
}

onMounted(() => load(1));
</script>

<template>
    <div>
        <div class="flex flex-wrap items-end justify-between gap-3 mb-5 animate-rise">
            <div>
                <span class="badge-soft mb-2">Journal des mouvements</span>
                <h1 class="text-heading font-semibold text-ink">Historique</h1>
            </div>
            <button class="btn-secondary" @click="exportExcel">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                Export Excel
            </button>
        </div>

        <div class="card p-4 mb-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
            <div>
                <label class="label">Type</label>
                <select v-model="filters.type" class="input">
                    <option value="">Tous</option>
                    <option value="entree">Entrée</option>
                    <option value="sortie">Sortie</option>
                </select>
            </div>
            <div>
                <label class="label">Livreur</label>
                <input v-model="filters.livreur" type="text" class="input" />
            </div>
            <div>
                <label class="label">Du</label>
                <input v-model="filters.date_from" type="date" class="input" />
            </div>
            <div>
                <label class="label">Au</label>
                <input v-model="filters.date_to" type="date" class="input" />
            </div>
            <div>
                <label class="label">Recherche</label>
                <input v-model="filters.search" type="text" class="input" placeholder="Article, note…" @keyup.enter="load(1)" />
            </div>
            <div class="flex gap-2">
                <button class="btn-primary" @click="load(1)">Filtrer</button>
                <button class="btn-secondary" @click="reset">Réinit.</button>
            </div>
        </div>

        <div class="card overflow-x-auto">
            <table class="w-full min-w-[760px]">
                <thead>
                    <tr class="border-b border-black/5">
                        <th class="th">Date</th>
                        <th class="th">Type</th>
                        <th class="th">Article</th>
                        <th class="th">Qté</th>
                        <th class="th">Livreur / Source</th>
                        <th class="th">Destination</th>
                        <th class="th">Auteur</th>
                        <th v-if="auth.isAdmin" class="th">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="m in mouvements.items" :key="m.id" class="border-b border-black/5 last:border-0 hover:bg-black/[0.02]">
                        <td class="td">{{ fmtDate(m.date_mouvement) }}</td>
                        <td class="td">
                            <span class="badge" :class="m.type === 'entree' ? 'badge-lime' : 'badge-ink'">
                                {{ m.type === 'entree' ? 'Entrée' : 'Sortie' }}
                            </span>
                        </td>
                        <td class="td">{{ m.article?.reference }} — {{ m.article?.designation }}</td>
                        <td class="td font-semibold">{{ m.quantite }}</td>
                        <td class="td">{{ m.type === 'entree' ? (m.source || '—') : (m.livreur || '—') }}</td>
                        <td class="td text-muted">{{ m.destination || '—' }}</td>
                        <td class="td text-muted">{{ m.user?.name }}</td>
                        <td v-if="auth.isAdmin" class="td">
                            <div class="flex gap-1">
                                <button class="btn-secondary !px-3 !py-1.5 text-xs" @click="openEdit(m)">Éditer</button>
                                <button class="btn-danger !px-3 !py-1.5 text-xs" @click="remove(m)">Suppr.</button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!mouvements.items.length && !mouvements.loading">
                        <td class="td text-muted text-center py-8" :colspan="auth.isAdmin ? 8 : 7">Aucun mouvement trouvé.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :meta="mouvements.meta" @change="load" />

        <Modal v-if="editing" :title="`Éditer le mouvement #${editing.id}`" @close="editing = null">
            <form @submit.prevent="saveEdit" class="space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="label">Quantité</label>
                        <input v-model.number="editForm.quantite" type="number" min="1" class="input" />
                        <p v-if="editErrors.quantite" class="field-error">{{ editErrors.quantite[0] }}</p>
                    </div>
                    <div>
                        <label class="label">Date</label>
                        <input v-model="editForm.date_mouvement" type="date" class="input" />
                    </div>
                </div>
                <div v-if="editing.type === 'sortie'">
                    <label class="label">Livreur</label>
                    <input v-model="editForm.livreur" type="text" class="input" />
                    <p v-if="editErrors.livreur" class="field-error">{{ editErrors.livreur[0] }}</p>
                </div>
                <div v-if="editing.type === 'sortie'">
                    <label class="label">Destination</label>
                    <input v-model="editForm.destination" type="text" class="input" />
                </div>
                <div v-if="editing.type === 'entree'">
                    <label class="label">Source</label>
                    <input v-model="editForm.source" type="text" class="input" />
                </div>
                <div>
                    <label class="label">Note</label>
                    <textarea v-model="editForm.note" rows="2" class="input"></textarea>
                </div>
                <p v-if="editErrors._global" class="field-error">{{ editErrors._global[0] }}</p>
                <div class="flex justify-end gap-2">
                    <button type="button" class="btn-secondary" @click="editing = null">Annuler</button>
                    <button type="submit" class="btn-primary">Enregistrer</button>
                </div>
            </form>
        </Modal>
    </div>
</template>
