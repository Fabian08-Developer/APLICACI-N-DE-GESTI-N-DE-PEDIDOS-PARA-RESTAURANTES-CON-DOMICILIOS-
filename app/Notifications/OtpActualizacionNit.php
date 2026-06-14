<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OtpActualizacionNit extends Notification
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
            ->subject('Código de verificación - Actualización de NIT')
            ->greeting('¡Hola!')
            ->line('Has solicitado actualizar el documento NIT de tu empresa en nuestra plataforma.')
            ->line('Tu código de verificación de seguridad es:')
            ->line('**' . $this->codigo . '**')
            ->line('Ingresa este código de 6 dígitos en el panel de configuración de tu negocio para confirmar la actualización del archivo.')
            ->line('Este código expirará en 10 minutos por razones de seguridad.')
            ->line('Si tú no solicitaste este cambio, puedes ignorar este correo.');
    }
}
