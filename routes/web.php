<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\CategoriaController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\MesaController;
use App\Http\Controllers\Admin\PedidoController;
use App\Http\Controllers\Admin\ProductoController;
use App\Http\Controllers\Admin\SalesReportController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Cliente\ClienteController;
use App\Http\Controllers\Cocina\DashboardController as CocinaDashboard;
use App\Http\Controllers\Mesero\DashboardController as MeseroDashboard;
use App\Http\Controllers\WompiWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas públicas — sin autenticación, sin sesión de mesa
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => redirect()->route('login'));

// Usuarios ya autenticados son redirigidos a su dashboard (GuestOnly middleware)
Route::middleware('guest.only')->group(function () {
    Route::get('/login',    [AuthController::class, 'mostrarLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/register',[AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth.custom');

/*
|--------------------------------------------------------------------------
| Rutas del cliente — acceso por QR
|
| ARQUITECTURA:
| - El token viaja en la URL (?t=TOKEN), no en session()
| - Esto permite que múltiples pestañas sean completamente independientes
| - ClienteTokenMiddleware lee el token del request, valida en BD
|   y adjunta el objeto SesionMesa a $request->attributes
| - Los controladores leen desde $request->attributes, nunca desde session()
|--------------------------------------------------------------------------
*/

// Punto de entrada del QR — sin middleware (genera la sesión)
Route::get('/mesa/{codigo}', [ClienteController::class, 'escanearQR'])->name('cliente.qr');

Route::prefix('cliente')->name('cliente.')->group(function () {

    // Rutas de inicio — no requieren sesión de mesa activa
    // (son el paso previo a tener token)
    Route::post('/sesion/individual',        [ClienteController::class, 'crearSesionIndividual'])->name('sesion.individual');
/*     Route::post('/sesion/compartida/crear',  [ClienteController::class, 'crearSesionCompartida'])->name('sesion.compartida.crear');
    Route::post('/sesion/compartida/unirse', [ClienteController::class, 'unirseASesion'])->name('sesion.compartida.unirse'); */
    Route::get('/sin-sesion',                [ClienteController::class, 'sinSesion'])->name('sin-sesion');

    // -----------------------------------------------------------------------
    // Rutas protegidas — requieren token válido en URL
    // ClienteTokenMiddleware valida el token y adjunta SesionMesa al request
    // -----------------------------------------------------------------------
    Route::middleware(\App\Http\Middleware\ClienteTokenMiddleware::class)->group(function () {

        // Menú y carrito — sin ownership de recurso específico
        Route::get('/menu',                             [ClienteController::class, 'menu'])->name('menu');
        Route::post('/carrito/agregar',                 [ClienteController::class, 'agregarAlCarrito'])->name('carrito.agregar');
        Route::post('/carrito/actualizar/{productoId}', [ClienteController::class, 'actualizarCantidadCarrito'])->name('carrito.actualizar');
        Route::post('/carrito/eliminar/{id}',           [ClienteController::class, 'eliminarDelCarrito'])->name('carrito.eliminar');

        // Confirmar pedido
        Route::post('/pedido/confirmar', [ClienteController::class, 'confirmarPedido'])->name('pedido.confirmar');

        // Pago y confirmación
        Route::get('/pago',                   [ClienteController::class, 'pago'])->name('pago');
        Route::post('/pago/procesar',         [ClienteController::class, 'procesarPago'])->name('pago.procesar');
        Route::get('/confirmacion',           [ClienteController::class, 'confirmacion'])->name('confirmacion');
        Route::get('/cancelacion/exitosa',    [ClienteController::class, 'cancelacionExitosa'])->name('cancelacion.exitosa');

        // Logout
        Route::post('/logout',             [ClienteController::class, 'logout'])->name('logout');
        Route::post('/logout/inactividad', [ClienteController::class, 'logoutInactividad'])->name('logout.inactividad');

        // -------------------------------------------------------------------
        // Rutas con ownership — el recurso debe pertenecer a esta sesión de mesa
        // ClienteOwnershipMiddleware verifica que pedidoId/pagoId sea de esta sesión
        // -------------------------------------------------------------------
        Route::middleware(\App\Http\Middleware\ClienteOwnershipMiddleware::class.':pedido')->group(function () {
            Route::get('/pedido/estado/{pedidoId}',    [ClienteController::class, 'estadoPedido'])->name('pedido.estado');
            Route::post('/pedido/{pedidoId}/cancelar', [ClienteController::class, 'cancelarPedido'])->name('pedido.cancelar');
        });

        Route::middleware(\App\Http\Middleware\ClienteOwnershipMiddleware::class.':pago')->group(function () {
            Route::get('/pago/pendiente/{pagoId}',  [ClienteController::class, 'pagoPendiente'])->name('pago.pendiente');
            Route::get('/pago/estado/{pagoId}',     [ClienteController::class, 'estadoPago'])->name('pago.estado');
            Route::post('/pago/simular/{pagoId}',   [ClienteController::class, 'simularConfirmacion'])->name('pago.simular');
        });
    });
});

/*
|--------------------------------------------------------------------------
| Administrador — acceso total
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')
    ->middleware(['auth.custom', 'rol:administrador'])
    ->group(function () {

        Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

        Route::prefix('categorias')->name('categorias.')->group(function () {
            Route::get('/',                 [CategoriaController::class, 'index'])->name('index');
            Route::post('/',                [CategoriaController::class, 'store'])->name('store');
            Route::get('/{id}/editar',      [CategoriaController::class, 'editar'])->name('editar');
            Route::post('/{id}/actualizar', [CategoriaController::class, 'actualizar'])->name('actualizar');
            Route::post('/{id}/eliminar',   [CategoriaController::class, 'eliminar'])->name('eliminar');
        });

        Route::prefix('mesas')->name('mesas.')->group(function () {
            Route::get('/',                 [MesaController::class, 'index'])->name('index');
            Route::post('/',                [MesaController::class, 'store'])->name('store');
            Route::get('/{id}/editar',      [MesaController::class, 'editar'])->name('editar');
            Route::post('/{id}/actualizar', [MesaController::class, 'actualizar'])->name('actualizar');
            Route::post('/{id}/eliminar',   [MesaController::class, 'eliminar'])->name('eliminar');
            Route::get('/{id}/qr',          [MesaController::class, 'verQR'])->name('qr');
        });

        Route::prefix('productos')->name('productos.')->group(function () {
            Route::get('/',                       [ProductoController::class, 'index'])->name('index');
            Route::post('/',                      [ProductoController::class, 'store'])->name('store');
            Route::get('/{id}/editar',            [ProductoController::class, 'editar'])->name('editar');
            Route::post('/{id}/actualizar',       [ProductoController::class, 'actualizar'])->name('actualizar');
            Route::post('/{id}/toggle',           [ProductoController::class, 'toggle'])->name('toggle');
            Route::post('/importar',              [ProductoController::class, 'importar'])->name('importar');
            Route::get('/plantilla-importacion',  [ProductoController::class, 'descargarPlantilla'])->name('plantilla');
            Route::get('/exportar',               [ProductoController::class, 'exportar'])->name('exportar');
        });

        Route::prefix('pedidos')->name('pedidos.')->group(function () {
            Route::get('/',                 [PedidoController::class, 'index'])->name('index');
            Route::get('/{id}',             [PedidoController::class, 'detalle'])->name('detalle');
            Route::post('/{id}/estado',     [PedidoController::class, 'cambiarEstado'])->name('estado');
            Route::post('/{id}/cancelar',   [PedidoController::class, 'cancelar'])->name('cancelar');
        });

        Route::prefix('usuarios')->name('usuarios.')->group(function () {
            Route::get('/',                 [UsuarioController::class, 'index'])->name('index');
            Route::post('/',                [UsuarioController::class, 'store'])->name('store');
            Route::get('/{id}/editar',      [UsuarioController::class, 'editar'])->name('editar');
            Route::post('/{id}/actualizar', [UsuarioController::class, 'actualizar'])->name('actualizar');
            Route::post('/{id}/toggle',     [UsuarioController::class, 'toggle'])->name('toggle');
        });

        Route::prefix('reportes')->name('reportes.')->group(function () {
            Route::get('/ventas', [SalesReportController::class, 'index'])->name('ventas');
            Route::get('/exportar', [SalesReportController::class, 'export'])->name('exportar');
            
            // Programación de reportes
            Route::get('/programacion', [\App\Http\Controllers\Admin\ReportScheduleController::class, 'index'])->name('programacion.index');
            Route::post('/programacion', [\App\Http\Controllers\Admin\ReportScheduleController::class, 'store'])->name('programacion.store');
            Route::post('/programacion/prueba', [\App\Http\Controllers\Admin\ReportScheduleController::class, 'sendTest'])->name('programacion.test');
            Route::post('/programacion/{id}/eliminar', [\App\Http\Controllers\Admin\ReportScheduleController::class, 'destroy'])->name('programacion.destroy');
        });

        // Gestión de Domiciliarios
        Route::get('/domiciliarios', [\App\Http\Controllers\Admin\DomiciliarioController::class, 'index'])->name('domiciliarios.index');
        Route::post('/domiciliarios', [\App\Http\Controllers\Admin\DomiciliarioController::class, 'store'])->name('domiciliarios.store');
        Route::get('/domiciliarios/{domiciliario}', [\App\Http\Controllers\Admin\DomiciliarioController::class, 'show'])->name('domiciliarios.show');
        Route::put('/domiciliarios/{domiciliario}', [\App\Http\Controllers\Admin\DomiciliarioController::class, 'update'])->name('domiciliarios.update');
        Route::delete('/domiciliarios/{domiciliario}', [\App\Http\Controllers\Admin\DomiciliarioController::class, 'destroy'])->name('domiciliarios.destroy');

        // Zonas de Cobertura
        Route::get('/zonas-cobertura', [\App\Http\Controllers\Admin\ZonaCoberturaController::class, 'index'])->name('zonas.index');
        Route::post('/zonas-cobertura', [\App\Http\Controllers\Admin\ZonaCoberturaController::class, 'store'])->name('zonas.store');
        Route::put('/zonas-cobertura/{zona}', [\App\Http\Controllers\Admin\ZonaCoberturaController::class, 'update'])->name('zonas.update');
        Route::delete('/zonas-cobertura/{zona}', [\App\Http\Controllers\Admin\ZonaCoberturaController::class, 'destroy'])->name('zonas.destroy');
        Route::get('/zonas-cobertura/{id}/barrios', [\App\Http\Controllers\Admin\ZonaCoberturaController::class, 'getBarrios'])->name('zonas.barrios');
        Route::get('/zonas-cobertura/{id}', [\App\Http\Controllers\Admin\ZonaCoberturaController::class, 'show'])->name('zonas.show');
    });

/*
|--------------------------------------------------------------------------
| Mesero
|--------------------------------------------------------------------------
*/
Route::prefix('mesero')->name('mesero.')
    ->middleware(['auth.custom', 'rol:mesero'])
    ->group(function () {
        Route::get('/dashboard',              [MeseroDashboard::class, 'index'])->name('dashboard');
        Route::get('/mesas',                  [MeseroDashboard::class, 'mesas'])->name('mesas');
        Route::get('/historial',              [MeseroDashboard::class, 'historial'])->name('historial');
        Route::post('/pedidos/{id}/entregar', [MeseroDashboard::class, 'entregar'])->name('pedidos.entregar');
        Route::post('/pedidos/{id}/cancelar', [MeseroDashboard::class, 'cancelarPedido'])->name('pedidos.cancelar');
        Route::post('/mesas/{id}/liberar',    [MeseroDashboard::class, 'liberarMesa'])->name('mesas.liberar');
        Route::post('/sesion/{id}/cerrar',    [MeseroDashboard::class, 'cerrarSesion'])->name('sesiones.cerrar');
    });

/*
|--------------------------------------------------------------------------
| Cocina
|--------------------------------------------------------------------------
*/
Route::prefix('cocina')->name('cocina.')
    ->middleware(['auth.custom', 'rol:cocina'])
    ->group(function () {
        Route::get('/dashboard',                     [CocinaDashboard::class, 'index'])->name('dashboard');
        Route::post('/pedidos/{id}/estado/{estado}', [CocinaDashboard::class, 'cambiarEstado'])->name('estado');
        Route::get('/pedidos/nuevos',                [CocinaDashboard::class, 'pedidosNuevos'])->name('pedidos.nuevos');
    });

Route::get('/dashboard', fn() => redirect()->route('login'))->name('dashboard');

/*
|--------------------------------------------------------------------------
| Webhook Wompi — sin CSRF, autenticado por firma HMAC
|--------------------------------------------------------------------------
*/
Route::post('/wompi/webhook', [WompiWebhookController::class, 'handle'])
    ->name('wompi.webhook')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);