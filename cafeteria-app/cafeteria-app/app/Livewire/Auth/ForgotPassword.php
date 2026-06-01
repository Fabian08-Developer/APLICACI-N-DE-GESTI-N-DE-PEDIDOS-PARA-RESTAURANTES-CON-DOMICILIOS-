<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Mail\PasswordRecoveryCodeMail;
use App\Mail\PasswordChangedMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Carbon\Carbon;

class ForgotPassword extends Component
{
    public $correo;
    public $code;
    public $password;
    public $password_confirmation;
    public $step = 1; // 1: Email, 2: Code, 3: New Password
    public $lastCodeSentAt;

    protected function rules()
    {
        return [
            1 => ['correo' => 'required|email|exists:usuarios,correo'],
            2 => ['code' => 'required|digits:6'],
            3 => ['password' => 'required|min:8|confirmed'],
        ];
    }

    protected $messages = [
        'correo.required' => 'El correo electrónico es obligatorio.',
        'correo.email' => 'El formato del correo no es válido.',
        'correo.exists' => 'Este correo no está registrado en nuestro sistema.',
        'code.required' => 'El código es obligatorio.',
        'code.digits' => 'El código debe ser de 6 dígitos.',
        'password.required' => 'La contraseña es obligatoria.',
        'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        'password.confirmed' => 'Las contraseñas no coinciden.',
    ];

    public function sendCode()
    {
        $this->validate($this->rules()[1]);

        $user = User::where('correo', $this->correo)->first();

        // Verificar si se envió un código recientemente (throttle 1 min)
        $recent = DB::table('password_recovery_codes')
            ->where('email', $this->correo)
            ->where('created_at', '>', Carbon::now()->subMinute())
            ->first();

        if ($recent) {
            $this->addError('correo', 'Por favor espera un minuto antes de solicitar otro código.');
            return;
        }

        $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('password_recovery_codes')->insert([
            'email' => $this->correo,
            'code' => $code,
            'expires_at' => Carbon::now()->addMinutes(10),
            'created_at' => Carbon::now(),
        ]);

        Mail::to($this->correo)->send(new PasswordRecoveryCodeMail($code));

        $this->step = 2;
        $this->lastCodeSentAt = now();
    }

    public function verifyCode()
    {
        $this->validate($this->rules()[2]);

        $recovery = DB::table('password_recovery_codes')
            ->where('email', $this->correo)
            ->where('used', false)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$recovery || $recovery->code !== $this->code) {
            $this->addError('code', 'El código es incorrecto.');
            return;
        }

        if (Carbon::now()->isAfter($recovery->expires_at)) {
            $this->addError('code', 'El código ha expirado.');
            return;
        }

        $this->step = 3;
    }

    public function resetPassword()
    {
        $this->validate($this->rules()[3]);

        $user = User::where('correo', $this->correo)->first();
        $user->contrasena = Hash::make($this->password);
        $user->save();

        DB::table('password_recovery_codes')
            ->where('email', $this->correo)
            ->update(['used' => true]);

        Mail::to($this->correo)->send(new PasswordChangedMail());

        session()->flash('status', 'Tu contraseña ha sido actualizada con éxito.');
        return redirect()->route('login');
    }

    public function resendCode()
    {
        if ($this->lastCodeSentAt && Carbon::now()->diffInSeconds($this->lastCodeSentAt) < 60) {
            $this->addError('code', 'Espera 60 segundos antes de reenviar.');
            return;
        }

        $this->sendCode();
    }

    public function render()
    {
        return view('livewire.auth.forgot-password')->layout('layouts.guest');
    }
}
