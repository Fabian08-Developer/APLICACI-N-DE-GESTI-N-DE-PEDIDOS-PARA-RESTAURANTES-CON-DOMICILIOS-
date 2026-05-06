<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domiciliarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('telefono');
            $table->string('email')->nullable();
            $table->enum('vehiculo_tipo', ['moto', 'bicicleta', 'carro'])->default('moto');
            $table->string('placa')->nullable();
            $table->string('documento')->nullable();
            $table->decimal('calificacion', 3, 2)->default(5.00);
            $table->foreignId('zona_id')->constrained('zona_coberturas')->onDelete('cascade');
            $table->enum('estado', ['disponible', 'en_ruta', 'ocupado', 'fuera_servicio'])->default('disponible');
            $table->integer('pedidos_hoy')->default(0);
            $table->integer('pedidos_totales')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domiciliarios');
    }
};
