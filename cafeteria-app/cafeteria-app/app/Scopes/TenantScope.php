<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    private static ?string $tenantId = null;

    /**
     * Set a custom tenant_id for the duration of the current request.
     * Useful in middlewares or webhooks where we know the sucursal
     * but there is no traditional PHP session/Auth active yet.
     */
    public static function setTenantId(?string $id): void
    {
        self::$tenantId = $id;
    }

    /**
     * Resolve and return the current active branch ID based on the context:
     * static override -> authenticated user's branch -> session-stored active branch.
     */
    public static function getTenantId(): ?string
    {
        if (self::$tenantId) {
            return self::$tenantId;
        }

        if (auth()->check()) {
            return auth()->user()->sucursal_id;
        }

        if (session()->has('active_tenant_id')) {
            return session('active_tenant_id');
        }

        if (session()->has('active_branch_id')) {
            return session('active_branch_id');
        }

        return null;
    }

    /**
     * Apply the global scope filter.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Bypass scoping for super-admins
        if (auth()->check() && auth()->user()->hasRole('super-admin')) {
            return;
        }

        // Bypass scoping for managers (gerente) on models they need to see globally,
        // but normally Operational Models will be scoped once they select a branch.
        // Wait, if a gerente doesn't have a sucursal selected yet, they shouldn't be blocked.
        if (auth()->check() && auth()->user()->hasRole('gerente') && !self::getTenantId()) {
            return;
        }

        $sucursalId = self::getTenantId();

        if ($sucursalId) {
            $builder->where($model->getTable() . '.sucursal_id', $sucursalId);
        } else {
            // ✅ HIGH SECURITY:
            // If no sucursal context is established, inject a condition that always evaluates to false.
            // This prevents accidental data leaks across different branches.
            $builder->whereRaw('1 = 0');
        }
    }
}


