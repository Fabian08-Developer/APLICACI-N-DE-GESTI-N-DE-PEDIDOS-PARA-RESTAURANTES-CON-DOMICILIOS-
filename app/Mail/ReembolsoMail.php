<?php

namespace App\Mail;

use App\Models\Pago;
use App\Models\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReembolsoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Pedido $pedido,
        public readonly Pago   $pago,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '💰 Reembolso procesado — Pedido #' . $this->pedido->id,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reembolso',
        );
    }
}