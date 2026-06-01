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
                <p class="panel-eyebrow">Sistema de gestión</p>
                <h1 class="panel-titulo">
                    El arte de<br><em>servir bien</em><br>comienza aquí
                </h1>
                <div class="panel-profile">
                    <div class="panel-avatar">🍴</div>
                    <div>
                        <div class="panel-nombre">SGPD</div>
                        <div class="panel-rol">Gestión de restaurante</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FORM LOGIN --}}
    <div class="form-section form-login-section">
        <div class="form-inner">
            <div class="logo">
                <span class="logo-icono">🍴</span>
                <span class="logo-nombre">SG<span>PD</span></span>
            </div>
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
                        <div class="w-4 h-4 border-2 border-[#e8e4e0] rounded peer-checked:bg-[--ambar] peer-checked:border-[--ambar] transition-all"></div>
                        <span class="ml-2 text-[12px] font-bold text-stone-500 uppercase tracking-widest">Recordarme</span>
                    </label>
                    <a href="{{ route('password.request') }}" wire:navigate class="text-[11px] font-bold text-stone-400 uppercase tracking-widest hover:text-[--ambar] transition-colors">¿Olvidaste tu contraseña?</a>
                </div>

                <button type="submit" class="btn-submit">
                    <span wire:loading.remove wire:target="login">Ingresar al sistema</span>
                    <span wire:loading wire:target="login">Autenticando...</span>
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
            // Si venimos de un logout con _clear=1, limpiamos sessionStorage
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('_clear')) {
                sessionStorage.removeItem('staff_token');
                // Limpiar la URL de los parámetros para dejarla limpia
                const url = new URL(window.location);
                url.searchParams.delete('_clear');
                window.history.replaceState(null, '', url.pathname + url.search + url.hash);
            }
        })();
    </script>
</div>
