<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tabla pivote Sede × Barrio → Tarifa de envío.
     *
     * Resuelve el problema de cobertura y tarifas dinámicas:
     * cada sede puede asignar un costo y tiempo de entrega propio
     * a cada barrio que cubre, independientemente de las demás sedes.
     */
    public function up(): void
    {
        Schema::create('sucursal_barrio_tarifas', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('sucursal_id')->index();
            $table->uuid('barrio_id')->index();
            $table->decimal('costo_envio', 10, 2)->default(0);
            $table->integer('tiempo_estimado')->default(30)->comment('Minutos estimados de entrega');
            $table->boolean('activo')->default(true)->comment('Si esta sede cubre activamente este barrio');
            $table->timestamp('creado_en', 0)->nullable();
            $table->timestamp('actualizado_en', 0)->nullable();

            // Una sede solo puede tener UNA tarifa por barrio
            $table->unique(['sucursal_id', 'barrio_id']);

            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('cascade');
            $table->foreign('barrio_id')->references('id')->on('barrios')->onDelete('cascade');
        });

        if (DB::getDriverName() === 'pgsql') {
            // Poblar la pivote con los datos existentes:
            // Para cada barrio, crear una entrada en la pivote con el costo_envio
            // de su zona de cobertura como valor inicial.
            DB::statement("
                INSERT INTO sucursal_barrio_tarifas (id, sucursal_id, barrio_id, costo_envio, tiempo_estimado, activo, creado_en, actualizado_en)
                SELECT gen_random_uuid(), b.sucursal_id, b.id, z.costo_envio, z.tiempo_estimado, b.activo, NOW(), NOW()
                FROM barrios b
                JOIN zonas_cobertura z ON z.id = b.zona_id
                WHERE b.sucursal_id IS NOT NULL
                ON CONFLICT (sucursal_id, barrio_id) DO NOTHING
            ");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sucursal_barrio_tarifas');
    }
};
