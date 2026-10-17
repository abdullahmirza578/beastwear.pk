import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/style.css',
                'resources/css/components.css',
                'resources/css/responsive.css',
                'resources/css/theme.css',
            ],
            refresh: true,
        }),
    ],
});
