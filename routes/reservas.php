<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cliente\ReservaController;

/*
|--------------------------------------------------------------------------
| Rutas Públicas de Reservación de Mesas
|--------------------------------------------------------------------------
| No requieren autenticación de staff.
| Accesibles desde la web pública del restaurante.
*/

// Formulario y creación de reserva
Route::get('/s/{slug}/reservar',               [ReservaController::class, 'formulario'])->name('cliente.reservas.formulario');
Route::get('/s/{slug}/reservar/slots',         [ReservaController::class, 'slots'])->name('cliente.reservas.slots');
Route::post('/s/{slug}/reservar',              [ReservaController::class, 'crear'])->name('cliente.reservas.crear');

// Pago del depósito
Route::get('/s/{slug}/reservar/{codigo}/deposito',  [ReservaController::class, 'deposito'])->name('cliente.reservas.deposito');
Route::post('/s/{slug}/reservar/{codigo}/deposito', [ReservaController::class, 'procesarDeposito'])->name('cliente.reservas.deposito.procesar');

// Consulta y cancelación de reserva (acceso por código, sin slug)
Route::get('/reserva/{codigo}',                [ReservaController::class, 'confirmada'])->name('cliente.reservas.confirmada');
Route::get('/reserva/{codigo}/pdf',            [ReservaController::class, 'descargarPdf'])->name('cliente.reservas.pdf');
Route::get('/reserva/{codigo}/cancelar',       [ReservaController::class, 'cancelarFormulario'])->name('cliente.reservas.cancelar');
Route::post('/reserva/{codigo}/cancelar',      [ReservaController::class, 'cancelar'])->name('cliente.reservas.cancelar.procesar');
