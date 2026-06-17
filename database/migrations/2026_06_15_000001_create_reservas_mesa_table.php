<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservas_mesa', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('sucursal_id')->index();
            $table->uuid('mesa_id')->nullable()->index(); // nullable: asignación puede ser automática
            $table->uuid('mesero_id')->nullable()->index(); // mesero asignado al check-in
            $table->uuid('sesion_cliente_id')->nullable(); // se llena al hacer check-in

            // Código legible (ej. RES-A1B2C3)
            $table->string('codigo_reserva', 12)->unique();

            // Datos del cliente (sin requerir cuenta)
            $table->string('nombre_cliente', 150);
            $table->string('telefono_cliente', 30);
            $table->string('correo_cliente', 150);
            $table->integer('numero_personas');
            $table->text('notas_cliente')->nullable();
            $table->text('notas_internas')->nullable();

            // Fecha y hora
            $table->date('fecha_reserva')->index();
            $table->time('hora_inicio');
            $table->time('hora_fin'); // Calculado: hora_inicio + duracion_turno

            // Estado del ciclo de vida
            $table->string('estado', 30)->default('pendiente_pago')->index();
            // pendiente_pago | pendiente | confirmada | cliente_llego | completada | cancelada | no_show

            // Depósito de garantía
            $table->decimal('monto_deposito', 10, 2)->default(0);
            $table->boolean('deposito_pagado')->default(false);
            $table->timestamp('deposito_pagado_en', 0)->nullable();

            // Cancelación
            $table->string('cancelado_por', 20)->nullable(); // cliente | restaurante
            $table->text('motivo_cancelacion')->nullable();

            // Timestamps de eventos clave
            $table->timestamp('confirmado_en', 0)->nullable();
            $table->timestamp('cliente_llego_en', 0)->nullable();
            $table->timestamp('completado_en', 0)->nullable();
            $table->timestamp('cancelado_en', 0)->nullable();
            $table->timestamp('creado_en', 0)->nullable();
            $table->timestamp('actualizado_en', 0)->nullable();

            // Foreign keys
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('cascade');
            $table->foreign('mesa_id')->references('id')->on('mesas')->onDelete('set null');
            $table->foreign('mesero_id')->references('id')->on('usuarios')->onDelete('set null');
            $table->foreign('sesion_cliente_id')->references('id')->on('sesiones_cliente')->onDelete('set null');

            // Índice compuesto para búsquedas de disponibilidad
            $table->index(['mesa_id', 'fecha_reserva', 'estado'], 'idx_disponibilidad');
            $table->index(['sucursal_id', 'fecha_reserva', 'estado'], 'idx_reservas_dia');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas_mesa');
    }
};
