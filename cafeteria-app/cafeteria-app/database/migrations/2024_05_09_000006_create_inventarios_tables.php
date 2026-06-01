<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventarios', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('producto_id');
            $table->uuid('sucursal_id');
            $table->integer('stock_actual')->default(0);
            $table->integer('stock_minimo')->default(5)->nullable();
            $table->timestamp('creado_en', 0)->nullable();
            $table->timestamp('actualizado_en', 0)->nullable();

            $table->unique(['producto_id', 'sucursal_id']);
            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('cascade');
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('cascade');
        });

        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('inventario_id');
            $table->string('tipo', 20);
            $table->integer('cantidad');
            $table->string('motivo', 255)->nullable();
            $table->timestamp('movido_en', 0)->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('creado_en', 0)->nullable();

            $table->foreign('inventario_id')->references('id')->on('inventarios')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
        Schema::dropIfExists('inventarios');
    }
};
