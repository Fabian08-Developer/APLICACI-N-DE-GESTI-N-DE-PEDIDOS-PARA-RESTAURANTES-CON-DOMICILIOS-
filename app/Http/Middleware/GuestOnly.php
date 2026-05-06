<?php

namespace App\Http\Middleware;

use App\Models\Sesion;
use Closure;
use Illuminate\Http\Request;

/**
 * Si el usuario ya tiene un token válido y visita /login o /register,
 * lo redirigimos a su dashboard.
 *
 * ANTES: usaba Auth::check() (sesión PHP compartida)
 * AHORA: verifica el token del request (inyectado por staff-token.blade.php)
 */
class GuestOnly
{
    public function handle(Request $request, Closure $next)
    {
        // Leer token desde el request (igual que VerificarAutenticacion)
        $token = $request->header('X-Staff-Token')
              ?? $request->query('_st')
              ?? $request->input('_st');

        if ($token) {
            $sesion = Sesion::where('token', $token)
                ->where('activa', true)
                ->where('fecha_expiracion', '>', now())
                ->with('usuario.rol')
                ->first();

            if ($sesion) {
                $rol = $sesion->usuario->rol->nombre;

                $dashboardUrl = match($rol) {
                    'administrador' => route('admin.dashboard'),
                    'mesero'        => route('mesero.dashboard'),
                    'cocina'        => route('cocina.dashboard'),
                    default         => route('login'),
                };

                // Incluir _st para que el dashboard pueda autenticar
                $separator = str_contains($dashboardUrl, '?') ? '&' : '?';
                return redirect()->to($dashboardUrl . $separator . '_st=' . $token);
            }
        }

        return $next($request);
    }
}