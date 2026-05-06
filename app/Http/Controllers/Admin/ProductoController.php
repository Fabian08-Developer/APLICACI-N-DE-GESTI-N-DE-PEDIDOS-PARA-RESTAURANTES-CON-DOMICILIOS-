<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Producto;
use App\Exports\PlantillaProductosExport;
use App\Exports\ProductosExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ProductoController extends Controller
{
    /**
     * Listar todos los productos + formulario
     * Ruta: GET /admin/productos
     */
    public function index(Request $request)
    {
        $query = Producto::with('categoria');

        if ($request->filled('buscar')) {
            $busqueda = $request->input('buscar');
            // Nota: Se usa 'ilike' para PostgreSQL (ignorando mayúsculas/minúsculas).
            // Si la base es MySQL, cambiar a 'like'. Pero por el contexto anterior, es pgsql.
            $query->where(function ($q) use ($busqueda) {
                $q->where('nombre', 'ilike', '%' . $busqueda . '%')
                  ->orWhere('descripcion', 'ilike', '%' . $busqueda . '%');
            });
        }

        if ($request->filled('categoria')) {
            $query->where('categoria_id', $request->input('categoria'));
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado') === '1');
        }

        $productos  = $query->latest()->get();
        $categorias = Categoria::orderBy('nombre')->get();
        $editar     = null;

        return view('admin.productos.index', compact('productos', 'categorias', 'editar'));
    }

    /**
     * Crear un nuevo producto
     * Ruta: POST /admin/productos
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre'       => 'required|string|max:100',
            'descripcion'  => 'nullable|string',
            'precio'       => 'required|numeric|min:0',
            'categoria_id' => 'nullable|exists:categorias,id',
            'estado'       => 'boolean',
            'imagen'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'nombre.required' => 'El nombre del producto es obligatorio',
            'precio.required' => 'El precio es obligatorio',
            'precio.numeric'  => 'El precio debe ser un número',
            'precio.min'      => 'El precio no puede ser negativo',
            'imagen.image'    => 'El archivo debe ser una imagen',
            'imagen.mimes'    => 'La imagen debe ser de tipo: jpeg, png, jpg o webp',
            'imagen.max'      => 'La imagen no debe pesar más de 2MB',
        ]);

        $datos = [
            'nombre'       => $request->nombre,
            'descripcion'  => $request->descripcion,
            'precio'       => $request->precio,
            'categoria_id' => $request->categoria_id,
            'estado'       => $request->has('estado') ? true : false,
        ];

        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('productos', 'public');
            $datos['imagen'] = $path;
        }

        Producto::create($datos);

        return redirect()->route('admin.productos.index')
                         ->with('exito', 'Producto creado correctamente');
    }

    /**
     * Cargar producto en el formulario para editar
     * Ruta: GET /admin/productos/{id}/editar
     */
    public function editar($id)
    {
        $productos  = Producto::with('categoria')->latest()->get();
        $categorias = Categoria::orderBy('nombre')->get();
        $editar     = Producto::findOrFail($id);

        return view('admin.productos.index', compact('productos', 'categorias', 'editar'));
    }

    /**
     * Actualizar un producto
     * Ruta: POST /admin/productos/{id}/actualizar
     */
    public function actualizar(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        $request->validate([
            'nombre'       => 'required|string|max:100',
            'descripcion'  => 'nullable|string',
            'precio'       => 'required|numeric|min:0',
            'categoria_id' => 'nullable|exists:categorias,id',
            'imagen'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'nombre.required' => 'El nombre del producto es obligatorio',
            'precio.required' => 'El precio es obligatorio',
            'precio.numeric'  => 'El precio debe ser un número',
            'imagen.image'    => 'El archivo debe ser una imagen',
            'imagen.mimes'    => 'La imagen debe ser de tipo: jpeg, png, jpg o webp',
            'imagen.max'      => 'La imagen no debe pesar más de 2MB',
        ]);

        $datos = [
            'nombre'       => $request->nombre,
            'descripcion'  => $request->descripcion,
            'precio'       => $request->precio,
            'categoria_id' => $request->categoria_id,
        ];

        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior si existe
            if ($producto->imagen) {
                Storage::disk('public')->delete($producto->imagen);
            }
            $path = $request->file('imagen')->store('productos', 'public');
            $datos['imagen'] = $path;
        }

        $producto->update($datos);

        return redirect()->route('admin.productos.index')
                         ->with('exito', 'Producto actualizado correctamente');
    }

    /**
     * Alternar el estado de un producto (Activo/Inactivo)
     * Ruta: POST /admin/productos/{id}/toggle
     */
    public function toggle($id)
    {
        $producto = Producto::findOrFail($id);
        $producto->estado = !$producto->estado;
        $producto->save();

        $mensaje = $producto->estado 
            ? 'Producto activado correctamente' 
            : 'Producto desactivado correctamente';

        return redirect()->route('admin.productos.index')
                         ->with('exito', $mensaje);
    }

    /**
     * Importar productos masivamente desde CSV, Excel (.xlsx/.xls) o ZIP (híbrido).
     * Ruta: POST /admin/productos/importar
     */
    public function importar(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|max:20480',
        ], [
            'archivo.required' => 'Debes seleccionar un archivo.',
            'archivo.max'      => 'El archivo no debe superar los 20 MB.',
        ]);

        $archivo   = $request->file('archivo');
        $extension = strtolower($archivo->getClientOriginalExtension());

        $permitidos = ['csv', 'txt', 'xlsx', 'xls', 'zip'];
        if (!in_array($extension, $permitidos)) {
            return back()->with('error', 'El archivo debe ser .csv, .xlsx, .xls o .zip.');
        }

        $imagenesDir = null;
        $tmpDir      = null;
        $dataPath    = null;
        $dataExt     = null;

        try {
            if ($extension === 'zip') {
                $tmpDir = storage_path('app/temp/import_' . uniqid());
                mkdir($tmpDir, 0775, true);

                $zip = new \ZipArchive();
                if ($zip->open($archivo->getRealPath()) !== true) {
                    return back()->with('error', 'No se pudo abrir el archivo ZIP.');
                }
                $zip->extractTo($tmpDir);
                $zip->close();

                $foundFiles = array_merge(
                    glob($tmpDir . '/*.csv'),
                    glob($tmpDir . '/*.xlsx'),
                    glob($tmpDir . '/*.xls')
                );
                if (empty($foundFiles)) {
                    $this->limpiarDirectorio($tmpDir);
                    return back()->with('error', 'El ZIP no contiene un archivo .csv ni .xlsx válido.');
                }
                $dataPath    = $foundFiles[0];
                $dataExt     = strtolower(pathinfo($dataPath, PATHINFO_EXTENSION));
                $imagenesDir = $tmpDir;
            } else {
                $dataPath = $archivo->getRealPath();
                $dataExt  = $extension;
            }

            // ── Leer las filas del archivo ─────────────────────────────────────
            if (in_array($dataExt, ['xlsx', 'xls'])) {
                $filas = $this->leerFilasExcel($dataPath);
            } else {
                $filas = $this->leerFilasCsv($dataPath);
            }

            if (is_string($filas)) {
                return back()->with('error', $filas);
            }

            if (empty($filas)) {
                return back()->with('error', 'El archivo no tiene filas de datos válidas.');
            }

            // ── Importar dentro de una transacción ─────────────────────────────
            $importados = 0;
            DB::transaction(function () use ($filas, $imagenesDir, &$importados) {
                foreach ($filas as $dato) {
                    $rutaImagen = null;
                    $imagenCol  = trim($dato['imagen'] ?? '');

                    if ($imagenCol) {
                        if (str_starts_with($imagenCol, 'http://') || str_starts_with($imagenCol, 'https://')) {
                            $contenido = @file_get_contents($imagenCol);
                            if ($contenido !== false) {
                                $ext           = pathinfo(parse_url($imagenCol, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                                $nombreArchivo = 'productos/' . Str::uuid() . '.' . $ext;
                                Storage::disk('public')->put($nombreArchivo, $contenido);
                                $rutaImagen = $nombreArchivo;
                            }
                        } elseif ($imagenesDir) {
                            $rutaLocal = $imagenesDir . DIRECTORY_SEPARATOR . $imagenCol;
                            if (file_exists($rutaLocal)) {
                                $ext           = pathinfo($rutaLocal, PATHINFO_EXTENSION);
                                $nombreArchivo = 'productos/' . Str::uuid() . '.' . $ext;
                                Storage::disk('public')->put($nombreArchivo, file_get_contents($rutaLocal));
                                $rutaImagen = $nombreArchivo;
                            }
                        }
                    }

                    $categoriaId = null;
                    if (!empty($dato['categoria_id']) && is_numeric($dato['categoria_id'])) {
                        $categoriaId = (int) $dato['categoria_id'];
                    }

                    Producto::create([
                        'nombre'       => trim($dato['nombre']),
                        'descripcion'  => trim($dato['descripcion'] ?? ''),
                        'precio'       => (float) $dato['precio'],
                        'categoria_id' => $categoriaId,
                        'estado'       => true,
                        'imagen'       => $rutaImagen,
                    ]);

                    $importados++;
                }
            });

        } finally {
            if ($tmpDir && is_dir($tmpDir)) {
                $this->limpiarDirectorio($tmpDir);
            }
        }

        return redirect()->route('admin.productos.index')
            ->with('exito', "{$importados} producto(s) importado(s) exitosamente.");
    }

    /**
     * Leer filas desde un archivo Excel (.xlsx / .xls) usando PhpSpreadsheet.
     * @return array|string  Array de filas o string con mensaje de error.
     */
    private function leerFilasExcel(string $path): array|string
    {
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = $sheet->toArray(null, true, true, false);
        } catch (\Exception $e) {
            return 'No se pudo leer el archivo Excel: ' . $e->getMessage();
        }

        if (empty($rows)) {
            return 'El archivo Excel está vacío.';
        }

        // Buscar la fila de encabezados (primera fila que contenga "nombre" y "precio")
        $encabezados = null;
        $inicioData  = 0;
        foreach ($rows as $i => $row) {
            $celdas = array_map(fn($c) => strtolower(trim((string) ($c ?? ''))), $row);
            if (in_array('nombre', $celdas) && in_array('precio', $celdas)) {
                $encabezados = $celdas;
                $inicioData  = $i + 1;
                break;
            }
        }

        if (!$encabezados) {
            return 'No se encontraron los encabezados obligatorios (nombre, precio) en el Excel.';
        }

        $filas = [];
        for ($i = $inicioData; $i < count($rows); $i++) {
            $row    = $rows[$i];
            $nombre = trim((string) ($row[array_search('nombre', $encabezados)] ?? ''));
            $precio = trim((string) ($row[array_search('precio', $encabezados)] ?? ''));

            if (empty($nombre) || !is_numeric($precio)) {
                continue;
            }

            // Saltar filas informativas de la plantilla
            $nombreUpper = strtoupper($nombre);
            if (str_starts_with($nombreUpper, '===') || str_starts_with($nombreUpper, 'EJEMPLO')
                || str_starts_with($nombreUpper, 'NOTA') || str_starts_with($nombreUpper, 'CATEGORÍA')
                || str_starts_with($nombreUpper, 'CATEGORIA') || str_starts_with($nombreUpper, '✔')
                || str_starts_with($nombreUpper, '*') || str_starts_with($nombreUpper, 'ID ')) {
                continue;
            }

            $dato = [];
            foreach ($encabezados as $colIdx => $header) {
                if (!empty($header)) {
                    $dato[$header] = trim((string) ($row[$colIdx] ?? ''));
                }
            }
            $filas[] = $dato;
        }

        return $filas;
    }

    /**
     * Leer filas desde un archivo CSV.
     * @return array|string  Array de filas o string con mensaje de error.
     */
    private function leerFilasCsv(string $path): array|string
    {
        $handle = fopen($path, 'r');
        if (!$handle) {
            return 'No se pudo leer el archivo CSV.';
        }

        $encabezados = fgetcsv($handle, 0, ',');
        if (!$encabezados) {
            fclose($handle);
            return 'El archivo CSV está vacío o mal formado.';
        }

        // Normalizar encabezados + limpiar BOM
        $encabezados = array_map(function ($h) {
            $h = trim($h);
            $h = preg_replace('/^\x{FEFF}/u', '', $h);
            return strtolower($h);
        }, $encabezados);

        $requeridos = ['nombre', 'precio'];
        foreach ($requeridos as $col) {
            if (!in_array($col, $encabezados)) {
                fclose($handle);
                return "El CSV debe tener la columna: {$col}";
            }
        }

        $filas = [];
        $linea = 2;
        while (($fila = fgetcsv($handle, 0, ',')) !== false) {
            if (count($fila) !== count($encabezados)) {
                fclose($handle);
                return "La fila {$linea} tiene un número incorrecto de columnas.";
            }
            $dato   = array_combine($encabezados, $fila);
            $nombre = trim($dato['nombre'] ?? '');
            $precio = trim($dato['precio'] ?? '');

            if (empty($nombre) || !is_numeric($precio)) {
                $linea++;
                continue;
            }

            $nombreUpper = strtoupper($nombre);
            if (str_starts_with($nombreUpper, '===') || str_starts_with($nombreUpper, 'EJEMPLO')
                || str_starts_with($nombreUpper, 'NOTA') || str_starts_with($nombreUpper, 'CATEGORÍA')
                || str_starts_with($nombreUpper, 'CATEGORIA') || str_starts_with($nombreUpper, '✔')
                || str_starts_with($nombreUpper, '*') || str_starts_with($nombreUpper, 'ID ')) {
                $linea++;
                continue;
            }

            $filas[] = $dato;
            $linea++;
        }
        fclose($handle);

        return $filas;
    }


    /**
     * Descargar plantilla Excel de ejemplo.
     * Ruta: GET /admin/productos/plantilla-importacion
     */
    public function descargarPlantilla()
    {
        return Excel::download(
            new PlantillaProductosExport,
            'plantilla_productos_cafe_bambu.xlsx'
        );
    }


    /**
     * Exportar base de datos actual de productos a Excel.
     * Ruta: GET /admin/productos/exportar
     */
    public function exportar()
    {
        return Excel::download(
            new ProductosExport,
            'productos_cafe_bambu_' . date('d_m_Y') . '.xlsx'
        );
    }

    /**
     * Eliminar recursivamente un directorio temporal.
     */
    private function limpiarDirectorio(string $dir): void
    {
        if (!is_dir($dir)) return;
        $archivos = array_diff(scandir($dir), ['.', '..']);
        foreach ($archivos as $archivo) {
            $ruta = $dir . DIRECTORY_SEPARATOR . $archivo;
            is_dir($ruta) ? $this->limpiarDirectorio($ruta) : unlink($ruta);
        }
        rmdir($dir);
    }
}