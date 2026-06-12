<?php

namespace App\Mail;

use App\Models\LiquidacionDomiciliario;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ComprobanteLiquidacion extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public LiquidacionDomiciliario $liquidacion) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Comprobante de Liquidación de Caja — ' . $this->liquidacion->perfil->nombre,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.comprobante-liquidacion',
        );
    }
}
