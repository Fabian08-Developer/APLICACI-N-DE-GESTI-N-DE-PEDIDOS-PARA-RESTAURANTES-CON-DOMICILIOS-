<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CuentaEnRecuperacion extends Notification
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
                    ->subject('Tu cuenta está en estado de recuperación - SGPD')
                    ->greeting('¡Hola ' . $this->user->nombre . '!')
                    ->line('Te informamos que tu cuenta de Gerente y la información asociada a ' . $empresaNombre . ' han sido puestas en estado de recuperación por el Super Administrador.')
                    ->line('Esta cuenta y todos sus datos se mantendrán suspendidos por un período de 30 días.')
                    ->line('Para garantizar la legitimidad y prevenir fraudes, hemos habilitado una opción de recuperación directa donde podrás verificar la identidad de tu negocio:')
                    ->action('Verificar y Recuperar Cuenta', route('auth.recover-account', ['userId' => $this->user->id]))
                    ->line('IMPORTANTE: Si transcurren los 30 días y la cuenta no es restaurada, toda la información asociada será eliminada permanentemente del sistema de forma automática para evitar acumulación de datos residuales.')
                    ->line('Atentamente, el equipo de SGPD.');
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
