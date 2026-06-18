<script setup>
import { ref, reactive, computed } from 'vue';
import { useMouvementsStore } from '../stores/mouvements';
import ArticleSearch from '../components/ArticleSearch.vue';

const mouvements = useMouvementsStore();

const type = ref('entree');
const article = ref(null);
const today = new Date().toISOString().slice(0, 10);

const form = reactive({
    quantite: 1,
    date_mouvement: today,
    source: '',
    livreur: '',
    destination: '',
    note: '',
});

const errors = ref({});
const success = ref('');
const submitting = ref(false);

const stockDispo = computed(() => (article.value?.suivi_stock ? article.value.stock_actuel : null));
const sortieInsuffisante = computed(
    () => type.value === 'sortie' && stockDispo.value !== null && Number(form.quantite) > stockDispo.value
);

function resetForm() {
    article.value = null;
    form.quantite = 1;
    form.date_mouvement = today;
    form.source = '';
    form.livreur = '';
    form.destination = '';
    form.note = '';
    errors.value = {};
}

async function submit() {
    errors.value = {};
    success.value = '';
    if (!article.value) {
        errors.value.article_id = ['Veuillez sélectionner un article.'];
        return;
    }
    submitting.value = true;
    try {
        const payload = {
            article_id: article.value.id,
            quantite: Number(form.quantite),
            date_mouvement: form.date_mouvement,
            note: form.note || null,
        };
        if (type.value === 'entree') {
            payload.source = form.source || null;
            await mouvements.createEntree(payload);
        } else {
            payload.livreur = form.livreur;
            payload.destination = form.destination || null;
            await mouvements.createSortie(payload);
        }
        success.value = `${type.value === 'entree' ? 'Entrée' : 'Sortie'} enregistrée avec succès.`;
        resetForm();
    } catch (e) {
        if (e.response?.status === 422) {
            errors.value = e.response.data.errors || {};
        } else {
            errors.value = { _global: ['Une erreur est survenue. Réessayez.'] };
        }
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <div class="max-w-2xl mx-auto">
        <div class="mb-5 animate-rise">
            <span class="badge-soft mb-2">Saisie rapide</span>
            <h1 class="text-heading font-semibold text-ink">Nouveau mouvement</h1>
        </div>

        <div class="card p-6 animate-rise" style="animation-delay: 80ms">
            <!-- Bascule Entrée / Sortie -->
            <div class="flex rounded-pill p-1.5 mb-6 bg-black/[0.04]">
                <button
                    type="button"
                    class="flex-1 inline-flex items-center justify-center gap-2 py-2.5 rounded-pill text-sm font-medium transition-all"
                    :class="type === 'entree' ? 'bg-lime text-ink shadow-pill' : 'text-muted hover:text-ink'"
                    @click="type = 'entree'; errors = {}"
                >
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                    Entrée (réappro)
                </button>
                <button
                    type="button"
                    class="flex-1 inline-flex items-center justify-center gap-2 py-2.5 rounded-pill text-sm font-medium transition-all"
                    :class="type === 'sortie' ? 'bg-ink text-white shadow-pill' : 'text-muted hover:text-ink'"
                    @click="type = 'sortie'; errors = {}"
                >
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                    Sortie (livraison)
                </button>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="label">Article</label>
                    <ArticleSearch v-model="article" />
                    <p v-if="errors.article_id" class="field-error">{{ errors.article_id[0] }}</p>
                    <div v-if="article" class="mt-2 text-sm">
                        <span v-if="article.suivi_stock" class="badge-pale">
                            Stock disponible : <strong class="ml-1">{{ article.stock_actuel }} {{ article.unite }}</strong>
                        </span>
                        <span v-else class="badge-soft">Article non suivi (pas de niveau de stock)</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">Quantité</label>
                        <input v-model.number="form.quantite" type="number" min="1" class="input" required />
                        <p v-if="errors.quantite" class="field-error">{{ errors.quantite[0] }}</p>
                        <p v-else-if="sortieInsuffisante" class="field-error">
                            Stock insuffisant (disponible : {{ stockDispo }}).
                        </p>
                    </div>
                    <div>
                        <label class="label">Date</label>
                        <input v-model="form.date_mouvement" type="date" class="input" required />
                        <p v-if="errors.date_mouvement" class="field-error">{{ errors.date_mouvement[0] }}</p>
                    </div>
                </div>

                <!-- Champs spécifiques ENTRÉE -->
                <div v-if="type === 'entree'">
                    <label class="label">Source / fournisseur</label>
                    <input v-model="form.source" type="text" class="input" placeholder="Ex : Fournisseur, origine…" />
                </div>

                <!-- Champs spécifiques SORTIE -->
                <template v-else>
                    <div>
                        <label class="label">Livreur <span class="text-red-500">*</span></label>
                        <input v-model="form.livreur" type="text" class="input" placeholder="Nom du livreur" required />
                        <p v-if="errors.livreur" class="field-error">{{ errors.livreur[0] }}</p>
                    </div>
                    <div>
                        <label class="label">Destination / client</label>
                        <input v-model="form.destination" type="text" class="input" placeholder="Lieu ou client (texte libre)" />
                    </div>
                </template>

                <div>
                    <label class="label">Note</label>
                    <textarea v-model="form.note" rows="2" class="input" placeholder="Optionnel"></textarea>
                </div>

                <p v-if="errors._global" class="text-sm text-red-600">{{ errors._global[0] }}</p>
                <p v-if="success" class="text-sm text-ink bg-lime-pale rounded-2xl px-4 py-2.5 font-medium inline-flex items-center gap-2">
                    <svg class="w-4 h-4 text-lime-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                    {{ success }}
                </p>

                <div class="flex justify-end gap-2">
                    <button type="button" class="btn-secondary" @click="resetForm">Réinitialiser</button>
                    <button type="submit" class="btn-primary" :disabled="submitting || sortieInsuffisante">
                        {{ submitting ? 'Enregistrement…' : 'Enregistrer' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
