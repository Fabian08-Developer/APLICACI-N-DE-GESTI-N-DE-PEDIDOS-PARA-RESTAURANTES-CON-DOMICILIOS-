<div class="container-auth" id="contenedor">
    {{-- PANEL IMAGEN --}}
    <div class="panel-imagen" id="panelImagen">
        <div class="panel-floating-badge">
            <svg class="w-3.5 h-3.5 text-[#E07A5F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <span>Anti-Fraude Activo</span>
        </div>

        <div class="panel-contenido">
            <div class="top-bar">
                <span class="top-bar-label">SGPD</span>
            </div>

            <div class="panel-bottom">
                <p class="panel-eyebrow">Seguridad de Cuenta</p>
                <h1 class="panel-titulo">
                    Verificación de<br><em>Identidad</em><br>anti-fraude
                </h1>
                <div class="panel-profile">
                    <div class="panel-avatar">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div>
                        <div class="panel-nombre">SGPD Suite</div>
                        <div class="panel-rol">Sistema de Protección y Auditoría</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FORM SECTION --}}
    <div class="form-section form-login-section">
        <div class="form-inner">
            <a href="{{ route('login') }}" wire:navigate class="inline-flex items-center gap-2 text-[12px] font-bold text-[#6C757D] hover:text-[#E07A5F] transition-colors mb-6 uppercase tracking-wider">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver al login
            </a>

            <a href="{{ route('home') }}" class="logo">
                <span class="logo-icono">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </span>
                <span class="logo-nombre">SG<span>PD</span></span>
            </a>

            @if($success)
                <div class="text-center py-6">
                    <div class="w-16 h-16 rounded-full bg-emerald-500/10 text-emerald-500 flex items-center justify-center mx-auto mb-6 border border-emerald-500/20">
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
                        <label>NIT de la Empresa</label>
                        <input type="text" wire:model="nit" placeholder="900.123.456-7" required>
                        @error('nit') <p class="campo-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="campo">
                        <label>Correo de la Cuenta</label>
                        <input type="email" wire:model="correo" placeholder="gerencia@empresa.com" required>
                        @error('correo') <p class="campo-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="campo">
                        <label>Contraseña Actual o Anterior</label>
                        <input type="password" wire:model="password" placeholder="••••••••" required>
                        @error('password') <p class="campo-error">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="btn-submit">
                        <span wire:loading.remove wire:target="verifyAndRestore">Verificar y Restaurar Acceso</span>
                        <span wire:loading wire:target="verifyAndRestore" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Verificando...
                        </span>
                    </button>
                </form>
            @endif
            
            <p class="form-footer">
                ¿Ya recuerdas tus accesos? 
                <a href="{{ route('login') }}" wire:navigate>Volver al Login</a>
            </p>
        </div>
    </div>
</div>
