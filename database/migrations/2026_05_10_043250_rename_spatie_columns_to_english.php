<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Renombrar en tabla roles
        if (Schema::hasColumn('roles', 'nombre')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->renameColumn('nombre', 'name');
                $table->renameColumn('guard_nombre', 'guard_name');
            });
        }

        // Renombrar en tabla permisos
        if (Schema::hasColumn('permisos', 'nombre')) {
            Schema::table('permisos', function (Blueprint $table) {
                $table->renameColumn('nombre', 'name');
                $table->renameColumn('guard_nombre', 'guard_name');
            });
        }

        // Renombrar en tabla modelo_tiene_roles (polimorfismo que busca Spatie)
        if (Schema::hasColumn('modelo_tiene_roles', 'modelo_tipo')) {
            Schema::table('modelo_tiene_roles', function (Blueprint $table) {
                $table->renameColumn('modelo_tipo', 'model_type');
                $table->renameColumn('modelo_id', 'model_id');
            });
        }
        
        // Si hay un script inicial de roles (como lo pusimos en el SQL original) que haya fallado,
        // lo hacemos de manera limpia para asegurarnos que exista usando DB facade pero con 'name'
        DB::table('roles')->updateOrInsert(
            ['name' => 'gerente', 'guard_name' => 'web'],
            ['creado_en' => now(), 'actualizado_en' => now()]
        );
        DB::table('roles')->updateOrInsert(
            ['name' => 'administrador', 'guard_name' => 'web'],
            ['creado_en' => now(), 'actualizado_en' => now()]
        );
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->renameColumn('name', 'nombre');
            $table->renameColumn('guard_name', 'guard_nombre');
        });

        Schema::table('permisos', function (Blueprint $table) {
            $table->renameColumn('name', 'nombre');
            $table->renameColumn('guard_name', 'guard_nombre');
        });

        Schema::table('modelo_tiene_roles', function (Blueprint $table) {
            $table->renameColumn('model_type', 'modelo_tipo');
            $table->renameColumn('model_id', 'modelo_id');
        });
    }
};
