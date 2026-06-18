import { defineStore } from 'pinia';
import api, { setToken, getToken } from '../lib/api';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: getToken(),
        loading: false,
    }),
    getters: {
        isAuthenticated: (state) => !!state.token && !!state.user,
        isAdmin: (state) => state.user?.role === 'admin',
    },
    actions: {
        async login(email, password) {
            this.loading = true;
            try {
                const { data } = await api.post('/login', { email, password });
                this.token = data.token;
                this.user = data.user;
                setToken(data.token);
            } finally {
                this.loading = false;
            }
        },
        async fetchUser() {
            if (!this.token) return;
            try {
                const { data } = await api.get('/me');
                this.user = data.data ?? data;
            } catch {
                this.clear();
            }
        },
        async logout() {
            try {
                await api.post('/logout');
            } catch {
                // on ignore : on nettoie quand même côté client
            }
            this.clear();
        },
        clear() {
            this.user = null;
            this.token = null;
            setToken(null);
        },
    },
});
