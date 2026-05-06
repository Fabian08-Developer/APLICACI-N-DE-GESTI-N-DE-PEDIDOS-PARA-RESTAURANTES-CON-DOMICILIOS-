<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SalesSummaryNotification extends Notification
{
    use Queueable;

    protected $data;
    protected $sections;

    /**
     * Create a new notification instance.
     */
    public function __construct($data, $sections = [])
    {
        $this->data = $data;
        $this->sections = $sections;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $notifiable->preferred_channel ?? ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $metrics = $this->data['metrics'];
        
        return (new MailMessage)
                    ->subject('Reporte de Ventas Programado')
                    ->greeting('¡Hola!')
                    ->line('Adjuntamos el resumen de ventas solicitado.')
                    ->line('Ventas Totales: $' . number_format($metrics['ventasTotales'], 2))
                    ->line('Pedidos: ' . $metrics['totalPedidos'])
                    ->action('Ver Dashboard completo', url('/admin/reports/sales'))
                    ->line('Gracias por usar nuestro sistema.');
    }

    /**
     * Get the WhatsApp representation of the notification.
     */
    public function toWhatsApp(object $notifiable): string
    {
        $metrics = $this->data['metrics'];
        $start = $this->data['start'];
        $end = $this->data['end'];

        return "📊 *Reporte de Ventas ({$start} a {$end})*\n\n" .
               "💰 *Ventas:* $" . number_format($metrics['ventasTotales'], 2) . "\n" .
               "🛒 *Pedidos:* " . $metrics['totalPedidos'] . "\n" .
               "🎟️ *Ticket Prom:* $" . number_format($metrics['ticketPromedio'], 2) . "\n\n" .
               "Ver más: " . url('/admin/reports/sales');
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
