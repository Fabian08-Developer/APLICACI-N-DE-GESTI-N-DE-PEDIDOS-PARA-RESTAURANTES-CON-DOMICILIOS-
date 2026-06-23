# Cafetería App

Sistema integral para gestión de pedidos locales y domicilios en restaurantes y cafeterías.

## Arquitectura

La aplicación está diseñada bajo el patrón Modelo-Vista-Controlador (MVC) y dividida en dominios para asegurar alta escalabilidad y fácil mantenimiento.

- **Servicios (`app/Services`)**: Contiene la lógica de negocio core (Carrito, Pedidos, Pagos).
- **Rutas (`routes`)**: Modularizadas en `admin.php`, `mesero.php`, `cocina.php` y `cliente.php`. Además, se incluye un `api.php` para futuras integraciones.
- **Roles y Permisos**: Implementado usando `spatie/laravel-permission` para permitir alta granularidad.
- **Seguridad**: Uso de cookies HTTP-Only para la transmisión de tokens de personal y middlewares de Sanctum para la API.

## Stack de Tecnologías

### Backend
- **Laravel**: Framework principal (PHP)
- **PostgreSQL**: Base de Datos
- **Redis**: Sistema de Caché

### Frontend
- **Blade + Alpine.js + Livewire**: Arquitectura reactiva frontend
- **Dependencias Frontend**:
  - `alpinejs`
  - `@alpinejs/mask`
  - `@alpinejs/persist`

### Dependencias PHP
- `livewire/livewire`
- `laravel/sanctum` (Autenticación API)
- `spatie/laravel-permission` (Roles y Permisos)
- `barryvdh/laravel-dompdf` (Exportación a PDF)
- `maatwebsite/excel` (Importación/Exportación Excel)
- `simplesoftwareio/simple-qrcode` (Generador de Códigos QR)
- `laravel/reverb` (Servidor WebSocket)

## Instalación

1. Clonar el repositorio.
2. Instalar dependencias de PHP:
   ```bash
   composer install
   ```
3. Instalar dependencias de JS:
   ```bash
   npm install
   ```
4. Configurar el entorno:
   Copiar `.env.example` a `.env` y configurar la base de datos.
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
5. Migrar y popular la base de datos (incluyendo Roles de Spatie):
   ```bash
   php artisan migrate --seed
   ```
6. Iniciar los workers de colas (necesario para el envío de correos y procesos asíncronos):
   ```bash
   php artisan queue:work
   ```
   > [!IMPORTANT]
   > En producción, asegúrate de configurar Supervisor para mantener en ejecución `php artisan queue:work`.

7. Iniciar el servidor local:
   ```bash
   php artisan serve
   npm run dev
   ```

## Pruebas (Testing)

Se han incluido pruebas unitarias y de integración utilizando PHPUnit para los servicios de Cliente.
Para ejecutarlas:
```bash
php artisan test
```

## Contribución

Si deseas contribuir, asegúrate de correr los tests localmente y respetar la inyección de dependencias a través de la carpeta `app/Services`.
