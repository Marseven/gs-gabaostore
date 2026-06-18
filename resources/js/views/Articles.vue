<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useArticlesStore } from '../stores/articles';
import { useCategoriesStore } from '../stores/categories';
import Modal from '../components/Modal.vue';
import Pagination from '../components/Pagination.vue';

const articles = useArticlesStore();
const categories = useCategoriesStore();

const filters = reactive({ search: '', categorie_id: '', actif: '' });
const page = ref(1);

const showModal = ref(false);
const editingId = ref(null);
const errors = ref({});
const form = reactive({
    reference: '',
    designation: '',
    categorie_id: '',
    unite: 'pièce',
    prix: '',
    suivi_stock: true,
    seuil_alerte: 0,
    stock_initial: 0,
    actif: true,
});

// Gestion des catégories (mini-CRUD)
const showCats = ref(false);
const newCat = ref('');

async function load(p = 1) {
    page.value = p;
    const params = { page: p };
    if (filters.search) params.search = filters.search;
    if (filters.categorie_id) params.categorie_id = filters.categorie_id;
    if (filters.actif !== '') params.actif = filters.actif;
    await articles.fetch(params);
}

function openCreate() {
    editingId.value = null;
    Object.assign(form, {
        reference: '', designation: '', categorie_id: '', unite: 'pièce', prix: '',
        suivi_stock: true, seuil_alerte: 0, stock_initial: 0, actif: true,
    });
    errors.value = {};
    showModal.value = true;
}

function openEdit(a) {
    editingId.value = a.id;
    Object.assign(form, {
        reference: a.reference,
        designation: a.designation,
        categorie_id: a.categorie_id || '',
        unite: a.unite,
        prix: a.prix ?? '',
        suivi_stock: a.suivi_stock,
        seuil_alerte: a.seuil_alerte ?? 0,
        stock_initial: a.stock_initial,
        actif: a.actif,
    });
    errors.value = {};
    showModal.value = true;
}

async function save() {
    errors.value = {};
    const payload = {
        reference: form.reference,
        designation: form.designation,
        categorie_id: form.categorie_id || null,
        unite: form.unite || 'pièce',
        prix: form.prix !== '' ? Number(form.prix) : null,
        suivi_stock: form.suivi_stock,
        seuil_alerte: form.suivi_stock ? Number(form.seuil_alerte) : null,
        stock_initial: Number(form.stock_initial),
        actif: form.actif,
    };
    try {
        if (editingId.value) {
            await articles.update(editingId.value, payload);
        } else {
            await articles.create(payload);
        }
        showModal.value = false;
        load(page.value);
    } catch (e) {
        errors.value = e.response?.data?.errors || { _global: ['Erreur lors de l’enregistrement.'] };
    }
}

async function toggleActif(a) {
    if (a.actif) {
        if (!confirm(`Désactiver l’article « ${a.designation} » ?`)) return;
        await articles.remove(a.id);
    } else {
        await articles.update(a.id, { actif: true });
    }
    load(page.value);
}

async function addCategory() {
    if (!newCat.value.trim()) return;
    await categories.create({ nom: newCat.value.trim() });
    newCat.value = '';
}

async function removeCategory(c) {
    if (!confirm(`Supprimer la catégorie « ${c.nom} » ?`)) return;
    await categories.remove(c.id);
}

onMounted(() => {
    categories.fetch();
    load(1);
});
</script>

