<div class="p-6 lg:p-8 space-y-8 bg-[var(--bg-main)] min-h-screen">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-[#2C241B] uppercase tracking-tight">Panel de Control Global</h1>
            <p class="text-[#5C5246] text-sm mt-1">Bienvenido al centro de mando de la plataforma.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="flex h-3 w-3 relative">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
            </span>
            <span class="text-xs font-bold text-emerald-500 uppercase tracking-widest">Sistema Operativo</span>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Total Empresas --}}
        <div class="bg-white p-6 rounded-[32px] border border-[#2C241B]/10 shadow-sm group hover:border-[#E07A5F]/30 transition-all">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-[#E07A5F]/10 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-building text-[#E07A5F]"></i>
                </div>
                <span class="text-[10px] font-black text-[#5C5246] uppercase tracking-widest">Tenants</span>
            </div>
            <div class="text-4xl font-black text-[#2C241B] mb-1">{{ $totalEmpresas }}</div>
            <div class="text-sm font-bold text-[#5C5246] uppercase tracking-tight">Empresas Registradas</div>
        </div>

        {{-- Total Sucursales --}}
        <div class="bg-white p-6 rounded-[32px] border border-[#2C241B]/10 shadow-sm group hover:border-amber-500/30 transition-all">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-amber-500/10 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-store text-amber-500"></i>
                </div>
                <span class="text-[10px] font-black text-[#5C5246] uppercase tracking-widest">Sedes</span>
            </div>
            <div class="text-4xl font-black text-[#2C241B] mb-1">{{ $totalSucursales }}</div>
            <div class="text-sm font-bold text-[#5C5246] uppercase tracking-tight">Sucursales Activas</div>
        </div>

        {{-- Total Usuarios --}}
        <div class="bg-white p-6 rounded-[32px] border border-[#2C241B]/10 shadow-sm group hover:border-blue-500/30 transition-all">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-blue-500/10 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-users text-blue-500"></i>
                </div>
                <span class="text-[10px] font-black text-[#5C5246] uppercase tracking-widest">Comunidad</span>
            </div>
            <div class="text-4xl font-black text-[#2C241B] mb-1">{{ $totalUsuarios }}</div>
            <div class="text-sm font-bold text-[#5C5246] uppercase tracking-tight">Usuarios Totales</div>
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- Empresas Recientes --}}
        <div class="bg-white rounded-[40px] border border-[#2C241B]/10 overflow-hidden shadow-sm">
            <div class="p-8 border-b border-[#2C241B]/10 flex items-center justify-between">
                <h2 class="text-lg font-black text-[#2C241B] uppercase tracking-tighter">Empresas Recientes</h2>
                <a href="#" class="text-[10px] font-black text-[#E07A5F] uppercase tracking-widest hover:text-amber-400 transition-colors">Ver Todas</a>
            </div>
            <div class="p-4">
                @forelse($empresasRecientes as $empresa)
                    <div class="flex items-center justify-between p-4 rounded-3xl hover:bg-[#2C241B]/10]/30 transition-all group">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-[#FDFBF7] border border-[#2C241B]/10 rounded-2xl flex items-center justify-center text-[#5C5246] group-hover:border-[#E07A5F]/30 group-hover:text-[#E07A5F] transition-all">
                                <i class="fas fa-utensils"></i>
                            </div>
                            <div>
                                <div class="font-bold text-[#2C241B]">{{ $empresa->nombre }}</div>
                                <div class="text-[10px] text-[#5C5246] font-bold uppercase tracking-widest">NIT: {{ $empresa->nit }}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span @class([
                                'px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest',
                                'bg-emerald-500/10 text-emerald-500' => $empresa->activo,
                                'bg-rose-500/10 text-rose-500' => !$empresa->activo,
                            ])>
                                {{ $empresa->activo ? 'Activo' : 'Suspendido' }}
                            </span>
                            <i class="fas fa-chevron-right text-stone-700 text-xs"></i>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center">
                        <i class="fas fa-folder-open text-stone-800 text-4xl mb-4"></i>
                        <p class="text-[#5C5246] text-xs font-bold uppercase tracking-widest">No hay empresas registradas aún</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Soporte y Herramientas --}}
        <div class="space-y-6">
            <div class="bg-white p-8 rounded-[40px] border border-[#2C241B]/10 shadow-sm">
                <h2 class="text-lg font-black text-[#2C241B] uppercase tracking-tighter mb-6">Herramientas Rápidas</h2>
                <div class="grid grid-cols-2 gap-4">
                    <a href="{{ route('super-admin.users') }}" wire:navigate class="flex flex-col items-center justify-center p-6 rounded-[32px] bg-[#FDFBF7] border border-[#2C241B]/10 hover:border-[#E07A5F]/40 transition-all group text-center">
                        <i class="fas fa-search text-[#E07A5F] mb-3 group-hover:scale-110 transition-transform"></i>
                        <span class="text-[10px] font-black text-[#8B8175] uppercase tracking-widest">Buscar Usuario</span>
                    </a>
                    <a href="{{ route('super-admin.tenants') }}" wire:navigate class="flex flex-col items-center justify-center p-6 rounded-[32px] bg-[#FDFBF7] border border-[#2C241B]/10 hover:border-amber-500/40 transition-all group text-center">
                        <i class="fas fa-plus text-amber-500 mb-3 group-hover:scale-110 transition-transform"></i>
                        <span class="text-[10px] font-black text-[#8B8175] uppercase tracking-widest">Nueva Empresa</span>
                    </a>
                    <button wire:click="$set('showVariablesModal', true)" class="flex flex-col items-center justify-center p-6 rounded-[32px] bg-[#FDFBF7] border border-[#2C241B]/10 hover:border-blue-500/40 transition-all group text-center">
                        <i class="fas fa-cogs text-blue-500 mb-3 group-hover:scale-110 transition-transform"></i>
                        <span class="text-[10px] font-black text-[#8B8175] uppercase tracking-widest">Variables Globales</span>
                    </button>
                    <button wire:click="$set('showAvisoModal', true)" class="flex flex-col items-center justify-center p-6 rounded-[32px] bg-[#FDFBF7] border border-[#2C241B]/10 hover:border-rose-500/40 transition-all group text-center">
                        <i class="fas fa-bullhorn text-rose-500 mb-3 group-hover:scale-110 transition-transform"></i>
                        <span class="text-[10px] font-black text-[#8B8175] uppercase tracking-widest">Aviso Masivo</span>
                    </button>
                </div>
            </div>

            {{-- Versión y Mantenimiento --}}
            <div class="bg-gradient-to-br from-[#E07A5F]/20 to-[#FDFBF7] p-8 rounded-[40px] border border-[#E07A5F]/20 relative overflow-hidden">
                <div class="relative z-10">
                    <h2 class="text-lg font-black text-[#2C241B] uppercase tracking-tighter mb-2">Estado de la Plataforma</h2>
                    <p class="text-[#8B8175] text-xs leading-relaxed mb-6 font-bold">
                        Versión actual: <span class="text-[#2C241B] font-black">{{ $version_actual }}</span>. <br>
                        Próximo mantenimiento: <span class="text-[#2C241B] font-black">{{ $mantenimiento_fecha }}</span>.
                    </p>
                    <button wire:click="$set('showVersionesModal', true)" class="bg-[#2C241B] text-white px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-[#5C5246] transition-all shadow-xl">
                        Gestionar Versiones
                    </button>
                </div>
                <i class="fas fa-shield-alt absolute -bottom-4 -right-4 text-8xl text-[#2C241B]/5 rotate-12"></i>
            </div>
        </div>
    </div>

    {{-- MODAL VARIABLES GLOBALES --}}
    @if($showVariablesModal)
    <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-[#FDFBF7] backdrop-blur-xl" x-transition>
        <div class="bg-white rounded-[40px] shadow-2xl w-full max-w-lg border border-[#2C241B]/10 overflow-hidden relative">
            <div class="px-10 py-8 border-b border-[#2C241B]/10 flex items-center justify-between bg-[#FDFBF7]">
                <div>
                    <h3 class="text-2xl font-black text-[#2C241B] uppercase tracking-tight">Variables Globales</h3>
                    <p class="text-[#5C5246] text-xs mt-1 font-bold">Configuración del sistema</p>
                </div>
                <button wire:click="$set('showVariablesModal', false)" class="p-3 rounded-xl hover:bg-[#E6E2DB] text-[#5C5246] transition-colors border border-[#2C241B]/10 hover:border-[#2C241B]/10">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <form wire:submit.prevent="actualizarVariables" class="p-10 space-y-6">
                <div>
                    <label class="block text-[9px] font-black text-[#5C5246] uppercase tracking-widest mb-3">Nombre de la Plataforma</label>
                    <input wire:model="plataforma_nombre" type="text" class="w-full px-6 py-4 bg-[#FDFBF7] border border-[#2C241B]/10 rounded-2xl text-[#2C241B] placeholder-stone-700 focus:ring-2 focus:ring-[#E07A5F] focus:border-[#2C241B]/10 transition-all font-bold text-sm outline-none">
                    @error('plataforma_nombre') <span class="text-xs text-rose-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-[9px] font-black text-[#5C5246] uppercase tracking-widest mb-3">Correo Electrónico de Soporte</label>
                    <input wire:model="soporte_correo" type="email" class="w-full px-6 py-4 bg-[#FDFBF7] border border-[#2C241B]/10 rounded-2xl text-[#2C241B] placeholder-stone-700 focus:ring-2 focus:ring-[#E07A5F] focus:border-[#2C241B]/10 transition-all font-bold text-sm outline-none">
                    @error('soporte_correo') <span class="text-xs text-rose-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[9px] font-black text-[#5C5246] uppercase tracking-widest mb-3">Límite de Carga (MB)</label>
                        <input wire:model="subida_limite" type="number" class="w-full px-6 py-4 bg-[#FDFBF7] border border-[#2C241B]/10 rounded-2xl text-[#2C241B] placeholder-stone-700 focus:ring-2 focus:ring-[#E07A5F] focus:border-[#2C241B]/10 transition-all font-bold text-sm outline-none">
                        @error('subida_limite') <span class="text-xs text-rose-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex flex-col justify-end">
                        <div class="flex items-center justify-between p-4 bg-[#FDFBF7] rounded-2xl border border-[#2C241B]/10">
                            <span class="text-[10px] font-black text-[#2C241B] uppercase tracking-widest">Nuevos Registros</span>
                            <button type="button" wire:click="$set('registro_abierto', {{ !$registro_abierto ? 'true' : 'false' }})" 
                                class="relative inline-flex h-6 w-11 rounded-full border-2 border-[#2C241B]/10 transition-colors duration-300 {{ $registro_abierto ? 'bg-[#E07A5F]' : 'bg-[#E6E2DB]' }}">
                                <span class="inline-block h-5 w-5 transform rounded-full bg-white transition duration-300 shadow-xl {{ $registro_abierto ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-6 border-t border-[#2C241B]/10">
                    <button type="button" wire:click="$set('showVariablesModal', false)" class="flex-1 px-6 py-4 bg-[#FDFBF7] text-[#5C5246] font-black rounded-xl transition-all uppercase text-[9px] tracking-widest border border-[#2C241B]/10 hover:bg-[#E6E2DB]">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 px-6 py-4 bg-[#E07A5F] text-white font-black rounded-xl transition-all shadow-xl uppercase text-[9px] tracking-widest hover:bg-amber-700">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- MODAL AVISO MASIVO --}}
    @if($showAvisoModal)
    <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-[#FDFBF7] backdrop-blur-xl" x-transition>
        <div class="bg-white rounded-[40px] shadow-2xl w-full max-w-lg border border-[#2C241B]/10 overflow-hidden relative">
            <div class="px-10 py-8 border-b border-[#2C241B]/10 flex items-center justify-between bg-[#FDFBF7]">
                <div>
                    <h3 class="text-2xl font-black text-[#2C241B] uppercase tracking-tight">Aviso Masivo</h3>
                    <p class="text-[#5C5246] text-xs mt-1 font-bold">Enviar notificaciones a todos los tenants</p>
                </div>
                <button wire:click="$set('showAvisoModal', false)" class="p-3 rounded-xl hover:bg-[#E6E2DB] text-[#5C5246] transition-colors border border-[#2C241B]/10 hover:border-[#2C241B]/10">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <form wire:submit.prevent="publicarAviso" class="p-10 space-y-6">
                @if($aviso_activo)
                <div class="bg-[#7f1d1d]/30 border border-[#b91c1c]/40 p-4 rounded-2xl flex items-center justify-between">
                    <div>
                        <div class="text-[10px] font-black text-[#fca5a5] uppercase tracking-wider">Aviso Activo Actual</div>
                        <div class="text-xs text-[#fecdd3] mt-1 font-bold">{{ $aviso_titulo }}</div>
                    </div>
                    <button type="button" wire:click="desactivarAviso" class="px-4 py-2 bg-[#b91c1c] text-[#2C241B] text-[9px] font-black uppercase tracking-widest rounded-xl hover:bg-red-700 transition-colors">
                        Desactivar
                    </button>
                </div>
                @endif

                <div>
                    <label class="block text-[9px] font-black text-[#5C5246] uppercase tracking-widest mb-3">Título del Aviso</label>
                    <input wire:model="aviso_titulo" type="text" class="w-full px-6 py-4 bg-[#FDFBF7] border border-[#2C241B]/10 rounded-2xl text-[#2C241B] placeholder-stone-700 focus:ring-2 focus:ring-[#E07A5F] focus:border-[#2C241B]/10 transition-all font-bold text-sm outline-none" placeholder="Ej: Mantenimiento Programado este Domingo">
                    @error('aviso_titulo') <span class="text-xs text-rose-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-[9px] font-black text-[#5C5246] uppercase tracking-widest mb-3">Mensaje del Aviso</label>
                    <textarea wire:model="aviso_mensaje" rows="3" class="w-full px-6 py-4 bg-[#FDFBF7] border border-[#2C241B]/10 rounded-2xl text-[#2C241B] placeholder-stone-700 focus:ring-2 focus:ring-[#E07A5F] focus:border-[#2C241B]/10 transition-all font-bold text-sm outline-none resize-none" placeholder="Escribe el cuerpo del aviso..."></textarea>
                    @error('aviso_mensaje') <span class="text-xs text-rose-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-[#FDFBF7] rounded-2xl border border-[#2C241B]/10">
                        <div>
                            <div class="text-[10px] font-black text-[#2C241B] uppercase tracking-widest">Mostrar Banner Superior</div>
                            <div class="text-[9px] text-[#5C5246] font-bold uppercase mt-0.5">Se dibujará en la parte superior de las pantallas</div>
                        </div>
                        <button type="button" wire:click="$set('aviso_mostrar_banner', {{ !$aviso_mostrar_banner ? 'true' : 'false' }})" 
                            class="relative inline-flex h-6 w-11 rounded-full border-2 border-[#2C241B]/10 transition-colors duration-300 {{ $aviso_mostrar_banner ? 'bg-[#E07A5F]' : 'bg-[#E6E2DB]' }}">
                            <span class="inline-block h-5 w-5 transform rounded-full bg-white transition duration-300 shadow-xl {{ $aviso_mostrar_banner ? 'translate-x-5' : 'translate-x-0' }}"></span>
                        </button>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-[#FDFBF7] rounded-2xl border border-[#2C241B]/10">
                        <div>
                            <div class="text-[10px] font-black text-[#2C241B] uppercase tracking-widest">Enviar a Bandeja Histórica</div>
                            <div class="text-[9px] text-[#5C5246] font-bold uppercase mt-0.5">Crea una notificación en el buzón de cada usuario</div>
                        </div>
                        <button type="button" wire:click="$set('aviso_guardar_historial', {{ !$aviso_guardar_historial ? 'true' : 'false' }})" 
                            class="relative inline-flex h-6 w-11 rounded-full border-2 border-[#2C241B]/10 transition-colors duration-300 {{ $aviso_guardar_historial ? 'bg-[#E07A5F]' : 'bg-[#E6E2DB]' }}">
                            <span class="inline-block h-5 w-5 transform rounded-full bg-white transition duration-300 shadow-xl {{ $aviso_guardar_historial ? 'translate-x-5' : 'translate-x-0' }}"></span>
                        </button>
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-6 border-t border-[#2C241B]/10">
                    <button type="button" wire:click="$set('showAvisoModal', false)" class="flex-1 px-6 py-4 bg-[#FDFBF7] text-[#5C5246] font-black rounded-xl transition-all uppercase text-[9px] tracking-widest border border-[#2C241B]/10 hover:bg-[#E6E2DB]">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 px-6 py-4 bg-[#E07A5F] text-white font-black rounded-xl transition-all shadow-xl uppercase text-[9px] tracking-widest hover:bg-amber-700">
                        Publicar
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- MODAL GESTIONAR VERSIONES --}}
    @if($showVersionesModal)
    <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-[#FDFBF7] backdrop-blur-xl" x-transition>
        <div class="bg-white rounded-[40px] shadow-2xl w-full max-w-4xl border border-[#2C241B]/10 overflow-hidden relative">
            <div class="px-10 py-8 border-b border-[#2C241B]/10 flex items-center justify-between bg-[#FDFBF7]">
                <div>
                    <h3 class="text-2xl font-black text-[#2C241B] uppercase tracking-tight">Estado y Versiones</h3>
                    <p class="text-[#5C5246] text-xs mt-1 font-bold">Control de lanzamientos y mantenimientos del sistema</p>
                </div>
                <button wire:click="$set('showVersionesModal', false)" class="p-3 rounded-xl hover:bg-[#E6E2DB] text-[#5C5246] transition-colors border border-[#2C241B]/10 hover:border-[#2C241B]/10">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <div class="p-10 grid grid-cols-1 md:grid-cols-2 gap-8 max-h-[70vh] overflow-y-auto">
                {{-- Sección Izquierda: Próximo Mantenimiento y Agregar Versión --}}
                <div class="space-y-6">
                    <div>
                        <h4 class="text-xs font-black text-[#2C241B] uppercase tracking-wider mb-4 border-b border-[#2C241B]/10 pb-2">Programación de Mantenimiento</h4>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[9px] font-black text-[#5C5246] uppercase tracking-widest mb-2">Fecha / Detalle</label>
                                <div class="flex gap-2">
                                    <input wire:model="mantenimiento_fecha" type="text" class="flex-1 px-4 py-3 bg-[#FDFBF7] border border-[#2C241B]/10 rounded-xl text-[#2C241B] placeholder-stone-700 focus:ring-2 focus:ring-[#E07A5F] focus:border-[#2C241B]/10 transition-all font-bold text-xs outline-none" placeholder="Ej: Domingo 25 de Mayo, 02:00 AM">
                                    <button type="button" wire:click="actualizarMantenimiento" class="px-4 bg-[#E07A5F] text-white rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-amber-700 transition-all shadow">
                                        Fijar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-xs font-black text-[#2C241B] uppercase tracking-wider mb-4 border-b border-[#2C241B]/10 pb-2">Registrar Nueva Versión</h4>
                        <form wire:submit.prevent="agregarVersion" class="space-y-4">
                            <div>
                                <label class="block text-[9px] font-black text-[#5C5246] uppercase tracking-widest mb-2">Número de Versión</label>
                                <input wire:model="version_nueva_numero" type="text" class="w-full px-4 py-3 bg-[#FDFBF7] border border-[#2C241B]/10 rounded-xl text-[#2C241B] placeholder-stone-700 focus:ring-2 focus:ring-[#E07A5F] focus:border-[#2C241B]/10 transition-all font-bold text-xs outline-none" placeholder="Ej: v1.3.0">
                                @error('version_nueva_numero') <span class="text-xs text-rose-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-[9px] font-black text-[#5C5246] uppercase tracking-widest mb-2">Notas de Lanzamiento</label>
                                <textarea wire:model="version_nueva_notas" name="version_nueva_notas" rows="3" class="w-full px-4 py-3 bg-[#FDFBF7] border border-[#2C241B]/10 rounded-xl text-[#2C241B] placeholder-stone-700 focus:ring-2 focus:ring-[#E07A5F] focus:border-[#2C241B]/10 transition-all font-bold text-xs outline-none resize-none" placeholder="Ej: Implementación de Quick Tools y parches..."></textarea>
                                @error('version_nueva_notas') <span class="text-xs text-rose-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <button type="submit" class="w-full px-4 py-3 bg-[#FDFBF7] border border-[#2C241B]/10 text-[#2C241B] rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-[#E07A5F] hover:border-[#E07A5F] transition-all">
                                Guardar e Implementar
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Sección Derecha: Historial de Versiones --}}
                <div class="flex flex-col">
                    <h4 class="text-xs font-black text-[#2C241B] uppercase tracking-wider mb-4 border-b border-[#2C241B]/10 pb-2">Historial de Versiones</h4>
                    <div class="flex-1 overflow-y-auto max-h-80 space-y-4 pr-2">
                        @forelse($versiones_lista as $index => $v)
                        <div class="p-4 bg-[#FDFBF7] rounded-2xl border border-[#2C241B]/10 hover:border-[#E07A5F]/20 transition-all relative group">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-black text-[#2C241B] uppercase tracking-tight">{{ $v['version'] }}</span>
                                <span class="text-[9px] text-[#5C5246] font-bold uppercase">{{ $v['fecha'] }}</span>
                            </div>
                            <p class="text-[10px] text-[#8B8175] font-medium leading-relaxed mb-1">{{ $v['notas'] }}</p>
                            
                            <button type="button" wire:click="eliminarVersion({{ $index }})" class="absolute top-2 right-2 p-1.5 bg-[#FDFBF7] rounded-lg border border-[#2C241B]/10 hover:border-red-500/30 text-[#5C5246] hover:text-red-500 transition-all opacity-0 group-hover:opacity-100" title="Eliminar registro">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                        @empty
                        <div class="py-12 text-center text-[#5C5246] font-black text-[10px] uppercase tracking-wider">
                            No hay versiones registradas.
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

