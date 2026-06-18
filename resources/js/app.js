import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import App from './App.vue';
import { setUnauthorizedHandler } from './lib/api';
import { useAuthStore } from './stores/auth';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);

// Sur 401, on purge l'auth et on redirige vers la page de login.
setUnauthorizedHandler(() => {
    const auth = useAuthStore();
    auth.clear();
    if (router.currentRoute.value.name !== 'login') {
        router.push({ name: 'login' });
    }
});

app.mount('#app');
