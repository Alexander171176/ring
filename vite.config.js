import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            ssr: 'resources/js/ssr.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
    optimizeDeps: {
        include: [
            '@codemirror/state',
            '@codemirror/view',
            '@codemirror/commands',
            '@codemirror/history',
            '@codemirror/language',
            '@codemirror/lang-javascript',
            '@codemirror/theme-one-dark',
        ],
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    // Убираем vue-router, если он не используется в проекте
                    vue: ['vue'],
                    vendor: ['axios', 'lodash'],
                },
            },
        },
        chunkSizeWarningLimit: 1500, // Увеличиваем лимит до 1500 кБ
    },
});
