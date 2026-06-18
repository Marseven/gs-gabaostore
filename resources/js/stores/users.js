import { defineStore } from 'pinia';
import api from '../lib/api';

export const useUsersStore = defineStore('users', {
    state: () => ({
        items: [],
        loading: false,
    }),
    actions: {
        async fetch() {
            this.loading = true;
            try {
                const { data } = await api.get('/users');
                this.items = data.data;
            } finally {
                this.loading = false;
            }
        },
        async create(payload) {
            const { data } = await api.post('/users', payload);
            await this.fetch();
            return data.data;
        },
        async update(id, payload) {
            const { data } = await api.put(`/users/${id}`, payload);
            await this.fetch();
            return data.data;
        },
        async remove(id) {
            await api.delete(`/users/${id}`);
            await this.fetch();
        },
    },
});
