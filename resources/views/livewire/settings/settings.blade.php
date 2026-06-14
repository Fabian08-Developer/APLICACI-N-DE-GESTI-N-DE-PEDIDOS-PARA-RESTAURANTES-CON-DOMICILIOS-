<div class="w-full pb-20 pt-4 px-4 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-black text-[#2C241B] tracking-tight uppercase">Configuración</h1>
        <p class="text-[#5C5246] mt-1 text-sm font-medium">Administra tu cuenta, negocio y preferencias operativas</p>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">
        {{-- Sidebar Tabs Espresso --}}
        <div class="w-full lg:w-80 shrink-0">
            <div class="bg-white rounded-[32px] border border-[#2C241B]/10 p-4 space-y-2 shadow-2xl">
                <button 
                    wire:click="setTab('perfil')"
                    class="w-full flex items-center gap-4 px-4 py-3.5 rounded-xl font-black transition-all uppercase text-[9px] tracking-widest {{ $activeTab === 'perfil' ? 'bg-[#E07A5F] text-white shadow-xl shadow-orange-900/20' : 'text-[#5C5246] hover:bg-[#FDFBF7] hover:text-[#2C241B]' }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Mi Perfil
                </button>

                @if(!auth()->user()->hasRole('super-admin'))
                <button 
                    wire:click="setTab('negocio')"
                    class="w-full flex items-center gap-4 px-4 py-3.5 rounded-xl font-black transition-all uppercase text-[9px] tracking-widest {{ $activeTab === 'negocio' ? 'bg-[#E07A5F] text-white shadow-xl shadow-orange-900/20' : 'text-[#5C5246] hover:bg-[#FDFBF7] hover:text-[#2C241B]' }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Mi Negocio
                </button>
                @endif

                <button 
                    wire:click="setTab('seguridad')"
                    class="w-full flex items-center gap-4 px-4 py-3.5 rounded-xl font-black transition-all uppercase text-[9px] tracking-widest {{ $activeTab === 'seguridad' ? 'bg-[#E07A5F] text-white shadow-xl shadow-orange-900/20' : 'text-[#5C5246] hover:bg-[#FDFBF7] hover:text-[#2C241B]' }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Seguridad
                </button>
            </div>
        </div>

        {{-- Main Content Espresso --}}
        <div class="flex-1 max-w-4xl">
            <div class="bg-white rounded-[32px] border border-[#2C241B]/10 p-8 shadow-2xl relative overflow-hidden">
                @if($activeTab === 'perfil')
                    <div x-transition>
                        <h2 class="text-xl font-black text-[#2C241B] uppercase tracking-tight mb-8">Informacion Personal</h2>
                        
                        <div class="flex items-center gap-6 mb-8">
                            <div class="relative group">
                                <div class="w-20 h-20 rounded-2xl bg-[#FDFBF7] border border-[#2C241B]/10 flex items-center justify-center text-[#E07A5F] transition-all group-hover:border-[#E07A5F] shadow-xl">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <label class="absolute -bottom-1 -right-1 p-2 bg-[#E07A5F] text-white rounded-xl cursor-pointer shadow-xl hover:scale-110 transition-all border-2 border-white">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <input type="file" class="hidden">
                                </label>
                            </div>
                            <div>
                                <h4 class="text-[#2C241B] font-black text-lg uppercase tracking-tight">Foto de perfil</h4>
                                <p class="text-[#5C5246] font-medium mt-0.5 text-xs">JPG o PNG (Max 2MB)</p>
                            </div>
                        </div>

                        <form wire:submit.prevent="saveProfile" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[9px] font-black text-[#5C5246] uppercase tracking-widest mb-3">Nombre completo</label>
                                    <input wire:model="nombre_usuario" type="text" class="w-full px-6 py-3.5 bg-[#FDFBF7] border-[#2C241B]/10 rounded-xl text-[#2C241B] placeholder-[#8B8175] focus:ring-2 focus:ring-[#E07A5F] transition-all font-bold">
                                    @error('nombre_usuario') <span class="text-rose-500 text-[9px] font-black uppercase mt-2 block tracking-widest">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-[9px] font-black text-[#5C5246] uppercase tracking-widest mb-3">Correo Corporativo</label>
                                    <input wire:model="correo" type="email" class="w-full px-6 py-3.5 bg-[#FDFBF7] border-[#2C241B]/10 rounded-xl text-[#2C241B] placeholder-[#8B8175] focus:ring-2 focus:ring-[#E07A5F] transition-all font-bold">
                                    @error('correo') <span class="text-rose-500 text-[9px] font-black uppercase mt-2 block tracking-widest">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-[9px] font-black text-[#5C5246] uppercase tracking-widest mb-3">Teléfono de contacto</label>
                                    <input wire:model="telefono_usuario" type="text" class="w-full px-6 py-3.5 bg-[#FDFBF7] border-[#2C241B]/10 rounded-xl text-[#2C241B] placeholder-[#8B8175] focus:ring-2 focus:ring-[#E07A5F] transition-all font-bold">
                                    @error('telefono_usuario') <span class="text-rose-500 text-[9px] font-black uppercase mt-2 block tracking-widest">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-[9px] font-black text-[#5C5246] uppercase tracking-widest mb-3">Cargo / Rol</label>
                                    <div class="w-full px-6 py-3.5 bg-[#FDFBF7]/50 border border-[#2C241B]/10 rounded-xl text-[#5C5246] font-bold uppercase tracking-widest text-[10px] opacity-60">
                                        {{ $cargo }}
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end pt-4">
                                <button type="submit" class="inline-flex items-center gap-3 px-8 py-3.5 bg-[#E07A5F] hover:bg-[#C8644A] text-white font-black rounded-xl transition-all shadow-2xl shadow-orange-900/20 active:scale-95 uppercase text-[10px] tracking-widest">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                    Actualizar Perfil
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                @if($activeTab === 'negocio')
                    <div x-transition>
                        <h2 class="text-xl font-black text-[#2C241B] uppercase tracking-tight mb-8">Informacion del Negocio</h2>

                        <form wire:submit.prevent="saveBusiness" class="space-y-6">
                            <div>
                                <label class="block text-[9px] font-black text-[#5C5246] uppercase tracking-widest mb-3">Nombre de la Cafetería</label>
                                <input wire:model="nombre_empresa" type="text" class="w-full px-6 py-3.5 bg-[#FDFBF7] border-[#2C241B]/10 rounded-xl text-[#2C241B] placeholder-[#8B8175] focus:ring-2 focus:ring-[#E07A5F] transition-all font-bold">
                                @error('nombre_empresa') <span class="text-rose-500 text-[9px] font-black uppercase mt-2 block tracking-widest">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[9px] font-black text-[#5C5246] uppercase tracking-widest mb-3">NIT / Identificación fiscal</label>
                                    <div class="w-full px-6 py-3.5 bg-[#FDFBF7]/50 border border-[#2C241B]/10 rounded-xl text-[#5C5246] font-bold uppercase tracking-widest text-[10px] opacity-60">
                                        {{ $nit }}
                                    </div>
                                    <span class="text-[#5C5246] text-[9px] mt-2 block tracking-wider font-sans">
                                        * El NIT no es editable directamente. Actualícelo subiendo un documento de soporte en la sección inferior.
                                    </span>
                                </div>
                                <div>
                                    <label class="block text-[9px] font-black text-[#5C5246] uppercase tracking-widest mb-3">Tipo de establecimiento</label>
                                    <select wire:model="tipo_negocio" class="w-full px-6 py-3.5 bg-[#FDFBF7] border-[#2C241B]/10 rounded-xl text-[#2C241B] focus:ring-2 focus:ring-[#E07A5F] transition-all font-bold uppercase text-[10px] tracking-widest">
                                        <option value="Restaurante">Restaurante</option>
                                        <option value="Cafeteria">Cafetería</option>
                                        <option value="Panaderia">Panadería</option>
                                        <option value="Bar">Bar</option>
                                    </select>
                                </div>
                            </div>

                            <div class="flex justify-end pt-4">
                                <button type="submit" class="inline-flex items-center gap-3 px-8 py-3.5 bg-[#E07A5F] hover:bg-[#C8644A] text-white font-black rounded-xl transition-all shadow-2xl shadow-orange-900/20 active:scale-95 uppercase text-[10px] tracking-widest">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                    Guardar Configuración
                                </button>
                            </div>
                        </form>

                        <!-- Separador de Línea Premium -->
                        <div class="border-t border-[#2C241B]/10 my-8"></div>

                        <!-- Sección de Documentación / NIT -->
                        <div>
                            <h3 class="text-lg font-black text-[#2C241B] uppercase tracking-tight mb-4">Documentación del Negocio</h3>
                            <p class="text-[#5C5246] mb-6 text-xs font-medium font-sans">Visualiza, descarga y actualiza de manera segura el NIT (Número de Identificación Tributaria) de tu empresa.</p>

                            <div class="bg-[#FDFBF7] rounded-2xl border border-[#2C241B]/10 p-6 flex flex-col md:flex-row md:items-center justify-between gap-6">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 rounded-xl bg-orange-950/30 border border-[#E07A5F]/30 flex items-center justify-center text-[#E07A5F] shrink-0">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="text-[#2C241B] font-black text-sm uppercase tracking-tight">Archivo NIT de la Empresa</h4>
                                        @if($documento_path)
                                            <p class="text-emerald-500 font-black text-[9px] uppercase tracking-widest mt-1">✓ NIT cargado en el sistema</p>
                                        @else
                                            <p class="text-amber-500 font-black text-[9px] uppercase tracking-widest mt-1">⚠ Sin NIT registrado</p>
                                        @endif
                                    </div>
                                </div>

                                @if($documento_path)
                                    <button type="button" wire:click="downloadDocument" class="inline-flex items-center gap-2 px-6 py-3 border border-[#E07A5F]/30 hover:border-[#E07A5F] bg-[#E07A5F]/10 hover:bg-[#E07A5F]/20 text-[#2C241B] font-black rounded-xl transition-all shadow-md active:scale-95 uppercase text-[9px] tracking-widest shrink-0">
                                        <svg class="w-4 h-4 text-[#E07A5F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        Descargar NIT Actual
                                    </button>
                                @endif
                            </div>

                            @if($documento_pendiente_path)
                                <div class="mt-4 bg-[#78350F]/20 border border-[#E07A5F]/40 rounded-2xl p-6 flex items-start gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-orange-950/50 border border-[#E07A5F]/30 flex items-center justify-center text-[#E07A5F] shrink-0">
                                        <svg class="animate-pulse w-5 h-5 text-[#E07A5F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="text-amber-500 font-black text-xs uppercase tracking-wider">Solicitud de Actualización en Trámite</h4>
                                        <p class="text-[#8B8175] text-[11px] mt-1 leading-relaxed font-sans">
                                            Has enviado un nuevo documento NIT para revisión. El Super Administrador está revisando tu solicitud. Te notificaremos por correo electrónico una vez sea aprobada o rechazada.
                                        </p>
                                    </div>
                                </div>
                            @endif

                            <!-- Formulario de actualización de NIT -->
                            <div class="mt-6 bg-white/50 rounded-2xl border border-[#2C241B]/10 p-6">
                                <h4 class="text-[#2C241B] font-black text-xs uppercase tracking-widest mb-4">Actualizar Documento NIT</h4>
                                
                                @if($documento_pendiente_path)
                                    <p class="text-[#5C5246] text-[9px] font-black uppercase tracking-widest py-2">
                                        ⚠ Hay una solicitud de actualización en revisión. No es posible enviar una nueva hasta que sea procesada.
                                    </p>
                                @else
                                    <div class="space-y-4">
                                        <div class="relative">
                                            <label class="block text-[9px] font-black text-[#5C5246] uppercase tracking-widest mb-3">Selecciona un nuevo archivo (PDF, DOC, DOCX - Máx 10MB)</label>
                                            <input type="file" wire:model="nuevo_documento" id="nuevo_documento" class="hidden" accept=".pdf,.doc,.docx">
                                            
                                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4">
                                                <label for="nuevo_documento" class="flex items-center justify-center gap-3 px-6 py-3.5 bg-[#FDFBF7] border border-[#2C241B]/10 hover:border-stone-700 rounded-xl text-[#2C241B] font-bold cursor-pointer transition-all hover:bg-[#FDFBF7]/50 uppercase text-[10px] tracking-widest grow text-center sm:text-left">
                                                    <svg class="w-5 h-5 text-[#5C5246]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                                    </svg>
                                                    <span>
                                                        @if($nuevo_documento)
                                                            {{ $nuevo_documento->getClientOriginalName() }}
                                                        @else
                                                            Seleccionar archivo...
                                                        @endif
                                                    </span>
                                                </label>

                                                <button type="button" wire:click="iniciarActualizacionDocumento" 
                                                        class="inline-flex items-center justify-center gap-3 px-8 py-3.5 bg-[#E07A5F] hover:bg-[#C8644A] text-white font-black rounded-xl transition-all shadow-xl shadow-orange-900/10 active:scale-95 uppercase text-[10px] tracking-widest disabled:opacity-50 disabled:pointer-events-none"
                                                        @if(!$nuevo_documento) disabled @endif>
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18.2"/>
                                                    </svg>
                                                    Actualizar NIT
                                                </button>
                                            </div>
                                        </div>
                                        @error('nuevo_documento') 
                                            <span class="text-rose-500 text-[9px] font-black uppercase mt-2 block tracking-widest">{{ $message }}</span> 
                                        @enderror

                                        <div wire:loading wire:target="nuevo_documento" class="text-[#5C5246] text-[9px] font-black uppercase tracking-widest mt-2">
                                            <span class="inline-flex items-center gap-2">
                                                <svg class="animate-spin h-3 w-3 text-[#E07A5F]" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                Subiendo archivo temporal...
                                            </span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Modal de Validación OTP de 6 Dígitos -->
                        @if($showOtpModal)
                            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#2C241B]/80 backdrop-blur-sm animate-in fade-in duration-300">
                                <div class="bg-white border border-[#E07A5F]/30 p-8 rounded-[32px] max-w-md w-full shadow-2xl shadow-orange-950/20 relative overflow-hidden">
                                    
                                    {{-- Accent Top Bar --}}
                                    <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-amber-600 to-orange-800"></div>

                                    <div class="w-16 h-16 rounded-full bg-orange-950/30 border border-[#E07A5F]/30 flex items-center justify-center mb-6">
                                        <svg class="w-8 h-8 text-[#E07A5F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                    </div>

                                    <h3 class="text-xl font-black text-[#2C241B] mb-2 tracking-tight uppercase">Autenticación de Seguridad</h3>
                                    <p class="text-[#8B8175] text-xs mb-6 leading-relaxed font-medium">
                                        @if($pendingAction === 'profile')
                                            Para confirmar la actualización de tu perfil de usuario, introduce el código de verificación de 6 dígitos que hemos enviado a tu correo corporativo.
                                        @elseif($pendingAction === 'business')
                                            Para confirmar los cambios en la información de tu negocio, introduce el código de verificación de 6 dígitos que hemos enviado a tu correo corporativo.
                                        @else
                                            Para autorizar la actualización del archivo NIT, introduce el código de verificación de 6 dígitos que hemos enviado a tu correo corporativo.
                                        @endif
                                    </p>
                                    
                                    <div class="mb-6">
                                        <label class="block text-[9px] font-black text-[#5C5246] uppercase tracking-widest mb-3 text-center">Código OTP de 6 Dígitos</label>
                                        <input type="text" wire:model="otpIngresado" maxlength="6" placeholder="000000" 
                                               class="w-full bg-[#FDFBF7] border border-[#2C241B]/10 rounded-2xl py-4 text-center text-3xl font-black tracking-[0.75em] pl-[0.75em] text-[#2C241B] placeholder-[#8B8175] focus:border-[#E07A5F] focus:ring-0 outline-none transition-colors">
                                        
                                        @if($errorOtp) 
                                            <span class="text-rose-500 text-[10px] font-black uppercase mt-3 block tracking-widest text-center">{{ $errorOtp }}</span> 
                                        @endif
                                    </div>
                                    
                                    <div class="flex gap-4">
                                        <button type="button" wire:click="cancelActualizacionDocumento" class="flex-1 py-4 text-[#8B8175] font-black text-[10px] uppercase tracking-widest hover:text-[#2C241B] hover:bg-[#E6E2DB] rounded-2xl transition-all">
                                            Cancelar
                                        </button>
                                        <button type="button" wire:click="confirmarOtp" class="flex-1 bg-[#E07A5F] hover:bg-[#C8644A] text-white py-4 rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-lg shadow-orange-900/30 transition-all active:scale-95">
                                            Confirmar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                @if($activeTab === 'seguridad')
                    <div x-transition>
                        <h2 class="text-xl font-black text-[#2C241B] uppercase tracking-tight mb-8">Gestión de Acceso</h2>

                        <form wire:submit.prevent="changePassword" class="max-w-xl space-y-6">
                            <div>
                                <label class="block text-[9px] font-black text-[#5C5246] uppercase tracking-widest mb-3">Contrasena actual</label>
                                <input wire:model="current_password" type="password" class="w-full px-6 py-3.5 bg-[#FDFBF7] border-[#2C241B]/10 rounded-xl text-[#2C241B] placeholder-[#8B8175] focus:ring-2 focus:ring-[#E07A5F] transition-all font-bold">
                                @error('current_password') <span class="text-rose-500 text-[9px] font-black uppercase mt-2 block tracking-widest">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-[9px] font-black text-[#5C5246] uppercase tracking-widest mb-3">Nueva contrasena de acceso</label>
                                <input wire:model="new_password" type="password" class="w-full px-6 py-3.5 bg-[#FDFBF7] border-[#2C241B]/10 rounded-xl text-[#2C241B] placeholder-[#8B8175] focus:ring-2 focus:ring-[#E07A5F] transition-all font-bold">
                                @error('new_password') <span class="text-rose-500 text-[9px] font-black uppercase mt-2 block tracking-widest">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-[9px] font-black text-[#5C5246] uppercase tracking-widest mb-3">Confirmar nueva contrasena</label>
                                <input wire:model="new_password_confirmation" type="password" class="w-full px-6 py-3.5 bg-[#FDFBF7] border-[#2C241B]/10 rounded-xl text-[#2C241B] placeholder-[#8B8175] focus:ring-2 focus:ring-[#E07A5F] transition-all font-bold">
                            </div>

                            <div class="pt-4">
                                <button type="submit" class="inline-flex items-center gap-3 px-8 py-3.5 bg-[#E07A5F] hover:bg-[#C8644A] text-white font-black rounded-xl transition-all shadow-2xl shadow-orange-900/20 active:scale-95 uppercase text-[10px] tracking-widest">
                                    Actualizar Credenciales
                                </button>
                            </div>
                        </form>

                        {{-- Zona de Peligro (solo visible para no super-admins) --}}
                        @if(!auth()->user()->hasRole('super-admin'))
                        <div class="mt-16 pt-12 border-t border-rose-900/30">
                            <h3 class="text-xl font-black text-rose-500 uppercase tracking-tight mb-4">Zona de Peligro</h3>
                            <p class="text-[#8B8175] text-sm mb-6 max-w-xl leading-relaxed">
                                Eliminar tu cuenta borrará permanentemente toda tu información personal y los datos del negocio (sucursales, pedidos, etc). Esta acción <span class="font-bold text-rose-400">no se puede deshacer</span>.
                            </p>
                            
                            <button wire:click="confirmAccountDeletion" class="inline-flex items-center gap-4 px-8 py-4 border border-rose-500/50 hover:bg-rose-500 hover:text-[#2C241B] text-rose-500 font-black rounded-2xl transition-all shadow-lg shadow-rose-900/10 active:scale-95 uppercase text-xs tracking-widest">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Eliminar Cuenta y Negocio
                            </button>
                        </div>
                        @endif

                        {{-- Modal de Confirmación de Borrado --}}
                        @if($confirmingAccountDeletion)
                            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#2C241B]/80 backdrop-blur-sm animate-in fade-in duration-300">
                                <div class="bg-white border border-rose-500/30 p-8 rounded-[32px] max-w-md w-full shadow-2xl shadow-rose-900/20">
                                    <div class="w-16 h-16 rounded-full bg-rose-500/10 flex items-center justify-center mb-6">
                                        <svg class="w-8 h-8 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-2xl font-black text-[#2C241B] mb-2 tracking-tight">¿Estás seguro?</h3>
                                    <p class="text-[#8B8175] text-sm mb-6">
                                        Ingresa tu contraseña para confirmar que deseas eliminar tu cuenta de gerente y <strong class="text-rose-400">toda la información del negocio</strong>.
                                    </p>
                                    
                                    <div class="mb-8">
                                        <input type="password" wire:model="passwordForDeletion" placeholder="Tu contraseña actual" 
                                               class="w-full bg-[#FDFBF7] border border-[#2C241B]/10 rounded-2xl py-4 px-6 text-[#2C241B] placeholder-[#8B8175] focus:border-rose-500 focus:ring-0 outline-none">
                                        @error('passwordForDeletion') <span class="text-rose-500 text-[10px] font-black uppercase mt-2 block tracking-widest">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div class="flex gap-4">
                                        <button wire:click="cancelAccountDeletion" class="flex-1 py-4 text-[#8B8175] font-bold text-xs uppercase tracking-widest hover:text-[#2C241B] hover:bg-[#E6E2DB] rounded-2xl transition-colors">
                                            Cancelar
                                        </button>
                                        <button wire:click="deleteAccount" class="flex-1 bg-rose-600 hover:bg-rose-700 text-white py-4 rounded-2xl font-black text-xs uppercase tracking-widest shadow-lg shadow-rose-900/30 transition-colors">
                                            Sí, Eliminar Todo
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
