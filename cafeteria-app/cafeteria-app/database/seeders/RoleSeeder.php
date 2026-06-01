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
        // Roles principales
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $gerente = Role::firstOrCreate(['name' => 'gerente', 'guard_name' => 'web']);
        $admin = Role::firstOrCreate(['name' => 'administrador', 'guard_name' => 'web']);
        $staff = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']); // Cocina, Meseros, etc.

        // Permisos de ejemplo
        Permission::firstOrCreate(['name' => 'gestionar-sucursales', 'guard_name' => 'web'])->assignRole($gerente);
        Permission::firstOrCreate(['name' => 'ver-reportes-sede', 'guard_name' => 'web'])->assignRole($admin);
    }
}
