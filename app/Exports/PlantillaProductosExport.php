<?php

namespace App\Exports;

use App\Models\Categoria;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PlantillaProductosExport implements FromArray, WithStyles, WithColumnWidths, WithTitle
{
    public function title(): string
    {
        return 'Plantilla Productos';
    }

    public function array(): array
    {
        $categorias = Categoria::orderBy('id')->get(['id', 'nombre']);

        $filas = [];

        // ── Título ────────────────────────────────────────────────────────────
        $filas[] = ['CAFÉ BAMBÚ — Plantilla de Importación de Productos', '', '', '', ''];
        $filas[] = [''];

        // ── Encabezados de columna ────────────────────────────────────────────
        $filas[] = ['nombre', 'descripcion', 'precio', 'categoria_id', 'imagen'];

        // ── Sección 1: Imagen desde archivo (modo ZIP) ────────────────────────
        $filas[] = ['EJEMPLO 1: Imagen desde archivo (usa un .zip con las fotos)', '', '', '', ''];
        $filas[] = ['Café Espresso', 'Café negro intenso preparado al momento', 4500, 2, 'cafe_espresso.jpg'];
        $filas[] = ['Capuccino', 'Espresso con leche vaporizada y espuma suave', 6000, 2, 'capuccino.jpg'];
        $filas[] = ['Té Verde con Miel', 'Infusión relajante con miel natural', 4000, 2, 'te_verde.jpg'];

        // ── Sección 2: Imagen desde URL ───────────────────────────────────────
        $filas[] = ['EJEMPLO 2: Imagen desde URL (Internet — solo .csv)', '', '', '', ''];
        $filas[] = ['Jugo de Maracuyá', 'Jugo tropical natural sin azúcar', 5500, 3, 'https://images.unsplash.com/photo-1600271886742-f049cd451bba?w=400'];
        $filas[] = ['Jugo de Mora', 'Mora fresca licuada con agua y panela', 5000, 3, 'https://images.unsplash.com/photo-1622597467836-f3e2d606bcbd?w=400'];

        // ── Sección 3: Sin imagen ─────────────────────────────────────────────
        $filas[] = ['EJEMPLO 3: Sin imagen — dejar columna vacía', '', '', '', ''];
        $filas[] = ['Brownie de Chocolate', 'Porción generosa con chispas de chocolate', 4800, 4, ''];
        $filas[] = ['Torta de Zanahoria', 'Torta casera con crema de queso', 5200, 4, ''];
        $filas[] = ['Agua en Botella', 'Agua mineral 600ml', 2000, 1, ''];

        // ── Espacio ───────────────────────────────────────────────────────────
        $filas[] = [''];

        // ── Categorías disponibles ────────────────────────────────────────────
        $filas[] = ['CATEGORÍAS DISPONIBLES EN EL SISTEMA', '', '', '', ''];
        $filas[] = ['ID', 'Nombre', '', '', ''];
        foreach ($categorias as $cat) {
            $filas[] = [$cat->id, $cat->nombre, '', '', ''];
        }

        // ── Espacio ───────────────────────────────────────────────────────────
        $filas[] = [''];

        // ── Notas ─────────────────────────────────────────────────────────────
        $filas[] = ['NOTAS IMPORTANTES', '', '', '', ''];
        $filas[] = ['✔ Columnas obligatorias: nombre y precio', '', '', '', ''];
        $filas[] = ['✔ precio: solo números (ej: 5000 o 5000.50 — sin puntos de miles)', '', '', '', ''];
        $filas[] = ['✔ categoria_id: debe ser el número ID de la tabla (dejar vacío si no aplica)', '', '', '', ''];
        $filas[] = ['✔ imagen: nombre de archivo (modo ZIP) o URL completa (http...) o vacío', '', '', '', ''];
        $filas[] = ['✔ Al importar: el .csv y las fotos deben estar en la raíz del .zip', '', '', '', ''];
        $filas[] = ['✔ Si una fila tiene error — NO se importa ningún producto (todo o nada)', '', '', '', ''];

        return $filas;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 42,
            'B' => 48,
            'C' => 14,
            'D' => 16,
            'E' => 60,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        // ── Título principal (fila 1) ─────────────────────────────────────────
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold'  => true,
                'size'  => 16,
                'color' => ['argb' => 'FF2D2A26'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF5F0E8'],
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(40);

        // ── Encabezados de columna (fila 3) ───────────────────────────────────
        $sheet->getStyle('A3:E3')->applyFromArray([
            'font' => [
                'bold'  => true,
                'size'  => 11,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF4F46E5'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FF3730A3'],
                ],
            ],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(28);

        // ── Filas de sección (coloreadas como separadores) ────────────────────
        $seccionFilas = [4, 8, 11, 14];
        foreach ($seccionFilas as $fila) {
            if ($fila > $lastRow) continue;
            $sheet->mergeCells("A{$fila}:E{$fila}");
            $sheet->getStyle("A{$fila}")->applyFromArray([
                'font' => [
                    'bold'  => true,
                    'size'  => 10,
                    'color' => ['argb' => 'FF92400E'],
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFFEF3C7'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                ],
            ]);
            $sheet->getRowDimension($fila)->setRowHeight(24);
        }

        // ── Filas de datos de ejemplo — bordes suaves ─────────────────────────
        $datosFilas = [5, 6, 7, 9, 10, 12, 13, 15];
        foreach ($datosFilas as $fila) {
            if ($fila > $lastRow) continue;
            $sheet->getStyle("A{$fila}:E{$fila}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['argb' => 'FFE5E7EB'],
                    ],
                ],
                'font' => ['size' => 10],
            ]);
        }

        // ── Sección de categorías ─────────────────────────────────────────────
        for ($r = 16; $r <= $lastRow; $r++) {
            $val = $sheet->getCell("A{$r}")->getValue();
            if ($val === 'CATEGORÍAS DISPONIBLES EN EL SISTEMA') {
                $sheet->mergeCells("A{$r}:E{$r}");
                $sheet->getStyle("A{$r}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF1E40AF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDBEAFE']],
                ]);
                $nextRow = $r + 1;
                $sheet->getStyle("A{$nextRow}:B{$nextRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEFF6FF']],
                    'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFBFDBFE']]],
                ]);
                break;
            }
        }

        // ── Sección de Notas ──────────────────────────────────────────────────
        for ($r = 16; $r <= $lastRow; $r++) {
            $val = $sheet->getCell("A{$r}")->getValue();
            if ($val === 'NOTAS IMPORTANTES') {
                $sheet->mergeCells("A{$r}:E{$r}");
                $sheet->getStyle("A{$r}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF065F46']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD1FAE5']],
                ]);
                for ($n = $r + 1; $n <= $lastRow; $n++) {
                    $sheet->mergeCells("A{$n}:E{$n}");
                    $sheet->getStyle("A{$n}")->applyFromArray([
                        'font' => ['size' => 9, 'color' => ['argb' => 'FF374151']],
                    ]);
                }
                break;
            }
        }

        // ── Alineación general ────────────────────────────────────────────────
        $sheet->getStyle("A1:E{$lastRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("C1:C{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("D1:D{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }
}
