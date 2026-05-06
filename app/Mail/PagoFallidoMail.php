<?php

namespace App\Mail;

use App\Models\Pago;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PagoFallidoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Pago $pago) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '❌ Pago no completado — Pedido #' . $this->pago->pedido_id,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pago_fallido',
        );
    }
}
