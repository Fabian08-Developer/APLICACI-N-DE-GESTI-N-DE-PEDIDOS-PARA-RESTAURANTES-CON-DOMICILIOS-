<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Enums\RolUsuario;

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
        if (!$user->hasRole(RolUsuario::SUPER_ADMIN->value) && $user->empresa && !$user->empresa->activo) {
            throw ValidationException::withMessages([
                'correo' => 'Tu empresa ha sido suspendida o está inactiva. Contacta al administrador.',
            ]);
        }

        // 4. Verify sucursal is active for branch-level staff
        if (!$user->hasRole(RolUsuario::SUPER_ADMIN->value) && !$user->hasRole(RolUsuario::GERENTE->value)) {
            $sucursal = \App\Models\Sucursal::withoutGlobalScope(\App\Scopes\TenantScope::class)
                ->find($user->sucursal_id);
                
            if (!$sucursal || !$sucursal->activo) {
                throw ValidationException::withMessages([
                    'correo' => 'Tu sucursal asignada está inactiva o ha sido eliminada. Contacta al administrador.',
                ]);
            }
        }

        // 5. Case A: Super Admin or Gerente -> Traditional PHP Session login
        if ($user->hasRole(RolUsuario::SUPER_ADMIN->value) || $user->hasRole(RolUsuario::GERENTE->value)) {
            Auth::login($user, $this->remember);
            // DO NOT regenerate session in Livewire, it breaks the session cookie
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
        $redirectUrl = $this->redirigirSegunRol($user, $token);
        // Redirect passing token in a secure HTTP-only cookie
        $cookie = \Illuminate\Support\Facades\Cookie::make(
            'staff_token', 
            $token, 
            60 * 24 * 7, // 7 días
            '/', 
            null, 
            config('app.env') === 'production', 
            true, 
            false, 
            'Lax'
        );

        \Illuminate\Support\Facades\Cookie::queue($cookie);
        session(['staff_token' => $token]);
        return redirect()->to($redirectUrl);
    }

    private function redirigirSegunRol($user, $token): string
    {
        $roleName = $user->rol->name;
        $redirectUrl = match ($roleName) {
            RolUsuario::ADMINISTRADOR->value => '/admin/dashboard',
            RolUsuario::COCINA->value        => '/cocina/dashboard',
            RolUsuario::MESERO->value        => '/mesero/dashboard',
            RolUsuario::DOMICILIARIO->value  => '/domiciliario/dashboard',
            default                          => '/dashboard',
        };
        
        return $redirectUrl . '?_token_init=' . $token;
    }

    public function render()
    {
        return view('livewire.auth.login')->layout('layouts.guest');
    }
}



