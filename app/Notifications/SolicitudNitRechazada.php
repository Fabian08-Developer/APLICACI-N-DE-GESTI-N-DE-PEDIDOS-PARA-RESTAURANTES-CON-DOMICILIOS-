<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SolicitudNitRechazada extends Notification
{
    protected $nombreGerente;
    protected $nombreEmpresa;
    protected $motivoRechazo;

    public function __construct($nombreGerente, $nombreEmpresa, $motivoRechazo)
    {
        $this->nombreGerente = $nombreGerente;
        $this->nombreEmpresa = $nombreEmpresa;
        $this->motivoRechazo = $motivoRechazo;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Actualización de NIT Rechazada - Café Bambú')
                    ->greeting('Hola ' . $this->nombreGerente . ',')
                    ->line('Te informamos que la solicitud de actualización del documento NIT para tu empresa **' . $this->nombreEmpresa . '** no ha sido aprobada por el Super Administrador.')
                    ->line('**Motivo del rechazo:**')
                    ->line('"' . $this->motivoRechazo . '"')
                    ->line('Por favor, ingresa a la configuración de tu negocio, revisa las observaciones anteriores y sube una versión corregida del documento.')
                    ->line('Si tienes dudas adicionales, puedes ponerte en contacto con soporte técnico.')
                    ->salutation('Atentamente, El equipo de Café Bambú');
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
