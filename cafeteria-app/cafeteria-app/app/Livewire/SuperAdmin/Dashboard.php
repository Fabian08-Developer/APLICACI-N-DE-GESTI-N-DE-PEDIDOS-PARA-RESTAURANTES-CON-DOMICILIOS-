<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\User;
use Livewire\Component;

class Dashboard extends Component
{
    // Modals toggles
    public $showVariablesModal = false;
    public $showAvisoModal = false;
    public $showVersionesModal = false;

    // Variables globales
    public $plataforma_nombre = '';
    public $soporte_correo = '';
    public $subida_limite = 10;
    public $registro_abierto = true;

    // Aviso masivo
    public $aviso_titulo = '';
    public $aviso_mensaje = '';
    public $aviso_activo = false;
    public $aviso_mostrar_banner = false;
    public $aviso_guardar_historial = false;

    // Gestión de versiones / mantenimiento
    public $version_actual = 'v1.2.0-stable';
    public $mantenimiento_fecha = 'No programado';
    public $version_nueva_numero = '';
    public $version_nueva_notas = '';
    public $versiones_lista = [];

    public function mount()
    {
        $this->loadSettings();
    }

    public function loadSettings()
    {
        $path = storage_path('app/global_settings.json');
        if (file_exists($path)) {
            $settings = json_decode(file_get_contents($path), true);
        } else {
            $settings = [];
        }

        $this->plataforma_nombre = $settings['plataforma_nombre'] ?? 'Panel de Control Global';
        $this->soporte_correo = $settings['soporte_correo'] ?? 'soporte@cafeteria.com';
        $this->subida_limite = $settings['subida_limite'] ?? 10;
        $this->registro_abierto = $settings['registro_abierto'] ?? true;

        $this->aviso_titulo = $settings['aviso_titulo'] ?? '';
        $this->aviso_mensaje = $settings['aviso_mensaje'] ?? '';
        $this->aviso_activo = $settings['aviso_activo'] ?? false;
        $this->aviso_mostrar_banner = $settings['aviso_mostrar_banner'] ?? false;
        $this->aviso_guardar_historial = $settings['aviso_guardar_historial'] ?? false;

        $this->version_actual = $settings['version_actual'] ?? 'v1.2.0-stable';
        $this->mantenimiento_fecha = $settings['mantenimiento_fecha'] ?? 'No programado';
        $this->versiones_lista = $settings['versiones_lista'] ?? [
            ['version' => 'v1.2.0-stable', 'fecha' => '2026-05-22', 'notas' => 'Lanzamiento estable con control de pedidos en tiempo real y soporte multi-tenant.'],
            ['version' => 'v1.1.0', 'fecha' => '2026-05-15', 'notas' => 'Añadido módulo de liquidación de domiciliarios y mejoras de rendimiento.'],
            ['version' => 'v1.0.0', 'fecha' => '2026-05-09', 'notas' => 'Versión inicial de la plataforma con soporte para menús, sedes y comandas.']
        ];
    }

