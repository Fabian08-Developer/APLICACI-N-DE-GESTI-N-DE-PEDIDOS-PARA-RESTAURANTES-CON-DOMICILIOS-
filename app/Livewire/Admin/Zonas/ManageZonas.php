<?php

namespace App\Livewire\Admin\Zonas;

use Livewire\Component;
use App\Models\ZonaCobertura;
use App\Models\Barrio;
use App\Models\PerfilDomiciliario;
use App\Models\Pedido;
use App\Models\SucursalBarrioTarifa;
use Illuminate\Validation\Rule;

class ManageZonas extends Component
{
    // Form fields — Zona
    public ?string $zonaId = null;
    public string $nombre = '';
    public ?string $descripcion = null;
    public float $costo_envio = 0;
    public int $tiempo_estimado = 30;
    public bool $activo = true;
    public string $barrios = ''; // comma-separated barrios

    // Form fields — Barrio (editor individual)
    public ?string $barrioEditId = null;
    public string $barrioNombre = '';
    public ?float $barrioLat = null;
    public ?float $barrioLon = null;
    public float $barrioCosto = 0;
    public int $barrioTiempo = 30;
    public bool $barrioActivo = true;

    // Modal / Sidebar controls
    public bool $isOpen = false;
    public bool $isEdit = false;
    public bool $isBarrioEditorOpen = false;

    // Selected zone for details view
    public $selectedZona = null;

    // Propiedades para Modal Eliminar
    public $showModalEliminarLivewire = false;
    public $zona_eliminar_id;
    public $zona_eliminar_nombre = '';

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

    public function resetBarrioFields()
    {
        $this->barrioEditId  = null;
        $this->barrioNombre  = '';
        $this->barrioLat     = null;
        $this->barrioLon     = null;
        $this->barrioCosto   = 0;
        $this->barrioTiempo  = 30;
        $this->barrioActivo  = true;
        $this->isBarrioEditorOpen = false;
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

        // Sincronización inteligente no destructiva de barrios (preservando coordenadas y UUIDs)
        $existingBarrios = Barrio::where('zona_id', $zona->id)->get();
        $keptBarrioIds = [];

        if (!empty(trim($this->barrios))) {
            $barriosNames = explode(',', $this->barrios);
            foreach ($barriosNames as $name) {
                $name = trim($name);
                if ($name !== '') {
                    $existing = $existingBarrios->first(function ($b) use ($name) {
                        return mb_strtolower($b->nombre) === mb_strtolower($name);
                    });

                    if ($existing) {
                        // Actualizar datos básicos sin sobreescribir coordenadas
                        $existing->update([
                            'nombre'      => $name,
                            'sucursal_id' => $sucursal_id,
                            'activo'      => true,
                        ]);
                        $keptBarrioIds[] = $existing->id;

                        // Asegurar tarifa en la pivote sede-barrio
                        SucursalBarrioTarifa::updateOrCreate(
                            [
                                'sucursal_id' => $sucursal_id,
                                'barrio_id'   => $existing->id,
                            ],
                            [
                                'costo_envio'     => $this->costo_envio,
                                'tiempo_estimado' => $this->tiempo_estimado,
                                'activo'          => true,
                            ]
                        );
                    } else {
                        // Crear nuevo barrio con sucursal_id
                        $barrio = Barrio::create([
                            'zona_id'     => $zona->id,
                            'sucursal_id' => $sucursal_id,
                            'nombre'      => $name,
                            'activo'      => true,
                        ]);
                        $keptBarrioIds[] = $barrio->id;

                        // Propagar tarifa a la pivote sede-barrio
                        SucursalBarrioTarifa::updateOrCreate(
                            [
                                'sucursal_id' => $sucursal_id,
                                'barrio_id'   => $barrio->id,
                            ],
                            [
                                'costo_envio'     => $this->costo_envio,
                                'tiempo_estimado' => $this->tiempo_estimado,
                                'activo'          => true,
                            ]
                        );
                    }
                }
            }
        }

        // Eliminar únicamente los barrios que fueron eliminados explícitamente del input
        Barrio::where('zona_id', $zona->id)
            ->whereNotIn('id', $keptBarrioIds)
            ->delete();

        session()->flash('success', $msg);
        $this->closeSidebar();
    }

    public function openEliminarModal($id)
    {
        $zona = ZonaCobertura::find($id);
        if ($zona) {
            $this->zona_eliminar_id = $zona->id;
            $this->zona_eliminar_nombre = $zona->nombre;
            $this->showModalEliminarLivewire = true;
        }
    }

