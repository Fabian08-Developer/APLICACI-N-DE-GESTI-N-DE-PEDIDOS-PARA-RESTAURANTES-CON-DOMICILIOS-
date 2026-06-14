<div class="container-auth" id="contenedor">
    {{-- PANEL IMAGEN --}}
    <div class="panel-imagen" id="panelImagen">
        <span class="flotante">🛡️</span>
        <span class="flotante">🔐</span>
        <span class="flotante">🎋</span>

        <div class="panel-contenido">
            <div class="top-bar">
                <span class="top-bar-label">SGPD</span>
            </div>

            <div class="panel-bottom">
                <p class="panel-eyebrow">Seguridad</p>
                <h1 class="panel-titulo">
                    Verificación de<br><em>Identidad</em><br>anti-fraude
                </h1>
                <div class="panel-profile">
                    <div class="panel-avatar">🛡️</div>
                    <div>
                        <div class="panel-nombre">SGPD</div>
                        <div class="panel-rol">Sistema de Seguridad</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FORM SECTION --}}
    <div class="form-section form-login-section">
        <div class="form-inner">
            <div class="logo">
                <span class="logo-icono">🎋</span>
                <span class="logo-nombre">SG<span>PD</span></span>
            </div>

            @if($success)
                <div class="text-center py-6">
                    <div class="w-16 h-16 rounded-full bg-emerald-500/10 text-emerald-500 flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>

                    <h2 class="form-titulo">¡Identidad Verificada!</h2>
                    <p class="form-sub mb-8 leading-relaxed">
                        Tu cuenta de Gerente y toda la información asociada a tu negocio han sido restauradas con éxito en el sistema. Ya puedes acceder al panel de administración.
                    </p>

                    <a href="{{ route('login') }}" class="btn-submit inline-flex items-center justify-center text-center font-bold" style="text-decoration: none; width: 100%;">
                        Iniciar Sesión
                    </a>
                </div>
            @else
                <h2 class="form-titulo">Recuperar Cuenta</h2>
                <p class="form-sub">Por motivos de seguridad y prevención de fraudes, debes verificar los datos oficiales de tu negocio para restaurar la cuenta.</p>

                <form wire:submit.prevent="verifyAndRestore">
                    @error('verification')
                        <div class="bg-rose-500/10 border border-rose-500/30 text-rose-500 text-xs rounded-xl p-3 mb-5 font-medium leading-relaxed">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="campo">
                        <label>Correo de Gerente</label>
                        <input type="email" wire:model="correo" placeholder="ejemplo@correo.com" required readonly class="opacity-70 cursor-not-allowed">
                        @error('correo') <p class="campo-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="campo">
                        <label>NIT / RUT del Negocio</label>
                        <input type="text" wire:model="nit" placeholder="Ingresa el NIT del negocio registrado" required autocomplete="off">
                        @error('nit') <p class="campo-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="campo">
                        <label>Nombre de la Empresa / Negocio</label>
                        <input type="text" wire:model="nombre_empresa" placeholder="Nombre comercial registrado" required autocomplete="off">
                        @error('nombre_empresa') <p class="campo-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="campo">
                        <label>Teléfono de Contacto</label>
                        <input type="text" wire:model="telefono" placeholder="Teléfono del gerente o negocio" required autocomplete="off">
                        @error('telefono') <p class="campo-error">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="btn-submit">
                        <span wire:loading.remove wire:target="verifyAndRestore">Verificar y Restaurar Cuenta</span>
                        <span wire:loading wire:target="verifyAndRestore">Verificando datos...</span>
                    </button>
                </form>
            @endif
            
            <p class="form-footer">
                ¿Necesitas ayuda adicional? 
                <a href="mailto:soporte@sgpd.com">Contactar a Soporte</a>
            </p>
        </div>
    </div>
</div>
