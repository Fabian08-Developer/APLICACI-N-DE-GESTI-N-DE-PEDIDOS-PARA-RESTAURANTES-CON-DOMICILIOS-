<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Dashboard\AdminDashboard;
use App\Livewire\Admin\Categorias\ManageCategorias;
use App\Livewire\Admin\Mesas\ManageMesas;
use App\Livewire\Admin\Productos\ManageProductos;
use App\Livewire\Admin\Pedidos\ManagePedidos;
use App\Livewire\Admin\Domiciliarios\ManageDomiciliarios;
use App\Livewire\Admin\Zonas\ManageZonas;
use App\Livewire\Admin\Reportes\ManageReportes;
use App\Livewire\Admin\Usuarios\ManageUsuarios;
use App\Livewire\Admin\MapaSede;
use App\Livewire\Admin\Reservas\ManageReservas;

Route::prefix('admin')->name('admin.')->middleware(['auth.custom', 'role:administrador|gerente'])->group(function() {
    
    Route::get('/dashboard', AdminDashboard::class)->name('dashboard');
    Route::get('/mapa', MapaSede::class)->name('mapa');
    Route::get('/categorias', ManageCategorias::class)->name('categorias');
    
    Route::get('/mesas', ManageMesas::class)->name('mesas');
    Route::get('/mesas/{id}/imprimir-qr', [\App\Http\Controllers\Admin\MesaController::class, 'imprimirQr'])->name('mesas.imprimir-qr');
    
    Route::get('/reservas/crear', [\App\Http\Controllers\Admin\ReservaController::class, 'crear'])->name('reservas.crear');
    Route::post('/reservas', [\App\Http\Controllers\Admin\ReservaController::class, 'store'])->name('reservas.store');
    Route::get('/reservas', ManageReservas::class)->name('reservas.index');
    
    Route::get('/productos', ManageProductos::class)->name('productos');
    Route::get('/productos/exportar', [\App\Http\Controllers\Admin\ExcelProductosController::class, 'exportar'])->name('productos.exportar');
    Route::get('/productos/plantilla', [\App\Http\Controllers\Admin\ExcelProductosController::class, 'plantilla'])->name('productos.plantilla');
    Route::post('/productos/importar', [\App\Http\Controllers\Admin\ExcelProductosController::class, 'importar'])->name('productos.importar');
    
    Route::get('/pedidos', ManagePedidos::class)->name('pedidos');
    
    Route::prefix('zonas-cobertura')->name('zonas.')->group(function () {
        Route::get('/', ManageZonas::class)->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\ZonaCoberturaController::class, 'store'])->name('store');
        Route::put('/{zona}', [\App\Http\Controllers\Admin\ZonaCoberturaController::class, 'update'])->name('update');
        Route::delete('/{zona}', [\App\Http\Controllers\Admin\ZonaCoberturaController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/barrios', [\App\Http\Controllers\Admin\ZonaCoberturaController::class, 'getBarrios'])->name('barrios');
        Route::get('/{id}', [\App\Http\Controllers\Admin\ZonaCoberturaController::class, 'show'])->name('show');
    });

    Route::prefix('domiciliarios')->name('domiciliarios.')->group(function () {
        Route::get('/', ManageDomiciliarios::class)->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\DomiciliarioController::class, 'store'])->name('store');
        Route::put('/{domiciliario}', [\App\Http\Controllers\Admin\DomiciliarioController::class, 'update'])->name('update');
        Route::get('/{domiciliario}', [\App\Http\Controllers\Admin\DomiciliarioController::class, 'show'])->name('show');
    });

    Route::get('/reportes/exportar', [\App\Http\Controllers\Admin\ReporteExportController::class, 'exportar'])->name('reportes.exportar');
    Route::get('/reportes', ManageReportes::class)->name('reportes');

    Route::prefix('usuarios')->name('usuarios.')->group(function () {
        Route::get('/', ManageUsuarios::class)->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\UsuarioController::class, 'store'])->name('store');
        Route::delete('/{user}', [\App\Http\Controllers\Admin\UsuarioController::class, 'destroy'])->name('destroy');
    });
});
