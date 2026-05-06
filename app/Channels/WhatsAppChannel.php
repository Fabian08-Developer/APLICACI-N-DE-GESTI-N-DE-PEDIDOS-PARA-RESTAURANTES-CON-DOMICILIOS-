<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class WhatsAppChannel
{
    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toWhatsApp')) {
            return;
        }

        $message = $notification->toWhatsApp($notifiable);
        $to = $notifiable->routeNotificationFor('WhatsApp');

        if (!$to || !$message) {
            return;
        }

        // Aquí iría la integración real con Twilio, UltraMsg, etc.
        // Por ahora, simulamos el envío en los logs para pruebas.
        Log::info("WhatsApp enviado a {$to}: {$message}");
        
        // Simulación de éxito
        return true;
    }
}
