<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SolicitudRechazada extends Notification
{

    /**
     * Create a new notification instance.
     */
    protected $nombreGerente;

    public function __construct($nombreGerente)
    {
        $this->nombreGerente = $nombreGerente;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Solicitud de registro rechazada - SGPD')
                    ->greeting('Hola ' . $this->nombreGerente . ',')
                    ->line('Lamentamos informarte que tu solicitud de registro de negocio ha sido rechazada por el administrador después de revisar la documentación.')
                    ->line('Si crees que esto es un error o deseas más información, puedes ponerte en contacto con soporte.')
                    ->line('Gracias por tu interés en SGPD.');
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
