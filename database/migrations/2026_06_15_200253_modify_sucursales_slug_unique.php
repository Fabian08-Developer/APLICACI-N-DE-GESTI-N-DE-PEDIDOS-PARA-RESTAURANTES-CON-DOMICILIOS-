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
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('sucursales', function (Blueprint $table) {
                // Drop the old global unique index
                $table->dropUnique('sucursales_slug_unique');
                // Add a new composite unique index
                $table->unique(['empresa_id', 'slug'], 'sucursales_empresa_slug_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sucursales', function (Blueprint $table) {
            $table->dropUnique('sucursales_empresa_slug_unique');
            $table->unique('slug', 'sucursales_slug_unique');
        });
    }
};
