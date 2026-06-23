<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migración de rendimiento: índices compuestos para las consultas más frecuentes.
 *
 * Problema original: las queries más comunes filtran por combinaciones de columnas
 * (sucursal_id + estado + creado_en), pero solo existían índices individuales,
 * lo que forzaba full-table scans o merge de índices.
 *
 * Impacto esperado: reducción de 60-90% en tiempo de respuesta de las queries
 * principales de pedidos, reportes y domiciliarios.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── PEDIDOS ─────────────────────────────────────────────────────────
        // Query más frecuente: sucursal_id + tipo + estado (ManagePedidos, tabs)
        if (!$this->indexExists('pedidos', 'idx_pedidos_sucursal_tipo_estado')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->index(
                    ['sucursal_id', 'tipo', 'estado'],
                    'idx_pedidos_sucursal_tipo_estado'
                );
            });
        }

        // Reportes y estadísticas: sucursal_id + estado + creado_en
        if (!$this->indexExists('pedidos', 'idx_pedidos_sucursal_estado_fecha')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->index(
                    ['sucursal_id', 'estado', 'creado_en'],
                    'idx_pedidos_sucursal_estado_fecha'
                );
            });
        }

        // Domicilios activos: sucursal_id + tipo + perfil_domiciliario_id
        if (!$this->indexExists('pedidos', 'idx_pedidos_sucursal_domiciliario')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->index(
                    ['sucursal_id', 'perfil_domiciliario_id'],
                    'idx_pedidos_sucursal_domiciliario'
                );
            });
        }

        // ── DETALLE_PEDIDO ───────────────────────────────────────────────────
        // Reportes: sucursal_id + producto_id (JOINs con productos)
        if (!$this->indexExists('detalle_pedido', 'idx_detalle_sucursal_producto')) {
            Schema::table('detalle_pedido', function (Blueprint $table) {
                $table->index(
                    ['sucursal_id', 'producto_id'],
                    'idx_detalle_sucursal_producto'
                );
            });
        }

        // ── NOTIFICACIONES ───────────────────────────────────────────────────
        // CampanillaNotificaciones: usuario_id + leida + creado_en
        if (!$this->indexExists('notificaciones', 'idx_notificaciones_usuario_leida')) {
            Schema::table('notificaciones', function (Blueprint $table) {
                $table->index(
                    ['usuario_id', 'leida', 'creado_en'],
                    'idx_notificaciones_usuario_leida'
                );
            });
        }

        // ── PERFILES_DOMICILIARIO ────────────────────────────────────────────
        // ManageDomiciliarios y autoAsignar: sucursal_id + estado
        if (!$this->indexExists('perfiles_domiciliario', 'idx_domiciliarios_sucursal_estado')) {
            Schema::table('perfiles_domiciliario', function (Blueprint $table) {
                $table->index(
                    ['sucursal_id', 'estado'],
                    'idx_domiciliarios_sucursal_estado'
                );
            });
        }

        // ── HISTORIAL_ESTADO_PEDIDO ──────────────────────────────────────────
        // Drawer de detalle del pedido: pedido_id + cambiado_en
        if (!$this->indexExists('historial_estado_pedido', 'idx_historial_pedido_fecha')) {
            Schema::table('historial_estado_pedido', function (Blueprint $table) {
                $table->index(
                    ['pedido_id', 'cambiado_en'],
                    'idx_historial_pedido_fecha'
                );
            });
        }

        // ── LIQUIDACIONES_DOMICILIARIO ───────────────────────────────────────
        // getTieneBloqueoAttribute: perfil_domiciliario_id + estado
        if (!$this->indexExists('liquidaciones_domiciliario', 'idx_liquidaciones_perfil_estado')) {
            Schema::table('liquidaciones_domiciliario', function (Blueprint $table) {
                $table->index(
                    ['perfil_domiciliario_id', 'estado'],
                    'idx_liquidaciones_perfil_estado'
                );
            });
        }

        // ── SESIONES_CLIENTE ─────────────────────────────────────────────────
        // Filtros de mesa en ManagePedidos: mesa_id
        if (!$this->indexExists('sesiones_cliente', 'idx_sesiones_mesa')) {
            Schema::table('sesiones_cliente', function (Blueprint $table) {
                $table->index('mesa_id', 'idx_sesiones_mesa');
            });
        }
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_pedidos_sucursal_tipo_estado');
            $table->dropIndexIfExists('idx_pedidos_sucursal_estado_fecha');
            $table->dropIndexIfExists('idx_pedidos_sucursal_domiciliario');
        });

        Schema::table('detalle_pedido', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_detalle_sucursal_producto');
        });

        Schema::table('notificaciones', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_notificaciones_usuario_leida');
        });

        Schema::table('perfiles_domiciliario', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_domiciliarios_sucursal_estado');
        });

        Schema::table('historial_estado_pedido', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_historial_pedido_fecha');
        });

        Schema::table('liquidaciones_domiciliario', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_liquidaciones_perfil_estado');
        });

        Schema::table('sesiones_cliente', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_sesiones_mesa');
        });
    }

    /**
     * Verifica si un índice ya existe antes de intentar crearlo.
     * Evita errores si la migración se corre más de una vez.
     */
    private function indexExists(string $table, string $indexName): bool
    {
        if (DB::getDriverName() === 'sqlite') {
            // SQLite: buscar en sqlite_master
            $result = DB::select(
                "SELECT name FROM sqlite_master WHERE type='index' AND name=?",
                [$indexName]
            );
            return !empty($result);
        }

        // PostgreSQL / MySQL
        try {
            $result = DB::select(
                "SELECT indexname FROM pg_indexes WHERE tablename = ? AND indexname = ?",
                [$table, $indexName]
            );
            return !empty($result);
        } catch (\Throwable $e) {
            // MySQL fallback
            try {
                $result = DB::select(
                    "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
                    [$indexName]
                );
                return !empty($result);
            } catch (\Throwable $e2) {
                return false;
            }
        }
    }
};
