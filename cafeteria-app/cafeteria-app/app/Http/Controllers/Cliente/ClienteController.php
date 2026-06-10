<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Sucursal;
use App\Models\Mesa;
use App\Models\SesionCliente;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\ItemCarrito;
use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\HistorialEstadoPedido;
use App\Models\ZonaCobertura;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Pago;
use App\Models\User;
use App\Models\PerfilDomiciliario;
use App\Enums\EstadoPago;
use App\Enums\EstadoPedido;
use App\Traits\OrderEmailNotifications;

class ClienteController extends Controller
{
    use OrderEmailNotifications;
    public function escanearQR($sucursal_slug, $codigo)
    {
        $sucursal = Sucursal::where('slug', $sucursal_slug)->first();
        if (!$sucursal) {
            return redirect()->route('cliente.sin-sesion')->with('error', 'La sucursal especificada no existe.');
        }

        // Buscar mesa sin TenantScope ya que el cliente aún no tiene la sesión activa.
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

        // RF-C12: Si una mesa ya tiene una sesión activa (estado "ocupada"), permitir que nuevos clientes se unan a la misma sesión (mismo token).
        $sesionActiva = SesionCliente::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->where('mesa_id', $mesa->id)
            ->where('activo', true)
            ->latest()
            ->first();

        if ($sesionActiva) {
            return redirect()->route('cliente.menu', ['t' => $sesionActiva->token]);
        }

        // Si no hay sesión activa, creamos una nueva.
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

        // Cambiar el estado de la mesa a ocupada.
        $mesa->ocupar();

        return redirect()->route('cliente.menu', ['t' => $token]);
    }

    public function crearSesionIndividual(Request $request) { return back(); }

    public function accesoDomicilio($sucursal_slug)
    {
        $sucursal = Sucursal::where('slug', $sucursal_slug)->first();
        if (!$sucursal) {
            return redirect()->route('cliente.sin-sesion')->with('error', 'La sucursal especificada no existe.');
        }

        // RF-C03: Validar que el cliente solo pueda acceder al menú y registrarse dentro del horario laboral de la sucursal.
        if (!$this->isSucursalOpen($sucursal)) {
            $apertura = Carbon::parse($sucursal->hora_apertura)->format('g:i A');
            $cierre = Carbon::parse($sucursal->hora_cierre)->format('g:i A');
            return redirect()->route('cliente.sin-sesion')->with('error', "La sucursal está cerrada. El horario de atención para domicilios es de {$apertura} a {$cierre}.");
        }

        return view('cliente.acceso-domicilio', compact('sucursal'));
    }

