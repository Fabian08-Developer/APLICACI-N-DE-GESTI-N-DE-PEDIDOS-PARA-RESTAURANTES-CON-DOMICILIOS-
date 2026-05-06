<?php

namespace App\Exports;

use App\Models\Pedido;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $start;
    protected $end;

    public function __construct($start, $end)
    {
        $this->start = $start;
        $this->end = $end;
    }

    public function collection()
    {
        return Pedido::completado()
            ->rangoFechas($this->start, $this->end)
            ->with(['mesero', 'detalles.producto'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID Pedido',
            'Fecha',
            'Mesero',
            'Mesa (Sesión)',
            'Total Venta ($)',
            'Productos'
        ];
    }

    public function map($pedido): array
    {
        $productos = $pedido->detalles->map(function($d) {
            return $d->cantidad . 'x ' . ($d->producto ? $d->producto->nombre : 'Prod Eliminado');
        })->implode(', ');

        return [
            $pedido->id,
            $pedido->created_at->format('Y-m-d H:i:s'),
            $pedido->mesero ? $pedido->mesero->nombre : 'Automático / Cliente',
            $pedido->sesion_mesa_id ?? 'Domicilio/Sin mesa',
            $pedido->total,
            $productos
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '0F172A']]],
        ];
    }
}
