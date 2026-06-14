<div class="container-auth" id="contenedor">
    {{-- PANEL IMAGEN --}}
    <div class="panel-imagen" id="panelImagen">
        <span class="flotante">🍷</span>
        <span class="flotante">🫕</span>
        <span class="flotante">🥩</span>

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
                    <div class="panel-avatar">🍴</div>
                    <div>
                        <div class="panel-nombre">SGPD</div>
                        <div class="panel-rol">Proceso de verificación</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CONTENIDO DE CONFIRMACIÓN --}}
    <div class="form-section form-login-section">
        <div class="form-inner">
            <div class="logo">
                <span class="logo-icono">🍴</span>
                <span class="logo-nombre">SG<span>PD</span></span>
            </div>

            <div style="text-align: center; margin-bottom: 1.5rem;">
                <div style="width: 56px; height: 56px; background: rgba(34, 197, 94, 0.1); border: 1.5px solid rgba(34, 197, 94, 0.3); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto 1rem; animation: pulsar 2s infinite;">
                    ✓
                </div>
                <h2 class="form-titulo">¡Solicitud Enviada!</h2>
                <p class="form-sub" style="font-size: 0.85rem; line-height: 1.4; color: #666; margin-top: 0.5rem;">
                    Gracias por registrar tu negocio. Tu cuenta ha sido creada exitosamente y está pendiente de aprobación por el <strong>Super Administrador</strong>.
                </p>
            </div>

            {{-- Bloques informativos --}}
            <div style="display: flex; flex-direction: column; gap: 0.8rem; margin-bottom: 1.8rem;">
                <div style="display: flex; align-items: flex-start; gap: 0.75rem; background: #faf9f7; padding: 1rem; border-radius: 12px; border: 1px solid #e8e4e0;">
                    <div style="width: 28px; height: 28px; border-radius: 8px; background: rgba(201, 123, 34, 0.1); color: var(--ambar); display: flex; align-items: center; justify-content: center; font-size: 0.85rem; flex-shrink: 0; font-weight: bold;">
                        ✉
                    </div>
                    <div>
                        <h4 style="font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: var(--ambar); margin-bottom: 2px;">
                            Notificación por Correo
                        </h4>
                        <p style="font-size: 0.72rem; color: #888; line-height: 1.3; font-weight: 500;">
                            Te enviaremos un correo electrónico una vez que tu cuenta sea aprobada o si necesitamos información adicional.
                        </p>
                    </div>
                </div>

                <div style="display: flex; align-items: flex-start; gap: 0.75rem; background: #faf9f7; padding: 1rem; border-radius: 12px; border: 1px solid #e8e4e0;">
                    <div style="width: 28px; height: 28px; border-radius: 8px; background: rgba(201, 123, 34, 0.1); color: var(--ambar); display: flex; align-items: center; justify-content: center; font-size: 0.85rem; flex-shrink: 0; font-weight: bold;">
                        ⏱
                    </div>
                    <div>
                        <h4 style="font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: var(--ambar); margin-bottom: 2px;">
                            Tiempo de Respuesta
                        </h4>
                        <p style="font-size: 0.72rem; color: #888; line-height: 1.3; font-weight: 500;">
                            El proceso de verificación suele tardar menos de 24 horas hábiles.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Botón de Acción --}}
            <a href="{{ route('login') }}" class="btn-submit" style="text-decoration: none;">
                Volver al Inicio de Sesión
            </a>

            <p class="form-footer" style="font-size: 0.65rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; margin-top: 2rem;">
                © {{ date('Y') }} SGPD • Plataforma de Gestión
            </p>
        </div>
    </div>
</div>
