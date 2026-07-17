<?php

use Illuminate\Support\Facades\Route;

Route::prefix('mesero')->name('mesero.')
    ->middleware(['auth.custom', 'role:mesero'])
    ->group(function () {
        Route::get('/dashboard',              [\App\Http\Controllers\Mesero\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/mesas',                  [\App\Http\Controllers\Mesero\DashboardController::class, 'mesas'])->name('mesas');
        Route::get('/historial',              [\App\Http\Controllers\Mesero\DashboardController::class, 'historial'])->name('historial');
        Route::post('/pedidos/{id}/confirmar-pago', [\App\Http\Controllers\Mesero\DashboardController::class, 'confirmarPago'])->name('pedidos.confirmar-pago');
        Route::post('/pedidos/{id}/registrar-cobro', [\App\Http\Controllers\Mesero\DashboardController::class, 'registrarCobro'])->name('pedidos.registrar-cobro');
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

        // ─── Reservas (NUEVO) ───────────────────────────────────────────
        Route::prefix('reservas')->name('reservas.')->group(function () {
            Route::get('/',                    [\App\Http\Controllers\Mesero\ReservaController::class, 'index'])->name('index');
            Route::get('/crear',               [\App\Http\Controllers\Mesero\ReservaController::class, 'crear'])->name('crear');
            Route::post('/',                   [\App\Http\Controllers\Mesero\ReservaController::class, 'store'])->name('store');
            Route::post('/{id}/confirmar',     [\App\Http\Controllers\Mesero\ReservaController::class, 'confirmar'])->name('confirmar');
            Route::post('/{id}/check-in',      [\App\Http\Controllers\Mesero\ReservaController::class, 'checkIn'])->name('check-in');
            Route::post('/{id}/cancelar',      [\App\Http\Controllers\Mesero\ReservaController::class, 'cancelar'])->name('cancelar');
            Route::post('/{id}/aprobar-deposito', [\App\Http\Controllers\Mesero\ReservaController::class, 'aprobarDeposito'])->name('aprobar-deposito');
        });
    });
