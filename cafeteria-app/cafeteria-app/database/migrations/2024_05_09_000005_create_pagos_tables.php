<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('pedido_id')->index();
            $table->uuid('sucursal_id')->index();
            $table->string('metodo', 20);
            $table->decimal('monto', 10, 2);
            $table->string('estado', 20)->default('pendiente');
            $table->string('nequi_telefono', 20)->nullable();
            $table->string('nequi_correo', 150)->nullable();
            $table->string('referencia', 255)->nullable();
            $table->integer('intentos')->default(0);
            $table->timestamp('ultimo_intento_en', 0)->nullable();
            $table->timestamp('reembolsado_en', 0)->nullable();
            $table->timestamp('creado_en', 0)->nullable();
            $table->timestamp('actualizado_en', 0)->nullable();

            $table->foreign('pedido_id')->references('id')->on('pedidos')->onDelete('cascade');
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
