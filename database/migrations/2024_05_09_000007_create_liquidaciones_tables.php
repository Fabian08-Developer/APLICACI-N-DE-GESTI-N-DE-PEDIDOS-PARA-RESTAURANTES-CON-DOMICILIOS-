<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('liquidaciones_domiciliario', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('perfil_domiciliario_id')->index();
            $table->uuid('sucursal_id')->index();
            $table->uuid('aprobado_por');
            $table->decimal('monto', 10, 2);
            $table->string('estado', 20)->default('pendiente');
            $table->string('ruta_comprobante', 255)->nullable();
            $table->text('notas')->nullable();
            $table->timestamp('liquidado_en', 0)->nullable();
            $table->timestamp('creado_en', 0)->nullable();
            $table->timestamp('actualizado_en', 0)->nullable();

            $table->foreign('perfil_domiciliario_id', 'liq_perfil_fk')->references('id')->on('perfiles_domiciliario')->onDelete('cascade');
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('cascade');
            $table->foreign('aprobado_por')->references('id')->on('usuarios')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('liquidaciones_domiciliario');
    }
};
