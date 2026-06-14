<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ZonaCobertura;
use App\Models\Barrio;
use App\Models\PerfilDomiciliario;

use Illuminate\Validation\Rule;

class ZonaCoberturaController extends Controller
{
    // The index is handled by Livewire ManageZonas in V2, so we skip it here.

    public function store(Request $request)
    {
        $sucursal_id = auth()->user()->sucursal_id;
        if (!$sucursal_id) {
            return back()->withErrors(['general' => 'No tienes una sucursal asignada.']);
        }

        $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:150',
                Rule::unique('zonas_cobertura')->where(function ($query) use ($sucursal_id) {
                    return $query->where('sucursal_id', $sucursal_id);
                }),
            ],
            'costo_envio' => 'required|numeric|min:0',
            'tiempo_estimado' => 'required|integer|min:0',
            'descripcion' => 'nullable|string',
            'activo' => 'nullable|in:0,1',
        ], [
            'nombre.unique' => 'Ya existe una zona de cobertura con este nombre en esta sucursal.',
            'nombre.required' => 'El nombre de la zona es obligatorio.',
            'costo_envio.required' => 'El costo de envío es obligatorio.',
            'tiempo_estimado.required' => 'El tiempo estimado es obligatorio.',
        ]);

        $zona = ZonaCobertura::create([
            'sucursal_id' => $sucursal_id,
            'nombre' => $request->input('nombre'),
            'descripcion' => $request->input('descripcion'),
            'costo_envio' => $request->input('costo_envio'),
            'tiempo_estimado' => $request->input('tiempo_estimado'),
            'activo' => (bool)$request->input('activo'),
        ]);

        if ($request->filled('barrios')) {
            $barriosNames = explode(',', $request->input('barrios'));
            foreach ($barriosNames as $name) {
                $name = trim($name);
                if ($name !== '') {
                    Barrio::create([
                        'zona_id' => $zona->id,
                        'nombre' => $name,
                        'activo' => true
                    ]);
                }
            }
        }

        return redirect()->route('admin.zonas.index')->with('success', 'Zona creada correctamente.');
    }

    public function show($id)
    {
        $zona = ZonaCobertura::with(['barrios'])->find($id);
        if (!$zona) {
            return response()->json(['success' => false, 'message' => 'Zona no encontrada']);
        }

        $domiciliarios = PerfilDomiciliario::where('zona_id', $zona->id)->get()->map(function($dom) {
            return [
                'nombre' => $dom->nombre,
                'iniciales' => $dom->iniciales,
                'vehiculo' => ucfirst($dom->tipo_vehiculo) . ($dom->placa ? ' (' . $dom->placa . ')' : ''),
                'estado' => $dom->estado,
                'estado_color' => $dom->estado === 'disponible' ? 'success' : ($dom->estado === 'ocupado' ? 'warning' : ($dom->estado === 'en_ruta' ? 'info' : 'destructive'))
            ];
        });

        $data = [
            'id' => $zona->id,
            'nombre' => $zona->nombre,
            'descripcion' => $zona->descripcion,
            'costo_envio' => $zona->costo_envio,
            'tiempo_estimado' => $zona->tiempo_estimado,
            'activo' => (bool)$zona->activo,
            'barrios' => $zona->barrios->pluck('nombre')->toArray(),
            'domiciliarios' => $domiciliarios
        ];

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function update(Request $request, $id)
    {
        $zona = ZonaCobertura::find($id);
        if (!$zona) {
            return back()->withErrors(['general' => 'Zona no encontrada.']);
        }

        $sucursal_id = auth()->user()->sucursal_id;

        $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:150',
                Rule::unique('zonas_cobertura')->where(function ($query) use ($sucursal_id) {
                    return $query->where('sucursal_id', $sucursal_id);
                })->ignore($id),
            ],
            'costo_envio' => 'required|numeric|min:0',
            'tiempo_estimado' => 'required|integer|min:0',
            'descripcion' => 'nullable|string',
            'activo' => 'nullable|in:0,1',
        ], [
            'nombre.unique' => 'Ya existe una zona de cobertura con este nombre en esta sucursal.',
            'nombre.required' => 'El nombre de la zona es obligatorio.',
            'costo_envio.required' => 'El costo de envío es obligatorio.',
            'tiempo_estimado.required' => 'El tiempo estimado es obligatorio.',
        ]);

        $zona->update([
            'nombre' => $request->input('nombre'),
            'descripcion' => $request->input('descripcion'),
            'costo_envio' => $request->input('costo_envio'),
            'tiempo_estimado' => $request->input('tiempo_estimado'),
            'activo' => (bool)$request->input('activo'),
        ]);

        Barrio::where('zona_id', $zona->id)->delete();

        if ($request->filled('barrios')) {
            $barriosNames = explode(',', $request->input('barrios'));
            foreach ($barriosNames as $name) {
                $name = trim($name);
                if ($name !== '') {
                    Barrio::create([
                        'zona_id' => $zona->id,
                        'nombre' => $name,
                        'activo' => true
                    ]);
                }
            }
        }

        return redirect()->route('admin.zonas.index')->with('success', 'Zona actualizada correctamente.');
    }

    public function destroy($id)
    {
        $zona = ZonaCobertura::find($id);
        if ($zona) {
            $zona->delete();
        }
        return redirect()->route('admin.zonas.index')->with('success', 'Zona eliminada correctamente.');
    }

    public function getBarrios($id)
    {
        $barrios = Barrio::where('zona_id', $id)->get(['id', 'nombre']);
        return response()->json($barrios);
    }
}
