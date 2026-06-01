<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CodigoConfirmacionPerfil extends Notification
{
    use Queueable;

    protected $codigo;

    public function __construct($codigo)
    {
        $this->codigo = $codigo;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Código de seguridad para modificaciones')
            ->greeting('Confirmación de seguridad')
            ->line('Se ha solicitado una modificación sensible en tu perfil o en los datos del negocio.')
            ->line('Tu código de confirmación es: **' . $this->codigo . '**')
            ->line('Este código expirará en 15 minutos. No lo compartas con nadie.');
    }
}
