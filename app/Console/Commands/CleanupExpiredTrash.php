<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Sucursal;
use Illuminate\Support\Facades\Storage;

class CleanupExpiredTrash extends Command
{
    protected $signature = 'accounts:cleanup-trash';
    protected $description = 'Elimina de forma permanente las cuentas de gerentes en la papelera cuya antigüedad en recuperación supere los 30 días';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limitDate = now()->subDays(30);

        // Fetch gerentes in trash who exceeded 30 days
        $expiredGerentes = User::onlyTrashed()
            ->where('rol', 'gerente')
            ->where('eliminado_en', '<', $limitDate)
            ->get();

        if ($expiredGerentes->isEmpty()) {
            $this->info('No hay gerentes en recuperación expirados para eliminar.');
            return;
        }

        foreach ($expiredGerentes as $gerente) {
            $this->info("Procesando purga automática para el gerente: " . $gerente->correo . " (Eliminado el: " . $gerente->eliminado_en . ")");

            $empresaId = $gerente->empresa_id;
            if ($empresaId) {
                $empresa = Empresa::withTrashed()->find($empresaId);

                if ($empresa) {
                    // 1. Force delete all users belonging to this company
                    User::withTrashed()->where('empresa_id', $empresaId)->forceDelete();
                    $this->line(" - Todos los usuarios de la empresa '" . $empresa->nombre . "' han sido eliminados de forma definitiva.");

                    // 2. Force delete all sucursales belonging to this company
                    Sucursal::withTrashed()->where('empresa_id', $empresaId)->forceDelete();
                    $this->line(" - Todas las sucursales han sido eliminadas de forma definitiva.");

                    // 3. Remove physical document files from storage
                    if ($empresa->documento_path) {
                        if (Storage::disk('public')->exists($empresa->documento_path)) {
                            Storage::disk('public')->delete($empresa->documento_path);
                            $this->line(" - Archivo NIT original eliminado de storage.");
                        }
                    }
                    if ($empresa->documento_pendiente_path) {
                        if (Storage::disk('public')->exists($empresa->documento_pendiente_path)) {
                            Storage::disk('public')->delete($empresa->documento_pendiente_path);
                            $this->line(" - Archivo NIT pendiente eliminado de storage.");
                        }
                    }

                    // 4. Force delete the company
                    $empresa->forceDelete();
                    $this->line(" - La empresa '" . $empresa->nombre . "' ha sido eliminada permanentemente.");
                }
            } else {
                // If there's no company linked, just force-delete this single user
                $gerente->forceDelete();
            }

            $this->info("Purga completada exitosamente para el gerente.");
        }

        $this->info('Limpieza de papelera de gerentes completada.');
    }
}
