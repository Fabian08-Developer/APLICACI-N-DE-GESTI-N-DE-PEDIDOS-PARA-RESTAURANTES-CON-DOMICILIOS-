<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Tabla dedicada para los depósitos de garantía de reservas.
 * Se mantiene separada de `pagos` (que pertenece a pedidos de cocina).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos_reserva', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('reserva_id')->index();
            $table->uuid('sucursal_id')->index();

            // Montos
            $table->decimal('monto', 10, 2);       // Valor del depósito cobrado
            $table->decimal('monto_devuelto', 10, 2)->default(0); // Para reembolsos

            // Método de pago (reutiliza los mismos métodos que el sistema de pedidos)
            $table->string('metodo', 20);           // efectivo | nequi | transferencia | tarjeta

            // Estado del pago
            $table->string('estado', 20)->default('pendiente');
            // pendiente | aprobado | rechazado | reembolsado

            // Datos adicionales según método
            $table->string('nequi_telefono', 20)->nullable();
            $table->string('nequi_correo', 150)->nullable();
            $table->string('referencia', 255)->nullable();        // Comprobante / referencia bancaria
            $table->string('referencia_externa', 255)->nullable(); // Para pasarela de pagos futura

            // Control de intentos y tiempos
            $table->integer('intentos')->default(0);
            $table->timestamp('ultimo_intento_en', 0)->nullable();
            $table->timestamp('aprobado_en', 0)->nullable();
            $table->timestamp('reembolsado_en', 0)->nullable();

            // Notas internas (ej. "cliente pagó en efectivo en recepción")
            $table->text('notas')->nullable();

            $table->timestamp('creado_en', 0)->nullable();
            $table->timestamp('actualizado_en', 0)->nullable();

            $table->foreign('reserva_id')->references('id')->on('reservas_mesa')->onDelete('cascade');
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_reserva');
    }
};
