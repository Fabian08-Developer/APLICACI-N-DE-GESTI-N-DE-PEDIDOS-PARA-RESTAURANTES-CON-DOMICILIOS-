<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('CREATE EXTENSION IF NOT EXISTS "pgcrypto";');
        }

        Schema::create('empresas', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('nit', 20)->unique();
            $table->string('tipo_nit', 10)->default('nit');
            $table->string('nombre', 150);
            $table->text('direccion')->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_en', 0)->nullable();
            $table->timestamp('actualizado_en', 0)->nullable();
            $table->timestamp('eliminado_en', 0)->nullable();
        });

        Schema::create('sucursales', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('empresa_id')->index();
            $table->string('nombre', 150);
            $table->string('slug', 100)->unique();
            $table->text('direccion')->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('logo', 255)->nullable();
            $table->json('configuracion')->nullable();
            $table->boolean('activo')->default(true);
            $table->time('hora_apertura')->default('08:00:00')->nullable();
            $table->time('hora_cierre')->default('22:00:00')->nullable();
            $table->timestamp('creado_en', 0)->nullable();
            $table->timestamp('actualizado_en', 0)->nullable();
            $table->timestamp('eliminado_en', 0)->nullable();

            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
        });

        Schema::create('usuarios', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('empresa_id')->nullable()->index();
            $table->uuid('sucursal_id')->nullable()->index();
            $table->string('nombre', 150);
            $table->string('correo', 255)->unique();
            $table->string('contrasena', 255);
            $table->string('documento', 30)->nullable();
            $table->string('tipo_documento', 15)->default('cc');
            $table->string('telefono', 30)->nullable();
            $table->boolean('activo')->default(true);
            $table->string('rol', 50)->default('mesero');
            $table->timestamp('correo_verificado_en', 0)->nullable();
            $table->timestamp('ultimo_acceso_en', 0)->nullable();
            $table->string('token_recuerdo', 100)->nullable();
            $table->timestamp('creado_en', 0)->nullable();
            $table->timestamp('actualizado_en', 0)->nullable();
            $table->timestamp('eliminado_en', 0)->nullable();

            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('set null');
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('set null');
        });

        Schema::create('tokens_verificacion', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('usuario_id');
            $table->string('token', 255)->unique();
            $table->string('tipo', 30)->default('verificacion_correo');
            $table->timestamp('expira_en', 0);
            $table->timestamp('usado_en', 0)->nullable();
            $table->timestamp('creado_en', 0)->nullable();

            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombre', 125);
            $table->string('guard_nombre', 125)->default('web');
            $table->timestamp('creado_en', 0)->nullable();
            $table->timestamp('actualizado_en', 0)->nullable();

            $table->unique(['nombre', 'guard_nombre']);
        });

        Schema::create('permisos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombre', 125);
            $table->string('guard_nombre', 125)->default('web');
            $table->timestamp('creado_en', 0)->nullable();
            $table->timestamp('actualizado_en', 0)->nullable();

            $table->unique(['nombre', 'guard_nombre']);
        });

        Schema::create('modelo_tiene_roles', function (Blueprint $table) {
            $table->bigInteger('rol_id');
            $table->string('modelo_tipo', 255);
            $table->uuid('modelo_id');
            
            $table->primary(['rol_id', 'modelo_id', 'modelo_tipo']);
            $table->foreign('rol_id')->references('id')->on('roles')->onDelete('cascade');
        });

        Schema::create('rol_tiene_permisos', function (Blueprint $table) {
            $table->bigInteger('permiso_id');
            $table->bigInteger('rol_id');

            $table->primary(['permiso_id', 'rol_id']);
            $table->foreign('permiso_id')->references('id')->on('permisos')->onDelete('cascade');
            $table->foreign('rol_id')->references('id')->on('roles')->onDelete('cascade');
        });

        Schema::create('tokens_acceso_personal', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tokenable_tipo', 255);
            $table->uuid('tokenable_id');
            $table->string('nombre', 255);
            $table->string('token', 64)->unique();
            $table->text('habilidades')->nullable();
            $table->timestamp('ultimo_uso_en', 0)->nullable();
            $table->timestamp('expira_en', 0)->nullable();
            $table->timestamp('creado_en', 0)->nullable();
            $table->timestamp('actualizado_en', 0)->nullable();

            $table->index(['tokenable_tipo', 'tokenable_id'], 'idx_tap_tokenable');
        });

        DB::table('roles')->insert([
            ['nombre' => 'super-admin', 'guard_nombre' => 'web', 'creado_en' => now(), 'actualizado_en' => now()],
            ['nombre' => 'gerente', 'guard_nombre' => 'web', 'creado_en' => now(), 'actualizado_en' => now()],
            ['nombre' => 'administrador', 'guard_nombre' => 'web', 'creado_en' => now(), 'actualizado_en' => now()],
            ['nombre' => 'cocina', 'guard_nombre' => 'web', 'creado_en' => now(), 'actualizado_en' => now()],
            ['nombre' => 'mesero', 'guard_nombre' => 'web', 'creado_en' => now(), 'actualizado_en' => now()],
            ['nombre' => 'domiciliario', 'guard_nombre' => 'web', 'creado_en' => now(), 'actualizado_en' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('tokens_acceso_personal');
        Schema::dropIfExists('rol_tiene_permisos');
        Schema::dropIfExists('modelo_tiene_roles');
        Schema::dropIfExists('permisos');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('tokens_verificacion');
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('sucursales');
        Schema::dropIfExists('empresas');
    }
};
