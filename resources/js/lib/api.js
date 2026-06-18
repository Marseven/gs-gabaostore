import axios from 'axios';

const api = axios.create({
    baseURL: '/api/v1',
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    },
});

const TOKEN_KEY = 'gs_token';

export function setToken(token) {
    if (token) {
        localStorage.setItem(TOKEN_KEY, token);
    } else {
        localStorage.removeItem(TOKEN_KEY);
    }
}

export function getToken() {
    return localStorage.getItem(TOKEN_KEY);
}

// Intercepteur : ajoute le token Bearer à chaque requête.
api.interceptors.request.use((config) => {
    const token = getToken();
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Gestion globale du 401 : on purge la session et on renvoie vers /login.
let onUnauthorized = null;
export function setUnauthorizedHandler(fn) {
    onUnauthorized = fn;
}

api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401 && onUnauthorized) {
            onUnauthorized();
        }
        return Promise.reject(error);
    }
);

export default api;
