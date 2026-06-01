<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:cleanup-inactive-accounts')]
#[Description('Command description')]
class CleanupInactiveAccounts extends Command
{
    protected $signature = 'accounts:cleanup-inactive';
    protected $description = 'Elimina cuentas de gerentes inactivas por más de un mes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = now()->subMonth();

        $inactiveUsers = \App\Models\User::where('activo', false)
            ->where('creado_en', '<', $limit)
            ->get();

        foreach ($inactiveUsers as $user) {
            $this->info("Eliminando usuario inactivo: " . $user->correo);
            
            // Si el usuario tiene una empresa inactiva, también la eliminamos
            if ($user->empresa && !$user->empresa->activo) {
                $user->empresa->delete();
            }
            
            $user->delete();
        }

        $this->info('Limpieza completada.');
    }
}
