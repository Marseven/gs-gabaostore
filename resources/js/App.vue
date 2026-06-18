<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from './stores/auth';
import AppLayout from './components/AppLayout.vue';

const route = useRoute();
const auth = useAuthStore();

const useLayout = computed(() => auth.isAuthenticated && route.name !== 'login');
</script>

<template>
    <AppLayout v-if="useLayout">
        <router-view v-slot="{ Component }">
            <transition name="view" mode="out-in">
                <component :is="Component" :key="route.name" />
            </transition>
        </router-view>
    </AppLayout>
    <router-view v-else v-slot="{ Component }">
        <transition name="fade" mode="out-in">
            <component :is="Component" :key="route.name" />
        </transition>
    </router-view>
</template>
