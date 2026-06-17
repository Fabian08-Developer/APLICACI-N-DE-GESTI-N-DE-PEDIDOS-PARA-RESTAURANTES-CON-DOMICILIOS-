<div class="mi-pagina-wrapper">

    {{-- ── Encabezado ──────────────────────────────────────────── --}}
    <div class="mp-header">
        <div class="mp-header-info">
            <h1 class="mp-title">
                <svg width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                Mi Página
            </h1>
            <p class="mp-subtitle">Personaliza la tienda online que tus clientes ven cuando escanean el QR o buscan tu restaurante.</p>
        </div>

        {{-- Link público de la tienda --}}
        <div class="mp-link-card">
            <div class="mp-link-label">
                <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a10 10 0 100 20A10 10 0 0012 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/></svg>
                Tu tienda pública
            </div>
            <div class="mp-link-url" id="url-tienda">{{ $url_publica }}</div>
            <div class="mp-link-actions">
                <button onclick="copiarUrl()" class="btn-link-copy">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/>
                    </svg>
                    Copiar
                </button>
                <a href="{{ $url_publica }}" target="_blank" class="btn-link-open">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>
                    </svg>
                    Ver Tienda
                </a>
            </div>
        </div>
    </div>

    {{-- ── Tabs ─────────────────────────────────────────────────── --}}
    <div class="mp-tabs">
        <button wire:click="setTab('apariencia')"
                class="mp-tab {{ $activeTab === 'apariencia' ? 'active' : '' }}">
            🎨 Apariencia
        </button>
        <button wire:click="setTab('multimedia')"
                class="mp-tab {{ $activeTab === 'multimedia' ? 'active' : '' }}">
            🖼 Multimedia
        </button>
        <button wire:click="setTab('redes')"
                class="mp-tab {{ $activeTab === 'redes' ? 'active' : '' }}">
            📣 Redes Sociales
        </button>
    </div>

    {{-- ── PANEL: Apariencia ────────────────────────────────────── --}}
    @if($activeTab === 'apariencia')
    <form wire:submit.prevent="guardarApariencia" class="mp-panel">
        <div class="mp-panel-grid">

            {{-- Izquierda: Formulario --}}
            <div class="mp-form-col">
                <div class="mp-section-title">Colores de tu Marca</div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label class="form-label">Color Primario</label>
                        <div class="color-input-wrap">
                            <input type="color" wire:model.live="color_primario" class="color-picker">
                            <input type="text" wire:model.live="color_primario" class="form-control color-hex"
                                   placeholder="#e63946" pattern="^#[0-9A-Fa-f]{6}$">
                        </div>
                        @error('color_primario') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Color Secundario</label>
                        <div class="color-input-wrap">
                            <input type="color" wire:model.live="color_secundario" class="color-picker">
                            <input type="text" wire:model.live="color_secundario" class="form-control color-hex"
                                   placeholder="#1d3557" pattern="^#[0-9A-Fa-f]{6}$">
                        </div>
                        @error('color_secundario') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mp-section-title" style="margin-top:2rem">Contenido de la Tienda</div>

                <div class="form-group">
                    <label class="form-label">Título Principal</label>
                    <input type="text" wire:model="titulo_tienda" class="form-control"
                           placeholder="Ej: ¡El mejor sabor de la ciudad!">
                    @error('titulo_tienda') <span class="form-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Descripción / Bienvenida</label>
                    <textarea wire:model="descripcion" class="form-control" rows="3"
                              placeholder="Una breve descripción de tu restaurante para tus clientes..."></textarea>
                    @error('descripcion') <span class="form-error">{{ $message }}</span> @enderror
                </div>

                <div class="mp-section-title" style="margin-top:2rem">Opciones de Visibilidad</div>

                <div class="toggle-row">
                    <div class="toggle-info">
                        <span class="toggle-label">Mostrar mapa de sedes</span>
                        <span class="toggle-desc">Muestra un mapa interactivo con la ubicación de tus sucursales.</span>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" wire:model="mostrar_mapa">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                <div class="toggle-row">
                    <div class="toggle-info">
                        <span class="toggle-label">Mostrar listado de sedes</span>
                        <span class="toggle-desc">Muestra las cards de cada sucursal con el botón de domicilio.</span>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" wire:model="mostrar_sucursales">
                        <span class="toggle-slider"></span>
                    </label>
                </div>

                <button type="submit" class="btn-save" wire:loading.attr="disabled">
                    <span wire:loading.remove>
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
                        </svg>
                        Guardar Cambios
                    </span>
                    <span wire:loading>Guardando...</span>
                </button>
            </div>

            {{-- Derecha: Preview en vivo --}}
            <div class="mp-preview-col">
                <div class="mp-section-title">Vista Previa</div>
                <div class="preview-card" id="preview-card"
                     style="--pp: {{ $color_primario }}; --ps: {{ $color_secundario }}">
                    <div class="preview-hero" style="background: linear-gradient(135deg, {{ $color_secundario }}, #0f0f13)">
                        <div class="preview-logo-ph" style="background: linear-gradient(135deg, {{ $color_primario }}, {{ $color_secundario }})">
                            @if($logo_url_actual)
                                <img src="{{ asset('storage/'.$logo_url_actual) }}" style="width:100%;height:100%;object-fit:cover;border-radius:50%">
                            @else
                                {{ strtoupper(substr(auth()->user()->empresa?->nombre ?? 'E', 0, 1)) }}
                            @endif
                        </div>
                        <div class="preview-title" style="background: linear-gradient(135deg, #fff 40%, {{ $color_primario }}); -webkit-background-clip:text; -webkit-text-fill-color:transparent">
                            {{ Str::limit($titulo_tienda ?: '¡Bienvenido!', 32) }}
                        </div>
                        <div class="preview-desc">{{ Str::limit($descripcion ?: 'Tu descripción aquí', 70) }}</div>
                    </div>
                    <div class="preview-card-body">
                        <div class="preview-fake-card" style="border-top: 2px solid {{ $color_primario }}20">
                            <div class="preview-card-name" style="color:#fff">Sede Centro</div>
                            <div class="preview-fake-btn" style="background: linear-gradient(135deg, {{ $color_primario }}, {{ $color_secundario }})">
                                Pedir a Domicilio →
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    @endif

    {{-- ── PANEL: Multimedia ────────────────────────────────────── --}}
    @if($activeTab === 'multimedia')
    <form wire:submit.prevent="guardarMultimedia" class="mp-panel">
        <div class="media-grid">

            {{-- Logo --}}
            <div class="media-card">
                <div class="mp-section-title">Logo de la Empresa</div>
                <div class="mp-section-desc">Se muestra en el círculo central de tu tienda. Recomendado: 400×400 px, formato PNG.</div>
                @if($logo_upload)
                    <div class="media-preview">
                        <img src="{{ $logo_upload->temporaryUrl() }}" alt="Preview logo">
                        <div class="media-preview-label">Nuevo logo (sin guardar)</div>
                    </div>
                @elseif($logo_url_actual)
                    <div class="media-preview">
                        <img src="{{ asset('storage/'.$logo_url_actual) }}" alt="Logo actual">
                        <div class="media-preview-label">Logo actual</div>
                        <button type="button" wire:click="eliminarLogo" class="btn-remove-media">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6m4-6v6"/><path d="M9 6V4h6v2"/>
                            </svg>
                            Eliminar logo
                        </button>
                    </div>
                @else
                    <div class="media-empty">
                        <svg width="40" height="40" fill="none" stroke="rgba(255,255,255,.25)" stroke-width="1.5" viewBox="0 0 24 24">
                            <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                        </svg>
                        <span>Sin logo configurado</span>
                    </div>
                @endif
                <label class="btn-upload-area">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                    Subir Logo
                    <input type="file" wire:model="logo_upload" accept="image/*" class="hidden-input">
                </label>
                @error('logo_upload') <span class="form-error">{{ $message }}</span> @enderror
                <div wire:loading wire:target="logo_upload" class="upload-progress">Procesando imagen...</div>
            </div>

            {{-- Banner --}}
            <div class="media-card">
                <div class="mp-section-title">Banner / Portada</div>
                <div class="mp-section-desc">Imagen de fondo de la sección hero. Recomendado: 1920×600 px, JPG/PNG.</div>
                @if($banner_upload)
                    <div class="media-preview media-preview--wide">
                        <img src="{{ $banner_upload->temporaryUrl() }}" alt="Preview banner">
                        <div class="media-preview-label">Nuevo banner (sin guardar)</div>
                    </div>
                @elseif($banner_url_actual)
                    <div class="media-preview media-preview--wide">
                        <img src="{{ asset('storage/'.$banner_url_actual) }}" alt="Banner actual">
                        <div class="media-preview-label">Banner actual</div>
                        <button type="button" wire:click="eliminarBanner" class="btn-remove-media">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6m4-6v6"/><path d="M9 6V4h6v2"/>
                            </svg>
                            Eliminar banner
                        </button>
                    </div>
                @else
                    <div class="media-empty media-empty--wide">
                        <svg width="40" height="40" fill="none" stroke="rgba(255,255,255,.25)" stroke-width="1.5" viewBox="0 0 24 24">
                            <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                        </svg>
                        <span>Sin banner configurado · Se usará el color de tu marca</span>
                    </div>
                @endif
                <label class="btn-upload-area">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                    Subir Banner
                    <input type="file" wire:model="banner_upload" accept="image/*" class="hidden-input">
                </label>
                @error('banner_upload') <span class="form-error">{{ $message }}</span> @enderror
                <div wire:loading wire:target="banner_upload" class="upload-progress">Procesando imagen...</div>
            </div>
        </div>

        <button type="submit" class="btn-save" wire:loading.attr="disabled">
            <span wire:loading.remove>
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
                </svg>
                Guardar Imágenes
            </span>
            <span wire:loading>Subiendo...</span>
        </button>
    </form>
    @endif

    {{-- ── PANEL: Redes Sociales ────────────────────────────────── --}}
    @if($activeTab === 'redes')
    <form wire:submit.prevent="guardarRedes" class="mp-panel">
        <div class="mp-section-title">Redes Sociales y Contacto</div>
        <div class="mp-section-desc" style="margin-bottom: 2rem">Estos datos aparecen en el footer de tu tienda y activan el botón flotante de WhatsApp.</div>

        <div class="redes-form-grid">
            <div class="form-group red-group">
                <label class="form-label red-label">
                    <span class="red-icon wa-bg">
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    </span>
                    WhatsApp
                </label>
                <input type="text" wire:model="whatsapp" class="form-control"
                       placeholder="+573001234567 (solo número con indicativo)">
                <div class="form-hint">Incluye el código de país sin espacios. Ej: +573001234567</div>
                @error('whatsapp') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group red-group">
                <label class="form-label red-label">
                    <span class="red-icon ig-bg">
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                    </span>
                    Instagram
                </label>
                <input type="url" wire:model="instagram" class="form-control"
                       placeholder="https://instagram.com/mi-restaurante">
                @error('instagram') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group red-group">
                <label class="form-label red-label">
                    <span class="red-icon fb-bg">
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </span>
                    Facebook
                </label>
                <input type="url" wire:model="facebook" class="form-control"
                       placeholder="https://facebook.com/mi-restaurante">
                @error('facebook') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group red-group">
                <label class="form-label red-label">
                    <span class="red-icon tk-bg">
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.26 8.26 0 004.83 1.54V6.79a4.85 4.85 0 01-1.06-.1z"/></svg>
                    </span>
                    TikTok
                </label>
                <input type="url" wire:model="tiktok" class="form-control"
                       placeholder="https://tiktok.com/@mi-restaurante">
                @error('tiktok') <span class="form-error">{{ $message }}</span> @enderror
            </div>
        </div>

        <button type="submit" class="btn-save" wire:loading.attr="disabled">
            <span wire:loading.remove>
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
                </svg>
                Guardar Redes
            </span>
            <span wire:loading>Guardando...</span>
        </button>
    </form>
    @endif
