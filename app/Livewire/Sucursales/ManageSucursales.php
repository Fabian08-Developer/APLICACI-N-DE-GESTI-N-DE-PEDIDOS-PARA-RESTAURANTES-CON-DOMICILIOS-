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

    // Delete modal fields
    public $deleteModal = false;
    public $deleteSucursalId = null;
    public $passwordVerification = '';

    protected function rules()
    {
        $empresaId = auth()->user()->empresa_id;
        $ignoreId  = $this->sucursalId;

        $rules = [
            'nombre'    => ['required', 'string', 'max:150'],
            'slug'      => ['required', 'string', 'max:100'],
            'direccion' => 'nullable|string',
            'ciudad'    => 'required|string|max:100',
            'telefono'  => 'nullable|string|max:30',
            'activo'    => 'boolean',
            'latitud'   => 'nullable|numeric',
            'longitud'  => 'nullable|numeric',
        ];

        return $rules;
    }

    protected $messages = [
        'nombre.required'  => 'El nombre es obligatorio.',
        'ciudad.required'  => 'Debes seleccionar una ciudad.',
    ];

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
            'total'      => $all->count(),
            'activas'    => $all->where('activo', true)->count(),
            'inactivas'  => $all->where('activo', false)->count(),
            'ventas_mes' => $ventasMes,
        ];
    }

    public function getSucursalesProperty()
    {
        return Sucursal::where('empresa_id', auth()->user()->empresa_id)
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('nombre', 'ilike', '%' . $this->search . '%')
                        ->orWhere('ciudad', 'ilike', '%' . $this->search . '%')
                        ->orWhere('slug', 'ilike', '%' . $this->search . '%');
                });
            })
            ->when($this->filterStatus !== 'todas', function ($q) {
                $q->where('activo', $this->filterStatus === 'activas');
            })
            ->withCount('usuarios')
            ->withSum(['pedidos as ventas_mes_sum' => function ($query) {
                $query->whereNotIn('estado', ['cancelado', 'CANCELADO'])
                      ->whereDate('creado_en', '>=', \Carbon\Carbon::now()->startOfMonth());
            }], 'total')
            ->get();
    }

    public function updatedNombre($value)
    {
        if (!$this->isEditing) {
            $this->slug = Str::slug($value);
        }
    }

    public function openCreateModal()
    {
        if (!auth()->user()->hasAnyRole(['gerente', 'administrador', 'super-admin'])) {
            $this->dispatch('swal', ['title' => 'Sin permiso', 'text' => 'No tienes permisos.', 'icon' => 'error']);
            return;
        }
        $this->resetForm();
        $this->showModal = true;
    }

    public function resetForm()
    {
        $this->sucursalId = null;
        $this->nombre     = '';
        $this->slug       = '';
        $this->direccion  = '';
        $this->ciudad     = '';
        $this->telefono   = '';
        $this->activo     = true;
        $this->latitud    = null;
        $this->longitud   = null;
        $this->isEditing  = false;
        $this->resetErrorBag();
    }

    public function save()
    {
        // Generar slug único automáticamente
        $baseSlug = Str::slug($this->nombre ?: 'sucursal');
        $slug = $baseSlug;
        $count = 1;
        while (true) {
            $query = Sucursal::where('slug', $slug);
            if ($this->sucursalId) {
                $query->where('id', '!=', $this->sucursalId);
            }
            if (!$query->exists()) {
                break;
            }
            $slug = $baseSlug . '-' . $count;
            $count++;
        }
        $this->slug = $slug;

        // Validar — si falla, muestra alerta con campos faltantes
        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errores = collect($e->errors())->flatten()->implode(', ');
            $this->dispatch('swal', [
                'title' => 'Campos incompletos',
                'text'  => $errores,
                'icon'  => 'warning',
            ]);
            throw $e;
        }

        try {
            DB::beginTransaction();

            $data = [
                'empresa_id' => auth()->user()->empresa_id,
                'nombre'     => $this->nombre,
                'slug'       => $this->slug,
                'direccion'  => $this->direccion ?: null,
                'ciudad'     => $this->ciudad,
                'telefono'   => $this->telefono ?: null,
                'activo'     => (bool) $this->activo,
                'latitud'    => $this->latitud ?: null,
                'longitud'   => $this->longitud ?: null,
            ];

            if ($this->isEditing) {
                Sucursal::find($this->sucursalId)->update($data);
                $message = 'Sede actualizada correctamente.';
            } else {
                $data['empresa_id'] = auth()->user()->empresa_id;
                Sucursal::create($data);
                $message = 'Sede creada correctamente.';
            }

            DB::commit();
            $this->showModal = false;
            $this->resetForm();
            $this->dispatch('swal', ['title' => '¡Listo!', 'text' => $message, 'icon' => 'success']);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('swal', [
                'title' => 'Error al guardar',
                'text'  => $e->getMessage(),
                'icon'  => 'error',
            ]);
        }
    }

    public function edit($id)
    {
        $sucursal = Sucursal::findOrFail($id);

        $this->sucursalId = $sucursal->id;
        $this->nombre     = $sucursal->nombre;
        $this->slug       = $sucursal->slug;
        $this->direccion  = $sucursal->direccion;
        $this->ciudad     = $sucursal->ciudad;
        $this->telefono   = $sucursal->telefono;
        $this->activo     = $sucursal->activo;
        $this->latitud    = $sucursal->latitud;
        $this->longitud   = $sucursal->longitud;
        $this->isEditing  = true;
        $this->showModal  = true;
        $this->resetErrorBag();
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
        try {
            $sucursal = Sucursal::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);
            $user = auth()->user();
            $user->sucursal_id = $sucursal->id;
            $user->save();
            return $this->redirectRoute('admin.dashboard');
        } catch (\Exception $e) {
            $this->dispatch('swal', ['title' => 'Error', 'text' => $e->getMessage(), 'icon' => 'error']);
        }
    }

    public function confirmDelete($id)
    {
        $this->deleteSucursalId = $id;
        $this->passwordVerification = '';
        $this->deleteModal = true;
    }

    public function deleteSucursal()
    {
        $this->validate([
            'passwordVerification' => 'required',
        ], [
            'passwordVerification.required' => 'Debes ingresar tu contraseña para confirmar.',
        ]);

        if (!\Illuminate\Support\Facades\Hash::check($this->passwordVerification, auth()->user()->getAuthPassword())) {
            $this->addError('passwordVerification', 'La contraseña ingresada es incorrecta.');
            return;
        }

        try {
            DB::beginTransaction();
            $sucursal = Sucursal::findOrFail($this->deleteSucursalId);
            $sucursal->delete();
            DB::commit();

            $this->deleteModal = false;
            $this->deleteSucursalId = null;
            $this->passwordVerification = '';

            $this->dispatch('swal', ['title' => 'Eliminada', 'text' => 'La sucursal ha sido eliminada exitosamente.', 'icon' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('swal', ['title' => 'Error', 'text' => 'Ocurrió un error al intentar eliminar la sucursal.', 'icon' => 'error']);
        }
    }

    public function render()
    {
        return view('livewire.sucursales.manage-sucursales', [
            'stats'      => $this->stats,
            'sucursales' => $this->sucursales,
        ])->layout('layouts.app');
    }
}
