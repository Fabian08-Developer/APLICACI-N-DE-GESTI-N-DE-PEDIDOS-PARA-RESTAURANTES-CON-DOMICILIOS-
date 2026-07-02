<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PushSubscriptionController extends Controller
{
    /**
     * Guarda o actualiza la suscripción push del usuario autenticado.
     */
    public function store(Request $request)
    {
        $request->validate([
            'endpoint'        => 'required|string|url',
            'public_key'      => 'nullable|string',
            'auth_token'      => 'nullable|string',
            'content_encoding'=> 'nullable|string',
        ]);

        PushSubscription::updateOrCreate(
            ['endpoint' => $request->endpoint],
            [
                'user_id'          => Auth::id(),
                'public_key'       => $request->public_key,
                'auth_token'       => $request->auth_token,
                'content_encoding' => $request->content_encoding ?? 'aesgcm',
            ]
        );

        return response()->json(['status' => 'ok']);
    }

    /**
     * Elimina la suscripción push del usuario (cuando desactiva notificaciones).
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'endpoint' => 'required|string',
        ]);

        PushSubscription::where('user_id', Auth::id())
            ->where('endpoint', $request->endpoint)
            ->delete();

        return response()->json(['status' => 'ok']);
    }
}
