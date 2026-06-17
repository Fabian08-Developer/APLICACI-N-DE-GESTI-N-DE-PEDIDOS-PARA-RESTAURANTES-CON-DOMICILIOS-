<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Empresa;

class MiPagina extends Component
{
    use WithFileUploads;

    public string $activeTab = 'apariencia';

    // ── Apariencia ──────────────────────────────────────────────
    public string $color_primario   = '#e63946';
    public string $color_secundario = '#1d3557';
    public string $titulo_tienda    = '';
    public string $descripcion      = '';
    public bool   $mostrar_mapa       = true;
    public bool   $mostrar_sucursales = true;

    // ── Multimedia ──────────────────────────────────────────────
    public $logo_upload   = null; // archivo nuevo
    public $banner_upload = null; // archivo nuevo
    public ?string $logo_url_actual   = null;
    public ?string $banner_url_actual = null;

    // ── Redes Sociales ──────────────────────────────────────────
    public string $whatsapp  = '';
    public string $instagram = '';
    public string $facebook  = '';
    public string $tiktok    = '';

    // ── Computed ────────────────────────────────────────────────
    public string $url_publica = '';

    public function mount(): void
    {
        $user    = Auth::user();
        $empresa = $user->empresa;

        if (!$empresa) {
            return;
        }

        $a = $empresa->apariencia ?? [];

        $this->color_primario     = $a['color_primario']     ?? '#e63946';
        $this->color_secundario   = $a['color_secundario']   ?? '#1d3557';
        $this->titulo_tienda      = $a['titulo_tienda']      ?? '¡Bienvenido a ' . $empresa->nombre . '!';
        $this->descripcion        = $a['descripcion']        ?? 'Selecciona una sede y realiza tu pedido a domicilio.';
        $this->whatsapp           = $a['whatsapp']           ?? '';
        $this->instagram          = $a['instagram']          ?? '';
        $this->facebook           = $a['facebook']           ?? '';
        $this->tiktok             = $a['tiktok']             ?? '';
        $this->mostrar_mapa       = (bool) ($a['mostrar_mapa']       ?? true);
        $this->mostrar_sucursales = (bool) ($a['mostrar_sucursales'] ?? true);
        $this->logo_url_actual    = $a['logo_url']           ?? null;
        $this->banner_url_actual  = $a['banner_url']         ?? null;
        $this->url_publica        = url('/' . $empresa->slug);
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function guardarApariencia(): void
    {
        $this->validate([
            'color_primario'   => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'color_secundario' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'titulo_tienda'    => 'nullable|string|max:120',
            'descripcion'      => 'nullable|string|max:400',
        ], [
            'color_primario.regex'   => 'El color primario debe ser un código hexadecimal válido (#RRGGBB).',
            'color_secundario.regex' => 'El color secundario debe ser un código hexadecimal válido (#RRGGBB).',
        ]);

        $this->persistirApariencia();

        $this->dispatch('swal', [
            'title' => '¡Apariencia guardada!',
            'text'  => 'Los cambios de tu tienda se han aplicado correctamente.',
            'icon'  => 'success',
        ]);
    }

    public function guardarMultimedia(): void
    {
        $this->validate([
            'logo_upload'   => 'nullable|image|max:2048',
            'banner_upload' => 'nullable|image|max:5120',
        ], [
            'logo_upload.max'   => 'El logo no debe superar 2 MB.',
            'banner_upload.max' => 'El banner no debe superar 5 MB.',
        ]);

        $empresa = Auth::user()->empresa;
        $a       = $empresa->apariencia ?? [];

        if ($this->logo_upload) {
            if ($this->logo_url_actual) {
                Storage::disk('public')->delete($this->logo_url_actual);
            }
            $path = $this->logo_upload->store('empresa/logos', 'public');
            $a['logo_url']        = $path;
            $this->logo_url_actual = $path;
            $this->logo_upload     = null;
        }

        if ($this->banner_upload) {
            if ($this->banner_url_actual) {
                Storage::disk('public')->delete($this->banner_url_actual);
            }
            $path = $this->banner_upload->store('empresa/banners', 'public');
            $a['banner_url']        = $path;
            $this->banner_url_actual = $path;
            $this->banner_upload     = null;
        }

        $empresa->update(['apariencia' => $a]);

        $this->dispatch('swal', [
            'title' => '¡Imágenes actualizadas!',
            'text'  => 'El logo y banner de tu tienda han sido guardados.',
            'icon'  => 'success',
        ]);
    }

    public function guardarRedes(): void
    {
        $this->validate([
            'whatsapp'  => 'nullable|string|max:20',
            'instagram' => 'nullable|url|max:255',
            'facebook'  => 'nullable|url|max:255',
            'tiktok'    => 'nullable|url|max:255',
        ], [
            'instagram.url' => 'El enlace de Instagram debe ser una URL válida.',
            'facebook.url'  => 'El enlace de Facebook debe ser una URL válida.',
            'tiktok.url'    => 'El enlace de TikTok debe ser una URL válida.',
        ]);

        $this->persistirApariencia();

        $this->dispatch('swal', [
            'title' => '¡Redes sociales guardadas!',
            'text'  => 'Tus redes sociales ahora aparecen en tu tienda.',
            'icon'  => 'success',
        ]);
    }

    protected function persistirApariencia(): void
    {
        $empresa = Auth::user()->empresa;
        $a = $empresa->apariencia ?? [];

        $empresa->update([
            'apariencia' => array_merge($a, [
                'color_primario'     => $this->color_primario,
                'color_secundario'   => $this->color_secundario,
                'titulo_tienda'      => $this->titulo_tienda,
                'descripcion'        => $this->descripcion,
                'mostrar_mapa'       => $this->mostrar_mapa,
                'mostrar_sucursales' => $this->mostrar_sucursales,
                'whatsapp'           => $this->whatsapp,
                'instagram'          => $this->instagram,
                'facebook'           => $this->facebook,
                'tiktok'             => $this->tiktok,
            ]),
        ]);
    }

    public function eliminarLogo(): void
    {
        $empresa = Auth::user()->empresa;
        if ($this->logo_url_actual) {
            Storage::disk('public')->delete($this->logo_url_actual);
            $a = $empresa->apariencia ?? [];
            unset($a['logo_url']);
            $empresa->update(['apariencia' => $a]);
            $this->logo_url_actual = null;
        }
    }

    public function eliminarBanner(): void
    {
        $empresa = Auth::user()->empresa;
        if ($this->banner_url_actual) {
            Storage::disk('public')->delete($this->banner_url_actual);
            $a = $empresa->apariencia ?? [];
            unset($a['banner_url']);
            $empresa->update(['apariencia' => $a]);
            $this->banner_url_actual = null;
        }
    }

    public function render()
    {
        return view('livewire.admin.mi-pagina')->layout('layouts.app');
    }
}
