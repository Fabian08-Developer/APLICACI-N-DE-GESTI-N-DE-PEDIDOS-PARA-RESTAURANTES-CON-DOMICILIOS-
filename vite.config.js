import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/admin.css',
                'resources/css/cocina.css',
                'resources/js/cocina.js',
                'resources/css/mesero.css',
                'resources/js/mesero.js',
                'resources/css/menu.css',
                'resources/js/menu.js',
                'resources/css/pago.css',
                'resources/css/confirmacion.css',
                'resources/js/confirmacion.js',
            ],
            refresh: true,
        }),
    ],
});
