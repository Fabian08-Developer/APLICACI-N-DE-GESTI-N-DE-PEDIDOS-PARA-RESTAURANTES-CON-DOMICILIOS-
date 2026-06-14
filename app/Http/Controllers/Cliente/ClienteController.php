<?php
// @test: Provocando una revisión del auditor de IA 2.0
namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sucursal;
use App\Models\Mesa;
use App\Models\SesionCliente;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Pedido;
use App\Models\Pago;
use App\Models\Barrio;
use App\Enums\EstadoPago;
use App\Enums\EstadoPedido;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Services\Cliente\CarritoService;
use App\Services\Cliente\PedidoService;
use App\Services\Cliente\PagoService;
use App\Services\SucursalAssignmentService;

class ClienteController extends Controller
{
    protected $carritoService;
    protected $pedidoService;
    protected $pagoService;

    public function __construct(
        CarritoService $carritoService,
        PedidoService $pedidoService,
        PagoService $pagoService
    ) {
        $this->carritoService = $carritoService;
        $this->pedidoService = $pedidoService;
        $this->pagoService = $pagoService;
    }

    public function escanearQR($sucursal_slug, $codigo)
    {
        $sucursal = Sucursal::where('slug', $sucursal_slug)->first();
        if (!$sucursal) {
            return redirect()->route('cliente.sin-sesion')->with('error', 'La sucursal especificada no existe.');
        }

        $mesa = Mesa::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->where('sucursal_id', $sucursal->id)
            ->where('codigo_qr', $codigo)
            ->first();

        if (!$mesa) {
            return redirect()->route('cliente.sin-sesion')->with('error', 'La mesa especificada no existe o el código QR es inválido.');
        }

        if (!$mesa->qr_activo) {
            return redirect()->route('cliente.sin-sesion')->with('error', 'El código QR de esta mesa no está activo.');
        }

        $sesionActiva = SesionCliente::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->where('mesa_id', $mesa->id)
            ->where('activo', true)
            ->latest()
            ->first();

        if ($sesionActiva) {
            return redirect()->route('cliente.menu', ['t' => $sesionActiva->token]);
        }

        $token = Str::random(40);
        $sesion = SesionCliente::create([
            'sucursal_id' => $sucursal->id,
            'mesa_id' => $mesa->id,
            'zona_id' => $mesa->zona_id ?? null,
            'token' => $token,
            'tipo' => 'local',
            'nombre_cliente' => 'Cliente Mesa ' . $mesa->numero,
            'activo' => true,
        ]);

        $mesa->ocupar();

        return redirect()->route('cliente.menu', ['t' => $token]);
    }

    public function crearSesionIndividual(Request $request) { return back(); }

    public function accesoDomicilio($sucursal_slug)
    {
        // La ruta con slug de sucursal ya no es el único punto de entrada,
        // pero se mantiene para compatibilidad con QR fijos.
        // El slug sirve como empresa referencia para obtener los barrios disponibles.
        $sucursal = Sucursal::where('slug', $sucursal_slug)->first();
        if (!$sucursal) {
            return redirect()->route('cliente.sin-sesion')->with('error', 'La sucursal especificada no existe.');
        }

        // Obtener empresa de la sucursal y sus barrios con cobertura activa
        $empresaId = $sucursal->empresa_id;
        $barrios = Barrio::whereHas('tarifas', function ($q) use ($empresaId) {
                $q->where('activo', true)
                  ->whereHas('sucursal', function ($sq) use ($empresaId) {
                      $sq->where('activo', true)->where('empresa_id', $empresaId);
                  });
            })
            ->where('activo', true)
            ->orderBy('nombre')
            ->with('zona:id,nombre')
            ->get();

        return view('cliente.acceso-domicilio', compact('sucursal', 'barrios'));
    }

