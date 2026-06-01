<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programacion_reportes', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('sucursal_id');
            $table->boolean('activo')->default(true);
            $table->string('frecuencia', 20);
            $table->time('hora_envio');
            $table->json('dias')->nullable();
            $table->json('dias_mes')->nullable();
            $table->string('metodo', 20)->default('correo');
            $table->json('destinatarios')->nullable();
            $table->string('numero_whatsapp', 30)->nullable();
            $table->json('secciones')->nullable();
            $table->timestamp('ultimo_envio_en', 0)->nullable();
            $table->timestamp('proximo_envio_en', 0)->nullable();
            $table->timestamp('creado_en', 0)->nullable();
            $table->timestamp('actualizado_en', 0)->nullable();

            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('cascade');
        });

        Schema::create('notificaciones', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('usuario_id')->index();
            $table->string('tipo', 50)->nullable();
            $table->string('titulo', 150)->nullable();
            $table->text('mensaje')->nullable();
            $table->json('datos')->nullable();
            $table->boolean('leida')->default(false);
            $table->timestamp('creado_en', 0)->nullable();
            $table->timestamp('actualizado_en', 0)->nullable();

            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->index(['usuario_id', 'leida'], 'idx_notificaciones_leida');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
        Schema::dropIfExists('programacion_reportes');
    }
};
