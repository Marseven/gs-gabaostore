import { defineStore } from 'pinia';
import api from '../lib/api';

export const useCategoriesStore = defineStore('categories', {
    state: () => ({
        items: [],
        loading: false,
    }),
    actions: {
        async fetch() {
            this.loading = true;
            try {
                const { data } = await api.get('/categories');
                this.items = data.data;
            } finally {
                this.loading = false;
            }
        },
        async create(payload) {
            const { data } = await api.post('/categories', payload);
            await this.fetch();
            return data.data;
        },
        async update(id, payload) {
            const { data } = await api.put(`/categories/${id}`, payload);
            await this.fetch();
            return data.data;
        },
        async remove(id) {
            await api.delete(`/categories/${id}`);
            await this.fetch();
        },
    },
});
