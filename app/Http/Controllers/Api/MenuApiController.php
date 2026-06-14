<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class MenuApiController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'correo' => 'required|email',
            'contrasena' => 'required'
        ]);

        $user = User::where('correo', $request->correo)->first();

        if (!$user || !Hash::check($request->contrasena, $user->contrasena)) {
            return response()->json([
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        $token = $user->createToken('mobile-app-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }

    public function menu($sucursal_id)
    {
        // En un entorno multi-tenant, simulamos el TenantScope
        \App\Scopes\TenantScope::setTenantId($sucursal_id);

        $categorias = Categoria::activo()->orderBy('orden')->get();
        $productos = Producto::activoConCategoriaActiva()
            ->where('disponible', true)
            ->with(['variantes', 'adiciones'])
            ->get();

        return response()->json([
            'sucursal_id' => $sucursal_id,
            'categorias' => $categorias,
            'productos' => $productos,
        ]);
    }
}
