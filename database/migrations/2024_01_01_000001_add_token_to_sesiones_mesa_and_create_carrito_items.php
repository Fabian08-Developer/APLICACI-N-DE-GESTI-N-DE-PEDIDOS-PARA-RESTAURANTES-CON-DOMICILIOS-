<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Esta migración hace dos cosas:
 *
 * 1. Añade la columna `token` a sesiones_mesa (si no existe ya).
 *    El token identifica unívocamente la sesión de mesa en la URL.
 *
 * 2. Crea la tabla `carrito_items`.
 *    El carrito pasa de session() a la BD — esto es lo que permite
 *    que múltiples pestañas tengan carritos independientes.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Token en sesiones_mesa ────────────────────────────────────────
        if (!Schema::hasColumn('sesiones_mesa', 'token')) {
            Schema::table('sesiones_mesa', function (Blueprint $table) {
                $table->string('token', 64)->nullable()->unique()->after('tipo_sesion');
                $table->index('token');
            });
        }

        // ── 2. Tabla carrito_items ───────────────────────────────────────────
        if (!Schema::hasTable('carrito_items')) {
            Schema::create('carrito_items', function (Blueprint $table) {
                $table->id();

                // Cada ítem pertenece a una sesión de mesa específica
                // (no a una cookie de navegador)
                $table->foreignId('sesion_mesa_id')
                    ->constrained('sesiones_mesa')
                    ->cascadeOnDelete(); // al cerrar la sesión, se limpian los ítems

                $table->foreignId('producto_id')
                    ->constrained('productos')
                    ->cascadeOnDelete();

                // Guardamos nombre y precio en el momento de agregar
                // (por si el precio del producto cambia después)
                $table->string('nombre', 100);
                $table->decimal('precio', 10, 2);
                $table->integer('cantidad');
                $table->decimal('subtotal', 10, 2);

                $table->timestamps();

                // Un producto no se repite en el mismo carrito de mesa
                $table->unique(['sesion_mesa_id', 'producto_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('carrito_items');

        if (Schema::hasColumn('sesiones_mesa', 'token')) {
            Schema::table('sesiones_mesa', function (Blueprint $table) {
                $table->dropIndex(['token']);
                $table->dropColumn('token');
            });
        }
    }
};
