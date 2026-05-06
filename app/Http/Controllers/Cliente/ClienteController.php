<?php

namespace App\Http\Controllers\Cliente;

use App\Contracts\PasarelaPagoContract;
use App\Enums\EstadoPago;
use App\Enums\EstadoPedido;
use App\Http\Controllers\Controller;
use App\Mail\PagoAprobadoMail;
use App\Mail\PagoFallidoMail;
use App\Mail\PedidoCanceladoMail;
use App\Mail\ReembolsoMail;
use App\Models\Categoria;
use App\Models\DetallePedido;
use App\Models\HistorialEstadoPedido;
use App\Models\Mesa;
use App\Models\Pago;
use App\Models\Pedido;
use App\Models\SesionMesa;
use App\Services\AsignacionMeseroService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\OrderEmailNotifications;
use Illuminate\Support\Str;

class ClienteController extends Controller
{
    use OrderEmailNotifications;

    // =========================================================================
    // QR y sesión de mesa
    // =========================================================================

    /**
     * El cliente escanea el QR.
     *
     * ANTES: guardaba mesa_id en session() → compartido entre pestañas
     * AHORA: redirige directamente con el token en la URL → cada pestaña independiente
     *
     * Si ya existe una sesión activa para esta mesa, la reutiliza.
     * Si no, muestra la pantalla de acceso para que el cliente elija tipo de sesión.
     */
    public function escanearQR(string $codigo)
    {
        $mesa = Mesa::where('qr_codigo', $codigo)
            ->where('qr_activo', true)
            ->first();

        if (!$mesa) {
            return view('cliente.error', [
                'mensaje' => 'El código QR no es válido o ya no está activo.',
            ]);
        }

        // Cerrar sesiones expiradas por inactividad
        $sesionesActivas = SesionMesa::where('mesa_id', $mesa->id)
            ->where('estado', SesionMesa::ESTADO_ACTIVA)
            ->whereNotNull('token')
            ->get();

        foreach ($sesionesActivas as $sesionActiva) {
            if ($sesionActiva->updated_at->diffInMinutes(now()) >= \App\Http\Middleware\ClienteTokenMiddleware::TIMEOUT_MINUTOS) {
                $sesionActiva->cerrar(SesionMesa::MOTIVO_INACTIVIDAD);
            }
        }

        // Contar sesiones activas vigentes después de la limpieza
        $sesionesVigentes = SesionMesa::where('mesa_id', $mesa->id)
            ->where('estado', SesionMesa::ESTADO_ACTIVA)
            ->count();

        // Verificar capacidad de la mesa
        if ($mesa->capacidad && $sesionesVigentes >= $mesa->capacidad) {
            return view('cliente.error', [
                'mensaje' => 'Esta mesa ya está llena. Todos los puestos están ocupados.',
            ]);
        }

        // Siempre mostrar la pantalla de acceso para que cada cliente cree su propia sesión
        return view('cliente.acceso', compact('mesa'));
    }

