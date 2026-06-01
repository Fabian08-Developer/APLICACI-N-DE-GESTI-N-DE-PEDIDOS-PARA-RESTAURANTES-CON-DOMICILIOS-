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
                <p class="panel-eyebrow">Recuperación</p>
                <h1 class="panel-titulo">
                    Seguridad y<br><em>confianza</em><br>en cada paso
                </h1>
                <div class="panel-profile">
                    <div class="panel-avatar">🍴</div>
                    <div>
                        <div class="panel-nombre">SGPD</div>
                        <div class="panel-rol">Soporte Técnico</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FORM SECTION --}}
    <div class="form-section form-login-section">
        <div class="form-inner">
            <div class="logo">
                <span class="logo-icono">🍴</span>
                <span class="logo-nombre">SG<span>PD</span></span>
            </div>

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
                        <span wire:loading.remove wire:target="sendCode">Enviar código</span>
                        <span wire:loading wire:target="sendCode">Enviando...</span>
                    </button>
                </form>
            @elseif($step == 2)
                <h2 class="form-titulo">Verifica tu correo</h2>
                <p class="form-sub">Hemos enviado un código de 6 dígitos a {{ $correo }}</p>

                <form wire:submit.prevent="verifyCode">
                    <div class="campo" style="text-align: center;">
                        <input type="text" wire:model="code" maxlength="6" 
                               style="text-align: center; font-size: 2rem; letter-spacing: 0.5em; font-weight: 700; height: 80px;"
                               placeholder="000000">
                        @error('code') <p class="campo-error">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="btn-submit">
                        <span wire:loading.remove wire:target="verifyCode">Verificar código</span>
                        <span wire:loading wire:target="verifyCode">Verificando...</span>
                    </button>

                    <div style="margin-top: 1rem; text-align: center;">
                        <button type="button" wire:click="resendCode" wire:loading.attr="disabled" class="text-[12px] text-[--ambar] font-bold uppercase tracking-widest hover:underline">
                            <span wire:loading.remove wire:target="resendCode">¿No llegó el código? Reenviar</span>
                            <span wire:loading wire:target="resendCode">Reenviando...</span>
                        </button>
                    </div>
                </form>
            @elseif($step == 3)
                <h2 class="form-titulo">Nueva contraseña</h2>
                <p class="form-sub">Ingresa tu nueva contraseña para acceder.</p>

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
                        <span wire:loading.remove wire:target="resetPassword">Actualizar contraseña</span>
                        <span wire:loading wire:target="resetPassword">Actualizando...</span>
                    </button>
                </form>
            @endif
            
            <p class="form-footer">
                ¿Recordaste tu contraseña? 
                <a href="{{ route('login') }}" wire:navigate>Inicia sesión</a>
            </p>
        </div>
    </div>
</div>
