<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barrio;
use App\Services\SucursalAssignmentService;
use Illuminate\Http\JsonResponse;

class SucursalAssignmentController extends Controller
{
    public function __construct(
        private readonly SucursalAssignmentService $assignmentService
    ) {}

    /**
     * Dado un barrio_id, resuelve la mejor sede y retorna la tarifa de envío.
     * Consumido por el checkout de domicilio del cliente vía AJAX.
     */
    public function resolver(string $barrioId): JsonResponse
    {
        $resultado = $this->assignmentService->resolver($barrioId);

        if (!$resultado['tiene_cobertura']) {
            return response()->json([
                'tiene_cobertura' => false,
                'sucursal'        => null,
                'costo_envio'     => 0,
                'tiempo_estimado' => 0,
                'mensaje'         => $resultado['mensaje'],
            ]);
        }

        $sucursal = $resultado['sucursal'];

        return response()->json([
            'tiene_cobertura' => true,
            'sucursal'        => [
                'id'        => $sucursal->id,
                'nombre'    => $sucursal->nombre,
                'direccion' => $sucursal->direccion,
                'telefono'  => $sucursal->telefono,
            ],
            'costo_envio'     => $resultado['costo_envio'],
            'tiempo_estimado' => $resultado['tiempo_estimado'],
            'mensaje'         => null,
        ]);
    }

    /**
     * Retorna todos los barrios disponibles (con cobertura en al menos una sede activa).
     * Usado para poblar el select de barrios en el checkout de domicilio.
     */
    public function barrios(string $empresaId): JsonResponse
    {
        $barrios = Barrio::withoutGlobalScopes()->whereHas('tarifas', function ($q) use ($empresaId) {
                $q->where('activo', true)
                  ->whereHas('sucursal', function ($sq) use ($empresaId) {
                      $sq->where('activo', true)
                         ->where('empresa_id', $empresaId);
                  });
            })
            ->where('activo', true)
            ->orderBy('nombre')
            ->select(['id', 'nombre', 'zona_id'])
            ->with('zona:id,nombre')
            ->get();

        return response()->json($barrios);
    }
}
