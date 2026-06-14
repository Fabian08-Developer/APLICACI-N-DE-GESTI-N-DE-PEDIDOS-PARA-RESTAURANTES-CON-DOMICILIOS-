<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CuentaGerenteAprobada extends Notification
{

    /**
     * Create a new notification instance.
     */
    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Tu cuenta ha sido aprobada - SGPD')
                    ->greeting('¡Hola ' . $this->user->nombre . '!')
                    ->line('Nos complace informarte que tu solicitud de registro ha sido aprobada por el administrador.')
                    ->line('Ya puedes acceder a la plataforma con tus credenciales:')
                    ->line('- Correo: ' . $this->user->correo)
                    ->line('- Contraseña: La que ingresaste al momento del registro.')
                    ->action('Iniciar Sesión', url('/login'))
                    ->line('¡Bienvenido a SGPD!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
