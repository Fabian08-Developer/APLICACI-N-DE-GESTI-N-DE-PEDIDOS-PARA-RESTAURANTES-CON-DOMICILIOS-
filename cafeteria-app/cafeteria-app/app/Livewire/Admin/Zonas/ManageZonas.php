<?php

namespace App\Livewire\Admin\Zonas;

use Livewire\Component;
use App\Models\ZonaCobertura;
use App\Models\Barrio;
use App\Models\PerfilDomiciliario;
use App\Models\Pedido;
use Illuminate\Validation\Rule;

class ManageZonas extends Component
{
    // Form fields
    public ?string $zonaId = null;
    public string $nombre = '';
    public ?string $descripcion = null;
    public float $costo_envio = 0;
    public int $tiempo_estimado = 30;
    public bool $activo = true;
    public string $barrios = ''; // comma-separated barrios

    // Modal / Sidebar controls
    public bool $isOpen = false;
    public bool $isEdit = false;

    // Selected zone for details view
    public $selectedZona = null;

    protected function rules()
    {
        $sucursal_id = auth()->user()->sucursal_id;
        return [
            'nombre' => [
                'required',
                'string',
                'max:150',
                Rule::unique('zonas_cobertura')
                    ->where('sucursal_id', $sucursal_id)
                    ->ignore($this->zonaId),
            ],
            'costo_envio' => 'required|numeric|min:0',
            'tiempo_estimado' => 'required|integer|min:0',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ];
    }

    protected $messages = [
        'nombre.unique' => 'Ya existe una zona de cobertura con este nombre en esta sucursal.',
        'nombre.required' => 'El nombre de la zona es obligatorio.',
        'costo_envio.required' => 'El costo de envío es obligatorio.',
        'costo_envio.min' => 'El costo de envío no puede ser negativo.',
        'tiempo_estimado.required' => 'El tiempo estimado es obligatorio.',
        'tiempo_estimado.min' => 'El tiempo estimado no puede ser negativo.',
    ];

    public function mount()
    {
        if (!auth()->user()->sucursal_id) {
            return redirect()->route('sucursales');
        }
    }

    public function openCreate()
    {
        $this->resetValidation();
        $this->resetFields();
        $this->isEdit = false;
        $this->isOpen = true;
        $this->dispatch('open-sidebar');
    }

    public function openEdit($id)
    {
        $this->resetValidation();
        $zona = ZonaCobertura::with('barrios')->find($id);
        if ($zona) {
            $this->zonaId = $zona->id;
            $this->nombre = $zona->nombre;
            $this->descripcion = $zona->descripcion;
            $this->costo_envio = (float) $zona->costo_envio;
            $this->tiempo_estimado = (int) $zona->tiempo_estimado;
            $this->activo = (bool) $zona->activo;
            $this->barrios = $zona->barrios->pluck('nombre')->implode(', ');
            $this->isEdit = true;
            $this->isOpen = true;
            $this->dispatch('open-sidebar');
        }
    }

    public function closeSidebar()
    {
        $this->isOpen = false;
        $this->resetFields();
        $this->dispatch('close-sidebar');
    }

    public function resetFields()
    {
        $this->zonaId = null;
        $this->nombre = '';
        $this->descripcion = null;
        $this->costo_envio = 0;
        $this->tiempo_estimado = 30;
        $this->activo = true;
        $this->barrios = '';
    }

    public function save()
    {
        $this->validate();

        $sucursal_id = auth()->user()->sucursal_id;

        if ($this->isEdit) {
            $zona = ZonaCobertura::find($this->zonaId);
            if (!$zona) {
                session()->flash('error', 'Zona no encontrada.');
                return;
            }
            $zona->update([
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'costo_envio' => $this->costo_envio,
                'tiempo_estimado' => $this->tiempo_estimado,
                'activo' => $this->activo,
            ]);
            $msg = 'Zona actualizada correctamente.';
        } else {
            $zona = ZonaCobertura::create([
                'sucursal_id' => $sucursal_id,
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'costo_envio' => $this->costo_envio,
                'tiempo_estimado' => $this->tiempo_estimado,
                'activo' => $this->activo,
            ]);
            $msg = 'Zona creada correctamente.';
        }

        // Sync barrios
        Barrio::where('zona_id', $zona->id)->delete();

        if (!empty(trim($this->barrios))) {
            $barriosNames = explode(',', $this->barrios);
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

        session()->flash('success', $msg);
        $this->closeSidebar();
    }

    public function delete($id)
    {
        $zona = ZonaCobertura::find($id);
        if ($zona) {
            // Validate active orders
            $hasActiveOrders = Pedido::where('zona_id', $zona->id)
                ->whereNotIn('estado', ['entregado', 'cancelado'])
                ->exists();

            if ($hasActiveOrders) {
                session()->flash('error', 'No se puede eliminar la zona porque tiene pedidos activos en curso.');
                return;
            }

            $zona->delete();
            session()->flash('success', 'Zona eliminada correctamente.');
        }
    }

    public function toggleActivo($id)
    {
        $zona = ZonaCobertura::find($id);
        if ($zona) {
            $zona->update(['activo' => !$zona->activo]);
            session()->flash('success', 'Estado de la zona actualizado correctamente.');
        }
    }

    public function viewDetail($id)
    {
        $zona = ZonaCobertura::with('barrios')->find($id);
        if ($zona) {
            $doms = PerfilDomiciliario::with('usuario')
                ->where('zona_id', $zona->id)
                ->get()
                ->map(function($dom) {
                    return [
                        'nombre' => $dom->nombre,
                        'iniciales' => $dom->iniciales,
                        'vehiculo' => ucfirst($dom->tipo_vehiculo) . ($dom->placa ? ' (' . $dom->placa . ')' : ''),
                        'estado' => $dom->estado,
                        'estado_color' => $dom->estado === 'disponible' ? 'success' : ($dom->estado === 'ocupado' ? 'warning' : ($dom->estado === 'en_ruta' ? 'info' : 'destructive'))
                    ];
                })->toArray();

            $this->selectedZona = [
                'id' => $zona->id,
                'nombre' => $zona->nombre,
                'descripcion' => $zona->descripcion,
                'costo_envio' => $zona->costo_envio,
                'tiempo_estimado' => $zona->tiempo_estimado,
                'activo' => (bool)$zona->activo,
                'barrios' => $zona->barrios->pluck('nombre')->toArray(),
                'domiciliarios' => $doms
            ];
            $this->dispatch('open-detail-modal');
        }
    }

    public function render()
    {
        $user = auth()->user();
        $sucursal_id = $user->sucursal_id;

        $zonas = ZonaCobertura::with(['barrios'])
            ->withCount(['barrios' => function($query) {
                $query->where('activo', true);
            }])
            ->where('sucursal_id', $sucursal_id)
            ->get();

        foreach ($zonas as $zona) {
            $zona->domiciliarios_count = PerfilDomiciliario::where('zona_id', $zona->id)->count();
            $zona->domiciliarios = PerfilDomiciliario::where('zona_id', $zona->id)->get();
        }

        $stats = [
            'total' => $zonas->count(),
            'activas' => $zonas->where('activo', true)->count(),
            'costo_promedio' => $zonas->avg('costo_envio') ?: 0
        ];

        return view('livewire.admin.zonas.manage-zonas', [
            'stats' => $stats,
            'zonas' => $zonas,
        ])->layout('layouts.admin');
    }
}
