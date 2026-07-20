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
                        <div class="panel-nombre">SGPD Suite</div>
                        <div class="panel-rol">Auditoría y Validación de Cuenta</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CONTENIDO DE CONFIRMACIÓN --}}
    <div class="form-section form-login-section">
        <div class="form-inner">
            <a href="{{ route('home') }}" class="logo">
                <span class="logo-icono">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </span>
                <span class="logo-nombre">SG<span>PD</span></span>
            </a>

            <div style="text-align: center; margin-bottom: 1.8rem;">
                <div style="width: 60px; height: 60px; background: rgba(34, 197, 94, 0.12); border: 2px solid rgba(34, 197, 94, 0.4); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.2rem; color: #22C55E;">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h2 class="form-titulo">¡Solicitud Enviada!</h2>
                <p class="form-sub" style="line-height: 1.5; color: var(--text-muted); margin-top: 0.5rem;">
                    Gracias por registrar tu restaurante. Tu cuenta ha sido creada y está pendiente de aprobación oficial por parte del <strong>Super Administrador</strong>.
                </p>
            </div>

            {{-- Bloques informativos --}}
            <div style="display: flex; flex-direction: column; gap: 1rem; margin-bottom: 2rem;">
                <div style="display: flex; align-items: flex-start; gap: 0.85rem; background: var(--surface-2); padding: 1.1rem; border-radius: var(--radius-sm); border: 1px solid var(--border);">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(224, 122, 95, 0.12); color: var(--primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <h4 style="font-size: 0.74rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--primary); margin-bottom: 3px;">
                            Notificación por Correo
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
