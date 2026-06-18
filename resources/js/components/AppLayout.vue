<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const router = useRouter();

const nav = computed(() => [
    { name: 'dashboard', label: 'Tableau de bord', to: { name: 'dashboard' } },
    { name: 'saisie', label: 'Saisie', to: { name: 'saisie' } },
    { name: 'stock', label: 'Stock', to: { name: 'stock' } },
    { name: 'historique', label: 'Historique', to: { name: 'historique' } },
    ...(auth.isAdmin
        ? [
              { name: 'articles', label: 'Articles', to: { name: 'articles' } },
              { name: 'utilisateurs', label: 'Utilisateurs', to: { name: 'utilisateurs' } },
          ]
        : []),
]);

const initials = computed(() => {
    const n = auth.user?.name || '?';
    return n.split(' ').map((p) => p[0]).slice(0, 2).join('').toUpperCase();
});

async function logout() {
    await auth.logout();
    router.push({ name: 'login' });
}
</script>

<template>
    <div class="min-h-screen flex flex-col bg-canvas">
        <!-- En-tête flottant -->
        <header class="sticky top-0 z-30 px-4 pt-4 pb-2">
            <div class="max-w-[1400px] mx-auto flex items-center justify-between gap-4">
                <!-- Logo -->
                <div class="flex items-center gap-2 shrink-0">
                    <div class="w-11 h-11 rounded-2xl bg-ink text-white grid place-items-center">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                            <path d="M5 9h14M5 15h14M9 5l-1.5 14M16.5 5L15 19" />
                        </svg>
                    </div>
                </div>

                <!-- Navigation pilule -->
                <nav class="flex-1 flex justify-center">
                    <div class="bg-surface rounded-pill p-1.5 shadow-soft flex items-center gap-1 overflow-x-auto max-w-full">
                        <router-link
                            v-for="item in nav"
                            :key="item.name"
                            :to="item.to"
                            class="nav-pill whitespace-nowrap hover:bg-black/[0.04]"
                            active-class="nav-pill-active hover:!bg-ink"
                        >
                            {{ item.label }}
                        </router-link>
                    </div>
                </nav>

                <!-- Profil + déconnexion -->
                <div class="flex items-center gap-2 shrink-0">
                    <div class="hidden sm:flex items-center gap-2 bg-surface rounded-pill pl-3 pr-1.5 py-1.5 shadow-soft">
                        <div class="text-right leading-tight">
                            <p class="text-xs font-semibold text-ink">{{ auth.user?.name }}</p>
                            <p class="text-[10px] text-muted capitalize">{{ auth.user?.role }}</p>
                        </div>
                        <div class="w-8 h-8 rounded-full bg-lime text-ink grid place-items-center text-xs font-bold">
                            {{ initials }}
                        </div>
                    </div>
                    <button class="icon-btn" title="Déconnexion" @click="logout">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9" />
                        </svg>
                    </button>
                </div>
            </div>
        </header>

        <main class="flex-1 max-w-[1400px] w-full mx-auto px-4 pb-10 pt-2">
            <slot />
        </main>

        <footer class="text-center text-xs text-muted/70 py-5">
            MRTECH — Gestion de Stock · usage interne
        </footer>
    </div>
</template>
