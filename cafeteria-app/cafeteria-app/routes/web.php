<?php

use App\Livewire\Auth\RegisterManager;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\RegistrationPending;
use App\Livewire\Auth\RecoverAccount;
use App\Livewire\Dashboard\GerenteDashboard;
use App\Livewire\Sucursales\ManageSucursales;
use App\Livewire\Settings\Settings;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/registro/gerente', RegisterManager::class)->name('registro');
Route::get('/registro/pendiente', RegistrationPending::class)->name('registration.pending');
Route::get('/login', Login::class)->name('login');
Route::get('/recuperar-contrasena', ForgotPassword::class)->name('password.request');
Route::get('/recuperacion/verificar/{userId}', RecoverAccount::class)->name('auth.recover-account');

Route::post('/logout', function (Illuminate\Http\Request $request) {
    $token = $request->header('X-Staff-Token')
          ?? $request->query('_st')
          ?? $request->input('_st');

    if ($token) {
        \App\Models\Sesion::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->where('token', $token)
            ->update(['activa' => false]);

        return redirect()->route('login', ['_clear' => 1])
            ->with('exito', 'Sesión cerrada correctamente.');
    }

    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');


use App\Livewire\SuperAdmin\Dashboard as SuperAdminDashboard;

Route::get('/dashboard', function() {
    $user = auth()->user();
    if ($user->hasRole('super-admin')) {
        return redirect()->route('super-admin.dashboard');
    }
    if ($user->hasRole('administrador')) {
        return redirect()->route('admin.dashboard');
    }
    return app(GerenteDashboard::class)();
})->name('dashboard')->middleware('auth.custom');

// Rutas de Super Administrador (Dueño de la Plataforma)
use App\Livewire\SuperAdmin\ManageRequests;
use App\Livewire\SuperAdmin\ManageTenants;
use App\Livewire\SuperAdmin\ManageGlobalUsers;
use App\Livewire\SuperAdmin\ManageTrash;

Route::group(['prefix' => 'master', 'middleware' => ['auth', \App\Http\Middleware\EnsureIsSuperAdmin::class]], function() {
    Route::get('/dashboard', SuperAdminDashboard::class)->name('super-admin.dashboard');
    Route::get('/solicitudes', ManageRequests::class)->name('super-admin.requests');
    Route::get('/tenants', ManageTenants::class)->name('super-admin.tenants');
    Route::get('/usuarios', ManageGlobalUsers::class)->name('super-admin.users');
    Route::get('/papelera', ManageTrash::class)->name('super-admin.trash');
});

use App\Livewire\Admin\Categorias\ManageCategorias;
use App\Livewire\Admin\Mesas\ManageMesas;
use App\Livewire\Admin\Productos\ManageProductos;

Route::get('/sucursales', ManageSucursales::class)->name('sucursales')->middleware('auth.custom');
Route::get('/configuracion', Settings::class)->name('configuracion')->middleware('auth.custom');
Route::get('/reportes-globales', \App\Livewire\Gerente\GlobalReports::class)->name('gerente.reportes-globales')->middleware(['auth.custom', 'rol:gerente']);

use App\Livewire\Dashboard\AdminDashboard;

use App\Livewire\Admin\Pedidos\ManagePedidos;
use App\Livewire\Admin\Domiciliarios\ManageDomiciliarios;
use App\Livewire\Admin\Zonas\ManageZonas;
use App\Livewire\Admin\Reportes\ManageReportes;
use App\Livewire\Admin\Usuarios\ManageUsuarios;

Route::prefix('admin')->name('admin.')->middleware(['auth.custom', 'rol:administrador|gerente'])->group(function() {
    
    Route::get('/dashboard', AdminDashboard::class)->name('dashboard');
    Route::get('/categorias', ManageCategorias::class)->name('categorias');
    Route::get('/mesas', ManageMesas::class)->name('mesas');
    Route::get('/mesas/{id}/imprimir-qr', [\App\Http\Controllers\Admin\MesaController::class, 'imprimirQr'])->name('mesas.imprimir-qr');
    Route::get('/productos', ManageProductos::class)->name('productos');
    Route::get('/productos/exportar', [\App\Http\Controllers\Admin\ExcelProductosController::class, 'exportar'])->name('productos.exportar');
    Route::get('/productos/plantilla', [\App\Http\Controllers\Admin\ExcelProductosController::class, 'plantilla'])->name('productos.plantilla');
    Route::post('/productos/importar', [\App\Http\Controllers\Admin\ExcelProductosController::class, 'importar'])->name('productos.importar');
    Route::get('/pedidos', ManagePedidos::class)->name('pedidos');
    
    // Zonas de Cobertura routes
    Route::prefix('zonas-cobertura')->name('zonas.')->group(function () {
        Route::get('/', ManageZonas::class)->name('index'); // Redirigir la raiz al Livewire
        Route::post('/', [\App\Http\Controllers\Admin\ZonaCoberturaController::class, 'store'])->name('store');
        Route::put('/{zona}', [\App\Http\Controllers\Admin\ZonaCoberturaController::class, 'update'])->name('update');
        Route::delete('/{zona}', [\App\Http\Controllers\Admin\ZonaCoberturaController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/barrios', [\App\Http\Controllers\Admin\ZonaCoberturaController::class, 'getBarrios'])->name('barrios');
        Route::get('/{id}', [\App\Http\Controllers\Admin\ZonaCoberturaController::class, 'show'])->name('show');
    });

    // Domiciliarios routes
    Route::prefix('domiciliarios')->name('domiciliarios.')->group(function () {
        Route::get('/', ManageDomiciliarios::class)->name('index'); // Redirigir al Livewire
        Route::post('/', [\App\Http\Controllers\Admin\DomiciliarioController::class, 'store'])->name('store');
        Route::put('/{domiciliario}', [\App\Http\Controllers\Admin\DomiciliarioController::class, 'update'])->name('update');
        Route::get('/{domiciliario}', [\App\Http\Controllers\Admin\DomiciliarioController::class, 'show'])->name('show');
    });

    Route::get('/reportes/exportar', function () {
        $component = new \App\Livewire\Admin\Reportes\ManageReportes();
        $format = request('format');
        if ($format === 'pdf') {
            return $component->exportPdf();
        } elseif ($format === 'excel' || $format === 'csv') {
            return $component->exportExcel($format);
        }
        return abort(400);
    })->name('reportes.exportar');

    Route::get('/reportes', ManageReportes::class)->name('reportes');

    Route::prefix('usuarios')->name('usuarios.')->group(function () {
        Route::get('/', ManageUsuarios::class)->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\UsuarioController::class, 'store'])->name('store');
        Route::delete('/{user}', [\App\Http\Controllers\Admin\UsuarioController::class, 'destroy'])->name('destroy');
    });

});

/*
|--------------------------------------------------------------------------
| Mesero
|--------------------------------------------------------------------------
*/
Route::prefix('mesero')->name('mesero.')
    ->middleware(['auth.custom', 'rol:mesero'])
    ->group(function () {
        Route::get('/dashboard',              [\App\Http\Controllers\Mesero\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/mesas',                  [\App\Http\Controllers\Mesero\DashboardController::class, 'mesas'])->name('mesas');
        Route::get('/historial',              [\App\Http\Controllers\Mesero\DashboardController::class, 'historial'])->name('historial');
        Route::post('/pedidos/{id}/confirmar-pago', [\App\Http\Controllers\Mesero\DashboardController::class, 'confirmarPago'])->name('pedidos.confirmar-pago');
        Route::post('/pedidos/{id}/entregar', [\App\Http\Controllers\Mesero\DashboardController::class, 'entregar'])->name('pedidos.entregar');
        Route::post('/pedidos/{id}/cancelar', [\App\Http\Controllers\Mesero\DashboardController::class, 'cancelarPedido'])->name('pedidos.cancelar');
        Route::post('/mesas/{id}/liberar',    [\App\Http\Controllers\Mesero\DashboardController::class, 'liberarMesa'])->name('mesas.liberar');
        Route::post('/sesion/{id}/cerrar',    [\App\Http\Controllers\Mesero\DashboardController::class, 'cerrarSesion'])->name('sesiones.cerrar');

        // POS Mesero
        Route::get('/tomar-pedido',           [\App\Http\Controllers\Mesero\PedidoController::class, 'seleccionarMesa'])->name('tomar-pedido.mesas');
        Route::get('/tomar-pedido/mesa/{mesa}', [\App\Http\Controllers\Mesero\PedidoController::class, 'menuMesa'])->name('tomar-pedido.menu');
        Route::post('/tomar-pedido/mesa/{mesa}/carrito', [\App\Http\Controllers\Mesero\PedidoController::class, 'agregarAlCarrito'])->name('tomar-pedido.carrito.agregar');
        Route::put('/tomar-pedido/mesa/{mesa}/carrito/{id}', [\App\Http\Controllers\Mesero\PedidoController::class, 'actualizarCantidadCarrito'])->name('tomar-pedido.carrito.actualizar');
        Route::delete('/tomar-pedido/mesa/{mesa}/carrito/{id}', [\App\Http\Controllers\Mesero\PedidoController::class, 'eliminarDelCarrito'])->name('tomar-pedido.carrito.eliminar');
        Route::post('/tomar-pedido/mesa/{mesa}/confirmar', [\App\Http\Controllers\Mesero\PedidoController::class, 'confirmarPedido'])->name('tomar-pedido.confirmar');
    });

/*
|--------------------------------------------------------------------------
| Cocina
|--------------------------------------------------------------------------
*/
Route::prefix('cocina')->name('cocina.')
    ->middleware(['auth.custom', 'rol:cocina'])
    ->group(function () {
        Route::get('/dashboard',                     [\App\Http\Controllers\Cocina\DashboardController::class, 'index'])->name('dashboard');
        Route::post('/pedidos/{id}/estado/{estado}', [\App\Http\Controllers\Cocina\DashboardController::class, 'cambiarEstado'])->name('estado');
        Route::get('/pedidos/nuevos',                [\App\Http\Controllers\Cocina\DashboardController::class, 'pedidosNuevos'])->name('pedidos.nuevos');
        Route::post('/pedidos/verificar-estados',    [\App\Http\Controllers\Cocina\DashboardController::class, 'verificarEstados'])->name('pedidos.verificar-estados');
        
        // Disponibilidad rápida para Cocina (RF-100)
        Route::get('/disponibilidad',                                       [\App\Http\Controllers\Cocina\DashboardController::class, 'disponibilidad'])->name('disponibilidad');
        Route::post('/disponibilidad/toggle-producto/{id}',                 [\App\Http\Controllers\Cocina\DashboardController::class, 'toggleProducto'])->name('disponibilidad.toggle-producto');
        Route::post('/disponibilidad/toggle-variante/{varianteId}/{nombre}', [\App\Http\Controllers\Cocina\DashboardController::class, 'toggleVariante'])->name('disponibilidad.toggle-variante');
        Route::post('/disponibilidad/toggle-adicion/{id}',                  [\App\Http\Controllers\Cocina\DashboardController::class, 'toggleAdicion'])->name('disponibilidad.toggle-adicion');
        
        // Recetas de Cocina
        Route::get('/recetas',                                              [\App\Http\Controllers\Cocina\DashboardController::class, 'recetas'])->name('recetas');
        Route::post('/recetas/{id}/guardar',                                [\App\Http\Controllers\Cocina\DashboardController::class, 'guardarReceta'])->name('recetas.guardar');
    });

/*
|--------------------------------------------------------------------------
| Domiciliario
|--------------------------------------------------------------------------
*/
Route::prefix('domiciliario')->name('domiciliario.')->middleware(['auth.custom', 'rol:domiciliario'])->group(function () {
    // Livewire dashboard
    Route::get('/dashboard', \App\Livewire\Domiciliario\Dashboard::class)->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| Cliente / QR
|--------------------------------------------------------------------------
*/
Route::get('/s/{sucursal_slug}/mesa/{codigo}', [\App\Http\Controllers\Cliente\ClienteController::class, 'escanearQR'])->name('cliente.qr');
Route::get('/s/{sucursal_slug}/domicilio', [\App\Http\Controllers\Cliente\ClienteController::class, 'accesoDomicilio'])->name('cliente.domicilio');
Route::post('/s/{sucursal_slug}/domicilio/registro', [\App\Http\Controllers\Cliente\ClienteController::class, 'crearSesionDomicilio'])->name('cliente.domicilio.registro');

Route::prefix('cliente')->name('cliente.')->group(function () {
    Route::post('/sesion/individual', [\App\Http\Controllers\Cliente\ClienteController::class, 'crearSesionIndividual'])->name('sesion.individual');
    Route::get('/sin-sesion',         [\App\Http\Controllers\Cliente\ClienteController::class, 'sinSesion'])->name('sin-sesion');

    Route::middleware(\App\Http\Middleware\ClienteTokenMiddleware::class)->group(function () {
        Route::get('/menu',                             [\App\Http\Controllers\Cliente\ClienteController::class, 'menu'])->name('menu');
        Route::post('/carrito/agregar',                 [\App\Http\Controllers\Cliente\ClienteController::class, 'agregarAlCarrito'])->name('carrito.agregar');
        Route::post('/carrito/actualizar/{id}',         [\App\Http\Controllers\Cliente\ClienteController::class, 'actualizarCantidadCarrito'])->name('carrito.actualizar');
        Route::post('/carrito/eliminar/{id}',           [\App\Http\Controllers\Cliente\ClienteController::class, 'eliminarDelCarrito'])->name('carrito.eliminar');
        Route::post('/pedido/confirmar',                [\App\Http\Controllers\Cliente\ClienteController::class, 'confirmarPedido'])->name('pedido.confirmar');
        Route::get('/pago',                             [\App\Http\Controllers\Cliente\ClienteController::class, 'pago'])->name('pago');
        Route::post('/pago/procesar',                   [\App\Http\Controllers\Cliente\ClienteController::class, 'procesarPago'])->name('pago.procesar');
        Route::get('/confirmacion',                     [\App\Http\Controllers\Cliente\ClienteController::class, 'confirmacion'])->name('confirmacion');
        Route::get('/cancelacion/exitosa',              [\App\Http\Controllers\Cliente\ClienteController::class, 'cancelacionExitosa'])->name('cancelacion.exitosa');
        Route::post('/logout',                          [\App\Http\Controllers\Cliente\ClienteController::class, 'logout'])->name('logout');
        Route::post('/logout/inactividad',              [\App\Http\Controllers\Cliente\ClienteController::class, 'logoutInactividad'])->name('logout.inactividad');
        
        // Ownership routes
        Route::middleware(\App\Http\Middleware\ClienteOwnershipMiddleware::class.':pedido')->group(function () {
            Route::get('/pedido/estado/{pedidoId}',    [\App\Http\Controllers\Cliente\ClienteController::class, 'estadoPedido'])->name('pedido.estado');
            Route::post('/pedido/{pedidoId}/cancelar', [\App\Http\Controllers\Cliente\ClienteController::class, 'cancelarPedido'])->name('pedido.cancelar');
        });

        Route::middleware(\App\Http\Middleware\ClienteOwnershipMiddleware::class.':pago')->group(function () {
            Route::get('/pago/pendiente/{pagoId}',  [\App\Http\Controllers\Cliente\ClienteController::class, 'pagoPendiente'])->name('pago.pendiente');
            Route::get('/pago/estado/{pagoId}',     [\App\Http\Controllers\Cliente\ClienteController::class, 'estadoPago'])->name('pago.estado');
            Route::post('/pago/simular/{pagoId}',   [\App\Http\Controllers\Cliente\ClienteController::class, 'simularConfirmacion'])->name('pago.simular');
        });
    });
});

// Rutas de Verificación de Correo
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (Illuminate\Foundation\Auth\EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/dashboard')->with('success', 'Correo verificado correctamente.');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Illuminate\Http\Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Enlace de verificación enviado.');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');


