import { defineStore } from 'pinia';
import api from '../lib/api';

export const useMouvementsStore = defineStore('mouvements', {
    state: () => ({
        items: [],
        meta: null,
        loading: false,
    }),
    actions: {
        async fetch(params = {}) {
            this.loading = true;
            try {
                const { data } = await api.get('/mouvements', { params });
                this.items = data.data;
                this.meta = data.meta;
            } finally {
                this.loading = false;
            }
        },
        async createEntree(payload) {
            const { data } = await api.post('/mouvements/entree', payload);
            return data.data;
        },
        async createSortie(payload) {
            const { data } = await api.post('/mouvements/sortie', payload);
            return data.data;
        },
        async update(id, payload) {
            const { data } = await api.put(`/mouvements/${id}`, payload);
            return data.data;
        },
        async remove(id) {
            await api.delete(`/mouvements/${id}`);
        },
    },
});
