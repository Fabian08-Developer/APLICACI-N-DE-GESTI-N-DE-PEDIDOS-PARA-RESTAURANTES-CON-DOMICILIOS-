<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->cascadeOnDelete();
            $table->string('metodo_pago', 20);
            $table->decimal('monto', 10, 2);
            $table->string('estado', 20)->default('PENDIENTE');
            $table->string('referencia_transaccion', 255)->nullable();
            $table->timestamp('fecha_reembolso')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
