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
        Schema::create('modelo_tiene_permisos', function (Blueprint $table) {
            $table->bigInteger('permiso_id');
            $table->string('model_type', 255);
            $table->uuid('model_id');
            
            $table->index(['model_id', 'model_type'], 'model_has_permissions_model_id_model_type_index');

            $table->primary(['permiso_id', 'model_id', 'model_type']);
            $table->foreign('permiso_id')->references('id')->on('permisos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modelo_tiene_permisos');
    }
};
