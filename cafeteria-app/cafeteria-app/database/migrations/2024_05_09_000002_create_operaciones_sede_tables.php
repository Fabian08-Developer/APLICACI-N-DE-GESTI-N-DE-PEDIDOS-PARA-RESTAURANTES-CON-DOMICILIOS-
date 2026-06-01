<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mesas', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('sucursal_id')->index();
            $table->integer('numero');
            $table->integer('capacidad')->default(4);
            $table->string('estado', 30)->default('disponible');
            $table->string('codigo_qr', 255)->nullable();
            $table->boolean('qr_activo')->default(true);
            $table->timestamp('creado_en', 0)->nullable();
            $table->timestamp('actualizado_en', 0)->nullable();

            $table->unique(['sucursal_id', 'numero']);
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('cascade');
        });

        Schema::create('categorias', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('sucursal_id')->index();
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->string('icono', 100)->nullable();
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_en', 0)->nullable();
            $table->timestamp('actualizado_en', 0)->nullable();

            $table->unique(['sucursal_id', 'nombre']);
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('cascade');
        });

        Schema::create('productos', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('sucursal_id')->index();
            $table->uuid('categoria_id')->nullable()->index();
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2);
            $table->decimal('precio_oferta', 10, 2)->nullable();
            $table->string('imagen', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->boolean('disponible')->default(true);
            $table->timestamp('creado_en', 0)->nullable();
            $table->timestamp('actualizado_en', 0)->nullable();
            $table->timestamp('eliminado_en', 0)->nullable();

            $table->unique(['sucursal_id', 'nombre']);
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('cascade');
            $table->foreign('categoria_id')->references('id')->on('categorias')->onDelete('set null');
        });

        Schema::create('variantes_producto', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('producto_id');
            $table->string('nombre', 100);
            $table->json('opciones');
            $table->boolean('obligatorio')->default(true);
            $table->timestamp('creado_en', 0)->nullable();
            $table->timestamp('actualizado_en', 0)->nullable();

            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('cascade');
        });

        Schema::create('adiciones_producto', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('producto_id');
            $table->string('nombre', 100);
            $table->decimal('precio', 10, 2)->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_en', 0)->nullable();
            $table->timestamp('actualizado_en', 0)->nullable();

            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adiciones_producto');
        Schema::dropIfExists('variantes_producto');
        Schema::dropIfExists('productos');
        Schema::dropIfExists('categorias');
        Schema::dropIfExists('mesas');
    }
};
