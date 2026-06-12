<?php

namespace App\Services\Cliente;

use App\Models\Pago;
use App\Models\Pedido;
use App\Models\HistorialEstadoPedido;
use App\Enums\EstadoPago;
use App\Enums\EstadoPedido;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Traits\OrderEmailNotifications;

class PagoService
{
    use OrderEmailNotifications;

    public function procesarPago($sesion, $pedido, $metodo, array $datosPago = [])
    {
        if ($metodo === 'Nequi') {
            $intentosNequi = Pago::where('pedido_id', $pedido->id)
                ->where('metodo', 'Nequi')
                ->sum('intentos');

            if ($intentosNequi >= 3) {
                throw new \Exception('El pago por Nequi ha sido bloqueado tras 3 intentos fallidos. Por favor, selecciona Efectivo.');
            }

            if (empty($datosPago['nequi_telefono']) || empty($datosPago['nequi_correo'])) {
                throw new \Exception('Datos de Nequi incompletos.');
            }

            try {
                DB::beginTransaction();

                $pago = Pago::create([
                    'pedido_id' => $pedido->id,
                    'sucursal_id' => $pedido->sucursal_id,
                    'metodo' => 'Nequi',
                    'monto' => $pedido->total,
                    'estado' => EstadoPago::PENDIENTE->value,
                    'nequi_telefono' => $datosPago['nequi_telefono'],
                    'nequi_correo' => $datosPago['nequi_correo'],
                    'referencia' => 'NEQUI-' . strtoupper(Str::random(10)),
                    'intentos' => 0,
                ]);

                $pedido->update(['metodo_pago' => 'Nequi']);

                DB::commit();
                return $pago;
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } else {
            // Efectivo
            try {
                DB::beginTransaction();

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

                if ($pedido->tipo !== 'local') {
                    $pedido->update(['estado' => EstadoPedido::CREADO->value]);
                    HistorialEstadoPedido::create([
                        'pedido_id' => $pedido->id,
                        'sucursal_id' => $pedido->sucursal_id,
                        'estado' => EstadoPedido::CREADO->value,
                    ]);
                }

                DB::commit();
                return $pago;
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        }
    }

    public function simularConfirmacion($pagoId, $resultado)
    {
        $pago = Pago::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->with(['pedido' => function($q) {
                $q->withoutGlobalScope(\App\Scopes\TenantScope::class);
            }])
            ->lockForUpdate() // Prevenir race conditions
            ->find($pagoId);

        if (!$pago) {
            throw new \Exception('El pago no existe.');
        }

        $pedido = $pago->pedido;

        try {
            DB::beginTransaction();

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

                DB::commit();
                return ['success' => true, 'pedido' => $pedido];

            } else {
                $pago->increment('intentos');
                $pago->update([
                    'estado' => EstadoPago::FALLIDO->value,
                    'ultimo_intento_en' => now(),
                ]);

                $this->enviarEmailPago($pago, EstadoPago::FALLIDO->value);

                DB::commit();
                return [
                    'success' => false, 
                    'pedido' => $pedido,
                    'error_type' => $resultado === 'timeout' ? 'timeout' : 'declined',
                    'message' => $resultado === 'timeout' ? 'La transacción con Nequi ha expirado por tiempo de espera.' : 'El pago fue rechazado por el banco.'
                ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
