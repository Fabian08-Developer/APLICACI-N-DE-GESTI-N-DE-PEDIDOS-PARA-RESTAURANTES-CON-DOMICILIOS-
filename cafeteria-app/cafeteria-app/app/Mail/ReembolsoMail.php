<?php

namespace App\Mail;

use App\Models\Pedido;
use App\Models\Pago;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReembolsoMail extends Mailable
{
    use Queueable, SerializesModels;

    public Pedido $pedido;
    public Pago $pago;

    /**
     * Create a new message instance.
     */
    public function __construct(Pedido $pedido, Pago $pago)
    {
        $this->pedido = $pedido;
        $this->pago = $pago;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reembolso Procesado - SGPD',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.reembolso',
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
