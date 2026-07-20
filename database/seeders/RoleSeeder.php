<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Corregir discrepancia heredada ('cocinero' -> 'cocina') si existe
        $roleCocinero = Role::where('name', 'cocinero')->first();
        if ($roleCocinero) {
            $roleCocinero->name = 'cocina';
            $roleCocinero->save();
        }
        \Illuminate\Support\Facades\DB::table('usuarios')->where('rol', 'cocinero')->update(['rol' => 'cocina']);

        // Roles principales
        $roles = ['super-admin', 'gerente', 'administrador', 'cocina', 'mesero', 'domiciliario'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // Permisos de ejemplo
        $gerente = Role::where('name', 'gerente')->first();
        $admin = Role::where('name', 'administrador')->first();

        Permission::firstOrCreate(['name' => 'gestionar-sucursales', 'guard_name' => 'web'])->assignRole($gerente);
        Permission::firstOrCreate(['name' => 'ver-reportes-sede', 'guard_name' => 'web'])->assignRole($admin);

        // Migrar usuarios existentes
        $users = \App\Models\User::all();
        foreach($users as $user) {
            $rolOriginal = $user->getRawOriginal('rol');
            if ($rolOriginal) {
                // Asegurarse de que el rol exista
                Role::firstOrCreate(['name' => $rolOriginal, 'guard_name' => 'web']);
                // Asignar rol de Spatie si no lo tiene
                if (!$user->roles()->where('name', $rolOriginal)->exists()) {
                    $user->assignRole($rolOriginal);
                }
            }
        }
    }
}
