<?php

use Illuminate\Support\Facades\Route;

Route::prefix('cocina')->name('cocina.')
    ->middleware(['auth.custom', 'role:cocina'])
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
