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

const STATUTS = {
    valide: { label: 'Livré', class: 'badge-lime' },
    rate: { label: 'Raté', class: 'bg-red-100 text-red-700' },
    a_reprogrammer: { label: 'Plus tard', class: 'badge-ink' },
};
function statut(m) {
    return m.statut_livraison ? STATUTS[m.statut_livraison] : null;
}
// Affiche livreur (livraison), « Reçu: X » (sur place) ou la source (entrée).
function remiseLabel(m) {
    if (m.type === 'entree') return m.source || '—';
    if (m.mode_remise === 'sur_place') return m.recu_par ? `Reçu : ${m.recu_par}` : '—';
    return m.livreur || '—';
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
const editForm = reactive({
    quantite: 0, prix: '', numero: '', date_mouvement: '', livreur: '', destination: '',
    telephone: '', vendeur: '', mode_remise: 'livraison', recu_par: '',
    statut_livraison: '', commentaire_statut: '', source: '', note: '',
});
const editErrors = ref({});

function openEdit(m) {
    editing.value = m;
    Object.assign(editForm, {
        quantite: m.quantite,
        prix: m.prix ?? '',
        numero: m.numero || '',
        date_mouvement: m.date_mouvement,
        livreur: m.livreur || '',
        destination: m.destination || '',
        telephone: m.telephone || '',
        vendeur: m.vendeur || '',
        mode_remise: m.mode_remise || 'livraison',
        recu_par: m.recu_par || '',
        statut_livraison: m.statut_livraison || '',
        commentaire_statut: m.commentaire_statut || '',
        source: m.source || '',
        note: m.note || '',
    });
    editErrors.value = {};
}

async function saveEdit() {
    editErrors.value = {};
    try {
        await mouvements.update(editing.value.id, {
            quantite: Number(editForm.quantite),
            prix: editForm.prix !== '' ? Number(editForm.prix) : null,
            numero: editForm.numero || null,
            date_mouvement: editForm.date_mouvement,
            livreur: editForm.livreur || null,
            destination: editForm.destination || null,
            telephone: editForm.telephone || null,
            vendeur: editForm.vendeur || null,
            mode_remise: editForm.mode_remise,
            recu_par: editForm.recu_par || null,
            statut_livraison: editForm.statut_livraison || null,
            commentaire_statut: editForm.commentaire_statut || null,
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
            <table class="w-full min-w-[1200px]">
                <thead>
                    <tr class="border-b border-black/5">
                        <th class="th">Date</th>
                        <th class="th">Type</th>
                        <th class="th">Numéro</th>
                        <th class="th">Article</th>
                        <th class="th">Qté</th>
                        <th class="th">Prix</th>
                        <th class="th">Vendeur</th>
                        <th class="th">Livreur / Reçu / Source</th>
                        <th class="th">Destination</th>
                        <th class="th">Statut</th>
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
                        <td class="td text-muted">{{ m.numero || '—' }}</td>
                        <td class="td">{{ m.article?.reference }} — {{ m.article?.designation }}</td>
                        <td class="td font-semibold">{{ m.quantite }}</td>
                        <td class="td">{{ m.prix != null ? m.prix : '—' }}</td>
                        <td class="td text-muted">{{ m.vendeur || '—' }}</td>
                        <td class="td">
                            {{ remiseLabel(m) }}
                            <span v-if="m.type === 'sortie' && m.telephone" class="block text-[11px] text-muted">{{ m.telephone }}</span>
                            <span v-if="m.type === 'entree' && m.recu_par" class="block text-[11px] text-muted">Reçu : {{ m.recu_par }}</span>
                        </td>
                        <td class="td text-muted">{{ m.destination || '—' }}</td>
                        <td class="td">
                            <span v-if="statut(m)" class="badge" :class="statut(m).class" :title="m.commentaire_statut || ''">{{ statut(m).label }}</span>
                            <span v-else class="text-muted">—</span>
                        </td>
                        <td class="td text-muted">{{ m.user?.name }}</td>
                        <td v-if="auth.isAdmin" class="td">
                            <div class="flex gap-1">
                                <button class="btn-secondary !px-3 !py-1.5 text-xs" @click="openEdit(m)">Éditer</button>
                                <button class="btn-danger !px-3 !py-1.5 text-xs" @click="remove(m)">Suppr.</button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!mouvements.items.length && !mouvements.loading">
                        <td class="td text-muted text-center py-8" :colspan="auth.isAdmin ? 12 : 11">Aucun mouvement trouvé.</td>
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
                <template v-if="editing.type === 'sortie'">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="label">Numéro</label>
                            <input v-model="editForm.numero" type="text" class="input" />
                        </div>
                        <div>
                            <label class="label">Prix</label>
                            <input v-model="editForm.prix" type="number" step="0.01" min="0" class="input" />
                        </div>
                    </div>
                    <div>
                        <label class="label">Téléphone</label>
                        <input v-model="editForm.telephone" type="tel" class="input" />
                    </div>
                    <div>
                        <label class="label">Vendeur</label>
                        <input v-model="editForm.vendeur" type="text" class="input" />
                    </div>
                    <div>
                        <label class="label">Mode de remise</label>
                        <select v-model="editForm.mode_remise" class="input">
                            <option value="livraison">Livraison</option>
                            <option value="sur_place">Sur place</option>
                        </select>
                    </div>
                    <div v-if="editForm.mode_remise === 'livraison'">
                        <label class="label">Livreur</label>
                        <input v-model="editForm.livreur" type="text" class="input" />
                        <p v-if="editErrors.livreur" class="field-error">{{ editErrors.livreur[0] }}</p>
                    </div>
                    <div v-else>
                        <label class="label">Reçu par</label>
                        <input v-model="editForm.recu_par" type="text" class="input" />
                        <p v-if="editErrors.recu_par" class="field-error">{{ editErrors.recu_par[0] }}</p>
                    </div>
                    <div>
                        <label class="label">Destination / lieu</label>
                        <input v-model="editForm.destination" type="text" class="input" />
                    </div>
                    <div>
                        <label class="label">Statut</label>
                        <select v-model="editForm.statut_livraison" class="input">
                            <option value="">—</option>
                            <option value="valide">Livré</option>
                            <option value="rate">Raté</option>
                            <option value="a_reprogrammer">Plus tard</option>
                        </select>
                    </div>
                    <div v-if="editForm.statut_livraison === 'rate' || editForm.statut_livraison === 'a_reprogrammer'">
                        <label class="label">Commentaire</label>
                        <textarea v-model="editForm.commentaire_statut" rows="2" class="input"></textarea>
                        <p v-if="editErrors.commentaire_statut" class="field-error">{{ editErrors.commentaire_statut[0] }}</p>
                    </div>
                </template>
                <template v-if="editing.type === 'entree'">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="label">Numéro</label>
                            <input v-model="editForm.numero" type="text" class="input" />
                        </div>
                        <div>
                            <label class="label">Prix</label>
                            <input v-model="editForm.prix" type="number" step="0.01" min="0" class="input" />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="label">Vendeur</label>
                            <input v-model="editForm.vendeur" type="text" class="input" />
                        </div>
                        <div>
                            <label class="label">Reçu par</label>
                            <input v-model="editForm.recu_par" type="text" class="input" />
                        </div>
                    </div>
                    <div>
                        <label class="label">Source / fournisseur</label>
                        <input v-model="editForm.source" type="text" class="input" />
                    </div>
                </template>
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