</div>

<style>
/* ── LAYOUT ───────────────────────────────────────────────────── */
.mi-pagina-wrapper { max-width: 1100px; margin: 0 auto; padding: 2rem 1.5rem; }

/* ── HEADER ───────────────────────────────────────────────────── */
.mp-header { display: flex; gap: 2rem; align-items: flex-start; margin-bottom: 2.5rem; flex-wrap: wrap; }
.mp-header-info { flex: 1; min-width: 260px; }
.mp-title { font-size: 1.6rem; font-weight: 800; color: #2C241B; display: flex; align-items: center; gap: .65rem; margin-bottom: .4rem; }
.mp-subtitle { color: #5C5246; font-size: .92rem; line-height: 1.5; }

/* Link card */
.mp-link-card { background: #FFFFFF; border: 1px solid rgba(44,36,27,0.1); border-radius: 16px; padding: 1.25rem 1.5rem; min-width: 300px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
.mp-link-label { font-size: .75rem; font-weight: 600; color: #8B8175; text-transform: uppercase; letter-spacing: .05em; margin-bottom: .5rem; display: flex; align-items: center; gap: .4rem; }
.mp-link-url { font-family: monospace; font-size: .92rem; color: #E07A5F; margin-bottom: .85rem; word-break: break-all; }
.mp-link-actions { display: flex; gap: .6rem; }
.btn-link-copy, .btn-link-open {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .45rem 1rem; border-radius: 8px; font-size: .82rem; font-weight: 600;
    cursor: pointer; border: none; text-decoration: none; transition: all .15s;
}
.btn-link-copy { background: rgba(44,36,27,0.05); color: #5C5246; }
.btn-link-open { background: #E07A5F; color: #fff; }
.btn-link-copy:hover { background: rgba(44,36,27,0.1); color: #2C241B; }
.btn-link-open:hover { background: #d06a4f; }

/* ── TABS ─────────────────────────────────────────────────────── */
.mp-tabs { display: flex; gap: .5rem; margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 1px solid rgba(44,36,27,0.1); flex-wrap: wrap; }
.mp-tab { padding: .6rem 1.25rem; border-radius: 10px; border: 1px solid transparent; background: transparent; color: #5C5246; font-size: .9rem; font-weight: 600; cursor: pointer; transition: all .2s; }
.mp-tab:hover { background: rgba(44,36,27,0.05); color: #2C241B; }
.mp-tab.active { background: rgba(224,122,95,0.1); border-color: rgba(224,122,95,0.3); color: #E07A5F; }

/* ── PANEL ────────────────────────────────────────────────────── */
.mp-panel { animation: fadeIn .2s ease; }
@keyframes fadeIn { from { opacity:0; transform:translateY(6px) } to { opacity:1; transform:none } }
.mp-panel-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2.5rem; margin-bottom: 2rem; }
@media(max-width: 860px) { .mp-panel-grid { grid-template-columns: 1fr; } }
.mp-section-title { font-size: .8rem; font-weight: 800; text-transform: uppercase; letter-spacing: .08em; color: #8B8175; margin-bottom: 1rem; }
.mp-section-desc { font-size: .88rem; color: #5C5246; margin-bottom: 1rem; line-height: 1.5; }

/* ── FORMS ────────────────────────────────────────────────────── */
.form-group { margin-bottom: 1.25rem; }
.form-label { display: block; font-size: .84rem; font-weight: 600; color: #2C241B; margin-bottom: .45rem; }
.form-control { width: 100%; padding: .7rem 1rem; border-radius: 10px; border: 1px solid rgba(44,36,27,0.15); background: #FFFFFF; color: #2C241B; font-size: .9rem; outline: none; transition: border-color .2s; font-family: inherit; }
.form-control:focus { border-color: #E07A5F; box-shadow: 0 0 0 3px rgba(224,122,95,0.1); }
.form-error { font-size: .78rem; color: #dc2626; margin-top: .3rem; display: block; }
.form-hint { font-size: .76rem; color: #8B8175; margin-top: .3rem; }
.form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media(max-width: 480px) { .form-row-2 { grid-template-columns: 1fr; } }

/* Color Picker */
.color-input-wrap { display: flex; align-items: center; gap: .6rem; }
.color-picker { width: 46px; height: 42px; border-radius: 8px; border: 1px solid rgba(44,36,27,0.15); cursor: pointer; padding: 2px; background: #fff; }
.color-hex { flex: 1; }

/* Toggles */
.toggle-row { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 1rem; background: #FFFFFF; border: 1px solid rgba(44,36,27,0.1); border-radius: 12px; margin-bottom: .75rem; box-shadow: 0 1px 2px rgba(0,0,0,0.02); }
.toggle-info { flex: 1; }
.toggle-label { display: block; font-size: .88rem; font-weight: 600; color: #2C241B; margin-bottom: .2rem; }
.toggle-desc { font-size: .78rem; color: #5C5246; }
.toggle-switch { position: relative; width: 48px; height: 26px; flex-shrink: 0; }
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.toggle-slider { position: absolute; inset: 0; background: rgba(44,36,27,0.2); border-radius: 999px; cursor: pointer; transition: background .2s; }
.toggle-slider::before { content: ''; position: absolute; left: 3px; top: 3px; width: 20px; height: 20px; background: #fff; border-radius: 50%; transition: transform .2s; box-shadow: 0 1px 3px rgba(0,0,0,0.2); }
.toggle-switch input:checked + .toggle-slider { background: #E07A5F; }
.toggle-switch input:checked + .toggle-slider::before { transform: translateX(22px); }

/* Save Button */
.btn-save { display: inline-flex; align-items: center; gap: .6rem; padding: .8rem 1.75rem; border-radius: 12px; border: none; background: #E07A5F; color: #fff; font-size: .92rem; font-weight: 700; cursor: pointer; transition: opacity .2s; margin-top: 1.5rem; }
.btn-save:hover { opacity: .88; }
.btn-save:disabled { opacity: .6; cursor: not-allowed; }

/* ── PREVIEW (Se mantiene oscuro porque representa la tienda) ── */
.mp-preview-col { position: sticky; top: 1rem; }
.preview-card { background: #0f0f13; border-radius: 20px; overflow: hidden; border: 1px solid rgba(44,36,27,0.1); box-shadow: 0 20px 60px rgba(0,0,0,.15); }
.preview-hero { padding: 2rem 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: .75rem; }
.preview-logo-ph { width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 900; color: #fff; overflow: hidden; }
.preview-title { font-size: 1.1rem; font-weight: 800; color: #fff; }
.preview-desc { font-size: .78rem; color: rgba(255,255,255,.7); line-height: 1.4; }
.preview-card-body { padding: 1.25rem; }
.preview-fake-card { padding: 1rem; border-radius: 12px; background: rgba(255,255,255,.06); }
.preview-card-name { font-size: .88rem; font-weight: 700; margin-bottom: .75rem; color: #fff; }
.preview-fake-btn { text-align: center; padding: .6rem; border-radius: 8px; font-size: .78rem; font-weight: 700; color: #fff; }

/* ── MULTIMEDIA ───────────────────────────────────────────────── */
.media-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 1.5rem; }
@media(max-width: 720px) { .media-grid { grid-template-columns: 1fr; } }
.media-card { background: #FFFFFF; border: 1px solid rgba(44,36,27,0.1); border-radius: 16px; padding: 1.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
.media-preview { margin: 1rem 0; border-radius: 12px; overflow: hidden; position: relative; border: 1px solid rgba(44,36,27,0.1); }
.media-preview img { width: 100%; height: 160px; object-fit: cover; display: block; }
.media-preview--wide img { height: 140px; }
.media-preview-label { font-size: .74rem; color: #fff; padding: .4rem .6rem; background: rgba(0,0,0,.6); position: absolute; bottom: 0; left: 0; right: 0; text-align: center; }
.btn-remove-media { display: block; width: 100%; padding: .5rem; background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.2); color: #dc2626; border-radius: 8px; font-size: .78rem; font-weight: 600; cursor: pointer; margin-top: .5rem; display: flex; align-items: center; justify-content: center; gap: .4rem; }
.btn-remove-media:hover { background: rgba(239,68,68,.15); }
.media-empty { display: flex; flex-direction: column; align-items: center; gap: .6rem; padding: 2rem 1rem; text-align: center; color: #8B8175; font-size: .82rem; margin: 1rem 0; border: 1px dashed rgba(44,36,27,0.2); border-radius: 12px; background: rgba(44,36,27,0.02); }
.media-empty--wide { padding: 1.5rem; }
.media-empty svg { stroke: #8B8175; }
.btn-upload-area { display: flex; align-items: center; justify-content: center; gap: .5rem; padding: .7rem 1.25rem; border-radius: 10px; border: 1px dashed rgba(44,36,27,0.2); background: #FFFFFF; color: #5C5246; font-size: .85rem; font-weight: 600; cursor: pointer; transition: all .2s; margin-top: .5rem; }
.btn-upload-area:hover { border-color: #E07A5F; background: rgba(224,122,95,0.05); color: #E07A5F; }
.hidden-input { display: none; }
.upload-progress { font-size: .78rem; color: #8B8175; text-align: center; margin-top: .5rem; }

/* ── REDES ────────────────────────────────────────────────────── */
.redes-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }
@media(max-width: 640px) { .redes-form-grid { grid-template-columns: 1fr; } }
.red-group { background: #FFFFFF; border: 1px solid rgba(44,36,27,0.1); border-radius: 14px; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
.red-label { display: flex; align-items: center; gap: .65rem; margin-bottom: .75rem !important; }
.red-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
.wa-bg { background: rgba(37,211,102,.15); color: #16a34a; }
.ig-bg { background: rgba(225,48,108,.15); color: #db2777; }
.fb-bg { background: rgba(24,119,242,.15); color: #2563eb; }
.tk-bg { background: #000; color: #fff; }
</style>

<script>
function copiarUrl() {
    const url = document.getElementById('url-tienda').innerText.trim();
    navigator.clipboard.writeText(url).then(() => {
        const btn = document.querySelector('.btn-link-copy');
        const original = btn.innerHTML;
        btn.innerHTML = '<svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg> ¡Copiado!';
        btn.style.background = 'rgba(52,211,153,.2)';
        btn.style.color = '#34d399';
        setTimeout(() => { btn.innerHTML = original; btn.style.background = ''; btn.style.color = ''; }, 2000);
    });
}
</script>
