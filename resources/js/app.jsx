import React from 'react';
import ReactDOM from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';
import './bootstrap';
import '@/Components/Layouts/MainLayout';
import '@/Components/UI/index';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.jsx`, import.meta.glob('./Pages/**/*.jsx')),
    setup({ el, App, props, plugin }) {
        return ReactDOM.createRoot(el).render(
            <React.StrictMode>
                <App {...props} />
            </React.StrictMode>
        );
    },
    progress: {
        color: '#4F46E5',
    },
});
