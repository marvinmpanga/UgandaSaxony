import '../css/app.css';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import { Toaster } from 'sonner';

const appName = import.meta.env.VITE_APP_NAME || 'Uganda–Saxony Partnership';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    resolve: (name) => resolvePageComponent(`./pages/${name}.tsx`, import.meta.glob('./pages/**/*.tsx')) as any,
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    setup({ el, App, props }: { el: any; App: any; props: any }) {
        if (!el) {
            return;
        }
        const root = createRoot(el);

        root.render(
            <>
                <App {...props} />
                <Toaster richColors position="top-right" />
            </>
        );
    },
    progress: {
        color: '#20534f',
    },
});
