<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->boolean('permite_notas')->default(true);
            $table->integer('limite_minimo_adiciones')->default(0);
            $table->integer('limite_maximo_adiciones')->nullable();
        });

        Schema::create('adiciones_catalogo', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('sucursal_id')->index();
            $table->string('nombre', 100);
            $table->decimal('precio', 10, 2)->default(0.00);
            $table->boolean('activo')->default(true);
            $table->boolean('disponible')->default(true);
            $table->timestamp('creado_en', 0)->nullable();
            $table->timestamp('actualizado_en', 0)->nullable();

            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('cascade');
        });

        Schema::create('adicion_categoria', function (Blueprint $table) {
            $table->uuid('adicion_id');
            $table->uuid('categoria_id');
            $table->primary(['adicion_id', 'categoria_id']);

            $table->foreign('adicion_id')->references('id')->on('adiciones_catalogo')->onDelete('cascade');
            $table->foreign('categoria_id')->references('id')->on('categorias')->onDelete('cascade');
        });

        Schema::create('adicion_producto', function (Blueprint $table) {
            $table->uuid('adicion_id');
            $table->uuid('producto_id');
            $table->primary(['adicion_id', 'producto_id']);

            $table->foreign('adicion_id')->references('id')->on('adiciones_catalogo')->onDelete('cascade');
            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adicion_producto');
        Schema::dropIfExists('adicion_categoria');
        Schema::dropIfExists('adiciones_catalogo');

        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn(['permite_notas', 'limite_minimo_adiciones', 'limite_maximo_adiciones']);
        });
    }
};
