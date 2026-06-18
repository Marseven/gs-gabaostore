<script setup>
import { reactive, ref } from 'vue';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();

const initials = () => {
    const n = auth.user?.name || '?';
    return n.split(' ').map((p) => p[0]).slice(0, 2).join('').toUpperCase();
};

// --- Profil ---
const profile = reactive({ name: auth.user?.name || '', email: auth.user?.email || '' });
const profileErrors = ref({});
const profileMsg = ref('');
const savingProfile = ref(false);

async function saveProfile() {
    profileErrors.value = {};
    profileMsg.value = '';
    savingProfile.value = true;
    try {
        await auth.updateProfile({ name: profile.name, email: profile.email });
        profileMsg.value = 'Profil mis à jour.';
    } catch (e) {
        profileErrors.value = e.response?.data?.errors || { _global: ['Erreur lors de la mise à jour.'] };
    } finally {
        savingProfile.value = false;
    }
}

// --- Mot de passe ---
const pwd = reactive({ current_password: '', password: '', password_confirmation: '' });
const pwdErrors = ref({});
const pwdMsg = ref('');
const savingPwd = ref(false);

async function savePassword() {
    pwdErrors.value = {};
    pwdMsg.value = '';
    savingPwd.value = true;
    try {
        await auth.updatePassword({ ...pwd });
        pwdMsg.value = 'Mot de passe mis à jour.';
        pwd.current_password = '';
        pwd.password = '';
        pwd.password_confirmation = '';
    } catch (e) {
        pwdErrors.value = e.response?.data?.errors || { _global: ['Erreur lors du changement de mot de passe.'] };
    } finally {
        savingPwd.value = false;
    }
}
</script>

<template>
    <div class="max-w-3xl mx-auto">
        <div class="mb-6 animate-rise">
            <span class="badge-soft mb-2">Compte</span>
            <h1 class="text-heading font-semibold text-ink">Mon profil</h1>
        </div>

        <!-- En-tête identité -->
        <div class="card p-6 mb-5 flex items-center gap-4 animate-rise" style="animation-delay: 60ms">
            <div class="w-16 h-16 rounded-full bg-lime text-ink grid place-items-center text-xl font-bold shadow-pill animate-float">
                {{ initials() }}
            </div>
            <div>
                <p class="text-lg font-semibold text-ink">{{ auth.user?.name }}</p>
                <p class="text-sm text-muted">{{ auth.user?.email }}</p>
                <span class="badge mt-1.5" :class="auth.isAdmin ? 'badge-ink' : 'badge-soft'">{{ auth.user?.role }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- Infos -->
            <div class="card p-6 animate-rise" style="animation-delay: 120ms">
                <h2 class="font-semibold text-ink mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Informations
                </h2>
                <form @submit.prevent="saveProfile" class="space-y-3">
                    <div>
                        <label class="label">Nom</label>
                        <input v-model="profile.name" type="text" class="input" required />
                        <p v-if="profileErrors.name" class="field-error">{{ profileErrors.name[0] }}</p>
                    </div>
                    <div>
                        <label class="label">Email</label>
                        <input v-model="profile.email" type="email" class="input" required />
                        <p v-if="profileErrors.email" class="field-error">{{ profileErrors.email[0] }}</p>
                    </div>
                    <div>
                        <label class="label">Rôle</label>
                        <input :value="auth.user?.role" type="text" class="input opacity-60 cursor-not-allowed" disabled />
                        <p class="text-[11px] text-muted mt-1">Le rôle est géré par un administrateur.</p>
                    </div>
                    <p v-if="profileErrors._global" class="field-error">{{ profileErrors._global[0] }}</p>
                    <transition name="fade">
                        <p v-if="profileMsg" class="text-sm text-ink bg-lime-pale/80 rounded-2xl px-4 py-2.5 font-medium inline-flex items-center gap-2">
                            <svg class="w-4 h-4 text-lime-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                            {{ profileMsg }}
                        </p>
                    </transition>
                    <div class="flex justify-end">
                        <button type="submit" class="btn-primary" :disabled="savingProfile">
                            {{ savingProfile ? 'Enregistrement…' : 'Enregistrer' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Mot de passe -->
            <div class="card p-6 animate-rise" style="animation-delay: 180ms">
                <h2 class="font-semibold text-ink mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                    Mot de passe
                </h2>
                <form @submit.prevent="savePassword" class="space-y-3">
                    <div>
                        <label class="label">Mot de passe actuel</label>
                        <input v-model="pwd.current_password" type="password" class="input" required autocomplete="current-password" />
                        <p v-if="pwdErrors.current_password" class="field-error">{{ pwdErrors.current_password[0] }}</p>
                    </div>
                    <div>
                        <label class="label">Nouveau mot de passe</label>
                        <input v-model="pwd.password" type="password" class="input" required autocomplete="new-password" />
                        <p v-if="pwdErrors.password" class="field-error">{{ pwdErrors.password[0] }}</p>
                    </div>
                    <div>
                        <label class="label">Confirmer</label>
                        <input v-model="pwd.password_confirmation" type="password" class="input" required autocomplete="new-password" />
                    </div>
                    <p v-if="pwdErrors._global" class="field-error">{{ pwdErrors._global[0] }}</p>
                    <transition name="fade">
                        <p v-if="pwdMsg" class="text-sm text-ink bg-lime-pale/80 rounded-2xl px-4 py-2.5 font-medium inline-flex items-center gap-2">
                            <svg class="w-4 h-4 text-lime-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                            {{ pwdMsg }}
                        </p>
                    </transition>
                    <div class="flex justify-end">
                        <button type="submit" class="btn-primary" :disabled="savingPwd">
                            {{ savingPwd ? 'Mise à jour…' : 'Changer le mot de passe' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
