<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('calificaciones_domiciliario', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('pedido_id')->nullable()->index();
            $table->uuid('perfil_domiciliario_id')->index();
            $table->uuid('cliente_id')->nullable()->index();
            $table->integer('puntuacion')->default(5); // 1 to 5
            $table->text('comentario')->nullable();
            $table->timestamp('creado_en', 0)->useCurrent();
            $table->timestamp('actualizado_en', 0)->useCurrent();

            $table->foreign('pedido_id')->references('id')->on('pedidos')->onDelete('set null');
            $table->foreign('perfil_domiciliario_id', 'calif_perfil_fk')->references('id')->on('perfiles_domiciliario')->onDelete('cascade');
            $table->foreign('cliente_id')->references('id')->on('usuarios')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calificaciones_domiciliario');
    }
};
