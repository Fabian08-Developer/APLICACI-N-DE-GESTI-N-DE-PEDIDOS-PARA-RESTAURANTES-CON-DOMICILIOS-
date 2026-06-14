<?php

namespace App\Livewire\Shared;

use Livewire\Component;
use App\Models\Notificacion;

/**
 * Componente de campanilla de notificaciones.
 *
 * Se integra en layouts/admin.blade.php.
 * Escucha el evento Livewire 'nuevaNotificacion' que dispara el JS de Echo
 * cuando llega un broadcast de Reverb al canal privado de la sucursal.
 */
class CampanillaNotificaciones extends Component
{
    public array $notificaciones = [];
    public int   $noLeidas       = 0;
    public bool  $panelAbierto   = false;

    // Recibe el evento desde el layout JS → Livewire
    protected $listeners = ['nuevaNotificacion' => 'recargar'];

    public function mount(): void
    {
        $this->cargar();
    }

    private function cargar(): void
    {
        if (!auth()->check()) {
            return;
        }

        $items = Notificacion::where('usuario_id', auth()->id())
            ->latest('creado_en')
            ->take(15)
            ->get();

        $this->notificaciones = $items->map(fn ($n) => [
            'id'        => $n->id,
            'tipo'      => $n->tipo,
            'titulo'    => $n->titulo,
            'mensaje'   => $n->mensaje,
            'leida'     => $n->leida,
            'hace'      => $n->creado_en?->diffForHumans() ?? 'ahora',
        ])->toArray();

        $this->noLeidas = $items->where('leida', false)->count();
    }

    public function recargar(): void
    {
        $this->cargar();
    }

    public function togglePanel(): void
    {
        $this->panelAbierto = !$this->panelAbierto;
        if ($this->panelAbierto) {
            $this->cargar();
        }
    }

    public function marcarLeida(string $id): void
    {
        Notificacion::where('id', $id)
            ->where('usuario_id', auth()->id())
            ->update(['leida' => true]);

        $this->cargar();
    }

    public function marcarTodas(): void
    {
        Notificacion::where('usuario_id', auth()->id())
            ->where('leida', false)
            ->update(['leida' => true]);

        $this->cargar();
    }

    public function render()
    {
        return view('livewire.shared.campanilla-notificaciones');
    }
}
