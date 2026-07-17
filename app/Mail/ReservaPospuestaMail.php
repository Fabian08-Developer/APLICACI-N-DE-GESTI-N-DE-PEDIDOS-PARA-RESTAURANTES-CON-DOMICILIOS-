<?php

namespace App\Mail;

use App\Models\ReservaMesa;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservaPospuestaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly ReservaMesa $reserva,
        public readonly string $fechaAnterior,
        public readonly string $horaAnterior,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "📅 Reserva Reprogramada — {$this->reserva->codigo_reserva}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reservas.pospuesta',
        );
    }
}
