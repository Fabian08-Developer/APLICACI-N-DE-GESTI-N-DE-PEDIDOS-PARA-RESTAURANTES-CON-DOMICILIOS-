<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NuevaSolicitudNit extends Notification
{
    use Queueable;

    protected $nombreEmpresa;
    protected $nit;
    protected $nombreGerente;

    public function __construct($nombreEmpresa, $nit, $nombreGerente)
    {
        $this->nombreEmpresa = $nombreEmpresa;
        $this->nit = $nit;
        $this->nombreGerente = $nombreGerente;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Nueva Solicitud de Actualización de NIT - Café Bambú')
                    ->greeting('Hola Super Administrador,')
                    ->line('El gerente **' . $this->nombreGerente . '** de la empresa **' . $this->nombreEmpresa . '** (NIT: ' . $this->nit . ') ha subido un nuevo documento de NIT.')
                    ->line('Esta solicitud requiere tu revisión y aprobación.')
                    ->action('Revisar Solicitudes', url('/super-admin/requests'))
                    ->line('Por favor, ingresa al panel de control para aprobar o rechazar esta solicitud.')
                    ->salutation('Atentamente, El equipo de Café Bambú');
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
