<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PerfilDomiciliario;
use Spatie\Permission\Models\Role;

class DomiciliarioController extends Controller
{
    public function store(Request $request)
    {
        $sucursal_id = auth()->user()->sucursal_id;
        $empresa_id = auth()->user()->empresa_id;
        if (!$sucursal_id) {
            return back()->withErrors(['general' => 'No tienes una sucursal asignada.']);
        }

        $request->validate([
            'nombre' => ['required', 'string', 'max:150', 'not_regex:/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/u'],
            'telefono' => 'required|string|max:30',
            'vehiculo_tipo' => 'required|string|in:moto,bicicleta,carro',
            'placa' => 'nullable|string|max:20',
            'zona_id' => 'required|uuid|exists:zonas_cobertura,id',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.not_regex' => 'El nombre no debe contener emojis.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'zona_id.required' => 'La zona de trabajo es obligatoria.',
            'zona_id.exists' => 'La zona seleccionada no es válida.',
        ]);

        $email = 'dom.' . preg_replace('/[^a-zA-Z0-9]/', '', $request->input('nombre')) . rand(100, 999) . '@cafeteria.com';
        $user = User::create([
            'empresa_id' => $empresa_id,
            'sucursal_id' => $sucursal_id,
            'nombre' => $request->input('nombre'),
            'correo' => $email,
            'telefono' => $request->input('telefono'),
            'contrasena' => bcrypt('123456'),
            'activo' => true,
            'rol' => 'domiciliario',
        ]);

        $dom = PerfilDomiciliario::create([
            'usuario_id' => $user->id,
            'sucursal_id' => $sucursal_id,
            'zona_id' => $request->input('zona_id'),
            'tipo_vehiculo' => $request->input('vehiculo_tipo'),
            'placa' => $request->input('placa'),
            'estado' => 'disponible',
        ]);

        return redirect()->route('admin.domiciliarios.index')->with('success', 'Domiciliario creado correctamente.');
    }

    public function show($id)
    {
        $dom = PerfilDomiciliario::with(['usuario', 'zona', 'liquidaciones.aprobador', 'calificaciones.cliente'])->find($id);
        if (!$dom) {
            return response()->json(['success' => false, 'message' => 'Domiciliario no encontrado']);
        }

        $data = [
            'id' => $dom->id,
            'nombre' => $dom->nombre,
            'telefono' => $dom->telefono,
            'estado' => $dom->estado,
            'vehiculo_tipo' => $dom->tipo_vehiculo,
            'placa' => $dom->placa,
            'zona_id' => $dom->zona_id,
            'zona' => $dom->zona ? ['nombre' => $dom->zona->nombre] : null,
            'pedidos_hoy' => $dom->pedidos_hoy,
            'calificacion' => $dom->calificacion,
            'efectivo_pendiente' => $dom->efectivo_pendiente,
            'tiene_bloqueo' => $dom->tiene_bloqueo,
            'liquidaciones' => $dom->liquidaciones->map(function($liq) {
                return [
                    'id' => $liq->id,
                    'monto' => $liq->monto,
                    'fecha' => \Carbon\Carbon::parse($liq->liquidado_en)->format('d/m/Y H:i'),
                    'aprobador' => $liq->aprobador ? $liq->aprobador->nombre : 'Administrador',
                    'notas' => $liq->notas
                ];
            }),
            'calificaciones' => $dom->calificaciones->map(function($cal) {
                return [
                    'id' => $cal->id,
                    'puntuacion' => $cal->puntuacion,
                    'comentario' => $cal->comentario,
                    'fecha' => \Carbon\Carbon::parse($cal->creado_en)->format('d/m/Y'),
                    'cliente' => $cal->cliente ? $cal->cliente->nombre : 'Cliente Anónimo'
                ];
            })
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
            'iniciales' => $dom->iniciales,
            'estado_color' => $dom->estado === 'disponible' ? 'success' : ($dom->estado === 'ocupado' ? 'warning' : ($dom->estado === 'en_ruta' ? 'info' : 'destructive'))
        ]);
    }

    public function update(Request $request, $id)
    {
        $dom = PerfilDomiciliario::find($id);
        if (!$dom) {
            return back()->withErrors(['general' => 'Domiciliario no encontrado.']);
        }

        $request->validate([
            'nombre' => ['required', 'string', 'max:150', 'not_regex:/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/u'],
            'telefono' => 'required|string|max:30',
            'vehiculo_tipo' => 'required|string|in:moto,bicicleta,carro',
            'placa' => 'nullable|string|max:20',
            'zona_id' => 'required|uuid|exists:zonas_cobertura,id',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.not_regex' => 'El nombre no debe contener emojis.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'zona_id.required' => 'La zona de trabajo es obligatoria.',
            'zona_id.exists' => 'La zona seleccionada no es válida.',
        ]);

        $dom->update([
            'zona_id' => $request->input('zona_id'),
            'tipo_vehiculo' => $request->input('vehiculo_tipo'),
            'placa' => $request->input('placa'),
        ]);

        if ($dom->usuario) {
            $dom->usuario->update([
                'nombre' => $request->input('nombre'),
                'telefono' => $request->input('telefono'),
            ]);
        }

        return redirect()->route('admin.domiciliarios.index')->with('success', 'Domiciliario actualizado correctamente.');
    }
}
