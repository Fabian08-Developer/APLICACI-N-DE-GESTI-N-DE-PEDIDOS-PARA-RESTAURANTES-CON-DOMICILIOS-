<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mesa;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;

class MesaController extends Controller
{
    /**
     * Genera e imprime el código QR de una mesa en formato PDF.
     */
    public function imprimirQr($id)
    {
        // Obtener la mesa con la sucursal
        $mesa = Mesa::with('sucursal')->findOrFail($id);

        // Aislamiento por sucursal
        if ($mesa->sucursal_id !== Auth::user()->sucursal_id) {
            abort(403, 'No autorizado para ver esta mesa.');
        }

        // Generar la URL que escanea el cliente
        $url = route('cliente.qr', [
            'sucursal_slug' => $mesa->sucursal->slug,
            'codigo' => $mesa->codigo_qr
        ]);

        // Generar el código QR como SVG
        $qrCodeSvg = QrCode::size(250)->margin(1)->generate($url);

        // Cargar vista PDF
        $pdf = Pdf::loadView('admin.mesas.pdf-qr', compact('mesa', 'qrCodeSvg', 'url'));

        // Retornar descarga del archivo
        return $pdf->download('QR_Mesa_' . $mesa->numero . '.pdf');
    }
}
