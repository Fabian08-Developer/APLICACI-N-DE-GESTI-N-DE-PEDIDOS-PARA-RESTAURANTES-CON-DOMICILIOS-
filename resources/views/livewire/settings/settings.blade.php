<div class="w-full pb-20 pt-4 px-4 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="mb-12 border-b border-[#2C241B]/10 pb-6">
        <h1 class="text-3xl font-black text-[#2C241B] tracking-tight uppercase">Configuración</h1>
        <p class="text-[#5C5246] mt-1 text-sm font-medium">Administra tu cuenta, negocio y preferencias operativas</p>
    </div>

    <div class="space-y-12 divide-y divide-[#2C241B]/10">
        
        {{-- Fila 1: Información Personal --}}
        <div class="space-y-6 pb-12">
            {{-- Encabezado de Sección --}}
            <div class="space-y-2">
                <div class="flex items-center gap-3 text-[#E07A5F]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <h2 class="text-lg font-black uppercase tracking-wider">Perfil de Usuario</h2>
                </div>
                <p class="text-xs text-[#5C5246] font-medium leading-relaxed font-sans max-w-3xl">
                    Gestiona tu información de contacto personal, tu rol asignado dentro del sistema y actualiza tu foto de perfil.
                </p>
            </div>

            {{-- Contenido de Sección: Formulario + Widget --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 max-w-7xl">
                {{-- Columna de Formulario --}}
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-[32px] border border-[#2C241B]/10 p-8 shadow-2xl relative overflow-hidden h-full">
                        <div class="flex flex-col sm:flex-row items-center gap-6 mb-8">
                            <div class="relative group shrink-0">
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
                            <div class="text-center sm:text-left">
                                <h4 class="text-[#2C241B] font-black text-md uppercase tracking-tight">Foto de perfil</h4>
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
                </div>

                {{-- Widget Lateral: Resumen de Cuenta --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-[32px] border border-[#2C241B]/10 p-8 shadow-2xl relative overflow-hidden h-full flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-xs font-black text-[#5C5246] uppercase tracking-widest">Resumen de Cuenta</h3>
                                <span class="px-3 py-1 bg-[#E07A5F]/10 text-[#E07A5F] text-[9px] font-black rounded-lg uppercase tracking-widest">
                                    {{ auth()->user()->hasRole('super-admin') ? 'Super Admin' : 'Gerente' }}
                                </span>
                            </div>
                            
                            <div class="space-y-4">
                                <div class="flex justify-between items-center py-2 border-b border-[#2C241B]/5">
                                    <span class="text-[10px] font-bold text-[#8B8175] uppercase">Estado</span>
                                    <span class="text-[10px] font-black text-emerald-500 uppercase tracking-wider flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Activo
                                    </span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-[#2C241B]/5">
                                    <span class="text-[10px] font-bold text-[#8B8175] uppercase">Miembro Desde</span>
                                    <span class="text-[10px] font-black text-[#2C241B]">
                                        {{ auth()->user()->created_at ? auth()->user()->created_at->format('d M, Y') : 'N/A' }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-[10px] font-bold text-[#8B8175] uppercase">ID de Usuario</span>
                                    <span class="text-[10px] font-black text-[#2C241B] font-mono">
                                        #{{ auth()->id() }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 pt-6 border-t border-[#2C241B]/10 bg-[#FDFBF7] -mx-8 -mb-8 p-6 space-y-2">
                            <h4 class="text-[10px] font-black text-[#2C241B] uppercase tracking-wider">Recordatorio</h4>
                            <p class="text-[10px] text-[#5C5246] font-medium leading-relaxed font-sans">
                                Mantén tu correo corporativo y teléfono actualizados para recibir alertas críticas sobre pedidos y cobros.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(!auth()->user()->hasRole('super-admin'))
        {{-- Fila 2: Información del Negocio --}}
        <div class="space-y-6 py-12">
            {{-- Encabezado de Sección --}}
            <div class="space-y-2">
                <div class="flex items-center gap-3 text-[#E07A5F]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <h2 class="text-lg font-black uppercase tracking-wider">Mi Negocio</h2>
                </div>
                <p class="text-xs text-[#5C5246] font-medium leading-relaxed font-sans max-w-3xl">
                    Administra el nombre de tu cafetería o restaurante, selecciona la categoría de tu local y visualiza o carga la documentación del NIT tributario.
                </p>
            </div>

            {{-- Contenido de Sección: Formulario + Widget --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 max-w-7xl">
                {{-- Columna de Formulario --}}
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-[32px] border border-[#2C241B]/10 p-8 shadow-2xl relative overflow-hidden h-full">
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
                            <h3 class="text-md font-black text-[#2C241B] uppercase tracking-tight mb-3">Documentación del Negocio</h3>
                            <p class="text-[#5C5246] mb-6 text-xs font-medium font-sans leading-relaxed">Visualiza, descarga y actualiza de manera segura el NIT (Número de Identificación Tributaria) de tu empresa.</p>

                            <div class="bg-[#FDFBF7] rounded-2xl border border-[#2C241B]/10 p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
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
                                        Descargar NIT
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
                    </div>
                </div>

                {{-- Widget Lateral: Información del Negocio --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-[32px] border border-[#2C241B]/10 p-8 shadow-2xl relative overflow-hidden h-full flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-xs font-black text-[#5C5246] uppercase tracking-widest">Información Comercial</h3>
                                <span class="px-3 py-1 bg-emerald-500/10 text-emerald-500 text-[9px] font-black rounded-lg uppercase tracking-widest">
                                    Facturación
                                </span>
                            </div>

                            <div class="space-y-4">
                                <div class="flex justify-between items-center py-2 border-b border-[#2C241B]/5">
                                    <span class="text-[10px] font-bold text-[#8B8175] uppercase">Estado NIT</span>
                                    @if($documento_pendiente_path)
                                        <span class="text-[10px] font-black text-amber-500 uppercase tracking-wider flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            En Revisión
                                        </span>
                                    @elseif($documento_path)
                                        <span class="text-[10px] font-black text-emerald-500 uppercase tracking-wider flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Verificado
                                        </span>
                                    @else
                                        <span class="text-[10px] font-black text-rose-500 uppercase tracking-wider flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Sin Registrar
                                        </span>
                                    @endif
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-[#2C241B]/5">
                                    <span class="text-[10px] font-bold text-[#8B8175] uppercase">Establecimiento</span>
                                    <span class="text-[10px] font-black text-[#2C241B] uppercase tracking-widest">
                                        {{ $tipo_negocio }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-[10px] font-bold text-[#8B8175] uppercase">Moneda</span>
                                    <span class="text-[10px] font-black text-[#2C241B]">
                                        COP ($)
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 pt-6 border-t border-[#2C241B]/10 bg-[#FDFBF7] -mx-8 -mb-8 p-6 space-y-2">
                            <h4 class="text-[10px] font-black text-[#2C241B] uppercase tracking-wider">⚠ Cumplimiento Fiscal</h4>
                            <p class="text-[10px] text-[#5C5246] font-medium leading-relaxed font-sans">
                                El NIT y el soporte en PDF son necesarios para autorizar las pasarelas de pago y emitir facturación electrónica válida a tus clientes.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Fila 3: Seguridad de Acceso --}}
        <div class="space-y-6 pt-12">
            {{-- Encabezado de Sección --}}
            <div class="space-y-2">
                <div class="flex items-center gap-3 text-[#E07A5F]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <h2 class="text-lg font-black uppercase tracking-wider">Seguridad y Acceso</h2>
                </div>
                <p class="text-xs text-[#5C5246] font-medium leading-relaxed font-sans max-w-3xl">
                    Protege tu cuenta actualizando tu contraseña periódicamente. Si eres gerente, también puedes realizar la eliminación completa de tu cuenta y de los datos del negocio desde aquí.
                </p>
            </div>

            {{-- Contenido de Sección: Formulario + Widget --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 max-w-7xl">
                {{-- Columna de Tarjetas --}}
                <div class="lg:col-span-2 space-y-8">
                    {{-- Tarjeta de Contraseña --}}
                    <div class="bg-white rounded-[32px] border border-[#2C241B]/10 p-8 shadow-2xl relative overflow-hidden">
                        <h3 class="text-md font-black text-[#2C241B] uppercase tracking-tight mb-6">Cambiar Contraseña</h3>
                        
                        <form wire:submit.prevent="changePassword" class="space-y-6">
                            <div>
                                <label class="block text-[9px] font-black text-[#5C5246] uppercase tracking-widest mb-3">Contraseña actual</label>
                                <input wire:model="current_password" type="password" class="w-full px-6 py-3.5 bg-[#FDFBF7] border-[#2C241B]/10 rounded-xl text-[#2C241B] placeholder-[#8B8175] focus:ring-2 focus:ring-[#E07A5F] transition-all font-bold">
                                @error('current_password') <span class="text-rose-500 text-[9px] font-black uppercase mt-2 block tracking-widest">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[9px] font-black text-[#5C5246] uppercase tracking-widest mb-3">Nueva contraseña de acceso</label>
                                    <input wire:model="new_password" type="password" class="w-full px-6 py-3.5 bg-[#FDFBF7] border-[#2C241B]/10 rounded-xl text-[#2C241B] placeholder-[#8B8175] focus:ring-2 focus:ring-[#E07A5F] transition-all font-bold">
                                    @error('new_password') <span class="text-rose-500 text-[9px] font-black uppercase mt-2 block tracking-widest">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-[9px] font-black text-[#5C5246] uppercase tracking-widest mb-3">Confirmar nueva contraseña</label>
                                    <input wire:model="new_password_confirmation" type="password" class="w-full px-6 py-3.5 bg-[#FDFBF7] border-[#2C241B]/10 rounded-xl text-[#2C241B] placeholder-[#8B8175] focus:ring-2 focus:ring-[#E07A5F] transition-all font-bold">
                                </div>
                            </div>

                            <div class="flex justify-end pt-4">
                                <button type="submit" class="inline-flex items-center gap-3 px-8 py-3.5 bg-[#E07A5F] hover:bg-[#C8644A] text-white font-black rounded-xl transition-all shadow-2xl shadow-orange-900/20 active:scale-95 uppercase text-[10px] tracking-widest">
                                    Actualizar Credenciales
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Tarjeta de Zona de Peligro --}}
                    @if(!auth()->user()->hasRole('super-admin'))
                    <div class="bg-white rounded-[32px] border border-rose-950/20 p-8 shadow-2xl relative overflow-hidden">
                        <h3 class="text-md font-black text-rose-500 uppercase tracking-tight mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-rose-500 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            Zona de Peligro
                        </h3>
                        <p class="text-[#8B8175] text-xs mb-6 leading-relaxed font-sans">
                            Eliminar tu cuenta borrará permanentemente toda tu información personal y los datos del negocio (sucursales, pedidos, etc). Esta acción <span class="font-bold text-rose-500">no se puede deshacer</span>.
                        </p>
                        
                        <button wire:click="confirmAccountDeletion" class="inline-flex items-center gap-4 px-8 py-4 border border-rose-500/50 hover:bg-rose-500 hover:text-[#2C241B] text-rose-500 font-black rounded-2xl transition-all shadow-lg shadow-rose-900/10 active:scale-95 uppercase text-xs tracking-widest w-full justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Eliminar Cuenta y Negocio
                        </button>
                    </div>
                    @endif
                </div>

                {{-- Widget Lateral: Seguridad --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-[32px] border border-[#2C241B]/10 p-8 shadow-2xl relative overflow-hidden h-full flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-xs font-black text-[#5C5246] uppercase tracking-widest">Seguridad de Cuenta</h3>
                                <span class="px-3 py-1 bg-emerald-500/10 text-emerald-500 text-[9px] font-black rounded-lg uppercase tracking-widest">
                                    Protegido
                                </span>
                            </div>

                            <div class="space-y-4">
                                <h4 class="text-[10px] font-black text-[#2C241B] uppercase tracking-wider mb-2">Recomendaciones:</h4>
                                
                                <div class="flex gap-3 items-start text-[10px] font-semibold text-[#5C5246]">
                                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span>Contraseña de mínimo 8 caracteres con números y símbolos.</span>
                                </div>
                                
                                <div class="flex gap-3 items-start text-[10px] font-semibold text-[#5C5246]">
                                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span>No compartas tus credenciales de acceso con el personal operativo.</span>
                                </div>

                                <div class="flex gap-3 items-start text-[10px] font-semibold text-[#5C5246]">
                                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span>Cambia tu clave cada 90 días para evitar intrusiones.</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 pt-6 border-t border-[#2C241B]/10 bg-[#FDFBF7] -mx-8 -mb-8 p-6 space-y-2">
                            <div class="flex items-center justify-between text-[10px] font-black text-[#2C241B] uppercase tracking-wider">
                                <span>Estado de Protección</span>
                                <span class="text-emerald-500">100% Seguro</span>
                            </div>
                            <div class="w-full bg-[#2C241B]/10 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-emerald-500 h-full w-full rounded-full"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
