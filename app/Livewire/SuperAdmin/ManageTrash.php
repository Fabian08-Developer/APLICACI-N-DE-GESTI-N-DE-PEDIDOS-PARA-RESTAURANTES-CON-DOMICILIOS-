<?php

namespace App\Livewire\SuperAdmin;

use App\Models\User;
use App\Models\Empresa;
use App\Models\Sucursal;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ManageTrash extends Component
{
    use WithPagination;

    public $search = '';
    public $confirmingForceDelete = false;
    public $userToForceDelete = null;
    public $deletePassword = '';

    public function resendRecoveryEmail($userId)
    {
        $user = User::onlyTrashed()->findOrFail($userId);
        
        try {
            $user->notify(new \App\Notifications\CuentaEnRecuperacion($user));
            $this->dispatch('swal', [
                'title' => 'Correo Enviado',
                'text'  => 'Se ha reenviado la notificación de recuperación al gerente.',
                'icon'  => 'success'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('swal', [
                'title' => 'Error',
                'text'  => 'No se pudo enviar el correo: ' . $e->getMessage(),
                'icon'  => 'error'
            ]);
        }
    }

    public function restoreUser($userId)
    {
        $user = User::onlyTrashed()->findOrFail($userId);
        $empresaId = $user->empresa_id;

        if ($empresaId) {
            // Restore company
            Empresa::onlyTrashed()->where('id', $empresaId)->restore();
            // Restore sucursales
            Sucursal::onlyTrashed()->where('empresa_id', $empresaId)->restore();
            // Restore all users of that company
            User::onlyTrashed()->where('empresa_id', $empresaId)->restore();
        } else {
            $user->restore();
        }

        // Send email notification to manager
        try {
            $freshUser = User::findOrFail($userId);
            $freshUser->notify(new \App\Notifications\CuentaRestaurada($freshUser));
        } catch (\Exception $e) {
            logger()->error('Error enviando correo de restauración de cuenta: ' . $e->getMessage());
        }

        $this->dispatch('swal', [
            'title' => 'Restaurado',
            'text'  => 'El gerente y toda la información asociada han sido restaurados correctamente y se le ha enviado un correo de notificación.',
            'icon'  => 'success'
        ]);
    }

    public function confirmForceDelete($userId)
    {
        $this->userToForceDelete = $userId;
        $this->deletePassword = '';
        $this->confirmingForceDelete = true;
    }

    public function cancelForceDelete()
    {
        $this->confirmingForceDelete = false;
        $this->userToForceDelete = null;
        $this->deletePassword = '';
    }

    public function forceDeleteUser()
    {
        // Verify super-admin password
        if (!Hash::check($this->deletePassword, Auth::user()->contrasena)) {
            $this->addError('deletePassword', 'La contraseña es incorrecta.');
            return;
        }

        $user = User::onlyTrashed()->findOrFail($this->userToForceDelete);
        $empresa = Empresa::onlyTrashed()->where('id', $user->empresa_id)->first();

        // Cascade delete if company exists
        if ($empresa) {
            // Force delete all users of the company
            User::onlyTrashed()->where('empresa_id', $empresa->id)->forceDelete();
            
            // Force delete sucursales of the company
            Sucursal::onlyTrashed()->where('empresa_id', $empresa->id)->forceDelete();

            // Delete storage documents
            if ($empresa->documento_path) {
                Storage::disk('public')->delete($empresa->documento_path);
            }
            if ($empresa->documento_pendiente_path) {
                Storage::disk('public')->delete($empresa->documento_pendiente_path);
            }

            // Force delete company
            $empresa->forceDelete();
        } else {
            $user->forceDelete();
        }

        $this->cancelForceDelete();

        $this->dispatch('swal', [
            'title' => 'Eliminado Permanente',
            'text'  => 'El gerente y todos sus datos asociados se han eliminado de forma definitiva.',
            'icon'  => 'success'
        ]);
    }

    public function render()
    {
        $query = User::onlyTrashed()
            ->where('rol', 'gerente')
            ->where(function($q) {
                $q->where('nombre', 'ilike', '%' . $this->search . '%')
                  ->orWhere('correo', 'ilike', '%' . $this->search . '%');
            });

        $users = $query->with(['empresa' => function($q) {
            $q->withTrashed();
        }])->paginate(10);

        return view('livewire.super-admin.manage-trash', [
            'users' => $users
        ])->layout('layouts.app');
    }
}
