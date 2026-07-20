<?php

namespace App\Jobs;

use App\Http\Controllers\Admin\ProgramacionReporteController;
use App\Mail\ReporteProgramadoMail;
use App\Models\ProgramacionReporte;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EnviarReporteProgramadoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 300; // 5 minutos entre reintentos

    public function __construct(public readonly ProgramacionReporte $programacion) {}

    public function handle(): void
    {
        $prog     = $this->programacion;
        $sucursal = $prog->sucursal;

        if (!$sucursal) {
            Log::warning("[EnviarReporteProgramadoJob] Sucursal no encontrada para programacion {$prog->id}");
            return;
        }

        // Obtener un usuario gerente/admin de esa sucursal para tener contexto de auth
        $user = $sucursal->empresa?->usuarios()
            ->where('sucursal_id', $sucursal->id)
            ->whereIn('rol', ['administrador', 'gerente', 'super-admin'])
            ->first();

        if (!$user) {
            Log::warning("[EnviarReporteProgramadoJob] Sin usuario admin para sucursal {$sucursal->id}");
            return;
        }

        // Generar el PDF del mes actual
        try {
            $pdfData    = ProgramacionReporteController::generarDatosReporte($user, $sucursal->id);
            $pdf        = Pdf::loadView('admin.reportes.pdf', $pdfData);
            $pdfContent = $pdf->output();
        } catch (\Throwable $e) {
            Log::error("[EnviarReporteProgramadoJob] Error generando PDF: {$e->getMessage()}");
            $this->fail($e);
            return;
        }

        $periodoLabel = 'Mes actual (' . now()->format('F Y') . ')';

        // Enviar por correo a cada destinatario
        if ($prog->metodo === 'email') {
            $destinatarios = $prog->destinatarios ?? [];
            foreach ($destinatarios as $email) {
                try {
                    Mail::to($email)->send(
                        new ReporteProgramadoMail($sucursal, $periodoLabel, $pdfContent)
                    );
                } catch (\Throwable $e) {
                    Log::error("[EnviarReporteProgramadoJob] Error enviando a {$email}: {$e->getMessage()}");
                }
            }
        }
        // WhatsApp: no implementado automáticamente — requiere API externa
        // Solo se registra el intento en log para futura integración
        if ($prog->metodo === 'whatsapp') {
            Log::info("[EnviarReporteProgramadoJob] WhatsApp pendiente de integración para {$prog->numero_whatsapp}");
        }

        // Actualizar timestamps de la programación
        $prog->ultimo_envio_en  = now();
        $prog->proximo_envio_en = $prog->calcularProximoEnvio();
        $prog->save();

        Log::info("[EnviarReporteProgramadoJob] Reporte enviado para sucursal {$sucursal->nombre} (prog {$prog->id})");
    }
}
