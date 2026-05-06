<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ZonaCobertura;
use App\Models\Barrio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ZonaCoberturaController extends Controller
{
    public function index()
    {
        $zonas = ZonaCobertura::withCount(['domiciliarios', 'barrios'])->with('barrios')->get();
        
        $stats = [
            'total' => $zonas->count(),
            'activas' => $zonas->where('activo', true)->count(),
            'costo_promedio' => $zonas->avg('costo_envio') ?? 0,
        ];

        return view('admin.zonas.index', compact('zonas', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'costo_envio' => 'required|numeric|min:0',
            'tiempo_estimado' => 'required|integer|min:1',
            'activo' => 'boolean',
            'barrios' => 'nullable|string', // Recibiremos una cadena separada por comas
        ]);

        DB::transaction(function () use ($validated) {
            $zona = ZonaCobertura::create($validated);

            if (!empty($validated['barrios'])) {
                $nombresBarrios = array_map('trim', explode(',', $validated['barrios']));
                foreach ($nombresBarrios as $nombre) {
                    if ($nombre != '') {
                        $zona->barrios()->create(['nombre' => $nombre]);
                    }
                }
            }
        });

        return redirect()->route('admin.zonas.index')
                         ->with('exito', 'Zona y barrios creados correctamente.');
    }

    public function update(Request $request, ZonaCobertura $zona)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'costo_envio' => 'required|numeric|min:0',
            'tiempo_estimado' => 'required|integer|min:1',
            'activo' => 'boolean',
            'barrios' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $zona) {
            $zona->update($validated);

            if (isset($validated['barrios'])) {
                // Sincronización simple: borrar y recrear (Scalable approach for this UI)
                $zona->barrios()->delete();
                $nombresBarrios = array_map('trim', explode(',', $validated['barrios']));
                foreach ($nombresBarrios as $nombre) {
                    if ($nombre != '') {
                        $zona->barrios()->create(['nombre' => $nombre]);
                    }
                }
            }
        });

        return redirect()->route('admin.zonas.index')
                         ->with('exito', 'Zona actualizada correctamente.');
    }

    public function destroy(ZonaCobertura $zona)
    {
        if ($zona->domiciliarios()->count() > 0) {
            return redirect()->back()->with('error', 'No se puede eliminar una zona que tiene domiciliarios asignados.');
        }

        $zona->delete();
        return redirect()->route('admin.zonas.index')
                         ->with('exito', 'Zona eliminada.');
    }

    public function getBarrios($id)
    {
        $barrios = Barrio::where('zona_id', $id)->where('activo', true)->get();
        return response()->json($barrios);
    }

    public function show($id)
    {
        $zona = ZonaCobertura::with(['barrios', 'domiciliarios'])->findOrFail($id);
        
        // Formatear domiciliarios con sus iniciales y colores de estado
        $domiciliarios = $zona->domiciliarios->map(function($dom) {
            return [
                'id' => $dom->id,
                'nombre' => $dom->nombre,
                'iniciales' => $dom->iniciales,
                'vehiculo' => $dom->vehiculo_tipo,
                'estado' => $dom->estado,
                'estado_color' => $dom->estado_color
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $zona->id,
                'nombre' => $zona->nombre,
                'descripcion' => $zona->descripcion,
                'costo_envio' => $zona->costo_envio,
                'tiempo_estimado' => $zona->tiempo_estimado,
                'activo' => $zona->activo,
                'barrios' => $zona->barrios->pluck('nombre'),
                'domiciliarios' => $domiciliarios
            ]
        ]);
    }
}
