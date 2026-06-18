import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from './stores/auth';

import Login from './views/Login.vue';
import Dashboard from './views/Dashboard.vue';
import MouvementForm from './views/MouvementForm.vue';
import Stock from './views/Stock.vue';
import Historique from './views/Historique.vue';
import Articles from './views/Articles.vue';
import Users from './views/Users.vue';
import Profile from './views/Profile.vue';

const routes = [
    { path: '/login', name: 'login', component: Login, meta: { public: true } },
    { path: '/', name: 'dashboard', component: Dashboard },
    { path: '/saisie', name: 'saisie', component: MouvementForm },
    { path: '/stock', name: 'stock', component: Stock },
    { path: '/historique', name: 'historique', component: Historique },
    { path: '/profil', name: 'profil', component: Profile },
    { path: '/articles', name: 'articles', component: Articles, meta: { admin: true } },
    { path: '/utilisateurs', name: 'utilisateurs', component: Users, meta: { admin: true } },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to) => {
    const auth = useAuthStore();

    // Charge l'utilisateur si un token existe mais pas encore d'user en mémoire.
    if (auth.token && !auth.user) {
        await auth.fetchUser();
    }

    if (to.meta.public) {
        if (auth.isAuthenticated && to.name === 'login') {
            return { name: 'dashboard' };
        }
        return true;
    }

    if (!auth.isAuthenticated) {
        return { name: 'login', query: { redirect: to.fullPath } };
    }

    if (to.meta.admin && !auth.isAdmin) {
        return { name: 'dashboard' };
    }

    return true;
});

export default router;
