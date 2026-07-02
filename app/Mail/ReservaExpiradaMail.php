<?php

namespace App\Mail;

use App\Models\ReservaMesa;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservaExpiradaMail extends Mailable
{
    use Queueable, SerializesModels;

    public ReservaMesa $reserva;

    /**
     * Create a new message instance.
     */
    public function __construct(ReservaMesa $reserva)
    {
        $this->reserva = $reserva;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reserva Cancelada por Ausencia - SGPD',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.reservas.expirada',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
