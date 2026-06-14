<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductosExport;
use App\Exports\ProductosTemplateExport;
use App\Imports\ProductosImport;

class ExcelProductosController extends Controller
{
    public function exportar()
    {
        return Excel::download(new ProductosExport(Auth::user()->sucursal_id), 'productos.xlsx');
    }

    public function plantilla()
    {
        return Excel::download(new ProductosTemplateExport(), 'plantilla_productos.xlsx');
    }

    public function importar(Request $request)
    {
        $request->validate([
            'archivoImportacion' => 'required|file|mimes:xlsx,xls'
        ], [
            'archivoImportacion.required' => 'Debes seleccionar un archivo.',
            'archivoImportacion.mimes' => 'El archivo debe ser un archivo de Excel (.xlsx o .xls).'
        ]);

        try {
            $importer = new ProductosImport(Auth::user()->sucursal_id);
            Excel::import($importer, $request->file('archivoImportacion'));

            if (count($importer->errors) > 0) {
                return back()->with('importErrors', $importer->errors);
            }

            return back()->with('importSuccess', "Se importaron correctamente {$importer->importedCount} productos.");
        } catch (\Exception $e) {
            return back()->with('importErrors', ['Ocurrió un error al procesar el archivo: ' . $e->getMessage()]);
        }
    }
}
