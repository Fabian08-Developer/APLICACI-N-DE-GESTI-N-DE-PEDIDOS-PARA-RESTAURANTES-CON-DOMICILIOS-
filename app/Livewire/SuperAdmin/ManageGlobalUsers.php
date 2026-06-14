<?php

namespace App\Livewire\SuperAdmin;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ManageGlobalUsers extends Component
{
    use WithPagination;

    public $search = '';
    public $roleFilter = '';

    // Delete modal state
    public $confirmingDelete = false;
    public $userToDelete = null;
    public $deletePassword = '';

    public function toggleStatus($userId)
    {
        $user = User::findOrFail($userId);
        $user->activo = !$user->activo;
        $user->save();

        $this->dispatch('swal', [
            'title' => $user->activo ? 'Activado' : 'Desactivado',
            'text'  => 'El estado del usuario ha sido actualizado.',
            'icon'  => 'success'
        ]);
    }

    public function confirmDelete($userId)
    {
        $this->userToDelete  = $userId;
        $this->deletePassword = '';
        $this->confirmingDelete = true;
    }

    public function cancelDelete()
    {
        $this->confirmingDelete = false;
        $this->userToDelete  = null;
        $this->deletePassword = '';
    }

    public function deleteUser()
    {
        // Verify super-admin password
        if (!Hash::check($this->deletePassword, Auth::user()->contrasena)) {
            $this->addError('deletePassword', 'La contraseña es incorrecta.');
            return;
        }

        $user = User::findOrFail($this->userToDelete);

        // Safety: cannot delete another super-admin
        if ($user->hasRole('super-admin')) {
            $this->addError('deletePassword', 'No puedes eliminar al Super Administrador.');
            return;
        }

        // Custom delete logic: Gerente goes to recovery (soft-delete), others are force-deleted
        if ($user->hasRole('gerente')) {
            $empresa = $user->empresa;
            if ($empresa) {
                // Soft delete all users of that company
                User::where('empresa_id', $empresa->id)->delete();
                // Soft delete sucursales of that company
                \App\Models\Sucursal::where('empresa_id', $empresa->id)->delete();
                // Soft delete company
                $empresa->delete();
            } else {
                $user->delete();
            }

            // Send email notification to manager
            try {
                $user->notify(new \App\Notifications\CuentaEnRecuperacion($user));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Error enviando correo de recuperación: " . $e->getMessage());
            }

            $successText = 'El gerente y su negocio han sido movidos a la papelera en estado de recuperación por 30 días.';
        } else {
            $user->forceDelete();
            $successText = 'El usuario ha sido eliminado permanentemente.';
        }

        $this->cancelDelete();

        $this->dispatch('swal', [
            'title' => 'Eliminado',
            'text'  => $successText,
            'icon'  => 'success'
        ]);
    }

    public function render()
    {
        $query = User::where('rol', '!=', 'super-admin')
            ->where(function ($q) {
                $q->where('nombre', 'ilike', '%' . $this->search . '%')
                  ->orWhere('correo', 'ilike', '%' . $this->search . '%');
            });

        if ($this->roleFilter) {
            $query->where('rol', $this->roleFilter);
        }

        $users = $query->with(['empresa'])->paginate(10);

        return view('livewire.super-admin.manage-global-users', [
            'users' => $users
        ])->layout('layouts.app');
    }
}