    public function crearSesionDomicilio(Request $request, $sucursal_slug)
    {
        $sucursal = Sucursal::where('slug', $sucursal_slug)->first();
        if (!$sucursal) {
            return redirect()->route('cliente.sin-sesion')->with('error', 'La sucursal especificada no existe.');
        }

        $validated = $request->validate([
            'nombre_cliente'    => 'required|string|max:255',
            'telefono_cliente'  => 'required|string|max:20',
            'direccion_cliente' => 'required|string|max:255',
            'barrio_id'         => 'nullable|uuid|exists:barrios,id',
            'latitud_entrega'   => 'nullable|numeric',
            'longitud_entrega'  => 'nullable|numeric',
        ]);

        // ── Asignación automática de sede ──────────────────────────────
        $sucursalAsignada = $sucursal; // Fallback: la sede del QR
        $barrioId = $validated['barrio_id'] ?? null;

        if ($barrioId) {
            $assignmentService = app(SucursalAssignmentService::class);
            $resultado = $assignmentService->resolver($barrioId);

            if (!$resultado['tiene_cobertura']) {
                return back()
                    ->withInput()
                    ->with('error', $resultado['mensaje']);
            }

            // Usar la sede óptima calculada por el servicio
            $sucursalAsignada = $resultado['sucursal'];
        }
        // ──────────────────────────────────────────────────────────────

        $token = Str::random(40);
        SesionCliente::create([
            'sucursal_id'      => $sucursalAsignada->id,
            'token'            => $token,
            'tipo'             => 'domicilio',
            'nombre_cliente'   => $validated['nombre_cliente'],
            'telefono_cliente' => $validated['telefono_cliente'],
            'direccion_cliente'=> $validated['direccion_cliente'],
            'barrio_id'        => $barrioId,
            'latitud'          => $validated['latitud_entrega'] ?? null,
            'longitud'         => $validated['longitud_entrega'] ?? null,
            'activo'           => true,
        ]);

        return redirect()->route('cliente.menu', ['t' => $token]);
    }

    public function sinSesion()
    {
        return view('cliente.sin-sesion');
    }

    public function menu(Request $request)
    {
        $sesion = $request->attributes->get('sesion_mesa');
        $token = $request->attributes->get('token_mesa');

        $categorias = Categoria::activo()->orderBy('orden')->get();
        $productos = Producto::activoConCategoriaActiva()
            ->where('disponible', true)
            ->with(['variantes', 'adiciones'])
            ->get();

        $itemsCarrito = $sesion->itemsCarrito()->with('producto')->get();

        return view('cliente.menu', compact('sesion', 'token', 'categorias', 'productos', 'itemsCarrito'));
    }

    public function logout(Request $request)
    {
        $sesion = $request->attributes->get('sesion_mesa');
        if ($sesion) {
            $sesion->cerrar();
        }
        return redirect()->route('cliente.sin-sesion')->with('success', 'Sesión cerrada correctamente.');
    }

    public function logoutInactividad(Request $request)
    {
        $sesion = $request->attributes->get('sesion_mesa');
        if ($sesion) {
            $sesion->cerrar();
        }
        return redirect()->route('cliente.sin-sesion')->with('error', 'Tu sesión ha expirado por inactividad.');
    }

    private function isSucursalOpen(Sucursal $sucursal): bool
    {
        if (config('app.env') !== 'production') {
            return true; // Permitir pruebas 24h en entornos no productivos
        }

        if (!$sucursal->hora_apertura || !$sucursal->hora_cierre) {
            return true;
        }

        $now = now();
        $currentTime = $now->format('H:i:s');

        if ($sucursal->hora_apertura <= $sucursal->hora_cierre) {
            return ($currentTime >= $sucursal->hora_apertura && $currentTime <= $sucursal->hora_cierre);
        } else {
            return ($currentTime >= $sucursal->hora_apertura || $currentTime <= $sucursal->hora_cierre);
        }
    }

