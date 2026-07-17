<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Elimina tablas creadas por scaffolding o módulos nunca implementados
     * que no tienen ninguna referencia activa en el código.
     *
     * Tablas eliminadas:
     * - movimientos_inventario  → FK depende de inventarios, se borra primero
     * - inventarios             → módulo de inventario nunca implementado
     * - tokens_verificacion     → creado en scaffolding inicial, nunca usado
     * - tokens_acceso_personal  → creado en scaffolding inicial, nunca usado
     * - password_reset_tokens   → tabla por defecto de Laravel, el proyecto usa 'sesiones'
     */
    public function up(): void
    {
        // 1. Primero las tablas con FK hacia otras de esta lista
        Schema::dropIfExists('movimientos_inventario');
        Schema::dropIfExists('inventarios');

        // 2. Tablas de scaffolding sin uso
        Schema::dropIfExists('tokens_verificacion');
        Schema::dropIfExists('tokens_acceso_personal');
        Schema::dropIfExists('password_reset_tokens');
    }

    public function down(): void
    {
        // No se recrean: la eliminación es definitiva.
        // Si se necesitan, restaurar desde las migraciones originales:
        // - inventarios/movimientos_inventario → 2024_05_09_000006
        // - tokens_verificacion/tokens_acceso_personal → 2024_05_09_000001
        // - password_reset_tokens → 0001_01_01_000000
    }
};
