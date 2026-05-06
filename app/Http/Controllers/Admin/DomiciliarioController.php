<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Domiciliario;
use App\Models\ZonaCobertura;
use Illuminate\Http\Request;

class DomiciliarioController extends Controller
{
    public function index(Request $request)
    {
        $query = Domiciliario::with('zona');

        // Filtros
        if ($request->filled('search')) {
            $query->where('nombre', 'like', '%' . $request->search . '%')
                  ->orWhere('telefono', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('estado') && $request->estado !== 'todos') {
            $query->where('estado', $request->estado);
        }

        $domiciliarios = $query->latest()->get();
        $zonas = ZonaCobertura::where('activo', true)->get();

        // Estadísticas
        $stats = [
            'total' => Domiciliario::count(),
            'disponibles' => Domiciliario::where('estado', 'disponible')->count(),
            'en_ruta' => Domiciliario::where('estado', 'en_ruta')->count(),
            'ocupados' => Domiciliario::where('estado', 'ocupado')->count(),
            'fuera_servicio' => Domiciliario::where('estado', 'fuera_servicio')->count(),
        ];

        return view('admin.domiciliarios.index', compact('domiciliarios', 'zonas', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'vehiculo_tipo' => 'required|in:moto,bicicleta,carro',
            'placa' => 'nullable|string|max:20',
            'zona_id' => 'required|exists:zona_coberturas,id',
            'documento' => 'nullable|string|max:20',
            'barrios' => 'nullable|array',
            'barrios.*' => 'exists:barrios,id',
        ]);

        $domiciliario = Domiciliario::create($validated);
        
        if ($request->has('barrios')) {
            $domiciliario->barrios()->sync($request->barrios);
        }

        return redirect()->route('admin.domiciliarios.index')
                         ->with('exito', 'Domiciliario registrado correctamente.');
    }

    public function update(Request $request, Domiciliario $domiciliario)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'vehiculo_tipo' => 'required|in:moto,bicicleta,carro',
            'placa' => 'nullable|string|max:20',
            'zona_id' => 'required|exists:zona_coberturas,id',
            'estado' => 'required|in:disponible,en_ruta,ocupado,fuera_servicio',
            'barrios' => 'nullable|array',
            'barrios.*' => 'exists:barrios,id',
        ]);

        $domiciliario->update($validated);

        if ($request->has('barrios')) {
            $domiciliario->barrios()->sync($request->barrios);
        } else {
            $domiciliario->barrios()->detach();
        }

        return redirect()->route('admin.domiciliarios.index')
                         ->with('exito', 'Datos actualizados correctamente.');
    }

    public function destroy(Domiciliario $domiciliario)
    {
        $domiciliario->delete();
        return redirect()->route('admin.domiciliarios.index')
                         ->with('exito', 'Domiciliario eliminado del sistema.');
    }

    /**
     * Retorna datos JSON para el modal de detalle o edición
     */
    public function show(Domiciliario $domiciliario)
    {
        $domiciliario->load(['zona', 'barrios']);
        return response()->json([
            'success' => true,
            'data' => $domiciliario,
            'iniciales' => $domiciliario->iniciales,
            'estado_color' => $domiciliario->estado_color,
            'barrios_ids' => $domiciliario->barrios->pluck('id')
        ]);
    }
}
