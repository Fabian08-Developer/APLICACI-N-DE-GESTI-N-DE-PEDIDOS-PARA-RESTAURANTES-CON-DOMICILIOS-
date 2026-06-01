<?php

namespace App\Livewire\Admin\Usuarios;

use Livewire\Component;

class ManageUsuarios extends Component
{
    public function mount()
    {
        if (!auth()->user()->sucursal_id) {
            return redirect()->route('sucursales');
        }
    }

    public function render()
    {
        $user = auth()->user();
        $sucursal_id = $user->sucursal_id;

        $usuarios = \App\Models\User::where('sucursal_id', $sucursal_id)
            ->where('id', '!=', $user->id)
            ->get();

        // Role restriction based on current user role
        if ($user->hasRole('gerente')) {
            $allowedRoleNames = ['administrador', 'cocina', 'mesero', 'domiciliario'];
        } elseif ($user->hasRole('administrador')) {
            $allowedRoleNames = ['cocina', 'mesero', 'domiciliario'];
        } else {
            $allowedRoleNames = [];
        }

        $roles = collect($allowedRoleNames)->map(function($name) {
            $nombre = $name === 'cocina' ? 'cocina' : ($name === 'mesero' ? 'Mesero' : ($name === 'administrador' ? 'Administrador' : ($name === 'domiciliario' ? 'Domiciliario' : ucfirst(str_replace('-', ' ', $name)))));
            return (object)[
                'id' => $name,
                'name' => $name,
                'nombre' => $nombre
            ];
        });

        $editar = null;

        return view('livewire.admin.usuarios.manage-usuarios', [
            'usuarios' => $usuarios,
            'roles' => $roles,
            'editar' => $editar
        ])->layout('layouts.admin');
    }
}
