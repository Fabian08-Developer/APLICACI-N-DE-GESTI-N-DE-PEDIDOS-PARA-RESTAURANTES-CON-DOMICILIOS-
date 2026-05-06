<?php

namespace App\Exports;

use App\Models\Producto;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ProductosExport implements FromArray, WithStyles, WithColumnWidths, WithTitle
{
    public function title(): string
    {
        return 'Productos';
    }

    public function array(): array
    {
        $productos = Producto::with('categoria')->orderBy('nombre')->get();

        $filas = [];

        // Título
        $filas[] = ['CAFÉ BAMBÚ — Listado de Productos', '', '', '', '', '', ''];
        $filas[] = ['Exportado: ' . now()->format('d/m/Y H:i'), '', '', '', '', '', ''];
        $filas[] = [''];

        // Encabezados
        $filas[] = ['ID', 'Nombre', 'Descripción', 'Precio', 'Categoría', 'Estado', 'Imagen'];

        // Datos
        foreach ($productos as $p) {
            $filas[] = [
                $p->id,
                $p->nombre,
                $p->descripcion ?? '',
                $p->precio,
                $p->categoria ? $p->categoria->nombre : 'Sin categoría',
                $p->estado ? 'Activo' : 'Inactivo',
                $p->imagen ?? '',
            ];
        }

        // Resumen
        $filas[] = [''];
        $filas[] = ['Total de productos: ' . $productos->count(), '', '', '', '', '', ''];
        $filas[] = [
            'Activos: ' . $productos->where('estado', true)->count()
            . ' | Inactivos: ' . $productos->where('estado', false)->count(),
            '', '', '', '', '', ''
        ];

        return $filas;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 32,
            'C' => 42,
            'D' => 14,
            'E' => 22,
            'F' => 12,
            'G' => 50,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        // ── Título (fila 1) ───────────────────────────────────────────────────
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 15, 'color' => ['argb' => 'FF2D2A26']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF5F0E8']],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(38);

        // ── Subtítulo fecha (fila 2) ──────────────────────────────────────────
        $sheet->mergeCells('A2:G2');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['size' => 9, 'italic' => true, 'color' => ['argb' => 'FF6B7280']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // ── Encabezados (fila 4) ──────────────────────────────────────────────
        $sheet->getStyle('A4:G4')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4F46E5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF3730A3'],
                ],
            ],
        ]);
        $sheet->getRowDimension(4)->setRowHeight(28);

        // ── Filas de datos ────────────────────────────────────────────────────
        if ($lastRow > 4) {
            $dataEnd = $lastRow;
            // Buscar dónde terminan los datos (fila vacía)
            for ($r = 5; $r <= $lastRow; $r++) {
                $val = $sheet->getCell("B{$r}")->getValue();
                if ($val === null || $val === '') {
                    $dataEnd = $r - 1;
                    break;
                }
            }

            if ($dataEnd >= 5) {
                // Bordes en filas de datos
                $sheet->getStyle("A5:G{$dataEnd}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FFE5E7EB'],
                        ],
                    ],
                    'font' => ['size' => 10],
                ]);

                // Zebra stripes
                for ($r = 5; $r <= $dataEnd; $r++) {
                    if ($r % 2 === 0) {
                        $sheet->getStyle("A{$r}:G{$r}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF9FAFB']],
                        ]);
                    }
                }

                // Colorear estados
                for ($r = 5; $r <= $dataEnd; $r++) {
                    $estado = $sheet->getCell("F{$r}")->getValue();
                    if ($estado === 'Activo') {
                        $sheet->getStyle("F{$r}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['argb' => 'FF065F46']],
                        ]);
                    } else {
                        $sheet->getStyle("F{$r}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['argb' => 'FF991B1B']],
                        ]);
                    }
                }
            }
        }

        // ── Alineación general ────────────────────────────────────────────────
        $sheet->getStyle("A1:G{$lastRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A4:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("D4:D{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("F4:F{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }
}
