<?php

namespace App\Traits;

use App\Scopes\TenantScope;
use App\Models\Sucursal;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToSucursal
{
    protected static function bootBelongsToSucursal(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function ($model) {
            if (empty($model->sucursal_id)) {
                $tenantId = \App\Scopes\TenantScope::getTenantId();
                if ($tenantId) {
                    $model->sucursal_id = $tenantId;
                }
            }
        });
    }

    /**
     * Relationship: the model belongs to a branch (sucursal).
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }
}
