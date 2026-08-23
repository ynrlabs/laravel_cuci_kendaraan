import React from 'react';
import ReactDOM from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import './bootstrap';
import '@/Components/Layouts/MainLayout';
import '@/Components/UI/index';


// Global loading screen component for Inertia page transitions
const LoadingScreen = () => {
    const [show, setShow] = React.useState(false);
    const [progress, setProgress] = React.useState(0);
    const progressIntervalRef = React.useRef(null);

    React.useEffect(() => {
        const stopLoading = () => {
            setProgress(100);
            if (progressIntervalRef.current) {
                clearInterval(progressIntervalRef.current);
                progressIntervalRef.current = null;
            }
            setTimeout(() => {
                setShow(false);
                setProgress(0);
            }, 200);
        };

        const cancelLoading = () => {
            if (progressIntervalRef.current) {
                clearInterval(progressIntervalRef.current);
                progressIntervalRef.current = null;
            }
            setShow(false);
            setProgress(0);
        };

        const onStart = () => {
            setShow(true);
            setProgress(0);
            if (progressIntervalRef.current) {
                clearInterval(progressIntervalRef.current);
            }
            progressIntervalRef.current = setInterval(() => {
                setProgress(prev => Math.min(prev + Math.random() * 10, 90));
            }, 100);
        };

        document.addEventListener('inertia:start', onStart);
        document.addEventListener('inertia:finish', stopLoading);
        document.addEventListener('inertia:cancel', cancelLoading);
        document.addEventListener('inertia:error', cancelLoading);
        document.addEventListener('inertia:invalid', cancelLoading);
        document.addEventListener('inertia:exception', cancelLoading);

        return () => {
            document.removeEventListener('inertia:start', onStart);
            document.removeEventListener('inertia:finish', stopLoading);
            document.removeEventListener('inertia:cancel', cancelLoading);
            document.removeEventListener('inertia:error', cancelLoading);
            document.removeEventListener('inertia:invalid', cancelLoading);
            document.removeEventListener('inertia:exception', cancelLoading);
            if (progressIntervalRef.current) {
                clearInterval(progressIntervalRef.current);
            }
        };
    }, []);

    if (!show) return null;

    return (
        <div className="fixed inset-0 z-[9999] flex items-center justify-center bg-black/30 backdrop-blur-sm transition-opacity duration-300">
            <div className="relative flex flex-col items-center">
                <div className="w-10 h-10 border-4 border-blue-500/30 border-t-blue-500 rounded-full animate-spin"></div>
                <div className="loading-text mt-4 text-center">
                    <span className="text-sm font-medium text-white">
                        Memuat halaman...
                    </span>
                </div>
                <div className="fixed top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-500 to-indigo-600 transform origin-left transition-transform duration-200"
                     style={{ transform: `scaleX(${progress / 100})` }} />
            </div>
        </div>
    );
};

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.jsx`, import.meta.glob('./Pages/**/*.jsx')),
    setup({ el, App, props, plugin }) {
        const root = el._reactRoot || (el._reactRoot = ReactDOM.createRoot(el));
        return root.render(
            <React.StrictMode>
                <LoadingScreen />
                <App {...props} />
            </React.StrictMode>
        );
    },
    progress: {
        color: '#4F46E5',
    },
});
