<?php

namespace App\Livewire\Admin\Domiciliarios;

use Livewire\Component;
use App\Models\PerfilDomiciliario;
use App\Models\LiquidacionDomiciliario;
use App\Models\ZonaCobertura;
use App\Mail\ComprobanteLiquidacion;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ManageDomiciliarios extends Component
{
    public string $activeTab = 'domiciliarios';

    // RF-135: Búsqueda por nombre o teléfono
    public string $busqueda = '';

    // RF-136: Filtro por estado
    public string $filtroEstado = '';

    // RF-137/138: Control del modal de liquidación
    public ?string $liquidandoId = null;
    public string $notasLiquidacion = '';
    public float $montoLiquidacion = 0.0;

    // Propiedades para Modal Eliminar
    public $showModalEliminarLivewire = false;
    public $domiciliario_eliminar_id;
    public $domiciliario_eliminar_nombre = '';

    public function mount()
    {
        if (!auth()->user()->sucursal_id) {
            return redirect()->route('sucursales');
        }
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    // RF-136: Actualizar filtro de estado
    public function setFiltroEstado(string $estado): void
    {
        $this->filtroEstado = $estado;
    }

    // RF-138: Abrir modal de confirmación de liquidación
    public function iniciarLiquidacion(string $domId): void
    {
        $dom = PerfilDomiciliario::find($domId);
        if ($dom && $dom->efectivo_pendiente > 0) {
            $this->liquidandoId = $domId;
            $this->montoLiquidacion = (float) $dom->efectivo_pendiente;
            $this->notasLiquidacion = '';
        }
    }

    // RF-138 + RF-139: Confirmar y ejecutar la liquidación
    public function confirmarLiquidacion(): void
    {
        if (!$this->liquidandoId) {
            return;
        }

        $dom = PerfilDomiciliario::with(['usuario', 'liquidaciones'])->find($this->liquidandoId);

        if (!$dom || $dom->efectivo_pendiente <= 0) {
            session()->flash('error', 'No hay efectivo pendiente para liquidar.');
            $this->cancelarLiquidacion();
            return;
        }

        // RF-138: Crear registro de liquidación
        $liquidacion = LiquidacionDomiciliario::create([
            'perfil_domiciliario_id' => $dom->id,
            'sucursal_id'            => $dom->sucursal_id,
            'aprobado_por'           => auth()->id(),
            'monto'                  => $dom->efectivo_pendiente,
            'estado'                 => 'completado',
            'notas'                  => $this->notasLiquidacion ?: null,
            'liquidado_en'           => now(),
        ]);

        // RF-138: Resetear efectivo_pendiente a 0
        $dom->update(['efectivo_pendiente' => 0]);

        // RF-139: Enviar comprobante por email
        try {
            $liquidacion->load(['perfil.usuario', 'aprobador']);
            $adminEmail = auth()->user()->correo;

            // Email al administrador
            Mail::to($adminEmail)->send(new ComprobanteLiquidacion($liquidacion));

            // Email al domiciliario (si tiene correo registrado)
            if ($dom->usuario && $dom->usuario->correo && $dom->usuario->correo !== $adminEmail) {
                Mail::to($dom->usuario->correo)->send(new ComprobanteLiquidacion($liquidacion));
            }
        } catch (\Throwable $e) {
            Log::error('Error enviando comprobante de liquidación: ' . $e->getMessage());
        }

        session()->flash('success', "Liquidación de $" . number_format($this->montoLiquidacion, 0, ',', '.') . " registrada. Comprobante enviado por email.");
        $this->cancelarLiquidacion();
    }

    // Cerrar modal sin ejecutar
    public function cancelarLiquidacion(): void
    {
        $this->liquidandoId = null;
        $this->notasLiquidacion = '';
        $this->montoLiquidacion = 0.0;
    }

    // Actualizar estado rápido desde el select de la tabla
    public function updateEstado($id, $estado)
    {
        $dom = PerfilDomiciliario::find($id);
        if ($dom) {
            $dom->update(['estado' => $estado]);
            session()->flash('success', 'Estado actualizado.');
        }
    }

    public function openEliminarModal($id)
    {
        $dom = PerfilDomiciliario::with('usuario')->find($id);
        if ($dom) {
            $this->domiciliario_eliminar_id = $dom->id;
            $this->domiciliario_eliminar_nombre = $dom->usuario ? $dom->usuario->nombre : 'Desconocido';
            $this->showModalEliminarLivewire = true;
        }
    }

    public function eliminarDomiciliario($id)
    {
        $dom = PerfilDomiciliario::find($id);
        if ($dom) {
            $usuario = $dom->usuario;
            $dom->delete();
            if ($usuario) {
                $usuario->delete();
            }
            session()->flash('success', 'Domiciliario y su cuenta de usuario eliminados correctamente.');
            $this->showModalEliminarLivewire = false;
        }
    }

    public function render()
    {
        $user = auth()->user();
        $sucursal_id = $user->sucursal_id;

        // RF-135: Búsqueda + RF-136: Filtro por estado
        $query = PerfilDomiciliario::with(['usuario:id,nombre,telefono,correo', 'zona:id,nombre', 'liquidaciones'])
            ->where('sucursal_id', $sucursal_id)
            ->when($this->filtroEstado !== '', fn($q) => $q->where('estado', $this->filtroEstado))
            ->when($this->busqueda !== '', function ($q) {
                $busq = '%' . $this->busqueda . '%';
                $q->whereHas('usuario', fn($u) =>
                    $u->where('nombre', 'like', $busq)
                      ->orWhere('telefono', 'like', $busq)
                );
            });

        $domiciliarios = $query->get();

        // RF-132: Estadísticas — 1 sola query agrupada en lugar de cargar toda la tabla de nuevo
        $statsRaw = PerfilDomiciliario::where('sucursal_id', $sucursal_id)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN estado = 'disponible' THEN 1 ELSE 0 END) as disponibles,
                SUM(CASE WHEN estado = 'en_ruta' THEN 1 ELSE 0 END) as en_ruta,
                SUM(CASE WHEN estado = 'ocupado' THEN 1 ELSE 0 END) as ocupados,
                SUM(CASE WHEN estado IN ('no_disponible', 'fuera_servicio') THEN 1 ELSE 0 END) as fuera_servicio
            ")
            ->first();

        $stats = [
            'total'          => $statsRaw->total ?? 0,
            'disponibles'    => $statsRaw->disponibles ?? 0,
            'en_ruta'        => $statsRaw->en_ruta ?? 0,
            'ocupados'       => $statsRaw->ocupados ?? 0,
            'fuera_servicio' => $statsRaw->fuera_servicio ?? 0,
        ];

        $zonas = ZonaCobertura::where('sucursal_id', $sucursal_id)
            ->where('activo', true)
            ->select('id', 'nombre')
            ->get();

        // Domiciliario que se está liquidando (para el modal)
        $liquidandoDom = $this->liquidandoId
            ? PerfilDomiciliario::with('usuario:id,nombre,correo')->find($this->liquidandoId)
            : null;

        $todasLiquidaciones = [];
        if ($this->activeTab === 'liquidaciones') {
            $todasLiquidaciones = LiquidacionDomiciliario::with(['perfil.usuario:id,nombre', 'aprobador:id,nombre'])
                ->where('sucursal_id', $sucursal_id)
                ->orderByDesc('liquidado_en')
                ->get();
        }

        return view('livewire.admin.domiciliarios.manage-domiciliarios', [
            'stats'              => $stats,
            'domiciliarios'      => $domiciliarios,
            'zonas'              => $zonas,
            'liquidandoDom'      => $liquidandoDom,
            'todasLiquidaciones' => $todasLiquidaciones,
        ])->layout('layouts.admin');
    }
}