    public function saveSettings()
    {
        $settings = [
            'plataforma_nombre' => $this->plataforma_nombre,
            'soporte_correo' => $this->soporte_correo,
            'subida_limite' => intval($this->subida_limite),
            'registro_abierto' => (bool)$this->registro_abierto,

            'aviso_titulo' => $this->aviso_titulo,
            'aviso_mensaje' => $this->aviso_mensaje,
            'aviso_activo' => (bool)$this->aviso_activo,
            'aviso_mostrar_banner' => (bool)$this->aviso_mostrar_banner,
            'aviso_guardar_historial' => (bool)$this->aviso_guardar_historial,

            'version_actual' => $this->version_actual,
            'mantenimiento_fecha' => $this->mantenimiento_fecha,
            'versiones_lista' => $this->versiones_lista,
        ];

        $path = storage_path('app/global_settings.json');
        
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($path, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function actualizarVariables()
    {
        $this->validate([
            'plataforma_nombre' => 'required|string|max:100',
            'soporte_correo' => 'required|email',
            'subida_limite' => 'required|integer|min:1|max:100',
        ]);

        $this->saveSettings();
        $this->showVariablesModal = false;

        $this->dispatch('swal', [
            'title' => '¡Configuración Guardada!',
            'text' => 'Las variables globales se han actualizado con éxito.',
            'icon' => 'success'
        ]);
    }

    public function publicarAviso()
    {
        $this->validate([
            'aviso_titulo' => 'required|string|max:100',
            'aviso_mensaje' => 'required|string|max:500',
        ]);

        $this->aviso_activo = true;
        $this->saveSettings();

        if ($this->aviso_guardar_historial) {
            $users = User::all();
            $now = now();
            $inserts = [];

            foreach ($users as $user) {
                $inserts[] = [
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'usuario_id' => $user->id,
                    'tipo' => 'aviso',
                    'titulo' => $this->aviso_titulo,
                    'mensaje' => $this->aviso_mensaje,
                    'datos' => json_encode(['autor' => 'Super Admin']),
                    'leida' => false,
                    'creado_en' => $now,
                    'actualizado_en' => $now,
                ];
            }

            foreach (array_chunk($inserts, 100) as $chunk) {
                \Illuminate\Support\Facades\DB::table('notificaciones')->insert($chunk);
            }
        }

        $this->showAvisoModal = false;

        $this->dispatch('swal', [
            'title' => '¡Aviso Publicado!',
            'text' => 'El aviso masivo ha sido activado en el sistema.',
            'icon' => 'success'
        ]);
    }

    public function desactivarAviso()
    {
        $this->aviso_activo = false;
        $this->saveSettings();
        $this->showAvisoModal = false;

        $this->dispatch('swal', [
            'title' => '¡Aviso Desactivado!',
            'text' => 'El aviso masivo ha sido desactivado del sistema.',
            'icon' => 'info'
        ]);
    }

    public function agregarVersion()
    {
        $this->validate([
            'version_nueva_numero' => 'required|string|max:20',
            'version_nueva_notas' => 'required|string|max:1000',
        ]);

        array_unshift($this->versiones_lista, [
            'version' => $this->version_nueva_numero,
            'fecha' => now()->format('Y-m-d'),
            'notas' => $this->version_nueva_notas,
        ]);

        $this->version_actual = $this->version_nueva_numero;
        $this->reset(['version_nueva_numero', 'version_nueva_notas']);
        $this->saveSettings();

        $this->dispatch('swal', [
            'title' => '¡Versión Registrada!',
            'text' => 'La versión ha sido añadida e implementada en el sistema.',
            'icon' => 'success'
        ]);
    }

    public function eliminarVersion($index)
    {
        if (isset($this->versiones_lista[$index])) {
            unset($this->versiones_lista[$index]);
            $this->versiones_lista = array_values($this->versiones_lista);
            
            if (count($this->versiones_lista) > 0) {
                $this->version_actual = $this->versiones_lista[0]['version'];
            } else {
                $this->version_actual = 'v1.0.0';
            }

            $this->saveSettings();

            $this->dispatch('swal', [
                'title' => 'Versión Eliminada',
                'text' => 'La versión seleccionada ha sido eliminada del historial.',
                'icon' => 'warning'
            ]);
        }
    }

    public function actualizarMantenimiento()
    {
        $this->saveSettings();
        $this->showVersionesModal = false;

        $this->dispatch('swal', [
            'title' => 'Mantenimiento Actualizado',
            'text' => 'La programación del mantenimiento de la plataforma ha sido actualizada.',
            'icon' => 'success'
        ]);
    }

    public function render()
    {
        return view('livewire.super-admin.dashboard', [
            'totalEmpresas' => Empresa::count(),
            'totalSucursales' => Sucursal::count(),
            'totalUsuarios' => User::count(),
            'empresasRecientes' => Empresa::latest()->take(5)->get(),
        ])->layout('layouts.app');
    }
}
