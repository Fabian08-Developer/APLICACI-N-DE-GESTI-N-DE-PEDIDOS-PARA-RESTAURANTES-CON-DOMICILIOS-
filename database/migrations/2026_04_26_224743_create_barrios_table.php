<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barrios', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('nombre');
            $blueprint->foreignId('zona_id')->constrained('zona_coberturas')->onDelete('cascade');
            $blueprint->boolean('activo')->default(true);
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barrios');
    }
};
