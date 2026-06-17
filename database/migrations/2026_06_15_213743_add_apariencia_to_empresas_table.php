<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Columna JSON para almacenar la personalización visual
     * de la tienda pública de cada empresa (Micro-CMS).
     */
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->json('apariencia')->nullable()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn('apariencia');
        });
    }
};