    /**
     * Crea una sesión individual para el cliente.
     *
     * ANTES: recibía mesa_id desde session() y guardaba sesion_mesa_id en session()
     * AHORA: recibe mesa_id desde el formulario (campo oculto) y redirige con ?t=TOKEN
     */
    public function crearSesionIndividual(Request $request)
    {
        $request->validate([
            'mesa_id' => 'required|exists:mesas,id',
        ]);

        $sesionMesa = \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            // Bloquear el registro de la mesa para evitar condiciones de carrera en capacidad
            $mesa = Mesa::where('id', $request->mesa_id)->lockForUpdate()->firstOrFail();

            // Contar sesiones activas actuales
            $sesionesActivas = SesionMesa::where('mesa_id', $mesa->id)
                ->where('estado', SesionMesa::ESTADO_ACTIVA)
                ->count();

            // Validar capacidad de la mesa
            if ($mesa->capacidad && $sesionesActivas >= $mesa->capacidad) {
                return null;
            }

            // Solo marcar como OCUPADA si es la primera sesión
            if ($sesionesActivas === 0) {
                $mesa->ocupar();
            }

            // Crear una sesión independiente para este cliente
            return SesionMesa::create([
                'mesa_id'               => $mesa->id,
                'codigo_grupo'          => strtoupper(Str::random(6)),
                'tipo_sesion'           => 'INDIVIDUAL',
                'estado'                => SesionMesa::ESTADO_ACTIVA,
                'participantes_activos' => 1,
                'fecha_inicio'          => now(),
                'token'                 => Str::random(64),
            ]);
        });

        if (!$sesionMesa) {
            return redirect()->back()->with('error', 'La mesa ya está llena. No hay puestos disponibles.');
        }

        // Cada cliente tiene su propio token → su propio carrito y pedidos
        return redirect()->route('cliente.menu', ['t' => $sesionMesa->token]);
    }

    // =========================================================================
    // Menú y carrito
    // =========================================================================

    /**
     * ANTES: leía sesion_mesa_id y carrito desde session()
     * AHORA: lee la SesionMesa desde $request->attributes (inyectada por el middleware)
     *        y el carrito desde la BD (tabla carrito_items o detalle_pedido borrador)
     *
     * El carrito también se mueve a la BD para que sea independiente por sesión de mesa,
     * no por cookie de navegador.
     */
    public function menu(Request $request)
    {
        /** @var SesionMesa $sesionMesa */
        $sesionMesa = $request->attributes->get('sesion_mesa');
        $token      = $request->attributes->get('token_mesa');

        $categorias = Categoria::with(['productos' => fn($q) => $q->where('estado', true)])
            ->get()
            ->filter(fn($c) => $c->productos->isNotEmpty());

        // ✅ El carrito se lee desde la BD por sesion_mesa_id — no desde session()
        $carrito      = $this->obtenerCarritoDB($sesionMesa->id);
        $totalCarrito = collect($carrito)->sum('subtotal');

        return view('cliente.menu', compact('categorias', 'carrito', 'totalCarrito', 'sesionMesa', 'token'));
    }

    /**
     * ANTES: guardaba el carrito en session(['carrito' => ...])
     * AHORA: guarda cada ítem en la tabla carrito_items por sesion_mesa_id
     */
    public function agregarAlCarrito(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad'    => 'required|integer|min:1',
        ]);

        /** @var SesionMesa $sesionMesa */
        $sesionMesa = $request->attributes->get('sesion_mesa');
        $token      = $request->attributes->get('token_mesa');
        $producto   = \App\Models\Producto::findOrFail($request->producto_id);

        // Buscar si ya existe en el carrito de esta sesión
        $item = \App\Models\CarritoItem::where('sesion_mesa_id', $sesionMesa->id)
            ->where('producto_id', $request->producto_id)
            ->first();

        if ($item) {
            $item->update([
                'cantidad' => $item->cantidad + $request->cantidad,
                'subtotal' => ($item->cantidad + $request->cantidad) * $producto->precio,
            ]);
        } else {
            \App\Models\CarritoItem::create([
                'sesion_mesa_id' => $sesionMesa->id,
                'producto_id'    => $request->producto_id,
                'nombre'         => $producto->nombre,
                'precio'         => $producto->precio,
                'cantidad'       => $request->cantidad,
                'subtotal'       => $request->cantidad * $producto->precio,
            ]);
        }

        return redirect()->route('cliente.menu', ['t' => $token])
            ->with('exito', '¡' . $producto->nombre . ' agregado al carrito!');
    }

    public function eliminarDelCarrito(Request $request, int $id)
    {
        /** @var SesionMesa $sesionMesa */
        $sesionMesa = $request->attributes->get('sesion_mesa');
        $token      = $request->attributes->get('token_mesa');

        \App\Models\CarritoItem::where('id', $id)
            ->where('sesion_mesa_id', $sesionMesa->id) // ownership
            ->delete();

        return redirect()->route('cliente.menu', ['t' => $token]);
    }

    public function actualizarCantidadCarrito(Request $request, int $productoId)
    {
        $request->validate(['delta' => 'required|integer|in:-1,1']);

        /** @var SesionMesa $sesionMesa */
        $sesionMesa = $request->attributes->get('sesion_mesa');

        $item = \App\Models\CarritoItem::where('sesion_mesa_id', $sesionMesa->id)
            ->where('producto_id', $productoId)
            ->first();

        if (!$item) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        $nuevaCantidad = $item->cantidad + $request->delta;

        if ($nuevaCantidad <= 0) {
            $item->delete();
            $carrito = $this->obtenerCarritoDB($sesionMesa->id);

            return response()->json([
                'eliminado'  => true,
                'total'      => number_format(collect($carrito)->sum('subtotal'), 2),
                'itemsCount' => count($carrito),
            ]);
        }

        $item->update([
            'cantidad' => $nuevaCantidad,
            'subtotal' => $nuevaCantidad * $item->precio,
        ]);

        $carrito = $this->obtenerCarritoDB($sesionMesa->id);

        return response()->json([
            'eliminado'  => false,
            'cantidad'   => $nuevaCantidad,
            'subtotal'   => number_format($item->subtotal, 2),
            'total'      => number_format(collect($carrito)->sum('subtotal'), 2),
            'itemsCount' => count($carrito),
        ]);
    }

    // =========================================================================
    // Pedido
    // =========================================================================

    /**
     * ANTES: leía carrito y sesion_mesa_id desde session()
     *        guardaba pedido_id en session()
     * AHORA: lee todo desde la BD usando sesion_mesa_id del token
     *        redirige con el token en la URL — el pedido se identifica por sesion_mesa_id
     */
    public function confirmarPedido(Request $request, AsignacionMeseroService $servicio)
    {
        /** @var SesionMesa $sesionMesa */
        $sesionMesa = $request->attributes->get('sesion_mesa');
        $token      = $request->attributes->get('token_mesa');

        $carrito = $this->obtenerCarritoDB($sesionMesa->id);

        if (empty($carrito)) {
            return redirect()->route('cliente.menu', ['t' => $token])
                ->with('error', 'Tu carrito está vacío');
        }

        $mesero = $servicio->obtenerMeseroDisponible($sesionMesa->mesa_id);
        if (!$mesero) {
            return redirect()->route('cliente.menu', ['t' => $token])
                ->with('error', 'No hay meseros disponibles en este momento.');
        }

        $total = collect($carrito)->sum('subtotal');

        $pedido = DB::transaction(function () use ($sesionMesa, $mesero, $total, $carrito) {
            // El pedido se crea como PENDIENTE_PAGO — no llega a cocina hasta que el pago sea exitoso
            $pedido = Pedido::create([
                'sesion_mesa_id' => $sesionMesa->id,
                'mesero_id'      => $mesero->id,
                'estado'         => EstadoPedido::PENDIENTE_PAGO->value,
                'total'          => $total,
            ]);

            foreach ($carrito as $item) {
                DetallePedido::create([
                    'pedido_id'       => $pedido->id,
                    'producto_id'     => $item['producto_id'],
                    'cantidad'        => $item['cantidad'],
                    'precio_unitario' => $item['precio'],
                    'subtotal'        => $item['subtotal'],
                    'estado'          => 'ACTIVO',
                ]);
            }

            HistorialEstadoPedido::create([
                'pedido_id' => $pedido->id,
                'estado'    => EstadoPedido::PENDIENTE_PAGO->value,
                'fecha'     => now(),
            ]);

            // ✅ Limpiar carrito de la BD (no de session())
            \App\Models\CarritoItem::where('sesion_mesa_id', $sesionMesa->id)->delete();

            return $pedido;
        });

        // ✅ Redirigir al pago con el token en la URL — sin guardar pedido_id en session()
        return redirect()->route('cliente.pago', ['t' => $token, 'pedido_id' => $pedido->id]);
    }

    /**
     * ANTES: comparaba $pedidoId con session('pedido_id')
     * AHORA: verifica que el pedido pertenezca a la sesion_mesa del token (middleware ownership)
     */
    public function estadoPedido(Request $request, int $pedidoId)
    {
        // El middleware ClienteOwnershipMiddleware ya verificó que este pedido
        // pertenece a la sesion_mesa activa — no necesitamos session()
        $pedido = Pedido::findOrFail($pedidoId);

        return response()->json([
            'estado'     => $pedido->estado,
            'cancelable' => $pedido->estado === EstadoPedido::CREADO->value,
            'updated_at' => $pedido->updated_at?->diffForHumans(),
        ]);
    }

    // =========================================================================
    // Pago
    // =========================================================================

    /**
     * ANTES: leía pedido_id desde session()
     * AHORA: recibe pedido_id como query param en la URL junto con el token
     */
    public function pago(Request $request)
    {
        /** @var SesionMesa $sesionMesa */
        $sesionMesa = $request->attributes->get('sesion_mesa');
        $token      = $request->attributes->get('token_mesa');

        // pedido_id viene en la URL: /cliente/pago?t=TOKEN&pedido_id=123
        $pedidoId = $request->query('pedido_id');

        if (!$pedidoId) {
            return redirect()->route('cliente.menu', ['t' => $token]);
        }

        // Verificar ownership: el pedido debe pertenecer a esta sesión de mesa
        $pedido = Pedido::with('detalles.producto')
            ->where('id', $pedidoId)
            ->where('sesion_mesa_id', $sesionMesa->id)
            ->firstOrFail();

        return view('cliente.pago', compact('pedido', 'token'));
    }

    public function procesarPago(Request $request)
    {
        $request->validate([
            'pedido_id'   => 'required|exists:pedidos,id',
            'metodo_pago' => 'required|in:EFECTIVO,NEQUI',
            'telefono'    => 'required_if:metodo_pago,NEQUI|nullable|regex:/^3[0-9]{9}$/',
            'email'       => 'required_if:metodo_pago,NEQUI|nullable|email|max:150',
        ]);

        /** @var SesionMesa $sesionMesa */
        $sesionMesa = $request->attributes->get('sesion_mesa');
        $token      = $request->attributes->get('token_mesa');

        // Verificar ownership en el controlador también
        $pedido = Pedido::where('id', $request->pedido_id)
            ->where('sesion_mesa_id', $sesionMesa->id)
            ->firstOrFail();

        if ($request->metodo_pago === 'EFECTIVO') {
            // Pago en efectivo → el mesero cobra en persona → el pedido pasa directo a CREADO
            Pago::create([
                'pedido_id'   => $pedido->id,
                'metodo_pago' => 'EFECTIVO',
                'monto'       => $pedido->total,
                'estado'      => EstadoPago::COMPLETADO->value,
            ]);

            $pedido->update(['estado' => EstadoPedido::CREADO->value]);

            HistorialEstadoPedido::create([
                'pedido_id' => $pedido->id,
                'estado'    => EstadoPedido::CREADO->value,
                'fecha'     => now(),
            ]);

            // ✅ Token y pedido_id en la URL — sin session()
            return redirect()->route('cliente.confirmacion', [
                't'        => $token,
                'pedido_id'=> $pedido->id,
            ]);
        }

        // Pago con Nequi
        $pasarela  = app(PasarelaPagoContract::class);
        $resultado = $pasarela->crearTransaccionNequi([
            'monto'     => $pedido->total,
            'telefono'  => $request->telefono,
            'pedido_id' => $pedido->id,
            'email'     => $request->email,
        ]);

        if (!$resultado['exito']) {
            return back()->withErrors(['telefono' => $resultado['mensaje']]);
        }

        $pago = Pago::create([
            'pedido_id'              => $pedido->id,
            'metodo_pago'            => 'NEQUI',
            'monto'                  => $pedido->total,
            'estado'                 => EstadoPago::PENDIENTE->value,
            'referencia_transaccion' => $resultado['referencia'],
            'telefono'               => $request->telefono,
            'email'                  => $request->email,
        ]);

        // ✅ Token y pago_id en la URL — sin session()
        return redirect()->route('cliente.pago.pendiente', [
            $pago->id,
            't' => $token,
        ]);
    }

    public function pagoPendiente(Request $request, int $pagoId)
    {
        /** @var SesionMesa $sesionMesa */
        $sesionMesa = $request->attributes->get('sesion_mesa');
        $token      = $request->attributes->get('token_mesa');

        // ✅ Ownership en el controlador: el pago debe ser de esta sesión de mesa
        $pago = Pago::with('pedido.detalles.producto')
            ->whereHas('pedido', fn($q) => $q->where('sesion_mesa_id', $sesionMesa->id))
            ->findOrFail($pagoId);

        if ($pago->estado === EstadoPago::COMPLETADO->value) {
            return redirect()->route('cliente.confirmacion', [
                't'         => $token,
                'pedido_id' => $pago->pedido_id,
            ]);
        }

        if ($pago->estado === EstadoPago::FALLIDO->value) {
            return redirect()->route('cliente.pago', [
                't'         => $token,
                'pedido_id' => $pago->pedido_id,
            ])->with('error', 'El pago con Nequi fue rechazado. Por favor intenta de nuevo.');
        }

        return view('cliente.pago_pendiente', [
            'pago'    => $pago,
            'pedido'  => $pago->pedido,
            'token'   => $token,
            'timeout' => config('wompi.timeout_polling', 120),
        ]);
    }

    public function estadoPago(Request $request, int $pagoId)
    {
        /** @var SesionMesa $sesionMesa */
        $sesionMesa = $request->attributes->get('sesion_mesa');

        // ✅ Ownership: el pago debe ser de esta sesión de mesa
        $pago = Pago::whereHas('pedido', fn($q) => $q->where('sesion_mesa_id', $sesionMesa->id))
            ->findOrFail($pagoId);

        return response()->json([
            'estado'     => $pago->estado,
            'referencia' => $pago->referencia_transaccion,
        ]);
    }

    public function simularConfirmacion(Request $request, int $pagoId)
    {
        abort_unless(app()->environment('local', 'testing'), 404);
        $request->validate(['estado' => 'required|in:COMPLETADO,FALLIDO']);

        /** @var SesionMesa $sesionMesa */
        $sesionMesa = $request->attributes->get('sesion_mesa');

        $pago = Pago::with('pedido.detalles.producto')
            ->whereHas('pedido', fn($q) => $q->where('sesion_mesa_id', $sesionMesa->id))
            ->findOrFail($pagoId);

        if (in_array($pago->estado, [EstadoPago::COMPLETADO->value, EstadoPago::FALLIDO->value])) {
            return response()->json(['ok' => true, 'msg' => 'Ya procesado', 'estado' => $pago->estado]);
        }

        DB::transaction(function () use ($pago, $request) {
            $pago->update(['estado' => $request->estado]);

            if ($request->estado === EstadoPago::COMPLETADO->value) {
                $pago->pedido->update(['estado' => EstadoPedido::CREADO->value]);

                HistorialEstadoPedido::create([
                    'pedido_id' => $pago->pedido_id,
                    'estado'    => EstadoPedido::CREADO->value,
                    'fecha'     => now(),
                ]);
            }
        });

        $this->enviarEmailPago($pago->fresh('pedido.detalles.producto'), $request->estado);

        return response()->json(['ok' => true, 'estado' => $request->estado]);
    }

    public function confirmacion(Request $request)
    {
        /** @var SesionMesa $sesionMesa */
        $sesionMesa = $request->attributes->get('sesion_mesa');
        $token      = $request->attributes->get('token_mesa');

        // pedido_id viene en la URL junto con el token
        $pedidoId = $request->query('pedido_id');

        if (!$pedidoId) {
            return redirect()->route('cliente.menu', ['t' => $token]);
        }

        $pedido = Pedido::with('detalles.producto')
            ->where('id', $pedidoId)
            ->where('sesion_mesa_id', $sesionMesa->id)
            ->firstOrFail();

        return view('cliente.confirmacion', compact('pedido', 'token'));
    }

    // =========================================================================
    // Cancelación de pedido
    // =========================================================================

    public function cancelarPedido(Request $request, int $pedidoId)
    {
        // ✅ El middleware ClienteOwnershipMiddleware ya verificó el ownership
        // No necesitamos comparar con session('pedido_id')
        $request->validate(['motivo' => 'nullable|string|max:255']);

        $pedido = Pedido::with(['detalles.producto', 'pagos'])->findOrFail($pedidoId);

        if (!in_array($pedido->estado, [EstadoPedido::PENDIENTE_PAGO->value, EstadoPedido::CREADO->value])) {
            $mensajes = [
                EstadoPedido::EN_COCINA->value      => 'Tu pedido ya está en cocina y no puede cancelarse.',
                EstadoPedido::EN_PREPARACION->value => 'Tu pedido ya está en preparación y no puede cancelarse.',
                EstadoPedido::LISTO->value          => 'Tu pedido ya está listo para entregarse.',
                EstadoPedido::ENTREGADO->value      => 'Tu pedido ya fue entregado.',
                EstadoPedido::CANCELADO->value      => 'Este pedido ya fue cancelado anteriormente.',
            ];

            return response()->json([
                'ok'      => false,
                'mensaje' => $mensajes[$pedido->estado] ?? 'Este pedido no puede cancelarse.',
            ], 422);
        }

        try {
            DB::transaction(function () use ($pedido, $request) {
                $pedido->update([
                    'estado'             => EstadoPedido::CANCELADO->value,
                    'fecha_cancelacion'  => now(),
                    'motivo_cancelacion' => $request->motivo ?? 'Cancelado por el cliente.',
                ]);

                HistorialEstadoPedido::create([
                    'pedido_id' => $pedido->id,
                    'estado'    => EstadoPedido::CANCELADO->value,
                    'fecha'     => now(),
                ]);

                $pedido->pagos()
                    ->whereIn('estado', [EstadoPago::PENDIENTE->value, EstadoPago::FALLIDO->value])
                    ->update(['estado' => EstadoPago::CANCELADO->value]);

                $pagoAprobado = $pedido->pagos()
                    ->where('estado', EstadoPago::COMPLETADO->value)
                    ->first();

                if ($pagoAprobado) {
                    $pagoAprobado->update([
                        'estado'          => EstadoPago::REEMBOLSADO->value,
                        'fecha_reembolso' => now(),
                    ]);
                }
            });
        } catch (\Throwable $e) {
            Log::error('Cancelación pedido: error', [
                'pedido_id' => $pedido->id,
                'error'     => $e->getMessage(),
            ]);

            return response()->json([
                'ok'      => false,
                'mensaje' => 'Error al cancelar. Intenta de nuevo.',
            ], 500);
        }

        $pedidoFresh = $pedido->fresh(['detalles.producto', 'pagos']);
        $this->enviarEmailCancelacion($pedidoFresh);
        $this->enviarEmailReembolso($pedidoFresh);

        // ✅ La URL de redirección incluye el token para mantener la sesión en la URL
        $token = $request->attributes->get('token_mesa');

        return response()->json([
            'ok'           => true,
            'mensaje'      => 'Tu pedido fue cancelado correctamente.',
            'redirect_url' => route('cliente.cancelacion.exitosa', ['t' => $token]),
        ]);
    }

    public function cancelacionExitosa(Request $request)
    {
        /** @var SesionMesa $sesionMesa */
        $sesionMesa = $request->attributes->get('sesion_mesa');
        $token      = $request->attributes->get('token_mesa');

        // El pedido cancelado más reciente de esta sesión
        $pedido = Pedido::with('detalles.producto')
            ->where('sesion_mesa_id', $sesionMesa->id)
            ->where('estado', EstadoPedido::CANCELADO->value)
            ->latest()
            ->firstOrFail();

        $teniaPagoAprobado = $pedido->pagos()
            ->whereIn('estado', [EstadoPago::COMPLETADO->value, EstadoPago::REEMBOLSADO->value])
            ->exists();

        $emailEnviado = $pedido->pagos()->whereNotNull('email')->exists();

        return view('cliente.cancelacion_exitosa', compact('pedido', 'teniaPagoAprobado', 'emailEnviado', 'token'));
    }

    // =========================================================================
    // Cierre de sesión
    // =========================================================================

    /**
     * ANTES: leía sesion_mesa_id desde session() y hacía flush() de session()
     * AHORA: lee la SesionMesa desde $request->attributes (token del middleware)
     *        No hace flush de session() — no hay nada que limpiar ahí
     */
    public function logout(Request $request)
    {
        $this->procesarCierre($request);
        return response('<script>window.close();</script>')->header('Content-Type', 'text/html');
    }

    public function logoutInactividad(Request $request)
    {
        $this->procesarCierre($request);
        return response()->json(['ok' => true]);
    }

    public function sinSesion()
    {
        return view('cliente.sin-sesion');
    }

    // =========================================================================
    // Helpers privados
    // =========================================================================

    private function procesarCierre(Request $request): void
    {
        /** @var SesionMesa|null $sesionMesa */
        $sesionMesa = $request->attributes->get('sesion_mesa');

        if (!$sesionMesa) {
            return;
        }

        if ($sesionMesa->estado !== SesionMesa::ESTADO_ACTIVA) {
            return;
        }

        // Cerrar solo ESTA sesión individual (usa cerrar() que ya valida si liberar la mesa)
        $sesionMesa->cerrar(SesionMesa::MOTIVO_MANUAL);
    }

    /**
     * Lee el carrito desde la BD por sesion_mesa_id.
     * Retorna array con la misma estructura que antes usaba session('carrito').
     */
    private function obtenerCarritoDB(int $sesionMesaId): array
    {
        return \App\Models\CarritoItem::with('producto')
            ->where('sesion_mesa_id', $sesionMesaId)
            ->get()
            ->keyBy('producto_id')
            ->map(fn($item) => [
                'producto_id' => $item->producto_id,
                'nombre'      => $item->nombre,
                'precio'      => $item->precio,
                'cantidad'    => $item->cantidad,
                'subtotal'    => $item->subtotal,
                'imagen'      => $item->producto?->imagen, // ← Cargamos la imagen desde el producto
            ])
            ->toArray();
    }
}