<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Empresa;
use App\Notifications\OtpActualizacionNit;

class Settings extends Component
{
    use WithFileUploads;

    public $activeTab = 'perfil';
    
    // Profile Fields
    public $nombre_usuario;
    public $correo;
    public $telefono_usuario;
    public $cargo = 'Gerente General';
    
    // Business Fields
    public $nombre_empresa;
    public $nit;
    public $direccion_empresa;
    public $ciudad_empresa;
    public $sitio_web = 'https://sgpd.com';
    public $tipo_negocio = 'Restaurante';

    // Password Fields
    public $current_password;
    public $new_password;
    public $new_password_confirmation;

    // Document Fields
    public $nuevo_documento;
    public $documento_path;
    public $documento_pendiente_path;
    public $showOtpModal = false;
    public $otpIngresado = '';
    public $errorOtp = '';
    public $pendingAction = '';

    public function mount()
    {
        $user = Auth::user();
        $this->nombre_usuario = $user->nombre;
        $this->correo = $user->correo;
        $this->telefono_usuario = $user->telefono;

        $empresa = $user->empresa;
        if ($empresa) {
            $this->nombre_empresa = $empresa->nombre;
            $this->nit = $empresa->nit;
            $this->direccion_empresa = $empresa->direccion;
            $this->ciudad_empresa = $empresa->ciudad;
            $this->documento_path = $empresa->documento_path;
            $this->documento_pendiente_path = $empresa->documento_pendiente_path;
        }
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function saveProfile()
    {
        $this->validate([
            'nombre_usuario' => 'required|string|max:150',
            'correo' => 'required|email|unique:usuarios,correo,' . Auth::id(),
            'telefono_usuario' => 'nullable|string|max:30',
        ]);

        $this->pendingAction = 'profile';
        $this->enviarOtpConfirmacion();
    }

    protected function actualizarPerfil()
    {
        $user = User::find(Auth::id());
        $user->update([
            'nombre' => $this->nombre_usuario,
            'correo' => $this->correo,
            'telefono' => $this->telefono_usuario,
        ]);

        $this->dispatch('swal', [
            'title' => '¡Perfil Actualizado!',
            'text' => 'Tus datos personales se han guardado con éxito.',
            'icon' => 'success'
        ]);
    }

    public function saveBusiness()
    {
        $user = Auth::user();
        $this->validate([
            'nombre_empresa' => 'required|string|max:150',
            'direccion_empresa' => 'nullable|string',
            'ciudad_empresa' => 'nullable|string|max:100',
        ]);

        $this->pendingAction = 'business';
        $this->enviarOtpConfirmacion();
    }

    protected function actualizarNegocio()
    {
        $user = Auth::user();
        $empresa = Empresa::find($user->empresa_id);
        if ($empresa) {
            $empresa->update([
                'nombre' => $this->nombre_empresa,
                'direccion' => $this->direccion_empresa,
                'ciudad' => $this->ciudad_empresa,
            ]);
        }

        $this->dispatch('swal', [
            'title' => '¡Negocio Actualizado!',
            'text' => 'Los datos de tu empresa se han guardado con éxito.',
            'icon' => 'success'
        ]);
    }

    protected function enviarOtpConfirmacion()
    {
        $codigo = random_int(100000, 999999);
        Cache::put('otp_config_' . Auth::id(), $codigo, now()->addMinutes(10));
        
        \Illuminate\Support\Facades\Log::info("Código OTP de configuración (" . $this->pendingAction . ") para usuario " . Auth::id() . ": " . $codigo);
        
        if ($this->pendingAction === 'document') {
            Auth::user()->notify(new OtpActualizacionNit($codigo));
        } else {
            Auth::user()->notify(new \App\Notifications\CodigoConfirmacionPerfil($codigo));
        }

        $this->showOtpModal = true;
        $this->otpIngresado = '';
        $this->errorOtp = '';

        $this->dispatch('swal', [
            'title' => 'Código Enviado',
            'text' => 'Se ha enviado un código de seguridad de 6 dígitos a tu correo electrónico.',
            'icon' => 'info'
        ]);
    }

    public function changePassword()
    {
        $this->validate([
            'current_password' => 'required|current_password',
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.current_password' => 'La contraseña actual no es correcta.',
            'new_password.confirmed' => 'La confirmación de la nueva contraseña no coincide.',
            'new_password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
        ]);

        $user = User::find(Auth::id());
        $user->update([
            'contrasena' => bcrypt($this->new_password)
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);

        $this->dispatch('swal', [
            'title' => '¡Contraseña Cambiada!',
            'text' => 'Tu contraseña se ha actualizado correctamente.',
            'icon' => 'success'
        ]);
    }

    public $confirmingAccountDeletion = false;
    public $passwordForDeletion;

    public function confirmAccountDeletion()
    {
        $this->confirmingAccountDeletion = true;
    }

    public function cancelAccountDeletion()
    {
        $this->confirmingAccountDeletion = false;
        $this->passwordForDeletion = '';
        $this->resetValidation('passwordForDeletion');
    }

    public function deleteAccount()
    {
        $this->validate([
            'passwordForDeletion' => 'required|current_password',
        ], [
            'passwordForDeletion.current_password' => 'La contraseña es incorrecta.',
            'passwordForDeletion.required' => 'La contraseña es obligatoria para eliminar la cuenta.',
        ]);

        $user = Auth::user();
        $empresaId = $user->empresa_id;

        Auth::logout();

        // Si tiene empresa, borramos los usuarios asociados y luego la empresa de forma física
        // Si la base de datos tiene cascada, esto limpia todo físicamente.
        if ($empresaId) {
            User::where('empresa_id', $empresaId)->forceDelete();
            Empresa::where('id', $empresaId)->forceDelete();
        } else {
            $user->forceDelete();
        }

        session()->invalidate();
        session()->regenerateToken();

        return redirect('/');
    }

    public function downloadDocument()
    {
        $user = Auth::user();
        $empresa = $user->empresa;
        if ($empresa && $empresa->documento_path) {
            return Storage::disk('public')->download($empresa->documento_path);
        }
        $this->dispatch('swal', [
            'title' => 'Error',
            'text' => 'El documento no está disponible para descargar.',
            'icon' => 'error'
        ]);
    }

    public function iniciarActualizacionDocumento()
    {
        $user = Auth::user();
        $empresa = $user->empresa;
        if ($empresa && $empresa->documento_pendiente_path) {
            $this->dispatch('swal', [
                'title' => 'Error',
                'text' => 'Ya existe una solicitud previa en revisión por el Super Admin.',
                'icon' => 'error'
            ]);
            return;
        }

        $this->validate([
            'nuevo_documento' => 'required|file|mimes:pdf,doc,docx|max:10240', // 10MB max
        ], [
            'nuevo_documento.required' => 'Debes seleccionar un archivo.',
            'nuevo_documento.mimes' => 'Solo se permiten archivos en formato PDF, DOC o DOCX.',
            'nuevo_documento.max' => 'El archivo no debe pesar más de 10MB.',
        ]);

        $this->pendingAction = 'document';
        $this->enviarOtpConfirmacion();
    }

    public function cancelActualizacionDocumento()
    {
        $this->showOtpModal = false;
        $this->otpIngresado = '';
        $this->errorOtp = '';
        $this->nuevo_documento = null;
        $this->pendingAction = '';
    }

    public function confirmarOtp()
    {
        $codigoCorrecto = Cache::get('otp_config_' . Auth::id());
        if (!$codigoCorrecto) {
            $this->errorOtp = 'El código ha expirado o no es válido. Por favor, solicita uno nuevo.';
            return;
        }

        if ($this->otpIngresado != $codigoCorrecto) {
            $this->errorOtp = 'El código ingresado es incorrecto.';
            return;
        }

        Cache::forget('otp_config_' . Auth::id());

        if ($this->pendingAction === 'profile') {
            $this->actualizarPerfil();
        } elseif ($this->pendingAction === 'business') {
            $this->actualizarNegocio();
        } elseif ($this->pendingAction === 'document') {
            $this->actualizarDocumento();
        }

        $this->showOtpModal = false;
        $this->otpIngresado = '';
        $this->errorOtp = '';
        $this->pendingAction = '';
    }

    protected function actualizarDocumento()
    {
        $user = Auth::user();
        $empresa = Empresa::find($user->empresa_id);

        if ($empresa) {
            if ($empresa->documento_pendiente_path) {
                Storage::disk('public')->delete($empresa->documento_pendiente_path);
            }

            $path = $this->nuevo_documento->store('solicitudes_registro', 'public');
            $empresa->update([
                'documento_pendiente_path' => $path
            ]);
            $this->documento_pendiente_path = $path;

            $superAdmins = User::where('rol', 'super-admin')->get();
            foreach ($superAdmins as $admin) {
                $admin->notify(new \App\Notifications\NuevaSolicitudNit($empresa->nombre, $empresa->nit, $user->nombre));
            }
        }

        $this->nuevo_documento = null;

        $this->dispatch('swal', [
            'title' => '¡Solicitud Enviada!',
            'text' => 'Tu solicitud de actualización de NIT ha sido enviada al Super Administrador para su revisión.',
            'icon' => 'success'
        ]);
    }

    public function render()
    {
        return view('livewire.settings.settings')->layout('layouts.app');
    }
}
