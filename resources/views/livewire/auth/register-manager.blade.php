<div class="container-auth container-wide" id="contenedor">
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
                <p class="panel-eyebrow">Únete a nosotros</p>
                <h1 class="panel-titulo">
                    Gestiona tu<br><em>restaurante</em><br>con elegancia
                </h1>
                <div class="panel-profile">
                    <div class="panel-avatar">🍴</div>
                    <div>
                        <div class="panel-nombre">SGPD</div>
                        <div class="panel-rol">Plataforma SaaS</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FORM REGISTER --}}
    <div class="form-section form-login-section">
        <div class="form-inner" style="max-width: 100%;">
            <div class="logo">
                <span class="logo-icono">🍴</span>
                <span class="logo-nombre">SG<span>PD</span></span>
            </div>

            @if($step == 1)
                <h2 class="form-titulo">Crear cuenta</h2>
                <p class="form-sub">Empieza a gestionar tu restaurante</p>

                @if (session()->has('error'))
                    <div class="alerta-error">{{ session('error') }}</div>
                @endif

                <form wire:submit.prevent="iniciarRegistro" class="space-y-6">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <!-- SECCIÓN 1: TU NEGOCIO -->
                        <div style="background: #fcfbf9; padding: 1.5rem; border-radius: 20px; border: 1.5px solid #f0ede9;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.2rem;">
                                <span style="background: var(--ambar); color: #fff; width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; font-weight: 800;">01</span>
                                <h3 style="font-size: 0.8rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; color: #555;">Tu Negocio</h3>
                            </div>
                            
                            <div class="campo" x-data="{ 
                                formatNit(val) {
                                    val = val.replace(/\D/g, '');
                                    if (val.length > 10) val = val.slice(0, 10);
                                    
                                    let formatted = '';
                                    if (val.length > 0) formatted = val.substring(0, 3);
                                    if (val.length > 3) formatted += '.' + val.substring(3, 6);
                                    if (val.length > 6) formatted += '.' + val.substring(6, 9);
                                    if (val.length > 9) formatted += '-' + val.substring(9, 10);
                                    
                                    return formatted;
                                }
                            }">
                                <label>NIT de la Empresa</label>
                                <input type="text" 
                                       wire:model="nit" 
                                       placeholder="900.123.456-7" 
                                       required
                                       x-on:input="$el.value = formatNit($el.value); $wire.set('nit', $el.value)"
                                >
                                <p style="font-size: 0.65rem; color: #999; margin-top: 4px; font-weight: 600;">Se aplicará el formato automáticamente: 000.000.000-0</p>
                                @error('nit') <p class="campo-error">{{ $message }}</p> @enderror
                            </div>
                            <div class="campo">
                                <label>Nombre Negocio</label>
                                <input type="text" wire:model="nombre_empresa" placeholder="Mi Cafetería" required
                                       x-on:input="$el.value = $el.value.replace(/[\u{1F300}-\u{1FAFF}]|[\u{2600}-\u{27BF}]/gu, ''); $wire.set('nombre_empresa', $el.value)">
                                @error('nombre_empresa') <p class="campo-error">{{ $message }}</p> @enderror
                            </div>

                            <div class="campo">
                                <label>Adjuntar RUT / NIT (PDF/Word)</label>
                                <div style="position: relative;">
                                    <input type="file" wire:model="documento" id="documento" style="display: none;" accept=".pdf,.doc,.docx">
                                    <label for="documento" style="display: block; padding: 12px; background: #fff; border: 2px dashed #e8e4e0; border-radius: 12px; text-align: center; cursor: pointer; color: #888; font-size: 0.8rem; transition: all 0.3s;">
                                        <span wire:loading.remove wire:target="documento">
                                            {{ $documento ? $documento->getClientOriginalName() : 'Seleccionar archivo...' }}
                                        </span>
                                        <span wire:loading wire:target="documento">Subiendo archivo...</span>
                                    </label>
                                </div>
                                @error('documento') <p class="campo-error">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- SECCIÓN 2: TU CUENTA -->
                        <div style="background: #fcfbf9; padding: 1.5rem; border-radius: 20px; border: 1.5px solid #f0ede9;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.2rem;">
                                <span style="background: var(--ambar); color: #fff; width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; font-weight: 800;">02</span>
                                <h3 style="font-size: 0.8rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; color: #555;">Tu Cuenta</h3>
                            </div>

                            <div class="campo">
                                <label>Nombre Completo</label>
                                <input type="text" wire:model="nombre" placeholder="Tu nombre" required
                                       x-on:input="$el.value = $el.value.replace(/[\u{1F300}-\u{1FAFF}]|[\u{2600}-\u{27BF}]/gu, ''); $wire.set('nombre', $el.value)">
                                @error('nombre') <p class="campo-error">{{ $message }}</p> @enderror
                            </div>

                            <div class="campo">
                                <label>Correo Electrónico</label>
                                <input type="email" wire:model="correo" placeholder="gerencia@empresa.com" required autocomplete="email">
                                @error('correo') <p class="campo-error">{{ $message }}</p> @enderror
                            </div>

                            <div class="campo">
                                <label>Número de Teléfono</label>
                                <input type="tel" wire:model="telefono" placeholder="300 123 4567" required>
                                @error('telefono') <p class="campo-error">{{ $message }}</p> @enderror
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="campo">
                                    <label>Contraseña</label>
                                    <input type="password" wire:model="contrasena" placeholder="••••••••" required>
                                </div>
                                <div class="campo">
                                    <label>Confirmar</label>
                                    <input type="password" wire:model="contrasena_confirmation" placeholder="••••••••" required>
                                </div>
                            </div>
                            @error('contrasena') <p class="campo-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">
                        <span wire:loading.remove wire:target="iniciarRegistro">Continuar registro</span>
                        <span wire:loading wire:target="iniciarRegistro">Procesando...</span>
                    </button>
                </form>
            @else
                {{-- PASO 2: VERIFICACIÓN --}}
                <div style="max-width: 420px; margin: 0 auto;">
                    <h2 class="form-titulo">Verificación</h2>
                    <p class="form-sub">Ingresa el código enviado a {{ $correo }}</p>

                    <div class="campo" style="text-align: center;">
                        <input type="text" wire:model="codigoIngresado" maxlength="6" 
                               style="text-align: center; font-size: 2rem; letter-spacing: 0.5em; font-weight: 700; height: 80px;"
                               placeholder="000000">
                        @error('codigoIngresado') <p class="campo-error">{{ $message }}</p> @enderror
                    </div>

                    <button wire:click="verificarYCrearCuenta" class="btn-submit">
                        <span wire:loading.remove wire:target="verificarYCrearCuenta">Finalizar Registro</span>
                        <span wire:loading wire:target="verificarYCrearCuenta">Verificando...</span>
                    </button>

                    <div style="margin-top: 1rem; text-align: center;">
                        <button wire:click="enviarNuevoCodigo" wire:loading.attr="disabled" class="text-[12px] text-[--ambar] font-bold uppercase tracking-widest hover:underline">
                            ¿No llegó el código? Reenviar
                        </button>
                        <br>
                        <button wire:click="$set('step', 1)" class="text-[12px] text-stone-400 font-bold uppercase tracking-widest mt-2 hover:underline">
                            ← Corregir datos
                        </button>
                    </div>
                </div>
            @endif
            
            <p class="form-footer">
                ¿Ya tienes una cuenta? 
                <a href="{{ route('login') }}" wire:navigate>Inicia sesión</a>
            </p>
        </div>
    </div>
</div>
