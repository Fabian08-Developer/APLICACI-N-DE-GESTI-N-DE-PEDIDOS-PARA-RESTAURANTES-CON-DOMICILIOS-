<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zonas_cobertura', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('sucursal_id')->index();
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->decimal('costo_envio', 10, 2)->default(0);
            $table->integer('tiempo_estimado')->default(30);
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_en', 0)->nullable();
            $table->timestamp('actualizado_en', 0)->nullable();

            $table->unique(['sucursal_id', 'nombre']);
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('cascade');
        });

        Schema::create('barrios', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('zona_id');
            $table->uuid('sucursal_id');
            $table->string('nombre', 150);
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_en', 0)->nullable();
            $table->timestamp('actualizado_en', 0)->nullable();

            $table->foreign('zona_id')->references('id')->on('zonas_cobertura')->onDelete('cascade');
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('cascade');
        });

        Schema::create('perfiles_domiciliario', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('usuario_id')->unique();
            $table->uuid('sucursal_id');
            $table->uuid('zona_id')->nullable();
            $table->string('tipo_vehiculo', 30)->default('moto');
            $table->string('placa', 20)->nullable();
            $table->string('documento', 30)->nullable();
            $table->string('estado', 30)->default('disponible');
            $table->decimal('efectivo_pendiente', 10, 2)->default(0);
            $table->decimal('limite_efectivo', 10, 2)->default(200000);
            $table->decimal('calificacion', 3, 2)->default(5.00);
            $table->integer('pedidos_hoy')->default(0);
            $table->integer('pedidos_totales')->default(0);
            $table->timestamp('creado_en', 0)->nullable();
            $table->timestamp('actualizado_en', 0)->nullable();

            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('cascade');
            $table->foreign('zona_id')->references('id')->on('zonas_cobertura')->onDelete('set null');
        });

        Schema::create('barrio_domiciliario', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('perfil_domiciliario_id');
            $table->uuid('barrio_id');
            $table->timestamp('creado_en', 0)->nullable();

            $table->unique(['perfil_domiciliario_id', 'barrio_id']);
            $table->foreign('perfil_domiciliario_id')->references('id')->on('perfiles_domiciliario')->onDelete('cascade');
            $table->foreign('barrio_id')->references('id')->on('barrios')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barrio_domiciliario');
        Schema::dropIfExists('perfiles_domiciliario');
        Schema::dropIfExists('barrios');
        Schema::dropIfExists('zonas_cobertura');
    }
};
