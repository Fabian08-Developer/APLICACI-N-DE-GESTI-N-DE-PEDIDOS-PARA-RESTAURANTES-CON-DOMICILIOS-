<?php

namespace App\Services\Cliente;

use App\Models\Producto;
use App\Models\ItemCarrito;

class CarritoService
{
    public function agregarAlCarrito($sesion, array $validated)
    {
        $producto = Producto::activoConCategoriaActiva()
            ->where('disponible', true)
            ->where('id', $validated['producto_id'])
            ->with(['variantes', 'adiciones'])
            ->first();

        if (!$producto) {
            throw new \Exception('El producto no está disponible.');
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
                    throw new \Exception("La variante '{$variante->nombre}' es obligatoria.");
                }
            }
        }

        // 2. Validar límites de adiciones
        $adicionesElegidas = $validated['adiciones_elegidas'] ?? [];
        $cantAdiciones = count($adicionesElegidas);
        if ($producto->limite_minimo_adiciones > 0 && $cantAdiciones < $producto->limite_minimo_adiciones) {
            throw new \Exception("Debes seleccionar al menos {$producto->limite_minimo_adiciones} adiciones.");
        }
        if ($producto->limite_maximo_adiciones !== null && $cantAdiciones > $producto->limite_maximo_adiciones) {
            throw new \Exception("No puedes seleccionar más de {$producto->limite_maximo_adiciones} adiciones.");
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
            return $duplicateItem;
        } else {
            return ItemCarrito::create([
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
    }

    public function actualizarCantidad($sesion, $id, $cantidad)
    {
        $item = $sesion->itemsCarrito()->where('id', $id)->first();
        if (!$item) {
            throw new \Exception('El item del carrito no existe.');
        }

        $item->cantidad = (int) $cantidad;
        $item->subtotal = $item->precio_unitario * $item->cantidad;
        $item->save();

        return $item;
    }

    public function eliminarDelCarrito($sesion, $id)
    {
        $item = $sesion->itemsCarrito()->where('id', $id)->first();
        if (!$item) {
            throw new \Exception('El item del carrito no existe.');
        }

        $item->delete();
        return true;
    }
}
