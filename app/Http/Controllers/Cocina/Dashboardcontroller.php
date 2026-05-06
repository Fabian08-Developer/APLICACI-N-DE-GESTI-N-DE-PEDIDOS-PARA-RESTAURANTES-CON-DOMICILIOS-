<?php

namespace App\Http\Controllers\Cocina;

use App\Enums\EstadoPedido;
use App\Http\Controllers\Controller;
use App\Models\HistorialEstadoPedido;
use App\Models\Pedido;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Máquina de estados — transiciones permitidas
    |--------------------------------------------------------------------------
    | Solo estas transiciones son válidas.
    | Evita saltos incorrectos en el flujo de cocina.
    */
    private const TRANSICIONES = [
        EstadoPedido::CREADO->value         => EstadoPedido::EN_COCINA->value,
        EstadoPedido::EN_COCINA->value      => EstadoPedido::EN_PREPARACION->value,
        EstadoPedido::EN_PREPARACION->value => EstadoPedido::LISTO->value,
        EstadoPedido::LISTO->value          => EstadoPedido::ENTREGADO->value,
    ];

    /*
    |--------------------------------------------------------------------------
    | Dashboard principal
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $pedidosNuevos = Pedido::with(['detalles.producto', 'sesionMesa.mesa'])
            ->where('estado', EstadoPedido::CREADO->value)
            ->oldest()
            ->get();

        $pedidosEnCocina = Pedido::with(['detalles.producto', 'sesionMesa.mesa'])
            ->where('estado', EstadoPedido::EN_COCINA->value)
            ->oldest()
            ->get();

        $pedidosEnPreparacion = Pedido::with(['detalles.producto', 'sesionMesa.mesa'])
            ->where('estado', EstadoPedido::EN_PREPARACION->value)
            ->oldest()
            ->get();

        $pedidosListos = Pedido::with(['detalles.producto', 'sesionMesa.mesa'])
            ->where('estado', EstadoPedido::LISTO->value)
            ->oldest()
            ->get();

        $pedidosEntregadosHoy = Pedido::where('estado', EstadoPedido::ENTREGADO->value)
            ->whereDate('updated_at', today())
            ->count();

        return view('cocina.dashboard', compact(
            'pedidosNuevos',
            'pedidosEnCocina',
            'pedidosEnPreparacion',
            'pedidosListos',
            'pedidosEntregadosHoy'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Cambiar estado (AJAX)
    |--------------------------------------------------------------------------
    */
    public function cambiarEstado(Request $request, int $id, string $estado): JsonResponse
    {
        $pedido = Pedido::findOrFail($id);

        // Validar que el estado existe
        if (! in_array($estado, array_column(EstadoPedido::cases(), 'value'))) {
            return response()->json([
                'ok'      => false,
                'mensaje' => "Estado '{$estado}' no existe.",
            ], 422);
        }

        // Validar transición permitida
        $estadoActual = $pedido->estado;
        $transicionPermitida = self::TRANSICIONES[$estadoActual] ?? null;

        if ($transicionPermitida !== $estado) {
            Log::warning('Cocina: transición inválida', [
                'pedido_id' => $pedido->id,
                'desde'     => $estadoActual,
                'hacia'     => $estado,
                'permitida' => $transicionPermitida,
            ]);

            return response()->json([
                'ok'      => false,
                'mensaje' => "No puedes cambiar de {$estadoActual} a {$estado}.",
            ], 422);
        }

        // Actualizar estado
        $pedido->update(['estado' => $estado]);

        // Guardar historial
        HistorialEstadoPedido::create([
            'pedido_id'  => $pedido->id,
            'estado'     => $estado,
            'usuario_id' => auth()->id(),
            'fecha'      => now(),
        ]);

        Log::info('Cocina: estado actualizado', [
            'pedido_id' => $pedido->id,
            'desde'     => $estadoActual,
            'hacia'     => $estado,
        ]);

        return response()->json([
            'ok'        => true,
            'mensaje'   => "Pedido #{$pedido->id} → {$estado}",
            'pedido_id' => $pedido->id,
            'estado'    => $estado,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Polling de pedidos nuevos
    |--------------------------------------------------------------------------
    */
    public function pedidosNuevos(Request $request): JsonResponse
    {
        $desde = $request->query('desde');

        $query = Pedido::with(['detalles.producto', 'sesionMesa.mesa'])
            ->where('estado', EstadoPedido::CREADO->value);

        if ($desde) {
            $query->where('created_at', '>', $desde);
        }

        $pedidos = $query->oldest()->get();

        return response()->json([
            'ok'      => true,
            'total'   => $pedidos->count(),
            'pedidos' => $pedidos->map(fn($p) => [
                'id'        => $p->id,
                'mesa'      => $p->sesionMesa?->mesa?->numero ?? '—',
                'minutos'   => $p->created_at->diffInMinutes(now()),
                'created_at'=> $p->created_at->toIso8601String(),
                'detalles'  => $p->detalles->map(fn($d) => [
                    'nombre'   => $d->producto?->nombre ?? '—',
                    'cantidad' => $d->cantidad,
                ]),
            ]),
        ]);
    }
}