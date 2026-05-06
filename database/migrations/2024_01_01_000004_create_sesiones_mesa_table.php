<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sesiones_mesa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mesa_id')->constrained('mesas');
            $table->string('estado', 20)->default('ACTIVA');
            $table->timestamp('fecha_inicio')->useCurrent();
            $table->timestamp('fecha_cierre')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sesiones_mesa');
    }
};
