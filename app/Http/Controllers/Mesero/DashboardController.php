<?php

namespace App\Http\Controllers\Mesero;

use App\Enums\EstadoPago;
use App\Enums\EstadoPedido;
use App\Http\Controllers\Controller;
use App\Traits\OrderEmailNotifications;
use App\Models\HistorialEstadoPedido;
use App\Models\Mesa;
use App\Models\Pago;
use App\Models\Pedido;
use App\Models\SesionCliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    use OrderEmailNotifications;

    // =========================================================================
    // Dashboard principal — pedidos activos
    // Ruta: GET /mesero/dashboard
    // =========================================================================

    public function index()
    {
        $usuarioId = auth()->id();

        $pedidosActivos = Pedido::with([
            'sesionMesa.mesa',
            'detalles.producto',
            'mesero',
        ])
            ->where('mesero_id', $usuarioId)
            ->where(function ($query) {
                $query->whereIn('estado', [
                    EstadoPedido::CREADO->value,
                    EstadoPedido::EN_PREPARACION->value,
                    EstadoPedido::LISTO->value,
                ])
                ->orWhere(function ($q) {
                    $q->where('estado', EstadoPedido::PENDIENTE_PAGO->value)
                      ->where('metodo_pago', 'Efectivo');
                })
                ->orWhere(function ($q) {
                    $q->where('estado', EstadoPedido::ENTREGADO->value)
                      ->where('estado_pago', EstadoPago::PENDIENTE->value)
                      ->whereDate('creado_en', today());
                });
            })
            ->latest()
            ->get();

        $pedidosHoy = Pedido::with(['sesionMesa.mesa'])
            ->where('mesero_id', $usuarioId)
            ->whereDate('creado_en', today())
            ->latest()
            ->get();

        $mesasDisponibles = Mesa::where('estado', Mesa::ESTADO_DISPONIBLE)
            ->orderBy('numero')
            ->get();

        return view('mesero.dashboard', compact(
            'pedidosActivos',
            'pedidosHoy',
            'mesasDisponibles',
        ));
    }

    // =========================================================================
    // Confirmar pago en efectivo por el mesero
    // Ruta: POST /mesero/pedidos/{id}/confirmar-pago
    // =========================================================================
    public function confirmarPago(Request $request, string $id)
    {
        $usuarioId = auth()->id();

        $pedido = Pedido::where('id', $id)
            ->where('mesero_id', $usuarioId)
            ->where('estado', EstadoPedido::PENDIENTE_PAGO->value)
            ->where('metodo_pago', 'Efectivo')
            ->firstOrFail();

        try {
            DB::transaction(function () use ($pedido, $usuarioId) {
                $pedido->update([
                    'estado'      => EstadoPedido::CREADO->value,
                    'estado_pago' => EstadoPago::COMPLETADO->value,
                    'pagado_en'   => now(),
                ]);

                HistorialEstadoPedido::create([
                    'pedido_id'   => $pedido->id,
                    'sucursal_id' => $pedido->sucursal_id,
                    'estado'      => EstadoPedido::CREADO->value,
                    'usuario_id'  => $usuarioId,
                    'cambiado_en' => now(),
                ]);

                // Actualizar el Pago asociado a COMPLETADO
                $pedido->pagos()
                    ->where('metodo', 'Efectivo')
                    ->where('estado', EstadoPago::PENDIENTE->value)
                    ->update([
                        'estado'         => EstadoPago::COMPLETADO->value,
                        'actualizado_en' => now(),
                    ]);
            });

            // Enviar email de confirmación (si hay correo registrado)
            $pagoCompletado = $pedido->pagos()->where('estado', EstadoPago::COMPLETADO->value)->first();
            if ($pagoCompletado) {
                $this->enviarEmailPago($pagoCompletado, EstadoPago::COMPLETADO->value);
            }

            Log::info('Pago en efectivo confirmado por mesero', [
                'pedido_id' => $pedido->id,
                'mesero_id' => $usuarioId,
            ]);

            return response()->json([
                'ok'      => true,
                'mensaje' => "Pago del pedido #{$pedido->id} confirmado y enviado a Cocina.",
            ]);
        } catch (\Throwable $e) {
            Log::error('Mesero: error al confirmar pago de pedido', [
                'pedido_id' => $pedido->id,
                'mesero_id' => $usuarioId,
                'error'     => $e->getMessage(),
            ]);

            return response()->json(['ok' => false, 'mensaje' => 'Error al confirmar pago. Intenta de nuevo.'], 500);
        }
    }

    // =========================================================================
    // Registrar cobro de un pedido (manual o en curso/entregado)
    // Ruta: POST /mesero/pedidos/{id}/registrar-cobro
    // =========================================================================
    public function registrarCobro(Request $request, string $id)
    {
        $usuarioId = auth()->id();

        $pedido = Pedido::where('id', $id)
            ->where('mesero_id', $usuarioId)
            ->where('estado_pago', EstadoPago::PENDIENTE->value)
            ->firstOrFail();

        $metodo = $request->input('metodo_pago', $pedido->metodo_pago ?? 'Efectivo');

        try {
            DB::transaction(function () use ($pedido, $metodo, $usuarioId) {
                $pedido->update([
                    'estado_pago' => EstadoPago::COMPLETADO->value,
                    'metodo_pago' => $metodo,
                    'pagado_en'   => now(),
                ]);

                $pagoPendiente = $pedido->pagos()
                    ->where('estado', EstadoPago::PENDIENTE->value)
                    ->first();

                if ($pagoPendiente) {
                    $pagoPendiente->update([
                        'estado'         => EstadoPago::COMPLETADO->value,
                        'metodo'         => $metodo,
                        'actualizado_en' => now(),
                    ]);
                } else {
                    $pedido->pagos()->create([
                        'sucursal_id' => $pedido->sucursal_id,
                        'monto'       => $pedido->total,
                        'metodo'      => $metodo,
                        'estado'      => EstadoPago::COMPLETADO->value,
                        'referencia'  => 'COBRO-MESERO-' . time(),
                        'intentos'    => 0,
                    ]);
                }
            });

            $pagoCompletado = $pedido->pagos()->where('estado', EstadoPago::COMPLETADO->value)->first();
            if ($pagoCompletado) {
                $this->enviarEmailPago($pagoCompletado, EstadoPago::COMPLETADO->value);
            }

            Log::info('Cobro de pedido registrado por mesero', [
                'pedido_id'   => $pedido->id,
                'mesero_id'   => $usuarioId,
                'metodo_pago' => $metodo,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'ok'      => true,
                    'mensaje' => "Cobro de \${$pedido->total} ({$metodo}) registrado correctamente.",
                ]);
            }

            return redirect()->back()->with('exito', "Cobro de \${$pedido->total} ({$metodo}) registrado correctamente.");
        } catch (\Throwable $e) {
            Log::error('Mesero: error al registrar cobro de pedido', [
                'pedido_id' => $pedido->id,
                'mesero_id' => $usuarioId,
                'error'     => $e->getMessage(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'mensaje' => 'Error al registrar cobro. Intenta de nuevo.'], 500);
            }

            return redirect()->back()->with('error', 'Error al registrar el cobro.');
        }
    }

    // =========================================================================
    // Gestión de mesas asignadas al mesero
    // Ruta: GET /mesero/mesas
    // =========================================================================

    /*
    |--------------------------------------------------------------------------
    | Muestra las mesas que tienen pedidos activos asignados al mesero.
    |
    | Estrategia: se parte desde Pedido (cuyas relaciones sesionMesa→mesa
    | ya funcionan en index()), se agrupa por mesa_id y se obtiene la Mesa
    | directamente — sin necesitar Mesa::sesiones() que no existe.
    |--------------------------------------------------------------------------
    */
    public function mesas()
    {
        $usuarioId = auth()->id();

        // 1. Obtener todas las sesiones ACTIVAS donde ESTE mesero haya atendido pedidos
        $sesiones = \App\Models\SesionCliente::with([
            'mesa',
            'pedidos' => function ($q) use ($usuarioId) {
                // Cargamos TODOS los pedidos de este mesero en la sesión actual
                // (incluyendo los Entregados, para que tenga contexto de la mesa)
                $q->where('mesero_id', $usuarioId)->with('detalles.producto');
            }
        ])
        ->where('activo', true)
        ->whereHas('pedidos', function($q) use ($usuarioId) {
            $q->where('mesero_id', $usuarioId);
        })
        ->get();

        // 2. Agrupar por mesa para la vista correctamente (soporta múltiples sesiones por mesa)
        $mesasConPedidos = $sesiones->groupBy('mesa_id')->map(function ($sesionesMesa) {
            $mesa = $sesionesMesa->first()->mesa;
            // Guardamos las sesiones dentro de la mesa
            $mesa->sesionesActivasMesero = $sesionesMesa;
            return $mesa;
        })->sortBy('numero')->values();

        // 3. Resumen para el encabezado (solo contamos los activos aquí)
        $pedidosActivos = $sesiones->flatMap->pedidos->filter(function($p) {
            return in_array($p->estado, [
                EstadoPedido::CREADO->value,
                EstadoPedido::EN_PREPARACION->value,
                EstadoPedido::LISTO->value,
            ]);
        });

        $resumenMesas = [
            'total_mesas'     => $mesasConPedidos->count(),
            'pedidos_activos' => $pedidosActivos->count(),
            'listos_entregar' => $pedidosActivos->where('estado', EstadoPedido::LISTO->value)->count(),
        ];

        return view('mesero.mesas', compact('mesasConPedidos', 'resumenMesas'));
    }

    // =========================================================================
    // Historial de pedidos con filtros
    // Ruta: GET /mesero/historial
    // =========================================================================

    /*
    |--------------------------------------------------------------------------
    | Filtros disponibles (todos opcionales):
    |   desde      → fecha inicio  (Y-m-d)
    |   hasta      → fecha fin     (Y-m-d)
    |   estado     → valor del Enum EstadoPedido
    |   mesa       → número de mesa
    |--------------------------------------------------------------------------
    */
    public function historial(Request $request)
    {
        $usuarioId = auth()->id();

        $query = Pedido::with(['sesionMesa.mesa', 'detalles'])
            ->where('mesero_id', $usuarioId);

        // ── Filtro por rango de fechas ─────────────────────────────────────
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');

        if ($desde) {
            $query->whereDate('creado_en', '>=', $desde);
        } else {
            // Por defecto: últimos 30 días
            $query->whereDate('creado_en', '>=', now()->subDays(30)->toDateString());
        }

        if ($hasta) {
            $query->whereDate('creado_en', '<=', $hasta);
        }

        // ── Filtro por estado ──────────────────────────────────────────────
        $estadoFiltro  = $request->query('estado');
        $estadosValidos = array_column(EstadoPedido::cases(), 'value');

        if ($estadoFiltro && in_array($estadoFiltro, $estadosValidos)) {
            $query->where('estado', $estadoFiltro);
        }

        // ── Filtro por número de mesa ──────────────────────────────────────
        $mesaFiltro = $request->query('mesa');
        if ($mesaFiltro) {
            $query->whereHas('sesionMesa.mesa', function ($q) use ($mesaFiltro) {
                $q->where('numero', $mesaFiltro);
            });
        }

        $pedidos = $query->latest()->paginate(20)->withQueryString();

        // ── Resumen del período filtrado ───────────────────────────────────
        // Clonar la query base (sin paginación) para los conteos
        $queryResumen = Pedido::where('mesero_id', $usuarioId);

        if ($desde) {
            $queryResumen->whereDate('creado_en', '>=', $desde);
        } else {
            $queryResumen->whereDate('creado_en', '>=', now()->subDays(30)->toDateString());
        }
        if ($hasta) {
            $queryResumen->whereDate('creado_en', '<=', $hasta);
        }
        if ($estadoFiltro && in_array($estadoFiltro, $estadosValidos)) {
            $queryResumen->where('estado', $estadoFiltro);
        }
        if ($mesaFiltro) {
            $queryResumen->whereHas('sesionMesa.mesa', fn($q) => $q->where('numero', $mesaFiltro));
        }

        $resumen = [
            'total_pedidos' => $pedidos->total(),
            'total_vendido' => (clone $queryResumen)->sum('total'),
            'entregados'    => (clone $queryResumen)->where('estado', EstadoPedido::ENTREGADO->value)->count(),
            'cancelados'    => (clone $queryResumen)->where('estado', EstadoPedido::CANCELADO->value)->count(),
        ];

        // Lista de mesas (para el select del filtro)
        $mesas = Mesa::orderBy('numero')->get(['id', 'numero']);

        return view('mesero.historial', compact(
            'pedidos',
            'resumen',
            'mesas',
            'desde',
            'hasta',
            'estadoFiltro',
            'mesaFiltro',
        ));
    }

    // =========================================================================
    // Cancelar pedido desde el panel del mesero
    // Ruta: POST /mesero/pedidos/{id}/cancelar
    // =========================================================================

    /*
    |--------------------------------------------------------------------------
    | Solo se puede cancelar si el pedido está en estado CREADO.
    | Una vez que llega a EN_COCINA, cocina ya está trabajando en él.
    | El mesero valida la titularidad del pedido antes de cancelar.
    |--------------------------------------------------------------------------
    */
    public function cancelarPedido(Request $request, string $id)
    {
        $usuarioId = auth()->id();

        // Verificar que el pedido pertenece a este mesero Y está en CREADO
        $pedido = Pedido::with(['detalles.producto', 'pagos'])
            ->where('id', $id)
            ->where('mesero_id', $usuarioId)
            ->firstOrFail();

        // Validar el estado antes de procesar
        if (!in_array($pedido->estado, [EstadoPedido::PENDIENTE_PAGO->value, EstadoPedido::CREADO->value])) {
            $mensajes = [
                EstadoPedido::EN_PREPARACION->value => 'El pedido ya está en preparación.',
                EstadoPedido::LISTO->value          => 'El pedido ya está listo para entregar.',
                EstadoPedido::ENTREGADO->value      => 'El pedido ya fue entregado.',
                EstadoPedido::CANCELADO->value      => 'El pedido ya estaba cancelado.',
            ];

            $mensaje = $mensajes[$pedido->estado] ?? 'No se puede cancelar en el estado actual.';

            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'mensaje' => $mensaje], 422);
            }

            return redirect()->route('mesero.dashboard')->with('error', $mensaje);
        }

        try {
            DB::transaction(function () use ($pedido, $request, $usuarioId) {
                $pedido->update([
                    'estado'             => EstadoPedido::CANCELADO->value,
                    'motivo_cancelacion' => $request->motivo ?? 'Cancelado por el mesero.',
                ]);

                HistorialEstadoPedido::create([
                    'pedido_id'   => $pedido->id,
                    'sucursal_id' => $pedido->sucursal_id,
                    'estado'      => EstadoPedido::CANCELADO->value,
                    'usuario_id'  => $usuarioId,
                    'cambiado_en' => now(),
                ]);

                // Cancelar pagos pendientes o fallidos asociados
                $pedido->pagos()
                    ->whereIn('estado', [
                        EstadoPago::PENDIENTE->value,
                        EstadoPago::FALLIDO->value,
                    ])
                    ->update(['estado' => EstadoPago::CANCELADO->value]);

                // ── NUEVO: Reembolsar pagos completados ──
                $pagoAprobado = $pedido->pagos()
                    ->where('estado', EstadoPago::COMPLETADO->value)
                    ->first();

                if ($pagoAprobado) {
                    $pagoAprobado->update([
                        'estado'         => EstadoPago::REEMBOLSADO->value,
                        'reembolsado_en' => now(),
                    ]);
                }
            });
        } catch (\Throwable $e) {
            Log::error('Mesero: error al cancelar pedido', [
                'pedido_id' => $pedido->id,
                'mesero_id' => $usuarioId,
                'error'     => $e->getMessage(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'mensaje' => 'Error al cancelar. Intenta de nuevo.'], 500);
            }

            return redirect()->route('mesero.dashboard')->with('error', 'Error al cancelar el pedido.');
        }

        Log::info('Pedido cancelado por el mesero', [
            'pedido_id' => $pedido->id,
            'mesero_id' => $usuarioId,
            'motivo'    => $request->motivo,
        ]);

        // ── NUEVO: Enviar Notificaciones por Correo ──
        $this->enviarEmailCancelacion($pedido->fresh());
        $this->enviarEmailReembolso($pedido->fresh());

        if ($request->expectsJson()) {
            return response()->json([
                'ok'      => true,
                'mensaje' => "Pedido #{$pedido->id} cancelado correctamente.",
            ]);
        }

        return redirect()->route('mesero.dashboard')
            ->with('exito', "Pedido #{$pedido->id} cancelado.");
    }

    // =========================================================================
    // Marcar pedido como entregado (AJAX)
    // Ruta: POST /mesero/pedidos/{id}/entregar
    // =========================================================================

    public function entregar(Request $request, string $id)
    {
        $usuarioId = auth()->id();

        $pedido = Pedido::where('id', $id)
            ->where('mesero_id', $usuarioId)
            ->where('estado', EstadoPedido::LISTO->value)
            ->firstOrFail();

        $pedido->update(['estado' => EstadoPedido::ENTREGADO->value]);

        HistorialEstadoPedido::create([
            'pedido_id'   => $pedido->id,
            'sucursal_id' => $pedido->sucursal_id,
            'estado'      => EstadoPedido::ENTREGADO->value,
            'usuario_id'  => $usuarioId,
            'cambiado_en' => now(),
        ]);

        Log::info('Pedido entregado por mesero', [
            'pedido_id' => $pedido->id,
            'mesero_id' => $usuarioId,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'ok'      => true,
                'mensaje' => "Pedido #{$pedido->id} entregado.",
            ]);
        }

        return redirect()->route('mesero.dashboard')
            ->with('exito', "Pedido #{$pedido->id} marcado como entregado.");
    }

    // =========================================================================
    // Liberar mesa (cerrar sesiones de cliente)
    // Ruta: POST /mesero/mesas/{id}/liberar
    // =========================================================================
    public function liberarMesa(Request $request, string $id)
    {
        // ✅ TenantScope ya filtra por sucursal, pero añadimos defensa explícita
        // para evitar que un mesero sin sucursal_id libere mesas ajenas.
        $mesa = Mesa::findOrFail($id);

        // Verificar ownership explícito
        $sucursalId = auth()->user()?->sucursal_id;
        if ($sucursalId && $mesa->sucursal_id !== $sucursalId) {
            abort(403, 'No tienes permiso para liberar esta mesa.');
        }

        $sesionesActivasCount = \App\Models\SesionCliente::where('mesa_id', $mesa->id)
            ->where('activo', true)
            ->count();

        // 🛡️ SEGURIDAD: Si hay sesiones, el botón global de liberar falla.
        if ($sesionesActivasCount > 0) {
            return redirect()->back()->with('error', 'No puedes liberar una mesa que tiene sesiones activas. Por favor, cierra las sesiones individuales primero.');
        }

        if ($mesa->estado !== Mesa::ESTADO_DISPONIBLE) {
            $mesa->update(['estado' => Mesa::ESTADO_DISPONIBLE]);
        }

        return redirect()->back()->with('exito', "Mesa {$mesa->numero} marcada como disponible correctamente.");
    }

    /**
     * Cierra una sesión individual (un cliente o grupo).
     */
    public function cerrarSesion(Request $request, string $id)
    {
        $sesion = \App\Models\SesionCliente::findOrFail($id);

        // ✅ Verificar que la sesión pertenece a la sucursal del mesero
        $sucursalId = auth()->user()?->sucursal_id;
        if ($sucursalId && $sesion->sucursal_id !== $sucursalId) {
            abort(403, 'No tienes permiso para cerrar esta sesión.');
        }

        // El método cerrar() ya cancela pedidos activos (zombies) automáticamente
        $sesion->cerrar();

        return redirect()->back()->with('exito', 'Sesión cerrada correctamente. Los pedidos pendientes han sido cancelados.');
    }
}