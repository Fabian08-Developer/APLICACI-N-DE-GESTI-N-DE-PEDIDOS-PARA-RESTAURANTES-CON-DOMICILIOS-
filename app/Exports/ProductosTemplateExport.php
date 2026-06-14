<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductosTemplateExport implements FromArray, WithHeadings
{
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

    public function array(): array
    {
        return [
            [
                'Hamburguesa Especial',
                'Deliciosa hamburguesa con queso, lechuga y salsa de la casa',
                '15000',
                '12000',
                'Hamburguesas',
                'Si',
                '0',
                '5',
                'Si',
                'Si'
            ]
        ];
    }
}
