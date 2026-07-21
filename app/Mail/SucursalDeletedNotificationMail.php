<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SucursalDeletedNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $nombreSucursal;

    public function __construct(string $nombreSucursal)
    {
        $this->nombreSucursal = $nombreSucursal;
    }

    public function build()
    {
        return $this->subject('Notificación de Eliminación de Sucursal')
                    ->view('emails.sucursal-deleted');
    }
}
