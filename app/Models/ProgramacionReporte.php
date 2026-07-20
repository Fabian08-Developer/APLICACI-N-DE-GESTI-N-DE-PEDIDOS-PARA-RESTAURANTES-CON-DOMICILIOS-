<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUuid;
use App\Traits\BelongsToSucursal;
use Carbon\Carbon;

class ProgramacionReporte extends Model
{
    use HasUuid, BelongsToSucursal;

    protected $table = 'programacion_reportes';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'sucursal_id',
        'activo',
        'frecuencia',
        'hora_envio',
        'dias',
        'dias_mes',
        'metodo',
        'destinatarios',
        'numero_whatsapp',
        'ultimo_envio_en',
        'proximo_envio_en',
    ];

    protected $casts = [
        'activo'          => 'boolean',
        'dias'            => 'array',
        'dias_mes'        => 'array',
        'destinatarios'   => 'array',
        'ultimo_envio_en' => 'datetime',
        'proximo_envio_en'=> 'datetime',
    ];

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    /**
     * Calcula el próximo timestamp de envío basado en la frecuencia configurada.
     * Si la hora de hoy ya pasó, avanza al siguiente ciclo.
     */
    public function calcularProximoEnvio(): Carbon
    {
        [$hour, $minute] = explode(':', substr($this->hora_envio, 0, 5));
        $tz = config('app.timezone', 'America/Bogota');
        $now = now($tz);

        switch ($this->frecuencia) {
            case 'daily':
                $candidate = $now->copy()->setTime((int)$hour, (int)$minute, 0);
                if ($candidate->lte($now)) {
                    $candidate->addDay();
                }
                return $candidate;

            case 'weekly':
                $dias = $this->dias ?? ['L'];
                $mapDias = ['L' => 1, 'M' => 2, 'X' => 3, 'J' => 4, 'V' => 5, 'S' => 6, 'D' => 7];
                $dayNumbers = array_map(fn($d) => $mapDias[$d] ?? 1, $dias);
                sort($dayNumbers);

                // Buscar el próximo día de la semana disponible
                for ($i = 0; $i <= 7; $i++) {
                    $candidate = $now->copy()->addDays($i)->setTime((int)$hour, (int)$minute, 0);
                    if (in_array($candidate->isoWeekday(), $dayNumbers) && $candidate->gt($now)) {
                        return $candidate;
                    }
                }
                return $now->copy()->addDay()->setTime((int)$hour, (int)$minute, 0);

            case 'monthly':
                $diasMes = $this->dias_mes ?? [1];
                sort($diasMes);

                foreach ($diasMes as $day) {
                    $candidate = $now->copy()->day($day)->setTime((int)$hour, (int)$minute, 0);
                    if ($candidate->day !== $day) {
                        continue; // Mes no tiene ese día (ej. 31 en febrero)
                    }
                    if ($candidate->gt($now)) {
                        return $candidate;
                    }
                }
                // Si ya pasaron todos los días de este mes, ir al primer día del mes siguiente
                $nextMonth = $now->copy()->addMonthNoOverflow()->startOfMonth();
                $firstDay = $diasMes[0] ?? 1;
                return $nextMonth->day($firstDay)->setTime((int)$hour, (int)$minute, 0);

            default:
                return $now->copy()->addDay()->setTime((int)$hour, (int)$minute, 0);
        }
    }

    /**
     * Devuelve texto legible de la frecuencia para mostrar en la UI.
     */
    public function getFrecuenciaTextoAttribute(): string
    {
        return match ($this->frecuencia) {
            'daily'   => 'Diario',
            'weekly'  => 'Semanal (' . implode(',', $this->dias ?? []) . ')',
            'monthly' => 'Mensual (días ' . implode(',', $this->dias_mes ?? []) . ')',
            default   => ucfirst($this->frecuencia),
        };
    }
}
