<script setup>
import { ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();

const email = ref('');
const password = ref('');
const error = ref('');

async function submit() {
    error.value = '';
    try {
        await auth.login(email.value, password.value);
        const redirect = route.query.redirect || { name: 'dashboard' };
        router.push(redirect);
    } catch (e) {
        error.value = e.response?.data?.message || 'Connexion impossible. Réessayez.';
    }
}
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-canvas px-4 relative overflow-hidden">
        <!-- Halo décoratif lime -->
        <div class="pointer-events-none absolute -top-32 -right-32 w-[28rem] h-[28rem] rounded-full bg-lime/30 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-40 -left-24 w-[24rem] h-[24rem] rounded-full bg-lime-pale/50 blur-3xl"></div>

        <div class="relative w-full max-w-sm animate-rise">
            <div class="flex flex-col items-center mb-7">
                <div class="w-14 h-14 rounded-2xl bg-ink text-white grid place-items-center mb-4 shadow-soft-lg">
                    <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                        <path d="M5 9h14M5 15h14M9 5l-1.5 14M16.5 5L15 19" />
                    </svg>
                </div>
                <h1 class="text-title font-semibold text-ink tracking-tight">Gestion de Stock</h1>
                <p class="text-sm text-muted mt-1">Connexion à l'espace interne</p>
            </div>

            <div class="card shadow-soft-lg p-7">
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="label">Email</label>
                        <input v-model="email" type="email" class="input" required autofocus placeholder="vous@gabaostore.ga" />
                    </div>
                    <div>
                        <label class="label">Mot de passe</label>
                        <input v-model="password" type="password" class="input" required placeholder="••••••••" />
                    </div>
                    <p v-if="error" class="text-sm text-red-600 bg-red-50 rounded-2xl px-3 py-2">{{ error }}</p>
                    <button type="submit" class="btn-accent w-full !py-3 text-[15px]" :disabled="auth.loading">
                        {{ auth.loading ? 'Connexion…' : 'Se connecter' }}
                        <svg v-if="!auth.loading" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14M13 6l6 6-6 6" />
                        </svg>
                    </button>
                </form>
            </div>

            <p class="text-center text-[11px] text-muted/70 mt-6">MRTECH — usage interne</p>
        </div>
    </div>
</template>
