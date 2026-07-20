<?php

namespace App\Mail;

use App\Models\Sucursal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReporteProgramadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Sucursal $sucursal,
        public readonly string   $periodo,
        protected readonly string   $pdfContent,   // PDF generado en memoria (string binario)
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "📊 Reporte de Ventas — {$this->sucursal->nombre} — {$this->periodo}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reporte-programado',
        );
    }

    public function attachments(): array
    {
        $filename = 'Reporte_Ventas_' . now()->format('Ym') . '_' . $this->sucursal->nombre . '.pdf';

        return [
            Attachment::fromData(fn () => $this->pdfContent, $filename)
                ->withMime('application/pdf'),
        ];
    }
}
