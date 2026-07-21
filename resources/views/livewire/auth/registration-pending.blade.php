<div class="container-auth" id="contenedor">
    {{-- PANEL IMAGEN --}}
    <div class="panel-imagen" id="panelImagen">
        <div class="panel-floating-badge">
            <svg class="w-3.5 h-3.5 text-[#E07A5F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Revisión en Curso</span>
        </div>

        <div class="panel-contenido">
            <div class="top-bar">
                <span class="top-bar-label">SGPD</span>
            </div>

            <div class="panel-bottom">
                <p class="panel-eyebrow">Solicitud de Registro</p>
                <h1 class="panel-titulo">
                    Estamos trabajando<br>en <em>aprobar</em><br>tu cuenta
                </h1>
                <div class="panel-profile">
                    <div class="panel-avatar">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div>
                        <div class="panel-nombre">Verificación Segura</div>
                        <div class="panel-rol">Administración Central</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FORM SECTION (Reusing same side container for text) --}}
    <div class="form-section flex items-center justify-center p-8">
        <div class="form-inner max-w-sm text-center">
            <div class="flex justify-center mb-6">
                <div style="width: 64px; height: 64px; border-radius: 16px; background: rgba(224, 122, 95, 0.1); color: #E07A5F; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            
            <h2 class="form-titulo mb-2" style="text-align: center;">¡Solicitud Recibida!</h2>
            <p class="form-sub mb-8" style="text-align: center;">
                Tu registro fue exitoso. Actualmente tu cuenta está en <strong style="color: #E07A5F;">revisión y auditoría</strong> por parte de la administración central.
            </p>

            <div style="text-align: left; display: flex; flex-direction: column; gap: 1rem; margin-bottom: 2rem;">
                <div style="display: flex; align-items: flex-start; gap: 0.85rem; background: var(--surface-2); padding: 1.1rem; border-radius: var(--radius-sm); border: 1px solid var(--border);">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(224, 122, 95, 0.12); color: #E07A5F; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <h4 style="font-size: 0.74rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #E07A5F; margin-bottom: 3px;">
                            Notificación Inmediata
                        </h4>
                        <p style="font-size: 0.82rem; color: var(--text-muted); line-height: 1.4; font-weight: 500;">
                            Te enviaremos un correo electrónico apenas tu cuenta sea verificada y habilitada para operar.
                        </p>
                    </div>
                </div>

                <div style="display: flex; align-items: flex-start; gap: 0.85rem; background: var(--surface-2); padding: 1.1rem; border-radius: var(--radius-sm); border: 1px solid var(--border);">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(61, 90, 128, 0.12); color: var(--secondary); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h4 style="font-size: 0.74rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--secondary); margin-bottom: 3px;">
                            Tiempo Estimado
                        </h4>
                        <p style="font-size: 0.82rem; color: var(--text-muted); line-height: 1.4; font-weight: 500;">
                            El tiempo habitual de revisión administrativa y verificación del NIT/RUT es de menos de 24 horas.
                        </p>
                    </div>
                </div>
            </div>

            <a href="{{ route('home') }}" class="btn-submit" style="text-decoration: none;">
                Volver a la Página Principal
            </a>
        </div>
    </div>
</div>
