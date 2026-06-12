<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Livewire\Admin\Reportes\ManageReportes;

class ReporteExportController extends Controller
{
    public function exportar(Request $request)
    {
        $component = new ManageReportes();
        $format = $request->input('format');

        if ($format === 'pdf') {
            return $component->exportPdf();
        } elseif ($format === 'excel' || $format === 'csv') {
            return $component->exportExcel($format);
        }

        return abort(400);
    }
}
