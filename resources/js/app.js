import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import '../css/app.css';
import './bootstrap';

createInertiaApp({
    // Résolution des pages selon la structure Pages/
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    // Optionnel : titre dynamique selon la page
    title: (title) => (title ? `${title} - MyMedicalApp` : 'MyMedicalApp'),
});
