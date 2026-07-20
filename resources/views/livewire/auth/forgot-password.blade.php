<div class="container-auth" id="contenedor">
    {{-- PANEL IMAGEN --}}
    <div class="panel-imagen" id="panelImagen">
        <div class="panel-floating-badge">
            <svg class="w-3.5 h-3.5 text-[#E07A5F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            <span>Protección de Datos</span>
        </div>

        <div class="panel-contenido">
            <div class="top-bar">
                <span class="top-bar-label">SGPD</span>
            </div>

            <div class="panel-bottom">
                <p class="panel-eyebrow">Recuperación de Acceso</p>
                <h1 class="panel-titulo">
                    Seguridad y<br><em>confianza</em><br>en cada paso
                </h1>
                <div class="panel-profile">
                    <div class="panel-avatar">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div>
                        <div class="panel-nombre">SGPD Suite</div>
                        <div class="panel-rol">Centro de Seguridad</div>
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

            @if($step == 1)
                <h2 class="form-titulo">¿Olvidaste tu contraseña?</h2>
                <p class="form-sub">Ingresa tu correo para recibir un código de verificación.</p>

                <form wire:submit.prevent="sendCode">
                    <div class="campo">
                        <label>Correo Electrónico</label>
                        <input type="email" wire:model="correo" placeholder="ejemplo@correo.com" required>
                        @error('correo') <p class="campo-error">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="btn-submit">
                        <span wire:loading.remove wire:target="sendCode">Enviar código de recuperación</span>
                        <span wire:loading wire:target="sendCode" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Enviando...
                        </span>
                    </button>
                </form>
            @elseif($step == 2)
                <h2 class="form-titulo text-center">Verifica tu correo</h2>
                <p class="form-sub text-center">Hemos enviado un código de 6 dígitos a <strong class="text-[#2D3142]">{{ $correo }}</strong></p>

                <form wire:submit.prevent="verifyCode">
                    <div class="campo my-6" style="text-align: center;">
                        <input type="text" wire:model="code" maxlength="6" 
                               style="text-align: center; font-size: 2.2rem; letter-spacing: 0.45em; font-weight: 700; height: 84px; border-radius: 16px; border: 2px solid var(--primary); background: #FAF9F7;"
                               placeholder="000000">
                        @error('code') <p class="campo-error">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="btn-submit">
                        <span wire:loading.remove wire:target="verifyCode">Verificar Código</span>
                        <span wire:loading wire:target="verifyCode" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Verificando...
                        </span>
                    </button>

                    <div style="margin-top: 1.5rem; text-align: center;">
                        <button type="button" wire:click="resendCode" wire:loading.attr="disabled" class="text-[12px] text-[#E07A5F] font-bold uppercase tracking-wider hover:underline">
                            <span wire:loading.remove wire:target="resendCode">¿No llegó el código? Reenviar código</span>
                            <span wire:loading wire:target="resendCode">Reenviando código...</span>
                        </button>
                    </div>
                </form>
            @elseif($step == 3)
                <h2 class="form-titulo">Nueva contraseña</h2>
                <p class="form-sub">Ingresa tu nueva contraseña para acceder de forma segura.</p>

                <form wire:submit.prevent="resetPassword">
                    <div class="campo">
                        <label>Nueva Contraseña</label>
                        <input type="password" wire:model="password" placeholder="••••••••" required>
                    </div>
                    <div class="campo">
                        <label>Confirmar Contraseña</label>
                        <input type="password" wire:model="password_confirmation" placeholder="••••••••" required>
                    </div>
                    @error('password') <p class="campo-error">{{ $message }}</p> @enderror

                    <button type="submit" class="btn-submit">
                        <span wire:loading.remove wire:target="resetPassword">Actualizar Contraseña</span>
                        <span wire:loading wire:target="resetPassword" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Actualizando...
                        </span>
                    </button>
                </form>
            @endif
            
            <p class="form-footer">
                ¿Recordaste tu contraseña? 
                <a href="{{ route('login') }}" wire:navigate>Inicia sesión aquí</a>
            </p>
        </div>
    </div>
</div>