    public function crearSesionDomicilio(Request $request, $sucursal_slug)
    {
        $sucursal = Sucursal::where('slug', $sucursal_slug)->first();
        if (!$sucursal) {
            return redirect()->route('cliente.sin-sesion')->with('error', 'La sucursal especificada no existe.');
        }

        // RF-C03: Validar horario laboral
        if (!$this->isSucursalOpen($sucursal)) {
            $apertura = Carbon::parse($sucursal->hora_apertura)->format('g:i A');
            $cierre = Carbon::parse($sucursal->hora_cierre)->format('g:i A');
            return redirect()->route('cliente.sin-sesion')->with('error', "La sucursal está cerrada. El horario de atención para domicilios es de {$apertura} a {$cierre}.");
        }

        $validated = $request->validate([
            'nombre_cliente' => 'required|string|max:255',
            'telefono_cliente' => 'required|string|max:20',
            'direccion_cliente' => 'required|string|max:255',
            'latitud_entrega' => 'nullable|numeric',
            'longitud_entrega' => 'nullable|numeric',
        ]);

        $token = Str::random(40);
        $sesion = SesionCliente::create([
            'sucursal_id' => $sucursal->id,
            'token' => $token,
            'tipo' => 'domicilio',
            'nombre_cliente' => $validated['nombre_cliente'],
            'telefono_cliente' => $validated['telefono_cliente'],
            'direccion_cliente' => $validated['direccion_cliente'],
            'latitud' => $validated['latitud_entrega'] ?? null,
            'longitud' => $validated['longitud_entrega'] ?? null,
            'activo' => true,
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

        // Obtener categorías y productos activos de la sucursal de la sesión
        $categorias = Categoria::activo()
            ->orderBy('orden')
            ->get();

        // Obtener productos de la sucursal activos
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

    // Helper para verificar horario
    private function isSucursalOpen(Sucursal $sucursal): bool
    {
        // NOTA: Desactivado temporalmente a petición del usuario para permitir pruebas 24h
        return true;

        if (!$sucursal->hora_apertura || !$sucursal->hora_cierre) {
            return true;
        }

        $now = now();
        $currentTime = $now->format('H:i:s');

        if ($sucursal->hora_apertura <= $sucursal->hora_cierre) {
            return ($currentTime >= $sucursal->hora_apertura && $currentTime <= $sucursal->hora_cierre);
        } else {
            // Horario nocturno
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

        $producto = Producto::activoConCategoriaActiva()
            ->where('disponible', true)
            ->where('id', $validated['producto_id'])
            ->with(['variantes', 'adiciones'])
            ->first();

        if (!$producto) {
            return $request->wantsJson()
                ? response()->json(['error' => 'El producto no está disponible.'], 422)
                : back()->with('error', 'El producto no está disponible.');
        }

        // 1. Validar variantes obligatorias
        $variantesElegidas = $validated['variantes_elegidas'] ?? [];
        foreach ($producto->variantes as $variante) {
            if ($variante->obligatorio) {
                $elegida = false;
                foreach ($variantesElegidas as $vName => $vOpcion) {
                    if ($vName === $variante->nombre) {
                        $elegida = true;
                        break;
                    }
                }
                if (!$elegida) {
                    return $request->wantsJson()
                        ? response()->json(['error' => "La variante '{$variante->nombre}' es obligatoria."], 422)
                        : back()->with('error', "La variante '{$variante->nombre}' es obligatoria.");
                }
            }
        }

        // 2. Validar límites de adiciones
        $adicionesElegidas = $validated['adiciones_elegidas'] ?? [];
        $cantAdiciones = count($adicionesElegidas);
        if ($producto->limite_minimo_adiciones > 0 && $cantAdiciones < $producto->limite_minimo_adiciones) {
            return $request->wantsJson()
                ? response()->json(['error' => "Debes seleccionar al menos {$producto->limite_minimo_adiciones} adiciones."], 422)
                : back()->with('error', "Debes seleccionar al menos {$producto->limite_minimo_adiciones} adiciones.");
        }
        if ($producto->limite_maximo_adiciones !== null && $cantAdiciones > $producto->limite_maximo_adiciones) {
            return $request->wantsJson()
                ? response()->json(['error' => "No puedes seleccionar más de {$producto->limite_maximo_adiciones} adiciones."], 422)
                : back()->with('error', "No puedes seleccionar más de {$producto->limite_maximo_adiciones} adiciones.");
        }

        // 3. Procesar variantes elegidas y calcular precio unitario
        $variantesFormateadas = [];
        $precioBase = $producto->precio_oferta && $producto->precio_oferta > 0 ? $producto->precio_oferta : $producto->precio;
        $hasFijo = false;
        $fijoPriceSum = 0;

        foreach ($producto->variantes as $variante) {
            $nombreGrupo = $variante->nombre;
            if (isset($variantesElegidas[$nombreGrupo])) {
                $nombreOpcion = $variantesElegidas[$nombreGrupo];
                $opcionEncontrada = null;
                foreach ($variante->opciones as $opc) {
                    if ($opc['nombre'] === $nombreOpcion) {
                        $opcionEncontrada = $opc;
                        break;
                    }
                }

                if ($opcionEncontrada) {
                    $precioOpcion = (float) $opcionEncontrada['precio'];
                    $tipoImpacto = $opcionEncontrada['tipo_impacto'] ?? 'incremental';
                    
                    if ($tipoImpacto === 'fijo') {
                        $fijoPriceSum += $precioOpcion;
                        $hasFijo = true;
                    }

                    $variantesFormateadas[] = [
                        'grupo' => $nombreGrupo,
                        'opcion' => $nombreOpcion,
                        'precio' => $precioOpcion,
                        'tipo_impacto' => $tipoImpacto,
                    ];
                }
            }
        }

        if ($hasFijo) {
            $baseCalculada = $fijoPriceSum;
        } else {
            $baseCalculada = $precioBase;
        }

        // Sumar variantes incrementales
        foreach ($variantesFormateadas as $vf) {
            if ($vf['tipo_impacto'] === 'incremental') {
                $baseCalculada += $vf['precio'];
            }
        }

        // Procesar adiciones elegidas y sumar costo
        $adicionesFormateadas = [];
        $adicionesDisponibles = $producto->adiciones_disponibles;

        foreach ($adicionesElegidas as $adicionId) {
            $adicionReal = $adicionesDisponibles->firstWhere('id', $adicionId);
            if ($adicionReal && $adicionReal->activo && ($adicionReal->disponible || is_null($adicionReal->getRawOriginal('disponible')))) {
                $precioAdicion = (float) $adicionReal->precio;
                $baseCalculada += $precioAdicion;
                $adicionesFormateadas[] = [
                    'id' => $adicionReal->id,
                    'nombre' => $adicionReal->nombre,
                    'precio' => $precioAdicion,
                ];
            }
        }

        $precioUnitario = $baseCalculada;
        $cantidad = (int) $validated['cantidad'];
        $subtotal = $precioUnitario * $cantidad;
        $notas = $validated['notas'] ?? null;

        // Buscar si ya existe un item idéntico en el carrito de esta sesión
        $existingItems = $sesion->itemsCarrito()->where('producto_id', $producto->id)->get();
        $duplicateItem = null;

        foreach ($existingItems as $item) {
            $vDB = $item->variantes_elegidas ?? [];
            $aDB = $item->adiciones_elegidas ?? [];
            $nDB = $item->notas;

            // Comparar variantes
            $sameVariantes = count($vDB) === count($variantesFormateadas);
            if ($sameVariantes) {
                foreach ($variantesFormateadas as $vf) {
                    $found = false;
                    foreach ($vDB as $vItem) {
                        if ($vItem['grupo'] === $vf['grupo'] && $vItem['opcion'] === $vf['opcion']) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $sameVariantes = false;
                        break;
                    }
                }
            }

            // Comparar adiciones
            $sameAdiciones = count($aDB) === count($adicionesFormateadas);
            if ($sameAdiciones) {
                foreach ($adicionesFormateadas as $af) {
                    $found = false;
                    foreach ($aDB as $aItem) {
                        if ($aItem['id'] === $af['id']) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $sameAdiciones = false;
                        break;
                    }
                }
            }

            // Comparar notas
            $sameNotas = $nDB === $notas;

            if ($sameVariantes && $sameAdiciones && $sameNotas) {
                $duplicateItem = $item;
                break;
            }
        }

        if ($duplicateItem) {
            $duplicateItem->cantidad += $cantidad;
            $duplicateItem->subtotal = $duplicateItem->precio_unitario * $duplicateItem->cantidad;
            $duplicateItem->save();
            $itemGuardado = $duplicateItem;
        } else {
            $itemGuardado = ItemCarrito::create([
                'sesion_cliente_id' => $sesion->id,
                'producto_id' => $producto->id,
                'sucursal_id' => $sesion->sucursal_id,
                'nombre_producto' => $producto->nombre,
                'precio_unitario' => $precioUnitario,
                'cantidad' => $cantidad,
                'subtotal' => $subtotal,
                'variantes_elegidas' => $variantesFormateadas,
                'adiciones_elegidas' => $adicionesFormateadas,
                'notas' => $notas,
            ]);
        }

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
    }

    public function actualizarCantidadCarrito(Request $request, $id)
    {
        $sesion = $request->attributes->get('sesion_mesa');
        
        $validated = $request->validate([
            'cantidad' => 'required|integer|min:1',
        ]);

        $item = $sesion->itemsCarrito()->where('id', $id)->first();

        if (!$item) {
            return $request->wantsJson()
                ? response()->json(['error' => 'El item del carrito no existe.'], 404)
                : back()->with('error', 'El item del carrito no existe.');
        }

        $item->cantidad = (int) $validated['cantidad'];
        $item->subtotal = $item->precio_unitario * $item->cantidad;
        $item->save();

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
    }

    public function eliminarDelCarrito(Request $request, $id)
    {
        $sesion = $request->attributes->get('sesion_mesa');

        $item = $sesion->itemsCarrito()->where('id', $id)->first();

        if (!$item) {
            return $request->wantsJson()
                ? response()->json(['error' => 'El item del carrito no existe.'], 404)
                : back()->with('error', 'El item del carrito no existe.');
        }

        $item->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Item eliminado del carrito.',
                'cart_total' => (float) $sesion->itemsCarrito()->sum('subtotal'),
                'cart_count' => (int) $sesion->itemsCarrito()->sum('cantidad'),
            ]);
        }

        return back()->with('success', 'Item eliminado del carrito.');
    }

    public function confirmarPedido(Request $request)
    {
        $sesion = $request->attributes->get('sesion_mesa');
        
        $items = $sesion->itemsCarrito()->with('producto')->get();
        if ($items->isEmpty()) {
            return $request->wantsJson()
                ? response()->json(['error' => 'El carrito está vacío.'], 422)
                : back()->with('error', 'El carrito está vacío.');
        }

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $subtotal = $items->sum('subtotal');
            $costoEnvio = 0.00;
            
            if ($sesion->tipo === 'domicilio' && $sesion->zona_id) {
                $zona = ZonaCobertura::find($sesion->zona_id);
                if ($zona) {
                    $costoEnvio = (float) $zona->costo_envio;
                }
            }

            $total = $subtotal + $costoEnvio;

            $pedido = Pedido::create([
                'sucursal_id' => $sesion->sucursal_id,
                'sesion_cliente_id' => $sesion->id,
                'zona_id' => $sesion->zona_id,
                'tipo' => $sesion->tipo,
                'estado' => EstadoPedido::PENDIENTE_PAGO->value,
                'estado_pago' => EstadoPago::PENDIENTE->value,
                'direccion_entrega' => $sesion->tipo === 'domicilio' ? $sesion->direccion_cliente : null,
                'latitud_entrega' => $sesion->tipo === 'domicilio' ? $sesion->latitud : null,
                'longitud_entrega' => $sesion->tipo === 'domicilio' ? $sesion->longitud : null,
                'subtotal' => $subtotal,
                'costo_envio' => $costoEnvio,
                'total' => $total,
            ]);

            // Auto-asignación inteligente de Mesero para pedidos locales (RF-C36)
            if ($sesion->tipo === 'local') {
                $mesero = User::where('sucursal_id', $sesion->sucursal_id)
                    ->where('rol', 'mesero')
                    ->where('activo', true)
                    ->get()
                    ->map(function ($m) {
                        $m->pedidos_activos_count = Pedido::where('mesero_id', $m->id)
                            ->whereNotIn('estado', ['ENTREGADO', 'CANCELADO'])
                            ->count();
                        return $m;
                    })
                    ->sortBy('pedidos_activos_count')
                    ->first();

                if ($mesero) {
                    $pedido->update(['mesero_id' => $mesero->id]);
                    $sesion->update(['mesero_id' => $mesero->id]);
                }
            }

            // Auto-asignación de Domiciliario para pedidos domicilio
            if ($sesion->tipo === 'domicilio') {
                $candidatos = PerfilDomiciliario::with(['liquidaciones', 'pedidosActivos'])
                    ->where('sucursal_id', $sesion->sucursal_id)
                    ->where('estado', 'disponible')
                    ->get()
                    ->filter(fn($d) => !$d->tiene_bloqueo);

                if ($candidatos->isNotEmpty()) {
                    $mismaZona = $candidatos->where('zona_id', $sesion->zona_id);
                    $candidatos = $mismaZona->isNotEmpty() ? $mismaZona : $candidatos;

                    $elegido = $candidatos
                        ->sortBy([
                            ['pedidos_hoy', 'asc'],
                            [fn($d) => $d->pedidosActivos->count(), 'asc'],
                        ])
                        ->first();

                    if ($elegido) {
                        $pedido->update(['perfil_domiciliario_id' => $elegido->id]);
                    }
                }
            }

            foreach ($items as $item) {
                DetallePedido::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $item->producto_id,
                    'sucursal_id' => $item->sucursal_id,
                    'nombre_producto' => $item->nombre_producto,
                    'precio_unitario' => $item->precio_unitario,
                    'cantidad' => $item->cantidad,
                    'subtotal' => $item->subtotal,
                    'variantes_elegidas' => $item->variantes_elegidas,
                    'adiciones_elegidas' => $item->adiciones_elegidas,
                    'notas' => $item->notas,
                    'estado' => 'activo',
                ]);
            }

