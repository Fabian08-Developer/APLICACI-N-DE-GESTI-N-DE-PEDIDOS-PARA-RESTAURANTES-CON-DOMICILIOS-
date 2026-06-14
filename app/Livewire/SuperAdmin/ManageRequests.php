<?php

namespace App\Livewire\SuperAdmin;

use App\Models\User;
use App\Models\Empresa;
use App\Notifications\CuentaGerenteAprobada;
use App\Notifications\SolicitudRechazada;
use App\Notifications\SolicitudNitAprobada;
use App\Notifications\SolicitudNitRechazada;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;

class ManageRequests extends Component
{
    use WithPagination;

    // Reject Modal Properties
    public $showRejectModal = false;
    public $selectedEmpresaId = null;
    public $motivoRechazo = '';

    public function approve($userId)
    {
        $user = User::findOrFail($userId);
        $empresa = $user->empresa;

        $user->activo = true;
        $user->save();

        if ($empresa) {
            $empresa->activo = true;
            $empresa->save();
        }

        \Illuminate\Support\Facades\Log::info("Aprobando cuenta y enviando correo a: " . $user->correo);
        
        try {
            $user->notify(new CuentaGerenteAprobada($user));
            \Illuminate\Support\Facades\Log::info("Notificación de aprobación enviada exitosamente.");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error al enviar notificación de aprobación: " . $e->getMessage());
        }

        $this->dispatch('swal', [
            'title' => 'Aprobado',
            'text' => 'La cuenta ha sido activada y el gerente ha sido notificado.',
            'icon' => 'success'
        ]);
    }

    public function reject($userId)
    {
        $user = User::findOrFail($userId);
        $empresa = $user->empresa;

        \Illuminate\Support\Facades\Log::info("Rechazando cuenta y enviando correo a: " . $user->correo);

        try {
            // Notificar rechazo antes de eliminar
            $user->notify(new SolicitudRechazada($user->nombre));
            \Illuminate\Support\Facades\Log::info("Notificación de rechazo enviada exitosamente.");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error al enviar notificación de rechazo: " . $e->getMessage());
        }
        
        if ($empresa) {
            // Eliminar documento si existe
            if ($empresa->documento_path) {
                Storage::disk('public')->delete($empresa->documento_path);
            }
            $empresa->delete();
        }

        $user->delete();

        $this->dispatch('swal', [
            'title' => 'Rechazado',
            'text' => 'La solicitud ha sido rechazada y eliminada.',
            'icon' => 'warning'
        ]);
    }

    public function downloadDoc($empresaId)
    {
        $empresa = Empresa::findOrFail($empresaId);
        if ($empresa->documento_path) {
            return Storage::disk('public')->download($empresa->documento_path);
        }
    }

    // Pending NIT Download
    public function downloadPendingDoc($empresaId)
    {
        $empresa = Empresa::findOrFail($empresaId);
        if ($empresa->documento_pendiente_path) {
            return Storage::disk('public')->download($empresa->documento_pendiente_path);
        }
    }

    // Approve NIT Update
    public function approveNitUpdate($empresaId)
    {
        $empresa = Empresa::findOrFail($empresaId);
        
        if ($empresa->documento_pendiente_path) {
            // Delete current active document if exists
            if ($empresa->documento_path) {
                Storage::disk('public')->delete($empresa->documento_path);
            }

            // Move pending to active
            $empresa->documento_path = $empresa->documento_pendiente_path;
            $empresa->documento_pendiente_path = null;
            $empresa->save();

            // Notify manager
            $manager = $empresa->usuarios()->where('rol', 'gerente')->first();
            if ($manager) {
                try {
                    $manager->notify(new SolicitudNitAprobada($manager->nombre, $empresa->nombre));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Error enviando notificación aprobación NIT: " . $e->getMessage());
                }
            }

            $this->dispatch('swal', [
                'title' => '¡NIT Actualizado!',
                'text' => 'La solicitud ha sido aprobada y el nuevo NIT se encuentra activo.',
                'icon' => 'success'
            ]);
        }
    }

    // Open/Close Reject Modal
    public function openRejectModal($empresaId)
    {
        $this->selectedEmpresaId = $empresaId;
        $this->motivoRechazo = '';
        $this->showRejectModal = true;
    }

    public function closeRejectModal()
    {
        $this->selectedEmpresaId = null;
        $this->motivoRechazo = '';
        $this->showRejectModal = false;
    }

    // Reject NIT Update
    public function rejectNitUpdate()
    {
        $this->validate([
            'motivoRechazo' => 'required|string|min:8|max:500'
        ], [
            'motivoRechazo.required' => 'El motivo de rechazo es obligatorio.',
            'motivoRechazo.min' => 'El motivo debe contener al menos 8 caracteres.',
            'motivoRechazo.max' => 'El motivo no puede exceder los 500 caracteres.'
        ]);

        $empresa = Empresa::findOrFail($this->selectedEmpresaId);

        if ($empresa->documento_pendiente_path) {
            // Delete pending file
            Storage::disk('public')->delete($empresa->documento_pendiente_path);

            $empresa->documento_pendiente_path = null;
            $empresa->save();

            // Notify manager with the reason
            $manager = $empresa->usuarios()->where('rol', 'gerente')->first();
            if ($manager) {
                try {
                    $manager->notify(new SolicitudNitRechazada($manager->nombre, $empresa->nombre, $this->motivoRechazo));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Error enviando notificación rechazo NIT: " . $e->getMessage());
                }
            }

            $this->closeRejectModal();

            $this->dispatch('swal', [
                'title' => 'Solicitud Rechazada',
                'text' => 'La solicitud de actualización de NIT ha sido rechazada y se envió el correo con el motivo al gerente.',
                'icon' => 'warning'
            ]);
        }
    }

    public function render()
    {
        $requests = User::where('rol', 'gerente')
            ->where('activo', false)
            ->with('empresa')
            ->paginate(10, ['*'], 'requests_page');

        $nitRequests = Empresa::whereNotNull('documento_pendiente_path')
            ->with(['usuarios' => function($query) {
                $query->where('rol', 'gerente');
            }])
            ->paginate(10, ['*'], 'nit_page');

        return view('livewire.super-admin.manage-requests', [
            'requests' => $requests,
            'nitRequests' => $nitRequests
        ])->layout('layouts.app');
    }
}
