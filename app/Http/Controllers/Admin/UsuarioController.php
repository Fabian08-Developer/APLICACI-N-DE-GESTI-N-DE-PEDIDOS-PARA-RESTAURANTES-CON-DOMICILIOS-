<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PerfilDomiciliario;

class UsuarioController extends Controller
{
    public function store(Request $request)
    {
        $sucursal_id = auth()->user()->sucursal_id;
        $empresa_id = auth()->user()->empresa_id;
        if (!$sucursal_id) {
            return back()->withErrors(['general' => 'No tienes una sucursal asignada.']);
        }

        // 1. Toggle status request
        if ($request->has('toggle_user_id')) {
            $userToToggle = User::find($request->input('toggle_user_id'));
            if ($userToToggle && $userToToggle->id !== auth()->id()) {
                // Authorization check
                if (!auth()->user()->canManage($userToToggle)) {
                    return back()->withErrors(['general' => 'No tienes permisos para modificar el estado de este usuario.']);
                }

                $userToToggle->update([
                    'activo' => !$userToToggle->activo
                ]);
                return redirect()->route('admin.usuarios.index')->with('success', 'Estado del usuario actualizado.');
            }
            return back()->withErrors(['general' => 'No se pudo actualizar el estado.']);
        }

        // Validate name (blocks emojis) for both creation and editing
        $request->validate([
            'nombre' => ['required', 'string', 'max:150', 'not_regex:/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/u'],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.not_regex' => 'El nombre no debe contener emojis.',
        ]);

        // 2. Edit request
        if ($request->has('user_id')) {
            $userToEdit = User::find($request->input('user_id'));
            if (!$userToEdit) {
                return back()->withErrors(['general' => 'Usuario no encontrado.']);
            }

            // Authorization check: Can the logged-in user manage this user?
            if (!auth()->user()->canManage($userToEdit)) {
                return back()->withErrors(['general' => 'No tienes permisos para editar este usuario.']);
            }

            $rol = $request->input('rol_id'); 
            if (!$rol) {
                return back()->withErrors(['rol_id' => 'Rol inválido.']);
            }

            // Authorization check for the new role to be assigned
            $currentUser = auth()->user();
            if ($currentUser->hasRole('gerente')) {
                $allowed = ['administrador', 'cocina', 'mesero', 'domiciliario'];
            } elseif ($currentUser->hasRole('administrador')) {
                $allowed = ['cocina', 'mesero', 'domiciliario'];
            } else {
                $allowed = [];
            }

            if (!in_array($rol, $allowed)) {
                return back()->withErrors(['rol_id' => 'No tienes permisos para asignar este rol.']);
            }

            if (User::where('correo', $request->input('email'))->where('id', '!=', $userToEdit->id)->exists()) {
                return back()->withErrors(['email' => 'El correo electrónico ya está registrado.']);
            }

            $oldRol = $userToEdit->rol->name;

            $updateData = [
                'nombre' => $request->input('nombre'),
                'correo' => $request->input('email'),
                'activo' => $request->has('estado'),
                'rol' => $rol,
            ];

            if ($request->filled('password')) {
                $updateData['contrasena'] = bcrypt($request->input('password'));
            }

            $userToEdit->update($updateData);

            // Handle PerfilDomiciliario assignment logic
            if ($oldRol !== 'domiciliario' && $rol === 'domiciliario') {
                PerfilDomiciliario::firstOrCreate(
                    ['usuario_id' => $userToEdit->id],
                    [
                        'sucursal_id' => $sucursal_id,
                        'tipo_vehiculo' => 'moto',
                        'estado' => 'disponible',
                    ]
                );
            } elseif ($oldRol === 'domiciliario' && $rol !== 'domiciliario') {
                PerfilDomiciliario::where('usuario_id', $userToEdit->id)->delete();
            }

            return redirect()->route('admin.usuarios.index')->with('success', 'Usuario actualizado correctamente.');
        }

        // 3. Create request
        $rol = $request->input('rol_id');
        if (!$rol) {
            return back()->withErrors(['rol_id' => 'Rol inválido.']);
        }

        // Authorization check
        $currentUser = auth()->user();
        if ($currentUser->hasRole('gerente')) {
            $allowed = ['administrador', 'cocina', 'mesero', 'domiciliario'];
        } elseif ($currentUser->hasRole('administrador')) {
            $allowed = ['cocina', 'mesero', 'domiciliario'];
        } else {
            $allowed = [];
        }

        if (!in_array($rol, $allowed)) {
            return back()->withErrors(['rol_id' => 'No tienes permisos para asignar este rol.']);
        }

        if (User::where('correo', $request->input('email'))->exists()) {
            return back()->withErrors(['email' => 'El correo electrónico ya está registrado.']);
        }

        $newUser = User::create([
            'empresa_id' => $empresa_id,
            'sucursal_id' => $sucursal_id,
            'nombre' => $request->input('nombre'),
            'correo' => $request->input('email'),
            'contrasena' => bcrypt($request->input('password')),
            'activo' => $request->has('estado'),
            'rol' => $rol,
        ]);

        if ($rol === 'domiciliario') {
            PerfilDomiciliario::create([
                'usuario_id' => $newUser->id,
                'sucursal_id' => $sucursal_id,
                'tipo_vehiculo' => 'moto',
                'estado' => 'disponible',
            ]);
        }

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    public function destroy($id)
    {
        // Don't let users delete themselves
        if ($id === auth()->id()) {
            return back()->with('error', 'No puedes eliminarte a ti mismo.')->withErrors(['general' => 'No puedes eliminarte a ti mismo.']);
        }

        $userToDelete = User::find($id);
        if ($userToDelete) {
            // Check if user belongs to the same sucursal
            if ($userToDelete->sucursal_id === auth()->user()->sucursal_id) {
                // Authorization check: Can the logged-in user manage this user?
                if (!auth()->user()->canManage($userToDelete)) {
                    return back()->with('error', 'No tienes permiso para eliminar este usuario.')->withErrors(['general' => 'No tienes permiso para eliminar este usuario.']);
                }

                if ($userToDelete->rol->name === 'domiciliario') {
                    PerfilDomiciliario::where('usuario_id', $userToDelete->id)->delete();
                }

                $userToDelete->delete();
                return redirect()->route('admin.usuarios.index')->with('success', 'Usuario eliminado correctamente.');
            }
            return back()->with('error', 'No tienes permiso para eliminar este usuario.')->withErrors(['general' => 'No tienes permiso para eliminar este usuario.']);
        }

        return back()->with('error', 'Usuario no encontrado.')->withErrors(['general' => 'Usuario no encontrado.']);
    }
}
