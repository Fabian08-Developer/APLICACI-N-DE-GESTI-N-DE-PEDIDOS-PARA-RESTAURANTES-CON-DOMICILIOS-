<?php

use Illuminate\Support\Facades\Route;

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
    $token = $request->cookie('staff_token')
          ?? $request->header('X-Staff-Token')
          ?? session('staff_token');

    if ($token) {
        \App\Models\Sesion::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->where('token', $token)
            ->update(['activa' => false]);
            
        $cookie = \Illuminate\Support\Facades\Cookie::forget('staff_token');
        session()->forget('staff_token');

        return redirect()->route('login', ['_clear' => 1])
            ->with('exito', 'Sesión cerrada correctamente.')
            ->withCookie($cookie);
    }

    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

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

Route::get('/sucursales', ManageSucursales::class)->name('sucursales')->middleware('auth.custom');
Route::get('/configuracion', Settings::class)->name('configuracion')->middleware('auth.custom');
Route::get('/reportes-globales', \App\Livewire\Gerente\GlobalReports::class)->name('gerente.reportes-globales')->middleware(['auth.custom', 'role:gerente']);
Route::get('/mapa-sedes', \App\Livewire\Gerente\MapaSedes::class)->name('gerente.mapa-sedes')->middleware(['auth.custom', 'role:gerente']);
Route::get('/mi-pagina', \App\Livewire\Admin\MiPagina::class)->name('gerente.mi-pagina')->middleware(['auth.custom', 'role:gerente']);

// Domiciliario route
Route::prefix('domiciliario')->name('domiciliario.')->middleware(['auth.custom', 'role:domiciliario'])->group(function () {
    Route::get('/dashboard', \App\Livewire\Domiciliario\Dashboard::class)->name('dashboard');
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

// ─── Notificaciones (API JSON para campanilla) ─────────────────────────────
Route::prefix('api/notificaciones')
    ->middleware('auth.custom')
    ->name('notificaciones.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\NotificacionesController::class, 'index'])
            ->name('index');
        Route::post('/{id}/leida', [\App\Http\Controllers\NotificacionesController::class, 'marcarLeida'])
            ->name('leida');
        Route::post('/todas-leidas', [\App\Http\Controllers\NotificacionesController::class, 'marcarTodasLeidas'])
            ->name('todas-leidas');
    });

// ─── Broadcasting auth (requerido por Reverb para canales privados) ─────────
require __DIR__ . '/channels.php';

// Modularized routes
require __DIR__ . '/admin.php';
require __DIR__ . '/mesero.php';
require __DIR__ . '/cocina.php';
require __DIR__ . '/cliente.php';
require __DIR__ . '/superadmin.php';
require __DIR__ . '/reservas.php';

// ─── Web Push Notifications ──────────────────────────────────────────────────
// Estas rutas guardan/eliminan la suscripción push del navegador del usuario.
Route::middleware('auth')->group(function () {
    Route::post('/push/subscribe',   [\App\Http\Controllers\PushSubscriptionController::class, 'store'])->name('push.subscribe');
    Route::post('/push/unsubscribe', [\App\Http\Controllers\PushSubscriptionController::class, 'destroy'])->name('push.unsubscribe');
});

// ─── Tienda online pública por empresa (DEBE IR AL FINAL) ────────────────
// Captura /{empresa_slug}; se declara después de todas las rutas del sistema
// para no interferir con /login, /dashboard, etc.
Route::get('/{empresa_slug}', \App\Http\Controllers\Cliente\EmpresaHomeController::class)
    ->name('empresa.home');
