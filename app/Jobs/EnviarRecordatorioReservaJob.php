<?php

namespace App\Jobs;

use App\Mail\RecordatorioReservaMail;
use App\Models\ReservaMesa;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class EnviarRecordatorioReservaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries    = 3;
    public int $timeout  = 60;

    public function __construct(
        public readonly string $reservaId
    ) {}

    public function handle(): void
    {
        $reserva = ReservaMesa::find($this->reservaId);

        // Si ya no existe o está en estado final, no enviar
        if (!$reserva || $reserva->estado->esFinal()) {
            return;
        }

        try {
            Mail::to($reserva->correo_cliente)
                ->send(new RecordatorioReservaMail($reserva));
        } catch (\Exception $e) {
            logger()->error('Error enviando recordatorio de reserva', [
                'reserva_id' => $this->reservaId,
                'error'      => $e->getMessage(),
            ]);
            throw $e; // Re-lanza para reintentos
        }
    }
}
