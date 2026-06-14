<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CuentaRestaurada extends Notification
{
    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $empresaNombre = $this->user->empresa ? $this->user->empresa->nombre : 'tu negocio';

        return (new MailMessage)
                    ->subject('Tu cuenta ha sido restaurada con éxito - SGPD')
                    ->greeting('¡Hola ' . $this->user->nombre . '!')
                    ->line('Te informamos con gusto que tu cuenta de Gerente y toda la información asociada a ' . $empresaNombre . ' han sido restauradas correctamente por el Super Administrador.')
                    ->line('Ya tienes acceso total a tu panel de administración, sucursales y operaciones comerciales.')
                    ->action('Iniciar Sesión', url('/login'))
                    ->line('Gracias por ser parte del equipo de SGPD.');
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