<template>
    <div>
        <div class="flex flex-wrap items-end justify-between gap-3 mb-5 animate-rise">
            <div>
                <span class="badge-soft mb-2">Référentiel</span>
                <h1 class="text-heading font-semibold text-ink">Articles</h1>
            </div>
            <div class="flex gap-2">
                <button class="btn-secondary" @click="showCats = true">Catégories</button>
                <button class="btn-accent" @click="openCreate">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                    Nouvel article
                </button>
            </div>
        </div>

        <div class="card p-4 mb-4 flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="label">Recherche</label>
                <input v-model="filters.search" type="text" class="input" @keyup.enter="load(1)" />
            </div>
            <div class="min-w-[160px]">
                <label class="label">Catégorie</label>
                <select v-model="filters.categorie_id" class="input">
                    <option value="">Toutes</option>
                    <option v-for="c in categories.items" :key="c.id" :value="c.id">{{ c.nom }}</option>
                </select>
            </div>
            <div class="min-w-[140px]">
                <label class="label">État</label>
                <select v-model="filters.actif" class="input">
                    <option value="">Tous</option>
                    <option value="1">Actifs</option>
                    <option value="0">Inactifs</option>
                </select>
            </div>
            <button class="btn-primary" @click="load(1)">Filtrer</button>
        </div>

        <div class="card overflow-x-auto">
            <table class="w-full min-w-[860px]">
                <thead>
                    <tr class="border-b border-black/5">
                        <th class="th">Référence</th>
                        <th class="th">Désignation</th>
                        <th class="th">Catégorie</th>
                        <th class="th">Prix</th>
                        <th class="th">Suivi</th>
                        <th class="th">Stock</th>
                        <th class="th">Seuil</th>
                        <th class="th">État</th>
                        <th class="th">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="a in articles.items" :key="a.id" class="border-b border-black/5 last:border-0 hover:bg-black/[0.02]" :class="!a.actif && 'opacity-50'">
                        <td class="td font-semibold">{{ a.reference }}</td>
                        <td class="td">{{ a.designation }}</td>
                        <td class="td text-muted">{{ a.categorie?.nom || '—' }}</td>
                        <td class="td">{{ a.prix != null ? a.prix : '—' }}</td>
                        <td class="td">{{ a.suivi_stock ? 'Oui' : 'Non' }}</td>
                        <td class="td">{{ a.suivi_stock ? `${a.stock_actuel} ${a.unite}` : '—' }}</td>
                        <td class="td text-muted">{{ a.seuil_alerte ?? '—' }}</td>
                        <td class="td">
                            <span class="badge" :class="a.actif ? 'badge-lime' : 'badge-soft'">
                                {{ a.actif ? 'Actif' : 'Inactif' }}
                            </span>
                        </td>
                        <td class="td">
                            <div class="flex gap-1">
                                <button class="btn-secondary !px-3 !py-1.5 text-xs" @click="openEdit(a)">Éditer</button>
                                <button class="!px-3 !py-1.5 text-xs" :class="a.actif ? 'btn-danger' : 'btn-accent'" @click="toggleActif(a)">
                                    {{ a.actif ? 'Désactiver' : 'Réactiver' }}
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!articles.items.length && !articles.loading">
                        <td class="td text-muted text-center py-8" colspan="9">Aucun article.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :meta="articles.meta" @change="load" />

        <!-- Modal article -->
        <Modal v-if="showModal" :title="editingId ? 'Éditer l’article' : 'Nouvel article'" @close="showModal = false">
            <form @submit.prevent="save" class="space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="label">Référence (SKU)</label>
                        <input v-model="form.reference" type="text" class="input" required />
                        <p v-if="errors.reference" class="field-error">{{ errors.reference[0] }}</p>
                    </div>
                    <div>
                        <label class="label">Unité</label>
                        <input v-model="form.unite" type="text" class="input" placeholder="pièce, carton…" />
                    </div>
                </div>
                <div>
                    <label class="label">Désignation</label>
                    <input v-model="form.designation" type="text" class="input" required />
                    <p v-if="errors.designation" class="field-error">{{ errors.designation[0] }}</p>
                </div>
                <div>
                    <label class="label">Prix unitaire</label>
                    <input v-model="form.prix" type="number" step="0.01" min="0" class="input" placeholder="Prix de l'article" />
                    <p v-if="errors.prix" class="field-error">{{ errors.prix[0] }}</p>
                </div>
                <div>
                    <label class="label">Catégorie</label>
                    <select v-model="form.categorie_id" class="input">
                        <option value="">— Aucune —</option>
                        <option v-for="c in categories.items" :key="c.id" :value="c.id">{{ c.nom }}</option>
                    </select>
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input v-model="form.suivi_stock" type="checkbox" class="rounded" />
                    Suivi de stock (calcul du niveau et alertes)
                </label>
                <div v-if="form.suivi_stock" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="label">Stock initial</label>
                        <input v-model.number="form.stock_initial" type="number" min="0" class="input" />
                    </div>
                    <div>
                        <label class="label">Seuil d’alerte</label>
                        <input v-model.number="form.seuil_alerte" type="number" min="0" class="input" />
                        <p v-if="errors.seuil_alerte" class="field-error">{{ errors.seuil_alerte[0] }}</p>
                    </div>
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input v-model="form.actif" type="checkbox" class="rounded" />
                    Actif
                </label>
                <p v-if="errors._global" class="field-error">{{ errors._global[0] }}</p>
                <div class="flex justify-end gap-2">
                    <button type="button" class="btn-secondary" @click="showModal = false">Annuler</button>
                    <button type="submit" class="btn-primary">Enregistrer</button>
                </div>
            </form>
        </Modal>

        <!-- Modal catégories -->
        <Modal v-if="showCats" title="Catégories" @close="showCats = false">
            <div class="space-y-2 mb-4">
                <div v-for="c in categories.items" :key="c.id" class="flex items-center justify-between py-1.5 border-b border-slate-100">
                    <span>{{ c.nom }} <span class="text-slate-400 text-xs">({{ c.articles_count ?? 0 }} article(s))</span></span>
                    <button class="btn-danger px-2 py-1 text-xs" @click="removeCategory(c)">Suppr.</button>
                </div>
                <p v-if="!categories.items.length" class="text-slate-400 text-sm">Aucune catégorie.</p>
            </div>
            <form @submit.prevent="addCategory" class="flex gap-2">
                <input v-model="newCat" type="text" class="input" placeholder="Nouvelle catégorie" />
                <button type="submit" class="btn-primary">Ajouter</button>
            </form>
        </Modal>
    </div>
</template>
