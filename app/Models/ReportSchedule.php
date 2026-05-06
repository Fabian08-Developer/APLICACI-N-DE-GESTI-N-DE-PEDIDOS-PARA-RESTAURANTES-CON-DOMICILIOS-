<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportSchedule extends Model
{
    protected $fillable = [
        'active', 'frequency', 'time', 'days', 'month_days', 
        'custom_config', 'method', 'recipients', 
        'whatsapp_number', 'sections', 'last_run_at', 'next_run_at'
    ];

    protected $casts = [
        'active' => 'boolean',
        'days' => 'array',
        'month_days' => 'array',
        'custom_config' => 'array',
        'recipients' => 'array',
        'sections' => 'array',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
    ];

    /**
     * Calcula la próxima fecha de ejecución basada en la frecuencia
     */
    public function calculateNextRun()
    {
        $now = now();
        $scheduledTime = $this->time; // HH:mm:ss
        
        // Base: hoy a la hora programada
        $next = \Carbon\Carbon::parse($now->format('Y-m-d') . ' ' . $scheduledTime);

        // Si ya pasó la hora hoy, empezamos a buscar desde mañana
        if ($next->isPast()) {
            $next->addDay();
        }

        switch ($this->frequency) {
            case 'daily':
                // Ya tenemos la fecha correcta (hoy o mañana)
                break;

            case 'weekly':
                // Buscar el próximo día de la semana permitido
                // Mapeo de L, M, X, J, V, S, D a 1-7 (Carbon)
                $map = ['L' => 1, 'M' => 2, 'X' => 3, 'J' => 4, 'V' => 5, 'S' => 6, 'D' => 7];
                $allowedDays = collect($this->days)->map(fn($d) => $map[$d] ?? null)->filter()->values()->toArray();
                
                if (!empty($allowedDays)) {
                    while (!in_array($next->dayOfWeekIso, $allowedDays)) {
                        $next->addDay();
                    }
                }
                break;

            case 'monthly':
                $allowedMonthDays = $this->month_days ?: [1];
                while (!in_array($next->day, $allowedMonthDays)) {
                    $next->addDay();
                }
                break;

            case 'custom':
                $val = $this->custom_config['value'] ?? 1;
                $unit = $this->custom_config['unit'] ?? 'days';
                // Para simplificar el custom, si es la primera vez usamos 'start', 
                // si no, sumamos el intervalo al last_run_at
                if ($this->last_run_at) {
                    $next = $this->last_run_at->copy();
                    if ($unit === 'days') $next->addDays($val);
                    elseif ($unit === 'weeks') $next->addWeeks($val);
                    elseif ($unit === 'months') $next->addMonths($val);
                } else {
                    $start = \Carbon\Carbon::parse($this->custom_config['start'] ?? now());
                    $next = $start->setTimeFromTimeString($scheduledTime);
                    if ($next->isPast()) $next->addDays($val);
                }
                break;
        }

        $this->next_run_at = $next;
        $this->save();
    }
}