            HistorialEstadoPedido::create([
                'pedido_id' => $pedido->id,
                'sucursal_id' => $sesion->sucursal_id,
                'estado' => EstadoPedido::PENDIENTE_PAGO->value,
            ]);

            $sesion->itemsCarrito()->delete();

            \Illuminate\Support\Facades\DB::commit();

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
            \Illuminate\Support\Facades\DB::rollBack();
            return $request->wantsJson()
                ? response()->json(['error' => 'Ocurrió un error al confirmar el pedido: ' . $e->getMessage()], 500)
                : back()->with('error', 'Ocurrió un error al confirmar el pedido.');
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

        $request->validate([
            'metodo' => 'required|in:Efectivo,Nequi',
        ]);

        $metodo = $request->input('metodo');

        if ($metodo === 'Nequi') {
            $intentosNequi = Pago::where('pedido_id', $pedido->id)
                ->where('metodo', 'Nequi')
                ->sum('intentos');
            if ($intentosNequi >= 3) {
                return back()->with('error', 'El pago por Nequi ha sido bloqueado tras 3 intentos fallidos. Por favor, selecciona Efectivo.');
            }

            $request->validate([
                'nequi_telefono' => ['required', 'regex:/^3\d{9}$/'],
                'nequi_correo' => 'required|email|max:150',
            ], [
                'nequi_telefono.required' => 'El número celular es obligatorio.',
                'nequi_telefono.regex' => 'El número celular de Nequi debe ser de 10 dígitos y comenzar con 3.',
                'nequi_correo.required' => 'El correo electrónico es obligatorio.',
                'nequi_correo.email' => 'Debes ingresar un correo electrónico válido.',
            ]);

            $pago = Pago::create([
                'pedido_id' => $pedido->id,
                'sucursal_id' => $pedido->sucursal_id,
                'metodo' => 'Nequi',
                'monto' => $pedido->total,
                'estado' => EstadoPago::PENDIENTE->value,
                'nequi_telefono' => $request->input('nequi_telefono'),
                'nequi_correo' => $request->input('nequi_correo'),
                'referencia' => 'NEQUI-' . strtoupper(Str::random(10)),
                'intentos' => 0,
            ]);

            $pedido->update(['metodo_pago' => 'Nequi']);

            return redirect()->route('cliente.pago.pendiente', ['pagoId' => $pago->id, 't' => $sesion->token]);
        } else {
            // Efectivo
            $pago = Pago::create([
                'pedido_id' => $pedido->id,
                'sucursal_id' => $pedido->sucursal_id,
                'metodo' => 'Efectivo',
                'monto' => $pedido->total,
                'estado' => EstadoPago::PENDIENTE->value,
                'referencia' => 'EFECTIVO-' . strtoupper(Str::random(10)),
                'intentos' => 0,
            ]);

            $pedido->update(['metodo_pago' => 'Efectivo']);

            if ($pedido->tipo === 'local') {
                return redirect()->route('cliente.pago.pendiente', ['pagoId' => $pago->id, 't' => $sesion->token]);
            } else {
                $pedido->update(['estado' => EstadoPedido::CREADO->value]);
                HistorialEstadoPedido::create([
                    'pedido_id' => $pedido->id,
                    'sucursal_id' => $pedido->sucursal_id,
                    'estado' => EstadoPedido::CREADO->value,
                ]);

                return redirect()->route('cliente.pago.pendiente', ['pagoId' => $pago->id, 't' => $sesion->token]);
            }
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
        $pedido = Pedido::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->where('sesion_cliente_id', $sesion->id)
            ->find($pedidoId);

        if (!$pedido || !in_array($pedido->estado, [EstadoPedido::PENDIENTE_PAGO->value, EstadoPedido::CREADO->value])) {
            return back()->with('error', 'No se puede cancelar este pedido.');
        }

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $pedido->update([
                'estado' => EstadoPedido::CANCELADO->value,
                'motivo_cancelacion' => 'Cancelado por el cliente.',
            ]);

            HistorialEstadoPedido::create([
                'pedido_id' => $pedido->id,
                'sucursal_id' => $pedido->sucursal_id,
                'estado' => EstadoPedido::CANCELADO->value,
            ]);

            $pagoCompletado = $pedido->pagos()
                ->where('estado', EstadoPago::COMPLETADO->value)
                ->first();

            if ($pagoCompletado) {
                $pagoCompletado->update([
                    'estado' => EstadoPago::REEMBOLSADO->value,
                    'reembolsado_en' => now(),
                ]);
                $pedido->update(['estado_pago' => EstadoPago::REEMBOLSADO->value]);
                
                $this->enviarEmailReembolso($pedido);
            }

            $this->enviarEmailCancelacion($pedido);

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->route('cliente.cancelacion.exitosa', ['t' => $sesion->token])
                ->with('success', 'Pedido cancelado con éxito.');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Error al cancelar el pedido.');
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
        $pago = Pago::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->with(['pedido' => function($q) {
                $q->withoutGlobalScope(\App\Scopes\TenantScope::class);
            }])
            ->find($pagoId);

        if (!$pago) {
            return back()->with('error', 'El pago no existe.');
        }

        $resultado = $request->input('resultado'); // 'approved', 'declined', 'timeout'
        $pedido = $pago->pedido;

        if ($resultado === 'approved') {
            $pago->update([
                'estado' => EstadoPago::COMPLETADO->value,
                'actualizado_en' => now(),
            ]);
            $pedido->update([
                'estado' => EstadoPedido::CREADO->value,
                'estado_pago' => EstadoPago::COMPLETADO->value,
                'pagado_en' => now(),
                'metodo_pago' => $pago->metodo,
            ]);

            HistorialEstadoPedido::create([
                'pedido_id' => $pedido->id,
                'sucursal_id' => $pedido->sucursal_id,
                'estado' => EstadoPedido::CREADO->value,
            ]);

            $this->enviarEmailPago($pago, EstadoPago::COMPLETADO->value);

            return redirect()->route('cliente.confirmacion', ['t' => $pedido->sesionCliente->token])
                ->with('success', 'Pago aprobado exitosamente.');

        } else {
            $pago->increment('intentos');
            $pago->update([
                'estado' => EstadoPago::FALLIDO->value,
                'ultimo_intento_en' => now(),
            ]);

            $this->enviarEmailPago($pago, EstadoPago::FALLIDO->value);

            if ($resultado === 'timeout') {
                session()->flash('pago_error', 'timeout');
            } else {
                session()->flash('pago_error', 'declined');
            }

            return redirect()->route('cliente.pago', ['t' => $pedido->sesionCliente->token])
                ->with('error', $resultado === 'timeout' ? 'La transacción con Nequi ha expirado por tiempo de espera.' : 'El pago fue rechazado por el banco.');
        }
    }
}
