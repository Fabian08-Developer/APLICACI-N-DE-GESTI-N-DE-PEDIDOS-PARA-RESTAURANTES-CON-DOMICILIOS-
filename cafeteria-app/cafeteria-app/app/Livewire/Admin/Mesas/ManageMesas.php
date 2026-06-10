<?php

namespace App\Livewire\Admin\Mesas;

use Livewire\Component;
use App\Models\Mesa;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class ManageMesas extends Component
{
    use WithPagination;

    public $numero;
    public $capacidad = 4;
    public $estado = 'disponible';
    public $mesa_id;
    public $isEditing = false;
    public $showModal = false;
    public $search = '';

    // Propiedades para QR Code Modal
    public $selectedMesaId;
    public $showQrModal = false;

    // Propiedades para Cerrar Sesión
    public $sesionACerrarId = null;
    public $sesionACerrarCliente = '';
    public $sesionACerrarMesa = '';
    public $showCerrarSesionModal = false;

    protected $rules = [
        'numero' => 'required|integer|min:1',
        'capacidad' => 'nullable|integer|min:1',
        'estado' => 'required|in:disponible,ocupada,pidiendo,esperando_pago,por_liberar',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function openQrModal($id)
    {
        $this->selectedMesaId = $id;
        $this->showQrModal = true;
    }

    public function render()
    {
        $sucursal_id = Auth::user()->sucursal_id;
        $query = Mesa::with('sesionActiva')->where('sucursal_id', $sucursal_id)->orderBy('numero');

        if ($this->search) {
            $query->where('numero', 'like', '%' . $this->search . '%')
                  ->orWhere('estado', 'like', '%' . $this->search . '%');
        }

        $mesas = $query->paginate(12);
        $total = Mesa::where('sucursal_id', $sucursal_id)->count();
        $selectedMesa = $this->selectedMesaId ? Mesa::find($this->selectedMesaId) : null;

        return view('livewire.admin.mesas.manage-mesas', compact('mesas', 'total', 'selectedMesa'))
               ->layout('layouts.admin');
    }

    public function confirmarCerrarSesion($sesionId)
    {
        $sesion = \App\Models\SesionCliente::with('mesa')->findOrFail($sesionId);
        
        if ($sesion->sucursal_id !== Auth::user()->sucursal_id) {
            abort(403);
        }

        $this->sesionACerrarId = $sesion->id;
        $this->sesionACerrarCliente = $sesion->nombre_cliente;
        $this->sesionACerrarMesa = $sesion->mesa ? $sesion->mesa->numero : 'N/A';
        $this->showCerrarSesionModal = true;
    }

    public function cerrarSesionConfirmada()
    {
        if (!$this->sesionACerrarId) return;

        $sesion = \App\Models\SesionCliente::findOrFail($this->sesionACerrarId);
        
        if ($sesion->sucursal_id !== Auth::user()->sucursal_id) {
            abort(403);
        }

        $sesion->cerrar();
        
        $this->showCerrarSesionModal = false;
        $this->sesionACerrarId = null;
        $this->dispatch('close-modal');
    }

    public function openCreateModal()
    {
        $this->resetInputFields();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $sucursal_id = Auth::user()->sucursal_id;

        if ($this->isEditing) {
            $mesa = Mesa::findOrFail($this->mesa_id);
            if ($mesa->sucursal_id !== $sucursal_id) {
                abort(403);
            }
            
            $exists = Mesa::where('sucursal_id', $sucursal_id)
                ->where('numero', $this->numero)
                ->where('id', '!=', $this->mesa_id)
                ->first();
                
            if ($exists) {
                $this->addError('numero', 'Ya existe una mesa con ese número en esta sucursal.');
                return;
            }

            $mesa->update([
                'numero' => $this->numero,
                'capacidad' => $this->capacidad,
                'estado' => $this->estado,
            ]);
        } else {
            $exists = Mesa::where('sucursal_id', $sucursal_id)
                ->where('numero', $this->numero)
                ->first();
                
            if ($exists) {
                $this->addError('numero', 'Ya existe una mesa con ese número en esta sucursal.');
                return;
            }

            $mesa = Mesa::create([
                'sucursal_id' => $sucursal_id,
                'numero'      => $this->numero,
                'capacidad'   => $this->capacidad,
                'estado'      => $this->estado,
            ]);

            $mesa->update([
                'codigo_qr' => Mesa::generarCodigoQR($mesa->id),
                'qr_activo' => true,
            ]);
        }

        $this->showModal = false;
        $this->resetInputFields();
        $this->dispatch('close-modal');
    }

    public function edit($id)
    {
        $mesa = Mesa::findOrFail($id);
        if ($mesa->sucursal_id !== Auth::user()->sucursal_id) {
            abort(403);
        }

        $this->mesa_id = $id;
        $this->numero = $mesa->numero;
        $this->capacidad = $mesa->capacidad;
        $this->estado = $mesa->estado;
        $this->isEditing = true;
        $this->showModal = true;
        
        $this->dispatch('data-loaded');
    }

    public function delete($id)
    {
        $mesa = Mesa::findOrFail($id);
        if ($mesa->sucursal_id !== Auth::user()->sucursal_id) {
            abort(403);
        }

        if ($mesa->sesionesCliente()->count() > 0) {
            $this->addError('general', 'No se puede eliminar la mesa porque tiene sesiones asociadas.');
            return;
        }

        $mesa->delete();
        $this->dispatch('close-modal');
    }

    public function regenerateQr($id)
    {
        $mesa = Mesa::findOrFail($id);
        if ($mesa->sucursal_id !== Auth::user()->sucursal_id) {
            abort(403);
        }

        $mesa->update([
            'codigo_qr' => Mesa::generarCodigoQR($mesa->id),
            'qr_activo' => true,
        ]);
    }

    private function resetInputFields()
    {
        $this->numero = '';
        $this->capacidad = 4;
        $this->estado = 'disponible';
        $this->mesa_id = null;
    }
}
