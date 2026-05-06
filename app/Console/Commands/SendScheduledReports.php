<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ReportSchedule;
use App\Services\Reports\ReportDataService;
use App\Notifications\SalesSummaryNotification;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

class SendScheduledReports extends Command
{
    protected $signature = 'reports:send-scheduled';
    protected $description = 'Envía los reportes de ventas programados que están pendientes';

    protected $reportService;

    public function __construct(ReportDataService $reportService)
    {
        parent::__construct();
        $this->reportService = $reportService;
    }

    public function handle()
    {
        $this->info('Iniciando envío de reportes programados...');

        $now = now();
        $pending = ReportSchedule::where('active', true)
            ->where('next_run_at', '<=', $now)
            ->get();

        if ($pending->isEmpty()) {
            $this->info('No hay reportes pendientes para enviar.');
            return;
        }

        foreach ($pending as $schedule) {
            $this->info("Procesando reporte ID: {$schedule->id} ({$schedule->method})");

            try {
                // Determinar el rango de fechas según la frecuencia
                $end = $now->format('Y-m-d');
                $start = $this->calculateStartDate($schedule, $now);

                $data = $this->reportService->getSalesSummary($start, $end);
                $notification = new SalesSummaryNotification($data, $schedule->sections);

                if ($schedule->method === 'email') {
                    foreach ($schedule->recipients as $email) {
                        Notification::route('mail', $email)->notify($notification);
                    }
                } elseif ($schedule->method === 'whatsapp') {
                    Notification::route('WhatsApp', $schedule->whatsapp_number)->notify($notification);
                }

                // Actualizar estado
                $schedule->last_run_at = $now;
                $schedule->calculateNextRun();

                $this->info("Reporte ID {$schedule->id} enviado correctamente.");
            } catch (\Exception $e) {
                $this->error("Error procesando reporte ID {$schedule->id}: " . $e->getMessage());
            }
        }

        $this->info('Proceso finalizado.');
    }

    /**
     * Calcula la fecha de inicio del reporte basada en la frecuencia
     */
    private function calculateStartDate($schedule, $now)
    {
        switch ($schedule->frequency) {
            case 'daily':
                return $now->copy()->subDay()->format('Y-m-d');
            case 'weekly':
                return $now->copy()->subWeek()->format('Y-m-d');
            case 'monthly':
                return $now->copy()->subMonth()->format('Y-m-d');
            case 'custom':
                $val = $schedule->custom_config['value'] ?? 1;
                $unit = $schedule->custom_config['unit'] ?? 'days';
                $date = $now->copy();
                if ($unit === 'days') $date->subDays($val);
                elseif ($unit === 'weeks') $date->subWeeks($val);
                elseif ($unit === 'months') $date->subMonths($val);
                return $date->format('Y-m-d');
            default:
                return $now->copy()->subDay()->format('Y-m-d');
        }
    }
}
