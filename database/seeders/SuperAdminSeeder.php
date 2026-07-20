<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = User::firstOrCreate(
            ['correo' => 'soportesgpd@gmail.com'],
            [
                'nombre' => 'Super Administrador',
                'contrasena' => Hash::make('admin123'), // Recomiendo cambiarla después
                'correo_verificado_en' => now(),
                'empresa_id' => null, // No pertenece a ninguna empresa específica
                'sucursal_id' => null, // No pertenece a ninguna sucursal específica
            ]
        );

        $superAdmin->assignRole('super-admin');
    }
}
