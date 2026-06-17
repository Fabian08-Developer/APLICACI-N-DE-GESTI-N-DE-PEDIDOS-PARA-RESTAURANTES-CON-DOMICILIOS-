import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/home.css',
                'resources/css/auth.css',
                'resources/css/acceso.css',
                'resources/css/admin.css',
                'resources/css/cancelacion-exitosa.css',
                'resources/css/categorias.css',
                'resources/css/cerrar_sesion.css',
                'resources/css/cocina.css',
                'resources/css/confirmacion.css',
                'resources/css/domiciliario.css',
                'resources/css/login.css',
                'resources/css/menu.css',
                'resources/css/mesas.css',
                'resources/css/mesero.css',
                'resources/css/pago.css',
                'resources/css/pago_pendiente.css',
                'resources/css/pedidos.css',
                'resources/css/productos.css',
                'resources/css/sesion_cerrada.css',
                'resources/css/usuarios.css',
                'resources/js/app.js',
                'resources/js/admin.js',
                'resources/js/bootstrap.js',
                'resources/js/categorias.js',
                'resources/js/cocina.js',
                'resources/js/confirmacion.js',
                'resources/js/echo-setup.js',
                'resources/js/login.js',
                'resources/js/menu.js',
                'resources/js/mesas.js',
                'resources/js/mesero.js',
                'resources/js/productos.js'
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});

