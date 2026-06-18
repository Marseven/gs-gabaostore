<script setup>
import { computed, ref, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();

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

const mobileOpen = ref(false);
// Referme le menu mobile à chaque changement de route.
watch(() => route.fullPath, () => { mobileOpen.value = false; });

async function logout() {
    mobileOpen.value = false;
    await auth.logout();
    router.push({ name: 'login' });
}
</script>

<template>
    <div class="min-h-screen flex flex-col bg-canvas">
        <!-- En-tête flottant -->
        <header class="sticky top-0 z-30 px-3 sm:px-4 pt-3 sm:pt-4 pb-2">
            <div class="max-w-[1400px] mx-auto flex items-center justify-between gap-2 sm:gap-4">
                <!-- Logo -->
                <router-link :to="{ name: 'dashboard' }" class="shrink-0">
                    <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl bg-ink text-white grid place-items-center animate-float shadow-glass">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                            <path d="M5 9h14M5 15h14M9 5l-1.5 14M16.5 5L15 19" />
                        </svg>
                    </div>
                </router-link>

                <!-- Navigation desktop (pilule verre) -->
                <nav class="hidden md:flex flex-1 justify-center">
                    <div class="glass rounded-pill p-1.5 flex items-center gap-1 overflow-x-auto max-w-full">
                        <router-link
                            v-for="item in nav"
                            :key="item.name"
                            :to="item.to"
                            class="nav-pill whitespace-nowrap hover:bg-white/40"
                            active-class="nav-pill-active hover:!bg-ink"
                        >
                            {{ item.label }}
                        </router-link>
                    </div>
                </nav>

                <!-- Actions droite -->
                <div class="flex items-center gap-2 shrink-0">
                    <router-link
                        :to="{ name: 'profil' }"
                        class="hidden sm:flex items-center gap-2 glass rounded-pill pl-3 pr-1.5 py-1.5 hover:bg-white/80 hover:-translate-y-0.5 transition-all duration-300 ease-spring"
                        active-class="ring-2 ring-ink/20"
                        title="Mon profil"
                    >
                        <div class="text-right leading-tight">
                            <p class="text-xs font-semibold text-ink max-w-[120px] truncate">{{ auth.user?.name }}</p>
                            <p class="text-[10px] text-muted capitalize">{{ auth.user?.role }}</p>
                        </div>
                        <div class="w-8 h-8 rounded-full bg-lime text-ink grid place-items-center text-xs font-bold shadow-pill">
                            {{ initials }}
                        </div>
                    </router-link>

                    <button class="hidden sm:inline-flex icon-btn" title="Déconnexion" @click="logout">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9" />
                        </svg>
                    </button>

                    <!-- Bouton menu mobile -->
                    <button
                        class="md:hidden icon-btn !w-10 !h-10"
                        :aria-expanded="mobileOpen"
                        aria-label="Menu"
                        @click="mobileOpen = !mobileOpen"
                    >
                        <svg v-if="!mobileOpen" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                        <svg v-else class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <!-- Menu mobile déroulant (verre) -->
            <transition name="modal">
                <nav v-if="mobileOpen" class="md:hidden max-w-[1400px] mx-auto mt-2 glass-strong rounded-card p-2">
                    <router-link
                        v-for="item in nav"
                        :key="item.name"
                        :to="item.to"
                        class="block px-4 py-3 rounded-2xl text-sm font-medium text-ink/80 hover:bg-white/50 transition"
                        active-class="!bg-ink !text-white"
                    >
                        {{ item.label }}
                    </router-link>
                    <div class="border-t border-white/40 mt-2 pt-2 flex items-center justify-between">
                        <router-link :to="{ name: 'profil' }" class="flex items-center gap-2 px-3 py-2 rounded-2xl hover:bg-white/50 transition">
                            <div class="w-8 h-8 rounded-full bg-lime text-ink grid place-items-center text-xs font-bold">{{ initials }}</div>
                            <div class="leading-tight">
                                <p class="text-xs font-semibold text-ink">{{ auth.user?.name }}</p>
                                <p class="text-[10px] text-muted capitalize">{{ auth.user?.role }}</p>
                            </div>
                        </router-link>
                        <button class="btn-secondary !py-2" @click="logout">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                            Déconnexion
                        </button>
                    </div>
                </nav>
            </transition>
        </header>

        <main class="flex-1 max-w-[1400px] w-full mx-auto px-3 sm:px-4 pb-10 pt-2">
            <slot />
        </main>

        <footer class="text-center text-xs text-muted/70 py-5 px-4">
            MRTECH — Gestion de Stock · usage interne
        </footer>
    </div>
</template>
