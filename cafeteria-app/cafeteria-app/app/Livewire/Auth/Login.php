<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class Login extends Component
{
    public $correo;
    public $contrasena;
    public $remember = false;

    protected $rules = [
        'correo' => 'required|email',
        'contrasena' => 'required',
    ];

    public function login()
    {
        $this->validate();

        // 1. Find user by email (bypass TenantScope during login)
        $user = \App\Models\User::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->where('correo', $this->correo)
            ->first();

        if (!$user || !\Illuminate\Support\Facades\Hash::check($this->contrasena, $user->contrasena)) {
            throw ValidationException::withMessages([
                'correo' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
            ]);
        }

        // 2. Verify user is active
        if (!$user->activo) {
            throw ValidationException::withMessages([
                'correo' => 'Tu cuenta está pendiente de aprobación o ha sido suspendida. Contacta al administrador.',
            ]);
        }

        // 3. Verify company is active (except for super-admins)
        if (!$user->hasRole('super-admin') && $user->empresa && !$user->empresa->activo) {
            throw ValidationException::withMessages([
                'correo' => 'Tu empresa ha sido suspendida o está inactiva. Contacta al administrador.',
            ]);
        }

        // 4. Case A: Super Admin or Gerente -> Traditional PHP Session login
        if ($user->hasRole('super-admin') || $user->hasRole('gerente')) {
            Auth::login($user, $this->remember);
            session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        // 5. Case B: Staff (Administrador, Waiter, Kitchen, etc.) -> Tab-Isolated Token Session login
        // Invalidate any previous token sessions for this user to keep it clean
        \App\Models\Sesion::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->where('usuario_id', $user->id)
            ->update(['activa' => false]);

        // Generate the tab-isolated session token
        $token = \Illuminate\Support\Str::random(80);
        \App\Models\Sesion::create([
            'sucursal_id'      => $user->sucursal_id,
            'usuario_id'       => $user->id,
            'token'            => $token,
            'ip'               => request()->ip(),
            'user_agent'       => request()->userAgent(),
            'fecha_expiracion' => now()->addDays(7),
            'activa'           => true,
        ]);

        // Determine destination dashboard
        $redirectUrl = $this->redirigirSegunRol($user);
        $separator = str_contains($redirectUrl, '?') ? '&' : '?';

        // Redirect passing _token_init to be captured by sessionStorage
        return redirect()->to($redirectUrl . $separator . '_token_init=' . $token);
    }

    private function redirigirSegunRol($user): string
    {
        if ($user->hasRole('administrador')) {
            return route('admin.dashboard');
        }
        if ($user->hasRole('mesero')) {
            return route('mesero.dashboard');
        }
        if ($user->hasRole('cocina')) {
            return route('cocina.dashboard');
        }
        if ($user->hasRole('domiciliario')) {
            return route('domiciliario.dashboard');
        }
        // Fallback for delivery or others if they access the panel
        return route('admin.dashboard');
    }

    public function render()
    {
        return view('livewire.auth.login')->layout('layouts.guest');
    }
}



