<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = Usuario::with('rol')->latest()->get();
        $roles    = Rol::all();
        $editar   = null;

        return view('admin.usuarios.index', compact('usuarios', 'roles', 'editar'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'   => 'required|string|max:100',
            'email'    => 'required|email|unique:usuarios,email',
            'password' => 'required|string|min:6',
            'rol_id'   => 'required|exists:roles,id',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'email.required'  => 'El correo es obligatorio',
            'email.unique'    => 'Ese correo ya está registrado',
            'password.min'    => 'La contraseña debe tener al menos 6 caracteres',
            'rol_id.required' => 'Debes seleccionar un rol',
        ]);

        Usuario::create([
            'nombre'   => $request->nombre,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'rol_id'   => $request->rol_id,
            'estado'   => $request->has('estado') ? true : false,
        ]);

        return redirect()->route('admin.usuarios.index')
                         ->with('exito', 'Usuario creado correctamente');
    }

    public function editar($id)
    {
        $usuarios = Usuario::with('rol')->latest()->get();
        $roles    = Rol::all();
        $editar   = Usuario::findOrFail($id);

        return view('admin.usuarios.index', compact('usuarios', 'roles', 'editar'));
    }

    public function actualizar(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);

        $request->validate([
            'nombre'   => 'required|string|max:100',
            'email'    => 'required|email|unique:usuarios,email,' . $id,
            'rol_id'   => 'required|exists:roles,id',
            'password' => 'nullable|string|min:6',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'email.unique'    => 'Ese correo ya está en uso',
            'password.min'    => 'La contraseña debe tener al menos 6 caracteres',
        ]);

        $datos = [
            'nombre' => $request->nombre,
            'email'  => $request->email,
            'rol_id' => $request->rol_id,
            'estado' => $request->has('estado') ? true : false,
        ];

        // Solo actualiza la contraseña si se escribió una nueva
        if ($request->filled('password')) {
            $datos['password'] = Hash::make($request->password);
        }

        $usuario->update($datos);

        return redirect()->route('admin.usuarios.index')
                         ->with('exito', 'Usuario actualizado correctamente');
    }

    public function toggle($id)
    {
        $usuario = Usuario::findOrFail($id);
        $usuario->update(['estado' => !$usuario->estado]);

        $accion = $usuario->estado ? 'activado' : 'desactivado';

        return redirect()->back()->with('exito', 'Usuario ' . $accion . ' correctamente');
    }
}