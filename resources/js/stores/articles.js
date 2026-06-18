import { defineStore } from 'pinia';
import api from '../lib/api';

export const useArticlesStore = defineStore('articles', {
    state: () => ({
        items: [],
        meta: null,
        loading: false,
    }),
    actions: {
        async fetch(params = {}) {
            this.loading = true;
            try {
                const { data } = await api.get('/articles', { params });
                this.items = data.data;
                this.meta = data.meta;
            } finally {
                this.loading = false;
            }
        },
        async create(payload) {
            const { data } = await api.post('/articles', payload);
            return data.data;
        },
        async update(id, payload) {
            const { data } = await api.put(`/articles/${id}`, payload);
            return data.data;
        },
        async remove(id) {
            await api.delete(`/articles/${id}`);
        },
        // Recherche légère pour l'autocomplétion (pagination max 20).
        async search(term) {
            const { data } = await api.get('/articles', {
                params: { search: term, actif: true, per_page: 20 },
            });
            return data.data;
        },
    },
});
