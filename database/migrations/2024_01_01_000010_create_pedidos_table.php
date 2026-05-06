<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_sesion_id')->constrained('sub_sesiones')->cascadeOnDelete();
            $table->foreignId('mesero_id')->constrained('usuarios');
            $table->string('estado', 30)->default('CREADO');
            $table->decimal('total', 10, 2)->nullable();
            $table->timestamp('fecha_cancelacion')->nullable();
            $table->string('motivo_cancelacion', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
