<div class="container-auth" id="contenedor">
    {{-- PANEL IMAGEN --}}
    <div class="panel-imagen" id="panelImagen">
        <div class="panel-floating-badge">
            <svg class="w-3.5 h-3.5 text-[#E07A5F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            <span>Rendimiento en Vivo</span>
        </div>
        <div class="panel-floating-badge">
            <svg class="w-3.5 h-3.5 text-[#E07A5F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Seguridad Bancaria</span>
        </div>

        <div class="panel-contenido">
            <div class="top-bar">
                <span class="top-bar-label">SGPD</span>
            </div>

            <div class="panel-bottom">
                <p class="panel-eyebrow">Plataforma Gastronómica</p>
                <h1 class="panel-titulo">
                    El arte de<br><em>servir bien</em><br>comienza aquí
                </h1>
                <div class="panel-profile">
                    <div class="panel-avatar">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                    </div>
                    <div>
                        <div class="panel-nombre">SGPD Suite</div>
                        <div class="panel-rol">Gestión Integral Inteligente</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FORM LOGIN --}}
    <div class="form-section form-login-section">
        <div class="form-inner">
            <a href="{{ route('home') }}" wire:navigate class="inline-flex items-center gap-2 text-[12px] font-bold text-[#6C757D] hover:text-[#E07A5F] transition-colors mb-6 uppercase tracking-wider">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver al inicio
            </a>

            <a href="{{ route('home') }}" class="logo">
                <span class="logo-icono">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </span>
                <span class="logo-nombre">SG<span>PD</span></span>
            </a>
            <h2 class="form-titulo">Bienvenido</h2>
            <p class="form-sub">Ingresa tus credenciales para continuar</p>

            <form wire:submit.prevent="login">
                <div class="campo">
                    <label>Correo Electrónico</label>
                    <input type="email" wire:model="correo" placeholder="ejemplo@correo.com" required autocomplete="email">
                    @error('correo') <p class="campo-error">{{ $message }}</p> @enderror
                </div>
                
                <div class="campo">
                    <label>Contraseña</label>
                    <input type="password" wire:model="contrasena" placeholder="••••••••" required autocomplete="current-password">
                    @error('contrasena') <p class="campo-error">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-between mb-6 px-1">
                    <label class="flex items-center cursor-pointer group">
                        <input type="checkbox" wire:model="remember" class="hidden peer">
                        <div class="w-4 h-4 border-2 border-[#EAE5DD] rounded peer-checked:bg-[#E07A5F] peer-checked:border-[#E07A5F] transition-all flex items-center justify-center">
                            <svg class="w-2.5 h-2.5 text-white opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <span class="ml-2 text-[12px] font-bold text-[#6C757D] uppercase tracking-wider">Recordarme</span>
                    </label>
                    <a href="{{ route('password.request') }}" wire:navigate class="text-[11px] font-bold text-[#ADB5BD] uppercase tracking-wider hover:text-[#E07A5F] transition-colors">¿Olvidaste tu contraseña?</a>
                </div>

                <button type="submit" class="btn-submit">
                    <span wire:loading.remove wire:target="login">Ingresar al sistema</span>
                    <span wire:loading.flex wire:target="login" class="items-center justify-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Autenticando...
                    </span>
                </button>
            </form>
            
            <p class="form-footer">
                ¿No tienes una cuenta? 
                <a href="{{ route('registro') }}" wire:navigate>Regístrate gratis</a>
            </p>
        </div>
    </div>

    <script>
        (function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('_clear')) {
                sessionStorage.removeItem('staff_token');
                const url = new URL(window.location);
                url.searchParams.delete('_clear');
                window.history.replaceState(null, '', url.pathname + url.search + url.hash);
            }
        })();
    </script>
</div>
