<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Livewire\Admin\Reportes\ManageReportes;

class ReporteExportController extends Controller
{
    public function exportar(Request $request)
    {
        $format = $request->input('format');

        if (!in_array($format, ['pdf', 'excel', 'csv'])) {
            return abort(400, 'Formato de exportación no válido.');
        }

        // Instanciar el componente y pasarle todos los parámetros del request
        // para que los filtros activos sean respetados en la exportación.
        $component = new ManageReportes();
        $component->period      = $request->input('period', 'mes');
        $component->start       = $request->input('start', '');
        $component->end         = $request->input('end', '');
        $component->categorias  = $request->input('categorias', []);
        $component->metodos_pago = $request->input('metodos_pago', []);
        $component->productos_top = $request->input('productos_top', []);

        if ($format === 'pdf') {
            return $component->exportPdf();
        }

        return $component->exportExcel($format);
    }
}