    public function agregarAlCarrito(Request $request)
    {
        $sesion = $request->attributes->get('sesion_mesa');
        
        $validated = $request->validate([
            'producto_id' => 'required|uuid|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
            'variantes_elegidas' => 'nullable|array',
            'adiciones_elegidas' => 'nullable|array',
            'notas' => 'nullable|string',
        ]);

        try {
            $itemGuardado = $this->carritoService->agregarAlCarrito($sesion, $validated);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Producto agregado al carrito.',
                    'item' => $itemGuardado,
                    'cart_total' => (float) $sesion->itemsCarrito()->sum('subtotal'),
                    'cart_count' => (int) $sesion->itemsCarrito()->sum('cantidad'),
                ]);
            }
            return back()->with('success', 'Producto agregado al carrito.');
        } catch (\Exception $e) {
            return $request->wantsJson()
                ? response()->json(['error' => $e->getMessage()], 422)
                : back()->with('error', $e->getMessage());
        }
    }

    public function actualizarCantidadCarrito(Request $request, $id)
    {
        $sesion = $request->attributes->get('sesion_mesa');
        $validated = $request->validate(['cantidad' => 'required|integer|min:1']);

        try {
            $item = $this->carritoService->actualizarCantidad($sesion, $id, $validated['cantidad']);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cantidad actualizada.',
                    'item' => $item,
                    'cart_total' => (float) $sesion->itemsCarrito()->sum('subtotal'),
                    'cart_count' => (int) $sesion->itemsCarrito()->sum('cantidad'),
                ]);
            }
            return back()->with('success', 'Cantidad actualizada.');
        } catch (\Exception $e) {
            return $request->wantsJson()
                ? response()->json(['error' => $e->getMessage()], 404)
                : back()->with('error', $e->getMessage());
        }
    }

    public function eliminarDelCarrito(Request $request, $id)
    {
        $sesion = $request->attributes->get('sesion_mesa');

        try {
            $this->carritoService->eliminarDelCarrito($sesion, $id);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item eliminado del carrito.',
                    'cart_total' => (float) $sesion->itemsCarrito()->sum('subtotal'),
                    'cart_count' => (int) $sesion->itemsCarrito()->sum('cantidad'),
                ]);
            }
            return back()->with('success', 'Item eliminado del carrito.');
        } catch (\Exception $e) {
            return $request->wantsJson()
                ? response()->json(['error' => $e->getMessage()], 404)
                : back()->with('error', $e->getMessage());
        }
    }

    public function confirmarPedido(Request $request)
    {
        $sesion = $request->attributes->get('sesion_mesa');
        
        try {
            $pedido = $this->pedidoService->confirmarPedido($sesion);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pedido confirmado con éxito.',
                    'pedido_id' => $pedido->id,
                    'redirigir' => route('cliente.pago', ['t' => $sesion->token]),
                ]);
            }
            return redirect()->route('cliente.pago', ['t' => $sesion->token])
                ->with('success', 'Pedido confirmado con éxito.');
        } catch (\Exception $e) {
            return $request->wantsJson()
                ? response()->json(['error' => 'Ocurrió un error al confirmar el pedido: ' . $e->getMessage()], 500)
                : back()->with('error', 'Ocurrió un error al confirmar el pedido: ' . $e->getMessage());
        }
    }

    public function pago(Request $request)
    {
        $sesion = $request->attributes->get('sesion_mesa');
        
        $pedido = Pedido::where('sesion_cliente_id', $sesion->id)
            ->where('estado', EstadoPedido::PENDIENTE_PAGO->value)
            ->latest('creado_en')
            ->first();

        if (!$pedido) {
            $pedidoActivo = Pedido::where('sesion_cliente_id', $sesion->id)
                ->whereNotIn('estado', [EstadoPedido::PENDIENTE_PAGO->value, EstadoPedido::CANCELADO->value])
                ->latest('creado_en')
                ->first();
            if ($pedidoActivo) {
                return redirect()->route('cliente.confirmacion', ['t' => $sesion->token]);
            }
            return redirect()->route('cliente.menu', ['t' => $sesion->token])
                ->with('error', 'No tienes pedidos pendientes de pago.');
        }

        $intentosNequi = Pago::where('pedido_id', $pedido->id)
            ->where('metodo', 'Nequi')
            ->sum('intentos');
        $nequiBloqueado = $intentosNequi >= 3;

        return view('cliente.pago', compact('sesion', 'pedido', 'nequiBloqueado'));
    }

    public function procesarPago(Request $request)
    {
        $sesion = $request->attributes->get('sesion_mesa');
        
        $pedido = Pedido::where('sesion_cliente_id', $sesion->id)
            ->where('estado', EstadoPedido::PENDIENTE_PAGO->value)
            ->latest('creado_en')
            ->first();

        if (!$pedido) {
            return back()->with('error', 'No tienes pedidos pendientes de pago.');
        }

        $request->validate(['metodo' => 'required|in:Efectivo,Nequi']);
        $metodo = $request->input('metodo');
        $datosPago = $request->only(['nequi_telefono', 'nequi_correo']);

        if ($metodo === 'Nequi') {
            $request->validate([
                'nequi_telefono' => ['required', 'regex:/^3\d{9}$/'],
                'nequi_correo' => 'required|email|max:150',
            ], [
                'nequi_telefono.required' => 'El número celular es obligatorio.',
                'nequi_telefono.regex' => 'El número celular de Nequi debe ser de 10 dígitos y comenzar con 3.',
                'nequi_correo.required' => 'El correo electrónico es obligatorio.',
                'nequi_correo.email' => 'Debes ingresar un correo electrónico válido.',
            ]);
        }

        try {
            $pago = $this->pagoService->procesarPago($sesion, $pedido, $metodo, $datosPago);
            return redirect()->route('cliente.pago.pendiente', ['pagoId' => $pago->id, 't' => $sesion->token]);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function confirmacion(Request $request)
    {
        $sesion = $request->attributes->get('sesion_mesa');
        
        $pedido = Pedido::where('sesion_cliente_id', $sesion->id)
            ->whereNotIn('estado', [EstadoPedido::PENDIENTE_PAGO->value])
            ->latest('creado_en')
            ->first();

        if (!$pedido) {
            return redirect()->route('cliente.menu', ['t' => $sesion->token])
                ->with('error', 'No tienes un pedido activo.');
        }

        return view('cliente.confirmacion', compact('sesion', 'pedido'));
    }

    public function cancelacionExitosa(Request $request)
    {
        $sesion = $request->attributes->get('sesion_mesa');
        return view('cliente.cancelacion', compact('sesion'));
    }

    public function estadoPedido($pedidoId, Request $request)
    {
        $sesion = $request->attributes->get('sesion_mesa');
        $pedido = Pedido::withoutGlobalScope(\App\Scopes\TenantScope::class)->find($pedidoId);

        if (!$pedido || $pedido->sesion_cliente_id !== $sesion->id) {
            return redirect()->route('cliente.menu', ['t' => $sesion->token])
                ->with('error', 'El pedido no existe o no te pertenece.');
        }

        return view('cliente.confirmacion', compact('sesion', 'pedido'));
    }

    public function cancelarPedido(Request $request, $pedidoId)
    {
        $sesion = $request->attributes->get('sesion_mesa');
        
        try {
            $this->pedidoService->cancelarPedido($sesion, $pedidoId);
            return redirect()->route('cliente.cancelacion.exitosa', ['t' => $sesion->token])
                ->with('success', 'Pedido cancelado con éxito.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function pagoPendiente($pagoId, Request $request)
    {
        $sesion = $request->attributes->get('sesion_mesa');
        $pago = Pago::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->with(['pedido' => function($q) {
                $q->withoutGlobalScope(\App\Scopes\TenantScope::class);
            }])
            ->find($pagoId);

        if (!$pago) {
            return redirect()->route('cliente.menu', ['t' => $sesion->token])
                ->with('error', 'El pago especificado no existe.');
        }

        return view('cliente.pago-pendiente', compact('sesion', 'pago'));
    }

    public function estadoPago($pagoId, Request $request)
    {
        $pago = Pago::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->with(['pedido' => function($q) {
                $q->withoutGlobalScope(\App\Scopes\TenantScope::class);
            }])
            ->find($pagoId);

        if (!$pago) {
            return response()->json([
                'resolved' => true,
                'redirigir' => route('cliente.sin-sesion'),
            ]);
        }

        $pedido = $pago->pedido;
        $token = $pedido->sesionCliente->token;

        if ($pago->estado === EstadoPago::COMPLETADO->value) {
            return response()->json([
                'resolved' => true,
                'estado' => $pago->estado,
                'redirigir' => route('cliente.confirmacion', ['t' => $token]),
            ]);
        }

        if ($pago->estado === EstadoPago::FALLIDO->value) {
            return response()->json([
                'resolved' => true,
                'estado' => $pago->estado,
                'redirigir' => route('cliente.pago', ['t' => $token]),
            ]);
        }

        if ($pago->metodo === 'Efectivo' && $pedido->tipo === 'domicilio') {
            return response()->json([
                'resolved' => true,
                'estado' => 'PENDIENTE',
                'redirigir' => route('cliente.confirmacion', ['t' => $token]),
            ]);
        }

        return response()->json([
            'resolved' => false,
            'estado' => $pago->estado,
        ]);
    }

    public function simularConfirmacion(Request $request, $pagoId)
    {
        $resultado = $request->input('resultado'); // 'approved', 'declined', 'timeout'
        
        try {
            $response = $this->pagoService->simularConfirmacion($pagoId, $resultado);
            $pedido = $response['pedido'];

            if ($response['success']) {
                return redirect()->route('cliente.confirmacion', ['t' => $pedido->sesionCliente->token])
                    ->with('success', 'Pago aprobado exitosamente.');
            } else {
                if ($response['error_type'] === 'timeout') {
                    session()->flash('pago_error', 'timeout');
                } else {
                    session()->flash('pago_error', 'declined');
                }

                return redirect()->route('cliente.pago', ['t' => $pedido->sesionCliente->token])
                    ->with('error', $response['message']);
            }
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function calificarDomiciliario(Request $request, $pedidoId)
    {
        $sesion = $request->attributes->get('sesion_mesa');
        $pedido = Pedido::withoutGlobalScope(\App\Scopes\TenantScope::class)->find($pedidoId);

        if (!$pedido || $pedido->sesion_cliente_id !== $sesion->id) {
            return response()->json(['error' => 'El pedido no existe o no te pertenece.'], 403);
        }

        if ($pedido->estado !== EstadoPedido::ENTREGADO->value) {
            return response()->json(['error' => 'Solo puedes calificar pedidos entregados.'], 400);
        }

        $validated = $request->validate([
            'puntuacion' => 'required|integer|min:1|max:5',
            'comentario' => 'nullable|string|max:500',
        ]);

        if (!$pedido->perfil_domiciliario_id) {
            return response()->json(['error' => 'No hay un domiciliario asignado a este pedido.'], 400);
        }

        $existe = \App\Models\CalificacionDomiciliario::where('pedido_id', $pedido->id)->exists();
        if ($existe) {
            return response()->json(['error' => 'Ya has calificado este pedido.'], 400);
        }

        \App\Models\CalificacionDomiciliario::create([
            'pedido_id' => $pedido->id,
            'perfil_domiciliario_id' => $pedido->perfil_domiciliario_id,
            'cliente_id' => null, // Clientes anónimos por ahora
            'puntuacion' => $validated['puntuacion'],
            'comentario' => $validated['comentario'],
        ]);

        $perfil = \App\Models\PerfilDomiciliario::find($pedido->perfil_domiciliario_id);
        if ($perfil) {
            $perfil->recalcularPromedio();
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Calificación enviada con éxito.']);
        }
        return back()->with('success', 'Calificación enviada con éxito.');
    }
}
