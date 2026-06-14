<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CodigoVerificacionRegistro extends Notification
{
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
            ->subject('Código de verificación de registro')
            ->greeting('¡Bienvenido!')
            ->line('Gracias por iniciar el registro de tu negocio en nuestra plataforma.')
            ->line('Tu código de verificación es: **' . $this->codigo . '**')
            ->line('Ingresa este código en la pantalla de registro para completar el proceso.')
            ->line('Este código expirará en 15 minutos.');
    }
}
