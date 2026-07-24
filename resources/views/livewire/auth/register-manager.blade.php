<div class="container-auth container-wide" id="contenedor">
    {{-- PANEL IMAGEN --}}
    <div class="panel-imagen" id="panelImagen">
        <div class="panel-floating-badge">
            <svg class="w-3.5 h-3.5 text-[#E07A5F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <span>100% en la Nube</span>
        </div>
        <div class="panel-floating-badge">
            <svg class="w-3.5 h-3.5 text-[#3D5A80]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            <span>Activación Inmediata</span>
        </div>

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
                    <div class="panel-avatar">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                    </div>
                    <div>
                        <div class="panel-nombre">SGPD Suite</div>
                        <div class="panel-rol">Plataforma Gastronómica SaaS</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FORM REGISTER --}}
    <div class="form-section form-login-section">
        <div class="form-inner" style="max-width: 100%;">
            <a href="{{ route('login') }}" wire:navigate class="inline-flex items-center gap-2 text-[12px] font-bold text-[#6C757D] hover:text-[#E07A5F] transition-colors mb-6 uppercase tracking-wider">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver al login
            </a>

            <a href="{{ route('home') }}" class="logo" style="margin-bottom: 1.2rem;">
                <span class="logo-icono" style="width: 34px; height: 34px;">
                    <svg class="w-4.5 h-4.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </span>
                <span class="logo-nombre" style="font-size: 1.4rem;">SG<span>PD</span></span>
            </a>

            @if($step == 1)
                <h2 class="form-titulo" style="font-size: 1.6rem; margin-bottom: 0.2rem;">Crear cuenta</h2>
                <p class="form-sub" style="margin-bottom: 1.1rem; font-size: 0.84rem;">Empieza a gestionar tu restaurante hoy mismo</p>

                @if (session()->has('error'))
                    <div class="alerta-error">{{ session('error') }}</div>
                @endif

                <form wire:submit.prevent="iniciarRegistro" class="space-y-4">
                    <div class="grid-registro-secciones">
                        <!-- SECCIÓN 1: TU NEGOCIO -->
                        <div class="seccion-caja">
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                                <span style="background: var(--primary); color: #fff; width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.68rem; font-weight: 700;">01</span>
                                <h3 style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-main);">Tu Negocio</h3>
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
                                <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 5px; font-weight: 500;">Formato automático: 000.000.000-0</p>
                                @error('nit') <p class="campo-error">{{ $message }}</p> @enderror
                            </div>
                            <div class="campo">
                                <label>Nombre Negocio</label>
                                <input type="text" wire:model="nombre_empresa" placeholder="Mi Restaurante Gourmet" required
                                       x-on:input="$el.value = $el.value.replace(/[\u{1F300}-\u{1FAFF}]|[\u{2600}-\u{27BF}]/gu, ''); $wire.set('nombre_empresa', $el.value)">
                                @error('nombre_empresa') <p class="campo-error">{{ $message }}</p> @enderror
                            </div>

                            <div class="campo">
                                <label>Adjuntar RUT / NIT (PDF/Word)</label>
                                <div style="position: relative;">
                                    <input type="file" wire:model="documento" id="documento" style="display: none;" accept=".pdf,.doc,.docx">
                                    <label for="documento" style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; padding: 14px; background: #FFFFFF; border: 1.5px dashed var(--border); border-radius: var(--radius-sm); text-align: center; cursor: pointer; color: var(--text-muted); font-size: 0.82rem; transition: all 0.25s ease;" onmouseover="this.style.borderColor='var(--primary)'; this.style.backgroundColor='#FFFDFB'" onmouseout="this.style.borderColor='var(--border)'; this.style.backgroundColor='#FFFFFF'">
                                        <svg class="w-6 h-6 text-[#E07A5F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                        </svg>
                                        <span wire:loading.remove wire:target="documento" class="font-medium">
                                            {{ $documento ? $documento->getClientOriginalName() : 'Seleccionar o arrastrar archivo...' }}
                                        </span>
                                        <span wire:loading wire:target="documento" class="font-medium text-[#E07A5F]">Subiendo archivo...</span>
                                    </label>
                                </div>
                                @error('documento') <p class="campo-error">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- SECCIÓN 2: TU CUENTA -->
                        <div class="seccion-caja">
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                                <span style="background: var(--primary); color: #fff; width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.68rem; font-weight: 700;">02</span>
                                <h3 style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-main);">Tu Cuenta</h3>
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

                            <div class="campo" x-data="{
                                formatPhone(val) {
                                    val = val.replace(/\D/g, '');
                                    if (val.length > 10) val = val.slice(0, 10);
                                    
                                    let formatted = '';
                                    if (val.length > 0) formatted = val.substring(0, 3);
                                    if (val.length > 3) formatted += ' ' + val.substring(3, 6);
                                    if (val.length > 6) formatted += ' ' + val.substring(6, 10);
                                    
                                    return formatted;
                                }
                            }">
                                <label>Número de Teléfono</label>
                                <input type="tel" wire:model="telefono" placeholder="300 123 4567" required
                                       x-on:input="$el.value = formatPhone($el.value); $wire.set('telefono', $el.value)">
                                @error('telefono') <p class="campo-error">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid-contrasenas" x-data="{ showPass: false }">
                                <div class="campo">
                                    <label>Contraseña</label>
                                    <div style="position: relative;">
                                        <input :type="showPass ? 'text' : 'password'" wire:model="contrasena" placeholder="••••••••" required style="padding-right: 2.5rem;">
                                        <button type="button" @click="showPass = !showPass" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; padding: 4px;">
                                            <svg x-show="!showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                            <svg x-show="showPass" class="w-4 h-4" style="display: none;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.978 9.978 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="campo">
                                    <label>Confirmar</label>
                                    <div style="position: relative;">
                                        <input :type="showPass ? 'text' : 'password'" wire:model="contrasena_confirmation" placeholder="••••••••" required>
                                    </div>
                                </div>
                            </div>
                            @error('contrasena') <p class="campo-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">
                        <span wire:loading.remove wire:target="iniciarRegistro">Continuar Registro</span>
                        <span wire:loading wire:target="iniciarRegistro" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Procesando...
                        </span>
                    </button>
                </form>
            @else
                {{-- PASO 2: VERIFICACIÓN --}}
                <div style="max-width: 420px; margin: 0 auto; padding: 1rem 0;">
                    <h2 class="form-titulo text-center">Verificación de Cuenta</h2>
                    <p class="form-sub text-center">Ingresa el código de 6 dígitos enviado a <strong class="text-[#2D3142]">{{ $correo }}</strong></p>

                    <div class="campo my-6" style="text-align: center;">
                        <input type="text" wire:model="codigoIngresado" maxlength="6" 
                               style="text-align: center; font-size: 2.2rem; letter-spacing: 0.45em; font-weight: 700; height: 84px; border-radius: 16px; border: 2px solid var(--primary); background: #FAF9F7;"
                               placeholder="000000">
                        @error('codigoIngresado') <p class="campo-error">{{ $message }}</p> @enderror
                    </div>

                    <button wire:click="verificarYCrearCuenta" class="btn-submit">
                        <span wire:loading.remove wire:target="verificarYCrearCuenta">Finalizar Registro</span>
                        <span wire:loading wire:target="verificarYCrearCuenta" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Verificando código...
                        </span>
                    </button>

                    <div style="margin-top: 1.5rem; text-align: center;">
                        <button wire:click="enviarNuevoCodigo" wire:loading.attr="disabled" class="text-[12px] text-[#E07A5F] font-bold uppercase tracking-wider hover:underline">
                            ¿No llegó el código? Reenviar código
                        </button>
                        <br>
                        <button wire:click="$set('step', 1)" class="text-[12px] text-[#ADB5BD] font-bold uppercase tracking-wider mt-3 hover:underline inline-flex items-center gap-1">
                            <span>←</span> Corregir datos de registro
                        </button>
                    </div>
                </div>
            @endif
            
            <p class="form-footer">
                ¿Ya tienes una cuenta registrada? 
                <a href="{{ route('login') }}" wire:navigate>Inicia sesión aquí</a>
            </p>
        </div>
    </div>
</div>
