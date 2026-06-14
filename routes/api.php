<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MenuApiController;
use App\Http\Controllers\Api\SucursalAssignmentController;

// Rutas API para la aplicación móvil u otras integraciones externas
Route::prefix('v1')->group(function () {
    
    // Auth endpoints (ejemplo con Sanctum)
    Route::post('/login', [MenuApiController::class, 'login']);

    // Endpoints públicos
    Route::get('/menu/{sucursal_id}', [MenuApiController::class, 'menu']);

    // ─── Asignación de sede para checkout de domicilio ─────────────────
    // Retorna la mejor sede para un barrio + costo de envío dinámico
    Route::get('/barrio/{barrioId}/sede', [SucursalAssignmentController::class, 'resolver'])
         ->name('api.barrio.sede');

    // Retorna los barrios con cobertura activa de una empresa
    Route::get('/empresa/{empresaId}/barrios', [SucursalAssignmentController::class, 'barrios'])
         ->name('api.empresa.barrios');
    // ───────────────────────────────────────────────────────────────────

    // Endpoints protegidos con Sanctum
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
        
        // Aquí irían endpoints para crear pedidos desde la app, ver historial, etc.
        // Route::post('/pedidos', [MenuApiController::class, 'crearPedido']);
    });
});

