<?php

namespace App\Livewire\Sucursales;

use Livewire\Component;
use App\Models\Sucursal;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ManageSucursales extends Component
{
    public $search = '';
    public $filterStatus = 'todas';
    public $viewMode = 'grid';

    // Form fields
    public $sucursalId;
    public $nombre;
    public $slug;
    public $direccion;
    public $ciudad;
    public $telefono;
    public $activo = true;
    public $latitud;
    public $longitud;
    
    public $isEditing = false;
    public $showModal = false;

    protected function rules()
    {
        $empresaId = auth()->user()->empresa_id;
        $ignoreId  = $this->sucursalId ?? 'NULL';

        return [
            // Nombre único dentro de la misma empresa
            'nombre'   => [
                'required', 'string', 'max:150',
                \Illuminate\Validation\Rule::unique('sucursales')
                    ->where('empresa_id', $empresaId)
                    ->ignore($ignoreId),
            ],
            // Slug único global (ya es UNIQUE en la BD)
            'slug'     => [
                'required', 'string', 'max:100',
                \Illuminate\Validation\Rule::unique('sucursales', 'slug')
                    ->ignore($ignoreId),
            ],
            'direccion' => 'nullable|string',
            'ciudad'    => 'required|string|max:100',
            // Teléfono único dentro de la misma empresa (si se proporciona)
            'telefono'  => [
                'nullable', 'string', 'max:30',
                \Illuminate\Validation\Rule::unique('sucursales')
                    ->where('empresa_id', $empresaId)
                    ->ignore($ignoreId),
            ],
            'activo'    => 'boolean',
            'latitud'   => 'nullable|numeric',
            'longitud'  => 'nullable|numeric',
        ];
    }

    public function getStatsProperty()
    {
        $all = Sucursal::where('empresa_id', auth()->user()->empresa_id)->get();
        
        $startOfMonth = \Carbon\Carbon::now()->startOfMonth();
        $ventasMes = \App\Models\Pedido::whereHas('sucursal', function ($query) {
                $query->where('empresa_id', auth()->user()->empresa_id);
            })
            ->whereNotIn('estado', ['cancelado', 'CANCELADO'])
            ->whereDate('creado_en', '>=', $startOfMonth)
            ->sum('total');

        return [
            'total' => $all->count(),
            'activas' => $all->where('activo', true)->count(),
            'inactivas' => $all->where('activo', false)->count(),
            'ventas_mes' => $ventasMes,
        ];
    }

    public function getSucursalesProperty()
    {
        return Sucursal::where('empresa_id', auth()->user()->empresa_id)
            ->when($this->search, function($q) {
                $q->where(function($sub) {
                    $sub->where('nombre', 'ilike', '%' . $this->search . '%')
                        ->orWhere('ciudad', 'ilike', '%' . $this->search . '%')
                        ->orWhere('slug', 'ilike', '%' . $this->search . '%');
                });
            })
            ->when($this->filterStatus !== 'todas', function($q) {
                $q->where('activo', $this->filterStatus === 'activas');
            })
            ->withCount('usuarios')
            ->withSum(['pedidos as ventas_mes_sum' => function ($query) {
                $query->whereNotIn('estado', ['cancelado', 'CANCELADO'])
                      ->whereDate('creado_en', '>=', \Carbon\Carbon::now()->startOfMonth());
            }], 'total')
            ->get();
    }

    // Auto-slug generation - Mejorado para evitar latencia innecesaria
    public function updatedNombre($value)
    {
        if (!$this->isEditing) {
            $this->slug = Str::slug($value);
        }
    }

    public function openCreateModal()
    {
        // Solo gerentes con empresa activa pueden abrir el modal de creación
        if (!auth()->user()->hasRole('gerente')) {
            $this->dispatch('swal', ['title' => 'Sin permiso', 'text' => 'Solo el gerente puede crear sucursales.', 'icon' => 'error']);
            return;
        }
        if (!auth()->user()->empresa || !auth()->user()->empresa->activo) {
            $this->dispatch('swal', ['title' => 'Empresa inactiva', 'text' => 'Tu empresa está suspendida. No puedes crear sucursales.', 'icon' => 'warning']);
            return;
        }

        $this->resetForm();
        $this->showModal = true;
    }

    public function resetForm()
    {
        $this->sucursalId = null;
        $this->nombre = '';
        $this->slug = '';
        $this->direccion = '';
        $this->ciudad = '';
        $this->telefono = '';
        $this->activo = true;
        $this->latitud = null;
        $this->longitud = null;
        $this->isEditing = false;
        $this->resetErrorBag();
    }

    public function save()
    {
        // ─── GUARDIA DE SEGURIDAD (segunda capa) ─────────────────────────────
        if (!$this->isEditing) {
            if (!auth()->user()->hasRole('gerente')) {
                return;
            }
            if (!auth()->user()->empresa || !auth()->user()->empresa->activo) {
                $this->dispatch('swal', ['title' => 'Empresa inactiva', 'text' => 'No puedes crear sucursales mientras tu empresa esté suspendida.', 'icon' => 'warning']);
                return;
            }
        }
        // ────────────────────────────────────────────────────────────────────

        $this->validate();

        // ─── LÍMITE DE SUCURSALES POR EMPRESA ───────────────────────────────
        // Un gerente solo puede crear hasta 3 sucursales por empresa.
        // El Super Admin puede modificar este límite en el futuro.
        if (!$this->isEditing) {
            $limite = 3;
            $totalActual = Sucursal::where('empresa_id', auth()->user()->empresa_id)->count();

            if ($totalActual >= $limite) {
                $this->dispatch('swal', [
                    'title' => 'Límite alcanzado',
                    'text'  => "Tu empresa ya tiene {$totalActual} sucursales registradas. El límite máximo es {$limite}. Contacta al administrador para ampliar tu plan.",
                    'icon'  => 'warning'
                ]);
                return;
            }
        }
        // ────────────────────────────────────────────────────────────────────

        try {
            DB::beginTransaction();

            $data = [
                'empresa_id' => auth()->user()->empresa_id,
                'nombre' => $this->nombre,
                'slug' => $this->slug,
                'direccion' => $this->direccion,
                'ciudad' => $this->ciudad,
                'telefono' => $this->telefono,
                'activo' => $this->activo,
                'latitud' => $this->latitud,
                'longitud' => $this->longitud,
            ];

            if ($this->isEditing) {
                Sucursal::find($this->sucursalId)->update($data);
                $message = 'Sede actualizada correctamente.';
            } else {
                Sucursal::create($data);
                $message = 'Sede creada correctamente.';
            }

            DB::commit();

            $this->showModal = false;
            $this->dispatch('swal', [
                'title' => '¡Éxito!',
                'text' => $message,
                'icon' => 'success'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al guardar la sede: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $this->resetForm();
        $sucursal = Sucursal::findOrFail($id);
        
        $this->sucursalId = $sucursal->id;
        $this->nombre = $sucursal->nombre;
        $this->slug = $sucursal->slug;
        $this->direccion = $sucursal->direccion;
        $this->ciudad = $sucursal->ciudad;
        $this->telefono = $sucursal->telefono;
        $this->activo = $sucursal->activo;
        $this->latitud = $sucursal->latitud;
        $this->longitud = $sucursal->longitud;
        
        $this->isEditing = true;
        $this->showModal = true;
        $this->dispatch('data-loaded');
    }

    public function toggleStatus($id)
    {
        $sucursal = Sucursal::findOrFail($id);
        $sucursal->activo = !$sucursal->activo;
        $sucursal->save();
    }

    public function gestionar($id)
    {
        $sucursal = Sucursal::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);
        
        $user = auth()->user();
        $user->update(['sucursal_id' => $sucursal->id]);

        return redirect()->route('admin.dashboard');
    }

    public function render()
    {
        return view('livewire.sucursales.manage-sucursales', [
            'stats' => $this->stats,
            'sucursales' => $this->sucursales
        ])->layout('layouts.app');
    }
}
