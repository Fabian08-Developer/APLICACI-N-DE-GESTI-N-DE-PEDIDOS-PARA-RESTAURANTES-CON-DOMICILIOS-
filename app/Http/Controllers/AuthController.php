<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\Sesion;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * GET /login
     */
    public function mostrarLogin()
    {
        $roles = Rol::whereNotIn('nombre', ['cliente'])->get();
        return view('auth.login', compact('roles'));
    }

    /**
     * POST /login
     *
     * ANTES: usaba Auth::login() + session() — compartido entre pestañas
     * AHORA: genera un token y redirige con ?_token_init=XXX
     *        El JS de staff-token.blade.php captura el token en sessionStorage (por pestaña)
     *        El middleware lee el token del request y autentica con Auth::onceUsingId()
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $usuario = Usuario::where('email', $request->email)->first();

        // ✅ Mensaje genérico — nunca revelar si el email existe o no
        if (!$usuario || !Hash::check($request->password, $usuario->password)) {
            return back()->with('error', 'El correo o la contraseña son incorrectos');
        }

        if (!$usuario->estado) {
            return back()->with('error', 'Tu cuenta está desactivada, contacta al administrador');
        }

        // Registrar el acceso
        $usuario->update(['ultimo_login' => now()]);

        // Desactivar sesiones previas de este usuario
        Sesion::where('usuario_id', $usuario->id)->update(['activa' => false]);

        // Crear nueva sesión con token
        $token = Str::random(80);
        Sesion::create([
            'usuario_id'       => $usuario->id,
            'token'            => $token,
            'ip'               => $request->ip(),
            'user_agent'       => $request->userAgent(),
            'fecha_expiracion' => now()->addDays(7),
            'activa'           => true,
        ]);

        // ✅ NO usar Auth::login() — escribe a la sesión PHP compartida entre pestañas
        // ✅ NO usar session(['token_usuario' => $token]) — es compartido entre pestañas
        // ✅ NO regenerar session — rompería los CSRF tokens de otras pestañas
        //
        // En vez: redirigir con el token en la URL.
        // El JS en staff-token.blade.php lo captura en sessionStorage (POR PESTAÑA)
        // y lo limpia de la URL inmediatamente.

        $redirectUrl = $this->redirigirSegunRol($usuario->rol->nombre);
        $separator = str_contains($redirectUrl, '?') ? '&' : '?';

        return redirect()
            ->to($redirectUrl . $separator . '_token_init=' . $token)
            ->with('exito', '¡Bienvenido, ' . $usuario->nombre . '!');
    }

    /**
     * POST /logout
     *
     * ANTES: usaba Auth::logout() + session()->invalidate()
     * AHORA: solo desactiva el token en BD.
     *        El JS de la página de login limpia sessionStorage.
     *        NO invalidamos la sesión PHP — otras pestañas la necesitan para CSRF.
     */
    public function logout(Request $request)
    {
        // Leer el token desde el request (inyectado por staff-token.blade.php)
        $token = $request->header('X-Staff-Token')
              ?? $request->query('_st')
              ?? $request->input('_st');

        if ($token) {
            Sesion::where('token', $token)->update(['activa' => false]);
        }

        // ✅ NO hacer Auth::logout() ni session()->invalidate()
        //    Eso destruiría la sesión PHP compartida y rompería otras pestañas.
        //    La "sesión" de esta pestaña se elimina al limpiar sessionStorage.

        // Redirigir a login con flag _clear para que el JS limpie sessionStorage
        return redirect()->route('login', ['_clear' => 1])
            ->with('exito', 'Sesión cerrada correctamente');
    }

    /**
     * GET /register
     */
    public function registerForm()
    {
        $roles = Rol::whereNotIn('nombre', ['cliente'])->get();
        return view('auth.register', compact('roles'));
    }

    /**
     * POST /register
     */
    public function register(Request $request)
    {
        $request->validate([
            'nombre'   => 'required|string|max:100',
            'email'    => 'required|email|unique:usuarios,email',
            'password' => 'required|string|min:6|confirmed',
            'rol_id'   => 'required|exists:roles,id',
        ], [
            'nombre.required'    => 'El nombre es obligatorio',
            'email.required'     => 'El correo es obligatorio',
            'email.unique'       => 'Ese correo ya está registrado',
            'password.min'       => 'La contraseña debe tener al menos 6 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
            'rol_id.required'    => 'Debes seleccionar un rol',
        ]);

        // ✅ No permitir registrar clientes desde este formulario
        $rol = Rol::findOrFail($request->rol_id);
        if ($rol->nombre === 'cliente') {
            return back()->with('error', 'No puedes registrar usuarios con rol de cliente.');
        }

        Usuario::create([
            'nombre'   => $request->nombre,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'rol_id'   => $request->rol_id,
            'estado'   => true,
        ]);

        return redirect()->route('login')
            ->with('exito', 'Cuenta creada exitosamente, ahora inicia sesión');
    }

    /**
     * Centraliza la lógica de redirección por rol.
     * Retorna la URL (no el nombre de ruta) para poder usarla en redirect()->to()
     */
    private function redirigirSegunRol(string $rol): string
    {
        return match($rol) {
            'administrador' => route('admin.dashboard'),
            'mesero'        => route('mesero.dashboard'),
            'cocina'        => route('cocina.dashboard'),
            default         => route('login'),
        };
    }
}