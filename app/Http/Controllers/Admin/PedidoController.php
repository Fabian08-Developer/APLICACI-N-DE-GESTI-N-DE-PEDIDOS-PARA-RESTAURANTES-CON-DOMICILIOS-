<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HistorialEstadoPedido;
use App\Models\Pedido;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    public function index(Request $request)
    {
        // Query base
        $query = Pedido::with(['mesero', 'sesionMesa.mesa', 'detalles']);

        // Aplicamos filtros desde el request
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('created_at', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('created_at', '<=', $request->fecha_fin);
        }
        if ($request->filled('mesa_id')) {
            $query->whereHas('sesionMesa', function ($q) use ($request) {
                $q->where('mesa_id', $request->mesa_id);
            });
        }
        if ($request->filled('mesero_id')) {
            $query->where('mesero_id', $request->mesero_id);
        }

        $pedidos = $query->latest()->get();

        // Para poblar los selects en la vista
        $mesas = \App\Models\Mesa::orderBy('numero')->get();
        $meseros = \App\Models\Usuario::whereHas('rol', function($q) {
            $q->where('nombre', 'mesero');
        })->get();

        return view('admin.pedidos.index', compact('pedidos', 'mesas', 'meseros'));
    }

    public function detalle($id)
    {
        $pedido = Pedido::with([
            'mesero',
            'sesionMesa.mesa',
            'detalles.producto',
            'historial.usuario',
        ])->findOrFail($id);

        return view('admin.pedidos.detalle', compact('pedido'));
    }

    public function cambiarEstado(Request $request, $id)
    {
        $pedido = Pedido::findOrFail($id);

        $request->validate([
            'estado' => 'required|in:CREADO,EN_COCINA,LISTO,ENTREGADO',
        ]);

        $pedido->update(['estado' => $request->estado]);

        HistorialEstadoPedido::create([
            'pedido_id'  => $pedido->id,
            'estado'     => $request->estado,
            'usuario_id' => auth()->id(),
            'fecha'      => now(),
        ]);

        return redirect()->back()
                         ->with('exito', 'Estado del pedido actualizado a ' . $request->estado);
    }

    public function cancelar(Request $request, $id)
    {
        $pedido = Pedido::findOrFail($id);

        $request->validate([
            'motivo_cancelacion' => 'required|string|max:255',
        ], [
            'motivo_cancelacion.required' => 'Debes indicar el motivo de cancelación',
        ]);

        $pedido->update([
            'estado'             => 'CANCELADO',
            'fecha_cancelacion'  => now(),
            'motivo_cancelacion' => $request->motivo_cancelacion,
        ]);

        HistorialEstadoPedido::create([
            'pedido_id'  => $pedido->id,
            'estado'     => 'CANCELADO',
            'usuario_id' => auth()->id(),
            'fecha'      => now(),
        ]);

        return redirect()->route('admin.pedidos.index')
                         ->with('exito', 'Pedido #' . $pedido->id . ' cancelado');
    }
}