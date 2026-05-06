<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mesa;
use Illuminate\Http\Request;

class MesaController extends Controller
{
    public function index()
    {
        $mesas  = Mesa::orderBy('numero')->get();
        $editar = null;

        return view('admin.mesas.index', compact('mesas', 'editar'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'numero'    => 'required|integer|unique:mesas,numero',
            'capacidad' => 'nullable|integer|min:1',
            'estado'    => 'required|in:DISPONIBLE,OCUPADA,RESERVADA',
        ], [
            'numero.required' => 'El número de mesa es obligatorio',
            'numero.unique'   => 'Ya existe una mesa con ese número',
        ]);

        // Creamos la mesa y generamos su código QR automáticamente
        $mesa = Mesa::create([
            'numero'    => $request->numero,
            'capacidad' => $request->capacidad,
            'estado'    => $request->estado,
        ]);

        $mesa->update([
            'qr_codigo' => Mesa::generarCodigoQR($mesa->id),
            'qr_activo' => true,
        ]);

        return redirect()->route('admin.mesas.index')
                         ->with('exito', 'Mesa creada. QR generado automáticamente.');
    }

    public function editar($id)
    {
        $mesas  = Mesa::orderBy('numero')->get();
        $editar = Mesa::findOrFail($id);

        return view('admin.mesas.index', compact('mesas', 'editar'));
    }

    public function actualizar(Request $request, $id)
    {
        $mesa = Mesa::findOrFail($id);

        $request->validate([
            'numero'    => 'required|integer|unique:mesas,numero,' . $id,
            'capacidad' => 'nullable|integer|min:1',
            'estado'    => 'required|in:DISPONIBLE,OCUPADA,RESERVADA',
        ]);

        $mesa->update($request->only('numero', 'capacidad', 'estado'));

        return redirect()->route('admin.mesas.index')
                         ->with('exito', 'Mesa actualizada correctamente');
    }

    public function eliminar($id)
    {
        $mesa = Mesa::findOrFail($id);

        try {
            $mesa->delete();
            return redirect()->route('admin.mesas.index')
                             ->with('exito', 'Mesa eliminada correctamente');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23503 || $e->errorInfo[1] == 1451) {
                return redirect()->route('admin.mesas.index')
                                 ->with('error', 'No se puede eliminar la mesa porque tiene sesiones o pedidos asociados.');
            }
            return redirect()->route('admin.mesas.index')
                             ->with('error', 'Ocurrió un error al intentar eliminar la mesa.');
        }
    }

    /**
     * Ver el enlace QR de una mesa
     * Ruta: GET /admin/mesas/{id}/qr
     */
    public function verQR($id)
    {
        $mesa = Mesa::findOrFail($id);

        if (!$mesa->qr_codigo) {
            $mesa->update([
                'qr_codigo' => Mesa::generarCodigoQR($mesa->id),
                'qr_activo' => true,
            ]);
        }

        $urlQR = url('/mesa/' . $mesa->qr_codigo);

        return view('admin.mesas.qr', compact('mesa', 'urlQR'));
    }
}