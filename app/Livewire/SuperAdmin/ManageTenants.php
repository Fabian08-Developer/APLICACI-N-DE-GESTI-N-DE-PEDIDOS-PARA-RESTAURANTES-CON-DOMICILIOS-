<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Empresa;
use Livewire\Component;
use Livewire\WithPagination;

class ManageTenants extends Component
{
    use WithPagination;

    public $search = '';

    public function toggleStatus($empresaId)
    {
        $empresa = Empresa::findOrFail($empresaId);
        $empresa->activo = !$empresa->activo;
        $empresa->save();

        // Si desactivamos la empresa, también podríamos desactivar sus usuarios
        if (!$empresa->activo) {
            $empresa->usuarios()->update(['activo' => false]);
        } else {
            // Si la activamos, activamos al menos al gerente
            $gerentesIds = $empresa->usuarios()->role('gerente')->pluck('usuarios.id');
            \App\Models\User::whereIn('id', $gerentesIds)->update(['activo' => true]);
        }

        $this->dispatch('swal', [
            'title' => $empresa->activo ? 'Activada' : 'Desactivada',
            'text' => 'El estado de la empresa ha sido actualizado.',
            'icon' => 'success'
        ]);
    }

    public function deleteEmpresa($empresaId)
    {
        $empresa = Empresa::findOrFail($empresaId);
        
        // Primero eliminar usuarios para evitar errores de integridad
        $empresa->usuarios()->delete();
        $empresa->delete();

        $this->dispatch('swal', [
            'title' => 'Eliminada',
            'text' => 'La empresa y sus usuarios han sido eliminados.',
            'icon' => 'warning'
        ]);
    }

    public function render()
    {
        $tenants = Empresa::where('nombre', 'ilike', '%' . $this->search . '%')
            ->orWhere('nit', 'like', '%' . $this->search . '%')
            ->withCount('usuarios')
            ->paginate(10);

        return view('livewire.super-admin.manage-tenants', [
            'tenants' => $tenants
        ])->layout('layouts.app');
    }
}
