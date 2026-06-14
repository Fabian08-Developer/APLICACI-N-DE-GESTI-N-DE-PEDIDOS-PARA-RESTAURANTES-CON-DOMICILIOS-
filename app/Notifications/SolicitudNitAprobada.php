<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SolicitudNitAprobada extends Notification
{
    protected $nombreGerente;
    protected $nombreEmpresa;

    public function __construct($nombreGerente, $nombreEmpresa)
    {
        $this->nombreGerente = $nombreGerente;
        $this->nombreEmpresa = $nombreEmpresa;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Actualización de NIT Aprobada - Café Bambú')
                    ->greeting('Hola ' . $this->nombreGerente . ',')
                    ->line('Nos complace informarte que la solicitud de actualización del documento NIT para tu empresa **' . $this->nombreEmpresa . '** ha sido revisada y aprobada por el Super Administrador.')
                    ->line('El nuevo documento ya se encuentra activo en el sistema y puedes descargarlo en cualquier momento desde tu panel de configuración del negocio.')
                    ->line('Gracias por mantener la información de tu negocio al día con Café Bambú.')
                    ->salutation('Atentamente, El equipo de Café Bambú');
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
