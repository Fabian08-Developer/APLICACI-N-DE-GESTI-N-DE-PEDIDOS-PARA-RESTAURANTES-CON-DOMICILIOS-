<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Sucursal;

class DeleteSucursalVerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $codigo;
    public $sucursal;

    public function __construct(string $codigo, Sucursal $sucursal)
    {
        $this->codigo = $codigo;
        $this->sucursal = $sucursal;
    }

    public function build()
    {
        return $this->subject('Código de verificación para eliminar sucursal')
                    ->view('emails.delete-sucursal-code');
    }
}
