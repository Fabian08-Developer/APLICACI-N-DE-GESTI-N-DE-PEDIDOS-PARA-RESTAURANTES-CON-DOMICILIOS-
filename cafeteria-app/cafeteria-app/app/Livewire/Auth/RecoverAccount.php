<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Models\Empresa;
use App\Models\Sucursal;
use Livewire\Component;

class RecoverAccount extends Component
{
    public $userId;
    public $correo;
    public $nit;
    public $nombre_empresa;
    public $telefono;
    public $success = false;

    public function mount($userId)
    {
        $this->userId = $userId;
        
        try {
            $user = User::onlyTrashed()->where('rol', 'gerente')->findOrFail($userId);
            $this->correo = $user->correo;
        } catch (\Exception $e) {
            // If user is already restored or not found, redirect to login with a message
            return redirect()->route('login');
        }
    }

    public function verifyAndRestore()
    {
        $this->validate([
            'correo' => 'required|email',
            'nit' => 'required|string',
            'nombre_empresa' => 'required|string',
            'telefono' => 'required|string',
        ], [
            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'El formato de correo no es válido.',
            'nit.required' => 'El NIT es obligatorio.',
            'nombre_empresa.required' => 'El nombre del negocio es obligatorio.',
            'telefono.required' => 'El teléfono es obligatorio.',
        ]);

        $user = User::onlyTrashed()->where('rol', 'gerente')->find($this->userId);

        if (!$user) {
            $this->addError('verification', 'Esta cuenta ya está activa o no existe en el sistema.');
            return;
        }

        $empresa = null;
        if ($user->empresa_id) {
            $empresa = Empresa::onlyTrashed()->find($user->empresa_id);
        }

        if (!$empresa) {
            $this->addError('verification', 'No se encontró la información de negocio asociada.');
            return;
        }

        // Exact comparison (case-insensitive and trimmed for strings)
        $emailMatch = strtolower(trim($user->correo)) === strtolower(trim($this->correo));
        $nitMatch = strtolower(trim($empresa->nit)) === strtolower(trim($this->nit));
        $nameMatch = strtolower(trim($empresa->nombre)) === strtolower(trim($this->nombre_empresa));
        $phoneMatch = strtolower(trim($user->telefono)) === strtolower(trim($this->telefono)) 
                   || strtolower(trim($empresa->telefono)) === strtolower(trim($this->telefono));

        if ($emailMatch && $nitMatch && $nameMatch && $phoneMatch) {
            // Restore everything
            if ($user->empresa_id) {
                // Restore company
                Empresa::onlyTrashed()->where('id', $user->empresa_id)->restore();
                // Restore sucursales
                Sucursal::onlyTrashed()->where('empresa_id', $user->empresa_id)->restore();
                // Restore all users of that company
                User::onlyTrashed()->where('empresa_id', $user->empresa_id)->restore();
            } else {
                $user->restore();
            }

            // Send notification to manager that they are restored successfully
            try {
                $freshUser = User::findOrFail($this->userId);
                $freshUser->notify(new \App\Notifications\CuentaRestaurada($freshUser));
            } catch (\Exception $e) {
                logger()->error('Error enviando correo de restauración desde formulario público: ' . $e->getMessage());
            }

            $this->success = true;
        } else {
            $this->addError('verification', 'La información de negocio proporcionada no coincide con nuestros registros de seguridad. Verifique los datos o contacte a soporte.');
        }
    }

    public function render()
    {
        return view('livewire.auth.recover-account')->layout('layouts.guest');
    }
}
