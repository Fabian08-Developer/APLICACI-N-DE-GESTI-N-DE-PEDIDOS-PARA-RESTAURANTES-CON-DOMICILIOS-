<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $table = 'categorias';

    protected $fillable = ['nombre', 'descripcion'];

    // Una categoría puede tener muchos productos
    public function productos()
    {
        return $this->hasMany(Producto::class, 'categoria_id');
    }
}