<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Http\JsonResponse;

class NotificacionesController extends Controller
{
    /**
     * Retorna las últimas notificaciones del usuario autenticado.
     * Usada por el componente Livewire CampanillaNotificaciones.
     */
    public function index(): JsonResponse
    {
        $notificaciones = Notificacion::where('usuario_id', auth()->id())
            ->latest('creado_en')
            ->take(20)
            ->get();

        return response()->json([
            'notificaciones' => $notificaciones,
            'no_leidas'      => $notificaciones->where('leida', false)->count(),
        ]);
    }

    /**
     * Marca una notificación específica como leída.
     * Seguridad: solo el propietario puede marcar sus notificaciones.
     */
    public function marcarLeida(string $id): JsonResponse
    {
        $afectados = Notificacion::where('id', $id)
            ->where('usuario_id', auth()->id())
            ->update(['leida' => true]);

        return response()->json(['ok' => $afectados > 0]);
    }

    /**
     * Marca todas las notificaciones no leídas del usuario como leídas.
     */
    public function marcarTodasLeidas(): JsonResponse
    {
        Notificacion::where('usuario_id', auth()->id())
            ->where('leida', false)
            ->update(['leida' => true]);

        return response()->json(['ok' => true]);
    }
}
