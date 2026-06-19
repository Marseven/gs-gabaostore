<script setup>
import { ref, reactive, computed, watch } from 'vue';
import { useMouvementsStore } from '../stores/mouvements';
import ArticleSearch from '../components/ArticleSearch.vue';

const mouvements = useMouvementsStore();

const type = ref('entree');
const article = ref(null);
const today = new Date().toISOString().slice(0, 10);

const form = reactive({
    quantite: 1,
    prix: '',
    numero: '',
    date_mouvement: today,
    source: '',
    livreur: '',
    destination: '',
    telephone: '',
    vendeur: '',
    mode_remise: 'livraison',
    recu_par: '',
    statut_livraison: 'valide',
    commentaire_statut: '',
    note: '',
});

const errors = ref({});
const success = ref('');
const submitting = ref(false);

const stockDispo = computed(() => (article.value?.suivi_stock ? article.value.stock_actuel : null));
const sortieInsuffisante = computed(
    () => type.value === 'sortie' && stockDispo.value !== null && Number(form.quantite) > stockDispo.value
);

// Préremplit le prix avec celui de l'article sélectionné (si défini et champ vide).
watch(article, (a) => {
    if (a && a.prix != null && !form.prix) {
        form.prix = a.prix;
    }
});

function resetForm() {
    article.value = null;
    Object.assign(form, {
        quantite: 1,
        prix: '',
        numero: '',
        date_mouvement: today,
        source: '',
        livreur: '',
        destination: '',
        telephone: '',
        vendeur: '',
        mode_remise: 'livraison',
        recu_par: '',
        statut_livraison: 'valide',
        commentaire_statut: '',
        note: '',
    });
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
            payload.prix = form.prix !== '' ? Number(form.prix) : null;
            payload.numero = form.numero || null;
            payload.vendeur = form.vendeur || null;
            payload.recu_par = form.recu_par || null;
            await mouvements.createEntree(payload);
        } else {
            payload.prix = form.prix !== '' ? Number(form.prix) : null;
            payload.numero = form.numero || null;
            payload.telephone = form.telephone || null;
            payload.vendeur = form.vendeur || null;
            payload.mode_remise = form.mode_remise;
            payload.statut_livraison = form.statut_livraison || null;
            payload.commentaire_statut = form.commentaire_statut || null;
            if (form.mode_remise === 'livraison') {
                payload.livreur = form.livreur;
                payload.destination = form.destination || null;
            } else {
                payload.recu_par = form.recu_par;
            }
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

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
                <template v-if="type === 'entree'">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="label">Numéro</label>
                            <input v-model="form.numero" type="text" class="input" placeholder="N° commande / bon" />
                            <p v-if="errors.numero" class="field-error">{{ errors.numero[0] }}</p>
                        </div>
                        <div>
                            <label class="label">Prix</label>
                            <input v-model="form.prix" type="number" step="0.01" min="0" class="input" placeholder="Montant" />
                            <p v-if="errors.prix" class="field-error">{{ errors.prix[0] }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="label">Vendeur</label>
                            <input v-model="form.vendeur" type="text" class="input" placeholder="Nom du vendeur" />
                        </div>
                        <div>
                            <label class="label">Reçu par</label>
                            <input v-model="form.recu_par" type="text" class="input" placeholder="Ex : reçu par Abou" />
                        </div>
                    </div>
                    <div>
                        <label class="label">Source / fournisseur</label>
                        <input v-model="form.source" type="text" class="input" placeholder="Ex : Fournisseur, origine…" />
                    </div>
                </template>

                <!-- Champs spécifiques SORTIE -->
                <template v-else>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="label">Numéro</label>
                            <input v-model="form.numero" type="text" class="input" placeholder="N° commande / vente" />
                            <p v-if="errors.numero" class="field-error">{{ errors.numero[0] }}</p>
                        </div>
                        <div>
                            <label class="label">Prix</label>
                            <input v-model="form.prix" type="number" step="0.01" min="0" class="input" placeholder="Montant" />
                            <p v-if="errors.prix" class="field-error">{{ errors.prix[0] }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="label">Numéro téléphone</label>
                            <input v-model="form.telephone" type="tel" class="input" placeholder="Téléphone client" />
                        </div>
                        <div>
                            <label class="label">Vendeur</label>
                            <input v-model="form.vendeur" type="text" class="input" placeholder="Nom du vendeur / revendeur" />
                        </div>
                    </div>

                    <!-- Mode de remise -->
                    <div>
                        <label class="label">Mode de remise</label>
                        <div class="flex rounded-pill p-1.5 bg-black/[0.04]">
                            <button type="button" class="flex-1 py-2 rounded-pill text-sm font-medium transition-all"
                                :class="form.mode_remise === 'livraison' ? 'bg-ink text-white shadow-pill' : 'text-muted hover:text-ink'"
                                @click="form.mode_remise = 'livraison'">
                                Livraison
                            </button>
                            <button type="button" class="flex-1 py-2 rounded-pill text-sm font-medium transition-all"
                                :class="form.mode_remise === 'sur_place' ? 'bg-ink text-white shadow-pill' : 'text-muted hover:text-ink'"
                                @click="form.mode_remise = 'sur_place'">
                                Sur place
                            </button>
                        </div>
                    </div>

                    <!-- Statut de livraison (au-dessus du livreur) -->
                    <div>
                        <label class="label">Statut</label>
                        <div class="flex flex-wrap gap-2">
                            <button v-for="s in [{v:'valide',l:'Livré'},{v:'rate',l:'Raté'},{v:'a_reprogrammer',l:'Plus tard'}]"
                                :key="s.v" type="button"
                                class="badge px-3 py-1.5 ring-1 transition-all"
                                :class="form.statut_livraison === s.v
                                    ? (s.v === 'rate' ? 'bg-red-600 text-white ring-red-600' : s.v === 'valide' ? 'bg-lime text-ink ring-lime' : 'bg-ink text-white ring-ink')
                                    : 'bg-white/50 text-ink/70 ring-white/60 hover:bg-white/80'"
                                @click="form.statut_livraison = s.v">
                                {{ s.l }}
                            </button>
                        </div>
                    </div>

                    <!-- Commentaire (raison) si raté ou plus tard -->
                    <div v-if="form.statut_livraison === 'rate' || form.statut_livraison === 'a_reprogrammer'">
                        <label class="label">
                            Commentaire / raison
                            <span v-if="form.statut_livraison === 'rate'" class="text-red-500">*</span>
                        </label>
                        <textarea v-model="form.commentaire_statut" rows="2" class="input" placeholder="Raison (échec, report…)"></textarea>
                        <p v-if="errors.commentaire_statut" class="field-error">{{ errors.commentaire_statut[0] }}</p>
                    </div>

                    <!-- Livraison : livreur + destination -->
                    <template v-if="form.mode_remise === 'livraison'">
                        <div>
                            <label class="label">Livreur <span class="text-red-500">*</span></label>
                            <input v-model="form.livreur" type="text" class="input" placeholder="Nom du livreur" />
                            <p v-if="errors.livreur" class="field-error">{{ errors.livreur[0] }}</p>
                        </div>
                        <div>
                            <label class="label">Lieu de livraison / client</label>
                            <input v-model="form.destination" type="text" class="input" placeholder="Lieu ou client (texte libre)" />
                        </div>
                    </template>

                    <!-- Sur place : reçu par -->
                    <div v-else>
                        <label class="label">Reçu par <span class="text-red-500">*</span></label>
                        <input v-model="form.recu_par" type="text" class="input" placeholder="Ex : reçu par Abou" />
                        <p v-if="errors.recu_par" class="field-error">{{ errors.recu_par[0] }}</p>
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
