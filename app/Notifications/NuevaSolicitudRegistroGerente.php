<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NuevaSolicitudRegistroGerente extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $empresa;
    public $user;

    public function __construct($empresa, $user)
    {
        $this->empresa = $empresa;
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
                    ->subject('Nueva Solicitud de Registro de Gerente - SGPD')
                    ->greeting('Hola Administrador,')
                    ->line('Se ha recibido una nueva solicitud de registro para un gerente de restaurante.')
                    ->line('Detalles del Negocio:')
                    ->line('- Nombre: ' . $this->empresa->nombre)
                    ->line('- NIT: ' . $this->empresa->nit)
                    ->line('Detalles del Gerente:')
                    ->line('- Nombre: ' . $this->user->nombre)
                    ->line('- Correo: ' . $this->user->correo)
                    ->action('Revisar Solicitud', url('/master/dashboard')) // Asumiendo que hay una ruta para esto
                    ->line('El documento adjunto está disponible en el servidor.');
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
