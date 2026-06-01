<?php

namespace App\Http\Controllers\Mesero;

use App\Enums\EstadoPago;
use App\Enums\EstadoPedido;
use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\DetallePedido;
use App\Models\HistorialEstadoPedido;
use App\Models\ItemCarrito;
use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\SesionCliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PedidoController extends Controller
{
    public function seleccionarMesa()
    {
        $sucursalId = auth()->user()->sucursal_id;
        
        $mesas = Mesa::where('sucursal_id', $sucursalId)
            ->orderBy('numero')
            ->get();
            
        // Obtener estado real de sesiones para mostrar si está ocupada
        $mesas->each(function ($mesa) {
            $mesa->tiene_sesion = SesionCliente::where('mesa_id', $mesa->id)
                ->where('activo', true)
                ->exists();
        });

        return view('mesero.tomar-pedido.seleccionar-mesa', compact('mesas'));
    }

    private function getOrCreateSesion(Mesa $mesa)
    {
        $sesionActiva = SesionCliente::where('mesa_id', $mesa->id)
            ->where('activo', true)
            ->latest()
            ->first();

        if ($sesionActiva) {
            return $sesionActiva;
        }

        $token = Str::random(40);
        $sesion = SesionCliente::create([
            'sucursal_id' => $mesa->sucursal_id,
            'mesa_id' => $mesa->id,
            'zona_id' => $mesa->zona_id ?? null,
            'token' => $token,
            'tipo' => 'local',
            'nombre_cliente' => 'Cliente Mesa ' . $mesa->numero . ' (Asistido por Mesero)',
            'activo' => true,
        ]);

        $mesa->ocupar();

        return $sesion;
    }

    public function menuMesa($mesaId)
    {
        $mesa = Mesa::findOrFail($mesaId);

        if ($mesa->sucursal_id !== auth()->user()->sucursal_id) {
            abort(403);
        }

        $sesion = $this->getOrCreateSesion($mesa);

        $categorias = Categoria::activo()
            ->orderBy('orden')
            ->get();

        $productos = Producto::activoConCategoriaActiva()
            ->where('disponible', true)
            ->with(['variantes', 'adicionesAsociadas'])
            ->get();

        $itemsCarrito = $sesion->itemsCarrito()->with('producto')->get();

        return view('mesero.tomar-pedido.menu', compact('mesa', 'sesion', 'categorias', 'productos', 'itemsCarrito'));
    }

    public function agregarAlCarrito(Request $request, $mesaId)
    {
        try {
            $mesa = Mesa::findOrFail($mesaId);

            if ($mesa->sucursal_id !== auth()->user()->sucursal_id) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            $sesion = $this->getOrCreateSesion($mesa);

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
            ->with(['variantes', 'adicionesAsociadas'])
            ->first();

        if (!$producto) {
            return response()->json(['error' => 'El producto no está disponible.'], 422);
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
                    return response()->json(['error' => "La variante '{$variante->nombre}' es obligatoria."], 422);
                }
            }
        }

        // 2. Validar límites de adiciones
        $adicionesElegidas = $validated['adiciones_elegidas'] ?? [];
        $cantAdiciones = count($adicionesElegidas);
        if ($producto->limite_minimo_adiciones > 0 && $cantAdiciones < $producto->limite_minimo_adiciones) {
            return response()->json(['error' => "Debes seleccionar al menos {$producto->limite_minimo_adiciones} adiciones."], 422);
        }
        if ($producto->limite_maximo_adiciones !== null && $cantAdiciones > $producto->limite_maximo_adiciones) {
            return response()->json(['error' => "No puedes seleccionar más de {$producto->limite_maximo_adiciones} adiciones."], 422);
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

        foreach ($variantesFormateadas as $vf) {
            if ($vf['tipo_impacto'] === 'incremental') {
                $baseCalculada += $vf['precio'];
            }
        }

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

        $existingItems = $sesion->itemsCarrito()->where('producto_id', $producto->id)->get();
        $duplicateItem = null;

        foreach ($existingItems as $item) {
            $vDB = $item->variantes_elegidas ?? [];
            $aDB = $item->adiciones_elegidas ?? [];
            $nDB = $item->notas;

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

            return response()->json([
                'success' => true,
                'message' => 'Producto agregado al carrito.',
                'item' => $itemGuardado,
                'cart_total' => (float) $sesion->itemsCarrito()->sum('subtotal'),
                'cart_count' => (int) $sesion->itemsCarrito()->sum('cantidad'),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Validación fallida: ' . json_encode($e->errors())], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage() . ' - Línea: ' . $e->getLine()], 500);
        }
    }

    public function actualizarCantidadCarrito(Request $request, $mesaId, $id)
    {
        $mesa = Mesa::findOrFail($mesaId);

        if ($mesa->sucursal_id !== auth()->user()->sucursal_id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $sesion = $this->getOrCreateSesion($mesa);
        
        $validated = $request->validate([
            'cantidad' => 'required|integer|min:1',
        ]);

        $item = $sesion->itemsCarrito()->where('id', $id)->first();

        if (!$item) {
            return response()->json(['error' => 'El item del carrito no existe.'], 404);
        }

        $item->cantidad = (int) $validated['cantidad'];
        $item->subtotal = $item->precio_unitario * $item->cantidad;
        $item->save();

        return response()->json([
            'success' => true,
            'message' => 'Cantidad actualizada.',
            'item' => $item,
            'cart_total' => (float) $sesion->itemsCarrito()->sum('subtotal'),
            'cart_count' => (int) $sesion->itemsCarrito()->sum('cantidad'),
        ]);
    }

    public function eliminarDelCarrito(Request $request, $mesaId, $id)
    {
        $mesa = Mesa::findOrFail($mesaId);

        if ($mesa->sucursal_id !== auth()->user()->sucursal_id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $sesion = $this->getOrCreateSesion($mesa);

        $item = $sesion->itemsCarrito()->where('id', $id)->first();

        if (!$item) {
            return response()->json(['error' => 'El item del carrito no existe.'], 404);
        }

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item eliminado del carrito.',
            'cart_total' => (float) $sesion->itemsCarrito()->sum('subtotal'),
            'cart_count' => (int) $sesion->itemsCarrito()->sum('cantidad'),
        ]);
    }

    public function confirmarPedido(Request $request, $mesaId)
    {
        $mesa = Mesa::findOrFail($mesaId);

        if ($mesa->sucursal_id !== auth()->user()->sucursal_id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $sesion = $this->getOrCreateSesion($mesa);
        
        $items = $sesion->itemsCarrito()->with('producto')->get();
        if ($items->isEmpty()) {
            return response()->json(['error' => 'El carrito está vacío.'], 422);
        }

        try {
            DB::beginTransaction();

            $subtotal = $items->sum('subtotal');
            $costoEnvio = 0.00; // Local
            $total = $subtotal + $costoEnvio;

            // Directamente CREADO y pago en EFECTIVO PENDIENTE para que el mesero lo confirme cuando cobre
            $pedido = Pedido::create([
                'sucursal_id' => $sesion->sucursal_id,
                'sesion_cliente_id' => $sesion->id,
                'zona_id' => $sesion->zona_id,
                'tipo' => $sesion->tipo,
                'estado' => EstadoPedido::CREADO->value, // Envío directo a cocina
                'estado_pago' => EstadoPago::PENDIENTE->value,
                'metodo_pago' => 'Efectivo',
                'mesero_id' => auth()->id(), // Mesero que tomó el pedido
                'direccion_entrega' => null,
                'subtotal' => $subtotal,
                'costo_envio' => $costoEnvio,
                'total' => $total,
            ]);

            $sesion->update(['mesero_id' => auth()->id()]);

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
                'estado' => EstadoPedido::CREADO->value,
            ]);

            $sesion->itemsCarrito()->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pedido enviado a cocina con éxito.',
                'redirigir' => route('mesero.dashboard'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Ocurrió un error al confirmar el pedido: ' . $e->getMessage()], 500);
        }
    }
}
