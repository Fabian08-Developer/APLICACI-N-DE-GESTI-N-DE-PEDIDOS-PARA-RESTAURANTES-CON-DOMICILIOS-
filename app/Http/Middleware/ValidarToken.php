<?php

namespace App\Http\Middleware;

use App\Models\Sesion;
use Closure;
use Illuminate\Http\Request;

class ValidarToken
{
    /**
     * Este middleware se ejecuta ANTES de cada ruta protegida.
     * Su trabajo es revisar si el token enviado es válido.
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. Obtenemos el token del header "Authorization: Bearer TU_TOKEN"
        $token = $request->bearerToken();

        // 2. Si no viene token, rechazamos la petición
        if (!$token) {
            return response()->json([
                'mensaje' => 'No enviaste un token, debes iniciar sesión',
            ], 401);
        }

        // 3. Buscamos ese token en la tabla "sesiones"
        $sesion = Sesion::where('token', $token)
                        ->where('activa', true)
                        ->first();

        // 4. Si no existe o está inactivo, rechazamos
        if (!$sesion) {
            return response()->json([
                'mensaje' => 'Token inválido o sesión cerrada, inicia sesión nuevamente',
            ], 401);
        }

        // 5. Verificamos que el token no haya expirado
        if ($sesion->fecha_expiracion && now()->isAfter($sesion->fecha_expiracion)) {
            $sesion->update(['activa' => false]);

            return response()->json([
                'mensaje' => 'Tu sesión expiró, inicia sesión nuevamente',
            ], 401);
        }

        // 6. Si todo está bien, adjuntamos el usuario al request
        //    Así en los controladores puedes usar $request->usuario
        $request->usuario = $sesion->usuario;

        // 7. Dejamos pasar la petición al controlador
        return $next($request);
    }
}