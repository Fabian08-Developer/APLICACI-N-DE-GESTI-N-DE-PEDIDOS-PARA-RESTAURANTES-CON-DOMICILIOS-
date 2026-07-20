<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Empresa;

use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Cache;
use App\Notifications\CodigoVerificacionRegistro;
use App\Notifications\NuevaSolicitudRegistroGerente;

class RegisterManager extends Component
{
    use WithFileUploads;

    // Control de Flujo
    public $step = 1;
    public $codigoIngresado;

    // Datos de la Empresa
    public $nit;
    public $nombre_empresa;
    public $documento; // Archivo PDF o Word

    // Datos de la Usuario (Gerente)
    public $nombre;
    public $correo;
    public $telefono;
    public $contrasena;
    public $contrasena_confirmation;

    protected function rules()
    {
        return [
            'nit' => ['required', 'regex:/^\d{3}\.\d{3}\.\d{3}-\d{1}$/', 'unique:empresas,nit'],
            'nombre_empresa' => ['required', 'string', 'max:150', 'not_regex:/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/u'],
            'documento' => 'required|file|mimes:pdf,doc,docx|max:10240', // 10MB max
            'nombre' => ['required', 'string', 'max:150', 'not_regex:/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/u'],
            'correo' => 'required|email|max:255|unique:usuarios,correo',
            'telefono' => 'required|string|max:20',
            'contrasena' => 'required|min:8|confirmed',
        ];
    }

    protected $messages = [
        'nit.required' => 'El NIT es obligatorio.',
        'nit.regex' => 'El formato del NIT debe ser 000.000.000-0',
        'nit.unique' => 'Este NIT ya se encuentra registrado.',
        'nombre_empresa.required' => 'El nombre del negocio es obligatorio.',
        'nombre_empresa.not_regex' => 'El nombre del negocio no debe contener emojis.',
        'documento.required' => 'Debes adjuntar un documento PDF o Word.',
        'documento.mimes' => 'Solo se permiten archivos en formato PDF, DOC o DOCX.',
        'documento.max' => 'El archivo no debe pesar más de 10MB.',
        'nombre.required' => 'Tu nombre completo es obligatorio.',
        'nombre.not_regex' => 'El nombre completo no debe contener emojis.',
        'correo.required' => 'El correo electrónico es obligatorio.',
        'correo.email' => 'El formato del correo no es válido.',
        'correo.unique' => 'Este correo ya está registrado.',
        'telefono.required' => 'El número de teléfono es obligatorio.',
        'contrasena.required' => 'La contraseña es obligatoria.',
        'contrasena.min' => 'La contraseña debe tener al menos 8 caracteres.',
        'contrasena.confirmed' => 'Las contraseñas no coinciden.',
    ];

    public function iniciarRegistro()
    {
        $this->validate();

        // Validar el dígito de verificación
        if (!$this->validarDV($this->nit)) {
            $this->addError('nit', 'El dígito de verificación no es válido.');
            return;
        }

        $this->enviarNuevoCodigo();

        $this->step = 2;
    }

    private function validarDV($nitFormateado)
    {
        // Limpiar puntos y guion
        $nitLimpio = str_replace(['.', '-'], '', $nitFormateado);
        
        $base = substr($nitLimpio, 0, 9);
        $dv_ingresado = (int) substr($nitLimpio, 9, 1);

        $dv_calculado = $this->calcularDV($base);

        return $dv_ingresado === $dv_calculado;
    }

    private function calcularDV($nit)
    {
        $arr = [3, 7, 13, 17, 19, 23, 29, 37, 41, 43, 47, 53, 59, 67, 71];
        $sumatoria = 0;
        
        $nitInvertido = strrev($nit);
        
        for ($i = 0; $i < strlen($nitInvertido); $i++) {
            $sumatoria += (int)$nitInvertido[$i] * $arr[$i];
        }
        
        $residuo = $sumatoria % 11;
        
        if ($residuo > 1) {
            return 11 - $residuo;
        }
        
        return $residuo;
    }

    public function enviarNuevoCodigo()
    {
        // 1. Generar código de 6 dígitos
        $codigo = random_int(100000, 999999);

        // 2. Guardar código en Cache temporal (10 min según RF)
        Cache::put('reg_code_' . $this->correo, $codigo, now()->addMinutes(10));

        // 3. Enviar código al correo
        \Illuminate\Support\Facades\Log::info("Enviando código de registro a: " . $this->correo . " - Código: " . $codigo);
        
        Notification::route('mail', $this->correo)
            ->notify(new CodigoVerificacionRegistro($codigo));
            
        $this->dispatch('swal', [
            'title' => 'Código Enviado',
            'text' => 'Se ha enviado un nuevo código a ' . $this->correo,
            'icon' => 'info'
        ]);
    }

    public function verificarYCrearCuenta()
    {
        $codigoCorrecto = Cache::get('reg_code_' . $this->correo);

        if (!$codigoCorrecto) {
            $this->addError('codigoIngresado', 'El código ha expirado. Por favor solicita uno nuevo.');
            return;
        }

        if ($this->codigoIngresado != $codigoCorrecto) {
            $this->addError('codigoIngresado', 'El código es incorrecto.');
            return;
        }

        try {
            DB::beginTransaction();

            // 0. Guardar el documento
            $documentoPath = $this->documento->store('solicitudes_registro', 'public');

            // 1. Crear la Empresa (Inactiva por defecto según RF)
            $empresa = Empresa::create([
                'nit' => $this->nit,
                'nombre' => $this->nombre_empresa,
                'tipo_nit' => 'nit',
                'activo' => false,
                'documento_path' => $documentoPath,
            ]);

            // 2. Crear el Usuario (Gerente) (Inactivo por defecto según RF)
            $user = User::create([
                'empresa_id' => $empresa->id,
                'nombre' => $this->nombre,
                'correo' => $this->correo,
                'telefono' => $this->telefono,
                'contrasena' => Hash::make($this->contrasena),
                'activo' => false,
                'correo_verificado_en' => now(),
            ]);

            // 3. Asignar el Rol de Gerente
            $user->assignRole('gerente');

            // 4. Notificar al Super Admin (RF-Nuevo)
            $superAdmin = User::role('super-admin')->first();
            if ($superAdmin) {
                $superAdmin->notify(new NuevaSolicitudRegistroGerente($empresa, $user));
            } else {
                // Fallback si no hay super-admin con rol, enviar a correo de config si existe
                Notification::route('mail', config('mail.from.address'))
                    ->notify(new NuevaSolicitudRegistroGerente($empresa, $user));
            }

            DB::commit();

            // Limpiar cache del código
            Cache::forget('reg_code_' . $this->correo);

            $this->dispatch('swal', [
                'title' => 'Solicitud Enviada',
                'text' => 'Tu cuenta ha sido creada y está pendiente de aprobación por el administrador.',
                'icon' => 'success'
            ]);

            return redirect()->route('registration.pending');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Ocurrió un error al intentar crear la cuenta: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.auth.register-manager')->layout('layouts.guest');
    }
}


