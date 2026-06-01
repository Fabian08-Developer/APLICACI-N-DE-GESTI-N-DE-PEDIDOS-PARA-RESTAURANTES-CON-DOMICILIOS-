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
        EstadoPedido::CREADO->value         => EstadoPedido::EN_PREPARACION->value,
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

        $pedidosEnPreparacion = Pedido::with(['detalles.producto', 'sesionMesa.mesa'])
            ->where('estado', EstadoPedido::EN_PREPARACION->value)
            ->oldest()
            ->get();

        $pedidosListos = Pedido::with(['detalles.producto', 'sesionMesa.mesa'])
            ->where('estado', EstadoPedido::LISTO->value)
            ->oldest()
            ->get();

        $pedidosEntregadosHoy = Pedido::where('estado', EstadoPedido::ENTREGADO->value)
            ->whereDate('actualizado_en', today())
            ->count();

        return view('cocina.dashboard', compact(
            'pedidosNuevos',
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
    public function cambiarEstado(Request $request, string $id, string $estado): JsonResponse
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

        // Actualizar estado y tiempos de preparación (RF-152)
        $updateData = ['estado' => $estado];
        if ($estado === EstadoPedido::EN_PREPARACION->value && !$pedido->en_cocina_en) {
            $updateData['en_cocina_en'] = now();
        } elseif ($estado === EstadoPedido::LISTO->value && !$pedido->listo_en) {
            $updateData['listo_en'] = now();
        }
        $pedido->update($updateData);

        // Guardar historial
        HistorialEstadoPedido::create([
            'pedido_id'   => $pedido->id,
            'sucursal_id' => $pedido->sucursal_id,
            'estado'      => $estado,
            'usuario_id'  => auth()->id(),
            'cambiado_en' => now(),
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
    | Polling de pedidos nuevos (RF-147, RF-148)
    |--------------------------------------------------------------------------
    */
    public function pedidosNuevos(Request $request): JsonResponse
    {
        $desde = $request->query('desde');

        $query = Pedido::with(['detalles.producto', 'sesionMesa.mesa'])
            ->where('estado', EstadoPedido::CREADO->value);

        if ($desde) {
            $query->where('creado_en', '>', $desde);
        }

        $pedidos = $query->oldest()->get();

        return response()->json([
            'ok'      => true,
            'total'   => $pedidos->count(),
            'pedidos' => $pedidos->map(fn($p) => [
                'id'        => $p->id,
                'mesa'      => $p->sesionMesa?->mesa?->numero ?? '—',
                'tipo'      => $p->tipo,
                'minutos'   => ($p->creado_en ?? $p->actualizado_en ?? now())->diffInMinutes(now()),
                'created_at'=> ($p->creado_en ?? $p->actualizado_en ?? now())->toIso8601String(),
                'detalles'  => $p->detalles->map(fn($d) => [
                    'nombre'             => $d->producto?->nombre ?? '—',
                    'cantidad'           => $d->cantidad,
                    'variantes_elegidas' => $d->variantes_elegidas,
                    'adiciones_elegidas' => $d->adiciones_elegidas,
                    'notas'              => $d->notas,
                ]),
            ]),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Sincronización de estados para comandas existentes (RF-151)
    |--------------------------------------------------------------------------
    */
    public function verificarEstados(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json([
                'ok'      => true,
                'estados' => new \stdClass(),
            ]);
        }

        $pedidos = Pedido::whereIn('id', $ids)->get(['id', 'estado']);
        $estados = $pedidos->pluck('estado', 'id');

        return response()->json([
            'ok'      => true,
            'estados' => $estados,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Disponibilidad rápida de cocina (RF-100)
    |--------------------------------------------------------------------------
    */
    public function disponibilidad()
    {
        $sucursal_id = auth()->user()->sucursal_id;

        $productos = \App\Models\Producto::with('variantes')
            ->where('sucursal_id', $sucursal_id)
            ->orderBy('nombre')
            ->get();

        $adiciones = \App\Models\AdicionCatalogo::where('sucursal_id', $sucursal_id)
            ->orderBy('nombre')
            ->get();

        return view('cocina.disponibilidad', compact('productos', 'adiciones'));
    }

    public function toggleProducto(Request $request, string $id)
    {
        $producto = \App\Models\Producto::findOrFail($id);
        if ($producto->sucursal_id !== auth()->user()->sucursal_id) {
            abort(403);
        }

        $tiempo = $request->input('tiempo'); // en minutos, 'resto_dia' o '3s'
        
        if ($tiempo && $producto->disponible) {
            // Solo pausar si estaba disponible y se envía un tiempo
            if ($tiempo === 'resto_dia') {
                $segundos = now()->diffInSeconds(now()->endOfDay());
            } elseif ($tiempo === '3s') {
                $segundos = 3;
            } else {
                $segundos = (int)$tiempo * 60; // minutos a segundos
            }
            $expiresAt = now()->addSeconds($segundos);
            \Illuminate\Support\Facades\Cache::put("producto_{$producto->id}_pausado", $expiresAt->timestamp, $expiresAt);
            // Aseguramos que el estado en BD quede true para que al expirar vuelva a estar disponible
            $producto->update(['disponible' => true]);
        } else {
            // Toggle manual normal, quitamos cualquier pausa temporal si existe
            \Illuminate\Support\Facades\Cache::forget("producto_{$producto->id}_pausado");
            // Usar el atributo raw de la base de datos por si estaba pausado
            $estadoDb = $producto->getRawOriginal('disponible');
            $producto->update(['disponible' => !$estadoDb]);
        }

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'disponible' => $producto->fresh()->disponible,
                'expira' => \Illuminate\Support\Facades\Cache::get("producto_{$producto->id}_pausado")
            ]);
        }

        return back()->with('exito', "Estado de disponibilidad del producto '{$producto->nombre}' actualizado.");
    }

    public function toggleVariante(Request $request, string $varianteId, string $nombre)
    {
        $variante = \App\Models\VarianteProducto::findOrFail($varianteId);
        if ($variante->producto->sucursal_id !== auth()->user()->sucursal_id) {
            abort(403);
        }

        $opciones = $variante->opciones;
        foreach ($opciones as $key => $opcion) {
            if ($opcion['nombre'] === $nombre) {
                $opciones[$key]['disponible'] = !($opcion['disponible'] ?? true);
                break;
            }
        }

        $variante->update(['opciones' => $opciones]);

        if ($request->ajax()) {
            return response()->json([
                'ok' => true,
                'opciones' => $opciones
            ]);
        }

        return back()->with('exito', "Disponibilidad de la opción '{$nombre}' actualizada.");
    }

    public function toggleAdicion(Request $request, string $id)
    {
        $adicion = \App\Models\AdicionCatalogo::findOrFail($id);
        if ($adicion->sucursal_id !== auth()->user()->sucursal_id) {
            abort(403);
        }

        $tiempo = $request->input('tiempo');
        
        if ($tiempo && $adicion->disponible) {
            if ($tiempo === 'resto_dia') {
                $segundos = now()->diffInSeconds(now()->endOfDay());
            } elseif ($tiempo === '3s') {
                $segundos = 3;
            } else {
                $segundos = (int)$tiempo * 60;
            }
            $expiresAt = now()->addSeconds($segundos);
            \Illuminate\Support\Facades\Cache::put("adicion_{$adicion->id}_pausada", $expiresAt->timestamp, $expiresAt);
            $adicion->update(['disponible' => true]);
        } else {
            \Illuminate\Support\Facades\Cache::forget("adicion_{$adicion->id}_pausada");
            $estadoDb = $adicion->getRawOriginal('disponible');
            $adicion->update(['disponible' => !$estadoDb]);
        }

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'disponible' => $adicion->fresh()->disponible,
                'expira' => \Illuminate\Support\Facades\Cache::get("adicion_{$adicion->id}_pausada")
            ]);
        }

        return back()->with('exito', "Disponibilidad de la adición '{$adicion->nombre}' actualizada.");
    }
}
