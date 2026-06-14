<?php

namespace App\Exports;

use App\Models\Producto;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Auth;

class ProductosExport implements FromCollection, WithHeadings, WithMapping
{
    protected $sucursalId;

    public function __construct($sucursalId = null)
    {
        $this->sucursalId = $sucursalId ?: Auth::user()->sucursal_id;
    }

    public function collection()
    {
        return Producto::with('categoria')
            ->where('sucursal_id', $this->sucursalId)
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'Descripcion',
            'Precio',
            'Precio Oferta',
            'Categoria',
            'Permite Notas (Si/No)',
            'Limite Minimo Adiciones',
            'Limite Maximo Adiciones',
            'Activo (Si/No)',
            'Disponible (Si/No)'
        ];
    }

    /**
     * @param Producto $producto
     */
    public function map($producto): array
    {
        return [
            $producto->nombre,
            $producto->descripcion,
            $producto->precio,
            $producto->precio_oferta,
            $producto->categoria ? $producto->categoria->nombre : '',
            $producto->permite_notas ? 'Si' : 'No',
            $producto->limite_minimo_adiciones,
            $producto->limite_maximo_adiciones,
            $producto->activo ? 'Si' : 'No',
            $producto->disponible ? 'Si' : 'No',
        ];
    }
}
