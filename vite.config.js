import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/builder.js',
            ],
            refresh: ['resources/views/**', 'app/Livewire/**'],
        }),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0',
        cors: true,
        hmr: { protocol: 'wss' },
    },
    build: {
        chunkSizeWarningLimit: 1200,
    },
});
