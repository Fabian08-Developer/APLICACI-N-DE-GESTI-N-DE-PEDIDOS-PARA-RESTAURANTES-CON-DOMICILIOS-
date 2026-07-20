<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permiso extends SpatiePermission
{
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';
}
