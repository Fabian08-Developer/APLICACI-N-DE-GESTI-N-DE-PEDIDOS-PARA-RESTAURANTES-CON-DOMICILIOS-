<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class VerificarCorreoGerente extends Notification
{
    use Queueable;

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        // Generamos una URL firmada segura que expira en 60 minutos
        $urlVerificacion = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $notifiable->getKey(), 'hash' => sha1($notifiable->getEmailForVerification())]
        );

        return (new MailMessage)
            ->subject('Verifica tu cuenta de Gerente')
            ->greeting('¡Hola, ' . $notifiable->nombre . '!')
            ->line('Gracias por registrar tu empresa. Para desbloquear todas las funciones operativas de tus sedes, por favor confirma tu correo.')
            ->action('Verificar Correo', $urlVerificacion)
            ->line('Si omitiste este paso en el registro, puedes completarlo en cualquier momento desde tu panel.');
    }
}