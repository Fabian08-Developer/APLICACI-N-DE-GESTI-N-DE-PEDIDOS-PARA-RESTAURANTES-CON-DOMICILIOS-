<?php

namespace App\Mail;

use App\Models\PagoReserva;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ComprobantePagoReservaMail extends Mailable
{
    use Queueable, SerializesModels;

    public PagoReserva $pago;

    /**
     * Create a new message instance.
     */
    public function __construct(PagoReserva $pago)
    {
        $this->pago = $pago;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Comprobante de Pago - Reserva SGPD',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.reservas.comprobante',
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
