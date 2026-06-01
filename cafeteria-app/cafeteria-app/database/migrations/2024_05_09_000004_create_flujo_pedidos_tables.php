<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sesiones_cliente', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('sucursal_id')->index();
            $table->uuid('mesa_id')->nullable();
            $table->uuid('zona_id')->nullable();
            $table->uuid('mesero_id')->nullable();
            $table->string('token', 64)->unique();
            $table->string('tipo', 20)->default('local');
            $table->string('nombre_cliente', 150)->nullable();
            $table->string('telefono_cliente', 30)->nullable();
            $table->string('correo_cliente', 150)->nullable();
            $table->text('direccion_cliente')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('ultima_actividad_en', 0)->nullable();
            $table->timestamp('creado_en', 0)->nullable();
            $table->timestamp('actualizado_en', 0)->nullable();

            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('cascade');
            $table->foreign('mesa_id')->references('id')->on('mesas')->onDelete('set null');
            $table->foreign('zona_id')->references('id')->on('zonas_cobertura')->onDelete('set null');
            $table->foreign('mesero_id')->references('id')->on('usuarios')->onDelete('set null');
        });

        Schema::create('items_carrito', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('sesion_cliente_id')->index();
            $table->uuid('producto_id');
            $table->uuid('sucursal_id');
            $table->string('nombre_producto', 150);
            $table->decimal('precio_unitario', 10, 2);
            $table->integer('cantidad')->default(1);
            $table->decimal('subtotal', 10, 2);
            $table->json('variantes_elegidas')->nullable();
            $table->json('adiciones_elegidas')->nullable();
            $table->text('notas')->nullable();
            $table->timestamp('creado_en', 0)->nullable();
            $table->timestamp('actualizado_en', 0)->nullable();

            $table->foreign('sesion_cliente_id')->references('id')->on('sesiones_cliente')->onDelete('cascade');
            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('cascade');
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('cascade');
        });

        Schema::create('pedidos', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('sucursal_id')->index();
            $table->uuid('sesion_cliente_id')->index();
            $table->uuid('mesero_id')->nullable();
            $table->uuid('perfil_domiciliario_id')->nullable()->index();
            $table->uuid('zona_id')->nullable();
            $table->string('tipo', 20)->default('local');
            $table->string('estado', 30)->default('pendiente')->index();
            $table->string('metodo_pago', 20)->nullable();
            $table->string('estado_pago', 20)->default('pendiente');
            $table->text('direccion_entrega')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('costo_envio', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->text('motivo_cancelacion')->nullable();
            $table->timestamp('pagado_en', 0)->nullable();
            $table->timestamp('en_cocina_en', 0)->nullable();
            $table->timestamp('listo_en', 0)->nullable();
            $table->timestamp('entregado_en', 0)->nullable();
            $table->timestamp('creado_en', 0)->nullable();
            $table->timestamp('actualizado_en', 0)->nullable();
            $table->timestamp('eliminado_en', 0)->nullable();

            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('cascade');
            $table->foreign('sesion_cliente_id')->references('id')->on('sesiones_cliente')->onDelete('cascade');
            $table->foreign('mesero_id')->references('id')->on('usuarios')->onDelete('set null');
            $table->foreign('perfil_domiciliario_id')->references('id')->on('perfiles_domiciliario')->onDelete('set null');
            $table->foreign('zona_id')->references('id')->on('zonas_cobertura')->onDelete('set null');
        });

        Schema::create('detalle_pedido', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('pedido_id')->index();
            $table->uuid('producto_id')->nullable();
            $table->uuid('sucursal_id')->index();
            $table->string('nombre_producto', 150);
            $table->decimal('precio_unitario', 10, 2);
            $table->integer('cantidad')->default(1);
            $table->decimal('subtotal', 10, 2);
            $table->json('variantes_elegidas')->nullable();
            $table->json('adiciones_elegidas')->nullable();
            $table->text('notas')->nullable();
            $table->string('estado', 20)->default('activo');
            $table->timestamp('creado_en', 0)->nullable();
            $table->timestamp('actualizado_en', 0)->nullable();

            $table->foreign('pedido_id')->references('id')->on('pedidos')->onDelete('cascade');
            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('set null');
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('cascade');
        });

        Schema::create('historial_estado_pedido', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('pedido_id')->index();
            $table->uuid('usuario_id')->nullable();
            $table->uuid('sucursal_id')->index();
            $table->string('estado', 30);
            $table->timestamp('cambiado_en', 0)->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('creado_en', 0)->nullable();

            $table->foreign('pedido_id')->references('id')->on('pedidos')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('set null');
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_estado_pedido');
        Schema::dropIfExists('detalle_pedido');
        Schema::dropIfExists('pedidos');
        Schema::dropIfExists('items_carrito');
        Schema::dropIfExists('sesiones_cliente');
    }
};
