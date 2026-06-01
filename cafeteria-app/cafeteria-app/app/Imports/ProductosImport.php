<?php

namespace App\Imports;

use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Auth;

class ProductosImport implements ToCollection, WithHeadingRow
{
    protected $sucursalId;
    public $importedCount = 0;
    public $errors = [];

    public function __construct($sucursalId = null)
    {
        $this->sucursalId = $sucursalId ?: Auth::user()->sucursal_id;
    }

    public function collection(Collection $rows)
    {
        $rowCount = 1; // plus 1 for header
        foreach ($rows as $row) {
            $rowCount++;
            
            // Normalize row data
            $nombre = trim($row['nombre'] ?? '');
            if (empty($nombre)) {
                $this->errors[] = "Fila {$rowCount}: El nombre es obligatorio.";
                continue;
            }

            $precio = $row['precio'] ?? '';
            $precio_oferta = $row['precio_oferta'] ?? null;
            $categoriaNombre = trim($row['categoria'] ?? '');
            
            $permiteNotasStr = strtolower(trim($row['permite_notas_si_no'] ?? $row['permite_notas_sino'] ?? $row['permite_notas'] ?? 'si'));
            $permiteNotas = ($permiteNotasStr === 'si' || $permiteNotasStr === 'sí' || $permiteNotasStr === 'yes' || $permiteNotasStr === '1');

            $activoStr = strtolower(trim($row['activo_si_no'] ?? $row['activo_sino'] ?? $row['activo'] ?? 'si'));
            $activo = ($activoStr === 'si' || $activoStr === 'sí' || $activoStr === 'yes' || $activoStr === '1');

            $disponibleStr = strtolower(trim($row['disponible_si_no'] ?? $row['disponible_sino'] ?? $row['disponible'] ?? 'si'));
            $disponible = ($disponibleStr === 'si' || $disponibleStr === 'sí' || $disponibleStr === 'yes' || $disponibleStr === '1');

            $limiteMin = isset($row['limite_minimo_adiciones']) ? intval($row['limite_minimo_adiciones']) : 0;
            
            $limiteMaxRaw = $row['limite_maximo_adiciones'] ?? null;
            $limiteMax = ($limiteMaxRaw !== null && trim($limiteMaxRaw) !== '') ? intval($limiteMaxRaw) : null;

            // Validate duplicate name in the same sucursal (case-insensitive)
            $exists = Producto::where('sucursal_id', $this->sucursalId)
                ->whereRaw('LOWER(nombre) = ?', [strtolower($nombre)])
                ->exists();

            if ($exists) {
                $this->errors[] = "Fila {$rowCount}: El producto '{$nombre}' ya existe en esta sucursal.";
                continue;
            }

            if (!is_numeric($precio) || $precio < 0) {
                $this->errors[] = "Fila {$rowCount}: El precio del producto '{$nombre}' debe ser un número mayor o igual a 0.";
                continue;
            }

            if ($precio_oferta !== null && trim($precio_oferta) !== '') {
                if (!is_numeric($precio_oferta) || $precio_oferta < 0) {
                    $this->errors[] = "Fila {$rowCount}: El precio de oferta del producto '{$nombre}' debe ser un número mayor o igual a 0.";
                    continue;
                }
            } else {
                $precio_oferta = null;
            }

            // Find or create category
            $categoriaId = null;
            if (!empty($categoriaNombre)) {
                $categoria = Categoria::where('sucursal_id', $this->sucursalId)
                    ->whereRaw('LOWER(nombre) = ?', [strtolower($categoriaNombre)])
                    ->first();

                if (!$categoria) {
                    $categoria = Categoria::create([
                        'sucursal_id' => $this->sucursalId,
                        'nombre' => $categoriaNombre,
                        'activo' => true
                    ]);
                }
                $categoriaId = $categoria->id;
            }

            // Create product
            Producto::create([
                'sucursal_id' => $this->sucursalId,
                'categoria_id' => $categoriaId,
                'nombre' => $nombre,
                'descripcion' => $row['descripcion'] ?? $row['descripción'] ?? null,
                'precio' => $precio,
                'precio_oferta' => $precio_oferta,
                'permite_notas' => $permiteNotas,
                'limite_minimo_adiciones' => $limiteMin,
                'limite_maximo_adiciones' => $limiteMax,
                'activo' => $activo,
                'disponible' => $disponible,
            ]);

            $this->importedCount++;
        }
    }
}