    public function eliminarZona($id)
    {
        $zona = ZonaCobertura::find($id);
        if ($zona) {
            // Validate active orders
            $hasActiveOrders = Pedido::where('zona_id', $zona->id)
                ->whereNotIn('estado', ['entregado', 'cancelado'])
                ->exists();

            if ($hasActiveOrders) {
                session()->flash('error', 'No se puede eliminar la zona porque tiene pedidos activos en curso.');
                $this->showModalEliminarLivewire = false;
                return;
            }

            $zona->delete();
            session()->flash('success', 'Zona eliminada correctamente.');
            $this->showModalEliminarLivewire = false;
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
                        'nombre'      => $dom->nombre,
                        'iniciales'   => $dom->iniciales,
                        'vehiculo'    => ucfirst($dom->tipo_vehiculo) . ($dom->placa ? ' (' . $dom->placa . ')' : ''),
                        'estado'      => $dom->estado,
                        'estado_color'=> $dom->estado === 'disponible' ? 'success' : ($dom->estado === 'ocupado' ? 'warning' : ($dom->estado === 'en_ruta' ? 'info' : 'destructive'))
                    ];
                })->toArray();

            $this->selectedZona = [
                'id'             => $zona->id,
                'nombre'         => $zona->nombre,
                'descripcion'    => $zona->descripcion,
                'costo_envio'    => $zona->costo_envio,
                'tiempo_estimado'=> $zona->tiempo_estimado,
                'activo'         => (bool)$zona->activo,
                'barrios'        => $zona->barrios->map(fn($b) => [
                    'id'             => $b->id,
                    'nombre'         => $b->nombre,
                    'tiene_ubicacion'=> !is_null($b->latitud) && !is_null($b->longitud),
                    'latitud'        => $b->latitud,
                    'longitud'       => $b->longitud,
                ])->toArray(),
                'domiciliarios'  => $doms,
            ];
            $this->dispatch('open-detail-modal');
        }
    }

    /**
     * Abre el editor de ubicación + tarifa para un barrio específico.
     */
    public function editarBarrioUbicacion(string $barrioId): void
    {
        $barrio = Barrio::find($barrioId);
        if (!$barrio) return;

        $sucursalId = auth()->user()->sucursal_id;

        // Cargar tarifa actual de la pivote (si existe)
        $tarifa = SucursalBarrioTarifa::where('sucursal_id', $sucursalId)
                                       ->where('barrio_id', $barrioId)
                                       ->first();

        $zona = ZonaCobertura::find($barrio->zona_id);

        $this->barrioEditId  = $barrio->id;
        $this->barrioNombre  = $barrio->nombre;
        $this->barrioLat     = $barrio->latitud ? (float) $barrio->latitud : null;
        $this->barrioLon     = $barrio->longitud ? (float) $barrio->longitud : null;
        $this->barrioCosto   = $tarifa ? (float) $tarifa->costo_envio : ($zona ? (float) $zona->costo_envio : 0);
        $this->barrioTiempo  = $tarifa ? (int) $tarifa->tiempo_estimado : ($zona ? (int) $zona->tiempo_estimado : 30);
        $this->barrioActivo  = $tarifa ? (bool) $tarifa->activo : true;
        $this->isBarrioEditorOpen = true;
        $this->dispatch('open-barrio-editor');
    }

    /**
     * Guarda las coordenadas y tarifa override de un barrio.
     */
    public function guardarBarrioUbicacion(): void
    {
        $this->validate([
            'barrioLat'   => 'nullable|numeric|between:-90,90',
            'barrioLon'   => 'nullable|numeric|between:-180,180',
            'barrioCosto' => 'required|numeric|min:0',
            'barrioTiempo'=> 'required|integer|min:0',
        ]);

        $sucursalId = auth()->user()->sucursal_id;

        // Actualizar coordenadas del barrio
        Barrio::where('id', $this->barrioEditId)->update([
            'latitud'  => $this->barrioLat,
            'longitud' => $this->barrioLon,
        ]);

        // Actualizar/crear tarifa en la pivote
        SucursalBarrioTarifa::updateOrCreate(
            ['sucursal_id' => $sucursalId, 'barrio_id' => $this->barrioEditId],
            [
                'costo_envio'     => $this->barrioCosto,
                'tiempo_estimado' => $this->barrioTiempo,
                'activo'          => $this->barrioActivo,
            ]
        );

        session()->flash('success', "Barrio '{$this->barrioNombre}' actualizado correctamente.");
        $this->resetBarrioFields();
        $this->dispatch('close-barrio-editor');
        // Refrescar el detalle de la zona si está abierto
        if ($this->selectedZona) {
            $this->viewDetail($this->selectedZona['id']);
        }
    }

    /**
     * Cierra el editor de barrio sin guardar.
     */
    public function cerrarBarrioEditor(): void
    {
        $this->resetBarrioFields();
        $this->dispatch('close-barrio-editor');
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
