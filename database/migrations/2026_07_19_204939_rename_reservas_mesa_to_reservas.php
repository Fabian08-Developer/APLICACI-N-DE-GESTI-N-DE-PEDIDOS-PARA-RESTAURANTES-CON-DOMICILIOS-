<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('reservas_mesa', 'reservas');
    }

    public function down(): void
    {
        Schema::rename('reservas', 'reservas_mesa');
    }
};
