<?php

use Illuminate\Support\Facades\Route;

Route::get('/{empresa_slug}/{sucursal_slug}/mesa/{codigo}', [\App\Http\Controllers\Cliente\ClienteController::class, 'escanearQR'])->name('cliente.qr');
Route::get('/{empresa_slug}/{sucursal_slug}/domicilio', [\App\Http\Controllers\Cliente\ClienteController::class, 'accesoDomicilio'])->name('cliente.domicilio');
Route::get('/{empresa_slug}/{sucursal_slug}/domicilio/verificar-barrio', [\App\Http\Controllers\Cliente\ClienteController::class, 'verificarBarrioDomicilio'])->name('cliente.domicilio.verificar-barrio');
Route::post('/{empresa_slug}/{sucursal_slug}/domicilio/registro', [\App\Http\Controllers\Cliente\ClienteController::class, 'crearSesionDomicilio'])->name('cliente.domicilio.registro');

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
        
        Route::middleware(\App\Http\Middleware\ClienteOwnershipMiddleware::class.':pedido')->group(function () {
            Route::get('/pedido/estado/{pedidoId}',    [\App\Http\Controllers\Cliente\ClienteController::class, 'estadoPedido'])->name('pedido.estado');
            Route::post('/pedido/{pedidoId}/cancelar', [\App\Http\Controllers\Cliente\ClienteController::class, 'cancelarPedido'])->name('pedido.cancelar');
            Route::post('/pedido/{pedidoId}/calificar', [\App\Http\Controllers\Cliente\ClienteController::class, 'calificarDomiciliario'])->name('pedido.calificar');
        });

        Route::middleware(\App\Http\Middleware\ClienteOwnershipMiddleware::class.':pago')->group(function () {
            Route::get('/pago/pendiente/{pagoId}',  [\App\Http\Controllers\Cliente\ClienteController::class, 'pagoPendiente'])->name('pago.pendiente');
            Route::get('/pago/estado/{pagoId}',     [\App\Http\Controllers\Cliente\ClienteController::class, 'estadoPago'])->name('pago.estado');
            Route::post('/pago/simular/{pagoId}',   [\App\Http\Controllers\Cliente\ClienteController::class, 'simularConfirmacion'])->name('pago.simular');
        });
    });
});
