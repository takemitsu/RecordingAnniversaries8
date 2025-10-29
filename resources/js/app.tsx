import './bootstrap';
import '../css/app.css';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot, hydrateRoot } from 'react-dom/client';
import { Toaster } from 'sonner';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./Pages/${name}.tsx`, import.meta.glob('./Pages/**/*.tsx')),
    setup({ el, App, props }) {
        const AppWithToaster = () => (
            <>
                <App {...props} />
                <Toaster position="top-right" />
            </>
        );

        if (import.meta.env.DEV) {
            createRoot(el).render(<AppWithToaster />);
            return;
        }

        hydrateRoot(el, <AppWithToaster />);
    },
    progress: {
        color: '#4B5563',
    },
});
