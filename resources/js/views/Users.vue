<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useUsersStore } from '../stores/users';
import { useAuthStore } from '../stores/auth';
import Modal from '../components/Modal.vue';

const users = useUsersStore();
const auth = useAuthStore();

const showModal = ref(false);
const editingId = ref(null);
const errors = ref({});
const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'operateur',
    actif: true,
});

function openCreate() {
    editingId.value = null;
    Object.assign(form, { name: '', email: '', password: '', password_confirmation: '', role: 'operateur', actif: true });
    errors.value = {};
    showModal.value = true;
}

function openEdit(u) {
    editingId.value = u.id;
    Object.assign(form, { name: u.name, email: u.email, password: '', password_confirmation: '', role: u.role, actif: u.actif });
    errors.value = {};
    showModal.value = true;
}

async function save() {
    errors.value = {};
    const payload = {
        name: form.name,
        email: form.email,
        role: form.role,
        actif: form.actif,
    };
    if (form.password) {
        payload.password = form.password;
        payload.password_confirmation = form.password_confirmation;
    }
    try {
        if (editingId.value) {
            await users.update(editingId.value, payload);
        } else {
            await users.create(payload);
        }
        showModal.value = false;
    } catch (e) {
        errors.value = e.response?.data?.errors || { _global: ['Erreur lors de l’enregistrement.'] };
    }
}

async function deactivate(u) {
    if (u.id === auth.user.id) {
        alert('Vous ne pouvez pas désactiver votre propre compte.');
        return;
    }
    if (!confirm(`Désactiver ${u.name} ?`)) return;
    await users.remove(u.id);
}

onMounted(() => users.fetch());
</script>

<template>
    <div>
        <div class="flex flex-wrap items-end justify-between gap-3 mb-5 animate-rise">
            <div>
                <span class="badge-soft mb-2">Comptes</span>
                <h1 class="text-heading font-semibold text-ink">Utilisateurs</h1>
            </div>
            <button class="btn-accent" @click="openCreate">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                Nouvel utilisateur
            </button>
        </div>

        <div class="card overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-black/5">
                        <th class="th">Nom</th>
                        <th class="th">Email</th>
                        <th class="th">Rôle</th>
                        <th class="th">État</th>
                        <th class="th">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="u in users.items" :key="u.id" class="border-b border-black/5 last:border-0 hover:bg-black/[0.02]" :class="!u.actif && 'opacity-50'">
                        <td class="td font-semibold">{{ u.name }}</td>
                        <td class="td">{{ u.email }}</td>
                        <td class="td">
                            <span class="badge" :class="u.role === 'admin' ? 'badge-ink' : 'badge-soft'">
                                {{ u.role }}
                            </span>
                        </td>
                        <td class="td">
                            <span class="badge" :class="u.actif ? 'badge-lime' : 'badge-soft'">
                                {{ u.actif ? 'Actif' : 'Inactif' }}
                            </span>
                        </td>
                        <td class="td">
                            <div class="flex gap-1">
                                <button class="btn-secondary !px-3 !py-1.5 text-xs" @click="openEdit(u)">Éditer</button>
                                <button v-if="u.actif && u.id !== auth.user.id" class="btn-danger !px-3 !py-1.5 text-xs" @click="deactivate(u)">
                                    Désactiver
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Modal v-if="showModal" :title="editingId ? 'Éditer l’utilisateur' : 'Nouvel utilisateur'" @close="showModal = false">
            <form @submit.prevent="save" class="space-y-3">
                <div>
                    <label class="label">Nom</label>
                    <input v-model="form.name" type="text" class="input" required />
                    <p v-if="errors.name" class="field-error">{{ errors.name[0] }}</p>
                </div>
                <div>
                    <label class="label">Email</label>
                    <input v-model="form.email" type="email" class="input" required />
                    <p v-if="errors.email" class="field-error">{{ errors.email[0] }}</p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Mot de passe {{ editingId ? '(laisser vide = inchangé)' : '' }}</label>
                        <input v-model="form.password" type="password" class="input" :required="!editingId" />
                        <p v-if="errors.password" class="field-error">{{ errors.password[0] }}</p>
                    </div>
                    <div>
                        <label class="label">Confirmation</label>
                        <input v-model="form.password_confirmation" type="password" class="input" :required="!editingId" />
                    </div>
                </div>
                <div>
                    <label class="label">Rôle</label>
                    <select v-model="form.role" class="input">
                        <option value="operateur">Opérateur</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input v-model="form.actif" type="checkbox" class="rounded" />
                    Compte actif
                </label>
                <p v-if="errors._global" class="field-error">{{ errors._global[0] }}</p>
                <div class="flex justify-end gap-2">
                    <button type="button" class="btn-secondary" @click="showModal = false">Annuler</button>
                    <button type="submit" class="btn-primary">Enregistrer</button>
                </div>
            </form>
        </Modal>
    </div>
</template>
