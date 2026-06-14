<div class="w-full pb-10" 
    x-data="{ 
        showForm: @entangle('showModal'),
        isFetching: false
    }"
    @open-modal.window="showForm = true; isFetching = true"
    @data-loaded.window="isFetching = false"
>
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
        <div>
            <h1 class="text-3xl font-black text-[#2C241B] tracking-tight uppercase">Sucursales</h1>
            <p class="text-[#5C5246] mt-1 text-base font-medium">Administra tu red operativa</p>
        </div>
        <button @click="showForm = true; $wire.openCreateModal()" class="inline-flex items-center px-6 py-3 bg-[#E07A5F] hover:bg-[#C8644A] text-white font-black rounded-xl transition-all shadow-xl uppercase text-[10px] tracking-widest">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Nueva Sucursal
        </button>
    </div>

    {{-- Stats Grid Fluid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        @foreach(['Total Sucursales' => $stats['total'], 'Activas' => $stats['activas'], 'Inactivas' => $stats['inactivas']] as $label => $value)
            <div class="bg-white rounded-[24px] p-6 border border-[#2C241B]/10 shadow-xl">
                <p class="text-[#5C5246] text-[9px] font-black uppercase tracking-widest mb-2">{{ $label }}</p>
                <h3 class="text-3xl font-black {{ $label === 'Activas' ? 'text-emerald-500' : ($label === 'Inactivas' ? 'text-stone-700' : 'text-[#2C241B]') }} tracking-tighter">{{ $value }}</h3>
            </div>
        @endforeach
        <div class="bg-white rounded-[24px] p-6 border border-[#2C241B]/10 shadow-xl">
            <p class="text-[#5C5246] text-[9px] font-black uppercase tracking-widest mb-2">Ventas Mes</p>
            <h3 class="text-3xl font-black text-[#2C241B] tracking-tighter">${{ number_format($stats['ventas_mes'], 0, ',', '.') }}</h3>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white rounded-[24px] p-4 border border-[#2C241B]/10 shadow-xl mb-8 flex flex-col md:flex-row gap-4 items-center">
        <div class="relative flex-1 w-full">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-[#5C5246]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input wire:model.live.debounce.300ms="search" type="text" 
                class="block w-full pl-12 pr-4 py-3.5 bg-[#FDFBF7] border-[#2C241B]/10 rounded-xl text-[#2C241B] placeholder-[#8B8175] focus:ring-2 focus:ring-[#E07A5F] focus:bg-[#FDFBF7] transition-all font-medium text-sm" 
                placeholder="Buscar por nombre o ciudad...">
        </div>

        <div class="flex items-center gap-4 w-full md:w-auto">
            <select wire:model.live="filterStatus" class="bg-[#FDFBF7] border-[#2C241B]/10 rounded-xl text-[#2C241B] px-6 py-3.5 focus:ring-2 focus:ring-[#E07A5F] transition-all cursor-pointer font-bold uppercase text-[10px] tracking-widest min-w-[140px]">
                <option value="todas">Todas</option>
                <option value="activas">Activas</option>
                <option value="inactivas">Inactivas</option>
            </select>

            <div class="flex bg-[#FDFBF7] p-1 rounded-xl border border-[#2C241B]/10">
                <button wire:click="$set('viewMode', 'grid')" class="p-2.5 rounded-lg transition-all {{ $viewMode === 'grid' ? 'bg-[#E07A5F] text-white shadow-lg' : 'text-[#5C5246] hover:text-[#2C241B]' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                </button>
                <button wire:click="$set('viewMode', 'list')" class="p-2.5 rounded-lg transition-all {{ $viewMode === 'list' ? 'bg-[#E07A5F] text-white shadow-lg' : 'text-[#5C5246] hover:text-[#2C241B]' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Grid View Espresso Fluid --}}
    @if($viewMode === 'grid')
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($sucursales as $sucursal)
                <div class="bg-white rounded-[32px] overflow-hidden border border-[#2C241B]/10 shadow-2xl transition-all hover:border-[#E07A5F]/30 group relative" wire:key="grid-{{ $sucursal->id }}">
                    <div class="h-1.5 w-full {{ $sucursal->activo ? 'bg-[#E07A5F]' : 'bg-[#E6E2DB]' }}"></div>
                    <div class="p-6">
                        <div class="mb-6">
                            <h3 class="text-xl font-black text-[#2C241B] group-hover:text-[#E07A5F] transition-colors leading-tight tracking-tight uppercase">{{ $sucursal->nombre }}</h3>
                            <p class="text-[#E07A5F] text-[10px] font-black tracking-widest uppercase opacity-70 mt-1">/s/{{ $sucursal->slug }}</p>
                        </div>

                        <div class="space-y-3 mb-8 text-[#5C5246]">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-[#FDFBF7] flex items-center justify-center text-[#E07A5F]"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
                                <span class="text-xs font-medium truncate">{{ $sucursal->direccion ?: 'Calle 5 #3-15' }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-[#FDFBF7] flex items-center justify-center text-[#E07A5F]"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg></div>
                                <span class="text-xs font-medium">{{ $sucursal->telefono ?: '3112533941' }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 mb-4">
                            <a href="https://maps.google.com/?q={{ $sucursal->latitud ?? $sucursal->direccion }}{{ $sucursal->longitud ? ','.$sucursal->longitud : '' }}" target="_blank" class="flex-1 flex items-center justify-center gap-1 py-2.5 bg-blue-50/50 hover:bg-blue-100 text-blue-600 rounded-xl text-[9px] font-black uppercase tracking-widest transition-colors"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Ir</a>
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $sucursal->telefono) }}" class="flex-1 flex items-center justify-center gap-1 py-2.5 bg-sky-50/50 hover:bg-sky-100 text-sky-600 rounded-xl text-[9px] font-black uppercase tracking-widest transition-colors"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg> Llamar</a>
                            <a href="https://wa.me/57{{ preg_replace('/[^0-9]/', '', $sucursal->telefono) }}" target="_blank" class="flex-1 flex items-center justify-center gap-1 py-2.5 bg-emerald-50/50 hover:bg-emerald-100 text-emerald-600 rounded-xl text-[9px] font-black uppercase tracking-widest transition-colors"><svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg> WhatsApp</a>
                        </div>

                        <div class="flex items-center gap-3">
                            <button wire:click="gestionar('{{ $sucursal->id }}')" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 bg-[#E07A5F] hover:bg-[#C8644A] text-white font-black rounded-xl transition-all shadow-xl uppercase text-[9px] tracking-widest">Gestionar</button>
                            <button @click="$dispatch('open-modal'); $wire.edit('{{ $sucursal->id }}')" class="p-3 bg-[#FDFBF7] border border-[#2C241B]/10 text-[#5C5246] hover:text-[#2C241B] rounded-xl transition-all"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-20 text-center"><p class="text-[#5C5246] text-lg font-black uppercase tracking-widest">Sin sucursales</p></div>
            @endforelse
        </div>
    @else
        {{-- List View Fluid --}}
        <div class="bg-white rounded-[32px] border border-[#2C241B]/10 overflow-hidden shadow-2xl">
            <table class="w-full text-left">
                <thead class="bg-[#FDFBF7]/50 border-b border-[#2C241B]/10">
                    <tr>
                        <th class="px-6 py-4 text-[9px] font-black text-[#5C5246] uppercase tracking-widest">Sucursal</th>
                        <th class="px-6 py-4 text-[9px] font-black text-[#5C5246] uppercase tracking-widest">Contacto</th>
                        <th class="px-6 py-4 text-[9px] font-black text-[#5C5246] uppercase tracking-widest">Estado</th>
                        <th class="px-6 py-4 text-[9px] font-black text-[#5C5246] uppercase tracking-widest text-center">Ventas</th>
                        <th class="px-6 py-4 text-[9px] font-black text-[#5C5246] uppercase tracking-widest text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#2C241B]/10]">
                    @foreach($sucursales as $sucursal)
                        <tr class="hover:bg-[#FDFBF7]/40 transition-colors group" wire:key="list-{{ $sucursal->id }}">
                            <td class="px-6 py-4">
                                <p class="font-black text-[#2C241B] text-base tracking-tight leading-tight uppercase">{{ $sucursal->nombre }}</p>
                                <p class="text-[#E07A5F] text-[9px] font-black tracking-widest uppercase opacity-70">/s/{{ $sucursal->slug }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-bold text-[#2C241B] text-xs">{{ $sucursal->telefono ?: '3112533941' }}</p>
                                <p class="text-[10px] text-[#5C5246] font-medium">{{ $sucursal->direccion ?: 'Calle 5 #3-15' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full {{ $sucursal->activo ? 'bg-emerald-500/10 text-emerald-500' : 'bg-rose-500/10 text-rose-500' }} border {{ $sucursal->activo ? 'border-emerald-500/20' : 'border-rose-500/20' }}">
                                    <div class="w-1.5 h-1.5 rounded-full {{ $sucursal->activo ? 'bg-emerald-500 shadow-[0_0_8px_#10b981]' : 'bg-rose-500 shadow-[0_0_8px_#f43f5e]' }}"></div>
                                    <span class="text-[9px] font-black uppercase tracking-widest">{{ $sucursal->activo ? 'Activa' : 'Inactiva' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="font-black text-[#2C241B] text-lg tracking-tighter">$ {{ number_format($sucursal->ventas_mes_sum ?? 0, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2 mb-2">
                                    <a href="https://maps.google.com/?q={{ $sucursal->latitud ?? $sucursal->direccion }}{{ $sucursal->longitud ? ','.$sucursal->longitud : '' }}" target="_blank" class="p-2 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg transition-colors"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg></a>
                                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $sucursal->telefono) }}" class="p-2 bg-sky-50 hover:bg-sky-100 text-sky-600 rounded-lg transition-colors"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg></a>
                                    <a href="https://wa.me/57{{ preg_replace('/[^0-9]/', '', $sucursal->telefono) }}" target="_blank" class="p-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-600 rounded-lg transition-colors"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg></a>
                                </div>
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="gestionar('{{ $sucursal->id }}')" class="px-3 py-1.5 bg-[#E07A5F] hover:bg-[#C8644A] text-white font-black rounded-lg transition-all shadow-xl uppercase text-[8px] tracking-widest">Gestionar</button>
                                    <button @click="$dispatch('open-modal'); $wire.edit('{{ $sucursal->id }}')" class="p-2 text-[#5C5246] hover:text-[#E07A5F] transition-all bg-[#FDFBF7] border border-[#2C241B]/10 hover:border-[#E07A5F]/30 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Modal Espresso Fluid --}}
    <div x-show="showForm" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-[#FDFBF7]/95 backdrop-blur-xl" x-transition>
        <div class="bg-white rounded-[40px] shadow-2xl w-full max-w-lg border border-[#2C241B]/10 overflow-hidden relative" @click.away="showForm = false">
            <div x-show="isFetching" class="absolute inset-0 bg-[#FDFBF7]/60 backdrop-blur-md z-10 flex items-center justify-center" x-cloak>
                <div class="flex flex-col items-center gap-4"><div class="w-12 h-12 border-4 border-[#E07A5F] border-t-transparent rounded-full animate-spin"></div><p class="text-[#2C241B] font-black uppercase text-[10px] tracking-widest animate-pulse">Cargando</p></div>
            </div>
            <div class="px-10 py-8 border-b border-[#2C241B]/10 flex items-center justify-between bg-[#FDFBF7]/30">
                <div><h3 class="text-2xl font-black text-[#2C241B] uppercase tracking-tight">{{ $isEditing ? 'Editar' : 'Nueva' }} Sucursal</h3><p class="text-[#5C5246] text-xs mt-1 font-bold">Datos operativos</p></div>
                <button @click="showForm = false" class="p-3 rounded-xl hover:bg-[#E6E2DB] text-[#5C5246] transition-colors border border-[#2C241B]/10 hover:border-[#2C241B]/10"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form wire:submit.prevent="save" class="p-10 space-y-8" @submit="$wire.showModal = false">
                <div class="space-y-6">
                    <div><label class="block text-[9px] font-black text-[#5C5246] uppercase tracking-widest mb-3">Nombre</label><input wire:model.blur="nombre" type="text" class="w-full px-6 py-4 bg-[#FDFBF7] border-[#2C241B]/10 rounded-2xl text-[#2C241B] placeholder-[#8B8175] focus:ring-2 focus:ring-[#E07A5F] transition-all font-bold text-sm"></div>
                    <div class="grid grid-cols-2 gap-6">
                        <div><label class="block text-[9px] font-black text-[#5C5246] uppercase tracking-widest mb-3">Ciudad</label><input wire:model.defer="ciudad" type="text" class="w-full px-6 py-4 bg-[#FDFBF7] border-[#2C241B]/10 rounded-2xl text-[#2C241B] placeholder-[#8B8175] focus:ring-2 focus:ring-[#E07A5F] transition-all font-bold text-sm"></div>
                        <div><label class="block text-[9px] font-black text-[#5C5246] uppercase tracking-widest mb-3">Teléfono</label><input wire:model.defer="telefono" type="text" class="w-full px-6 py-4 bg-[#FDFBF7] border-[#2C241B]/10 rounded-2xl text-[#2C241B] placeholder-[#8B8175] focus:ring-2 focus:ring-[#E07A5F] transition-all font-bold text-sm"></div>
                    </div>
                    <div x-data="{ 
                        query: @entangle('direccion'), 
                        lat: @entangle('latitud'), 
                        lon: @entangle('longitud'), 
                        suggestions: [], 
                        showSuggestions: false, 
                        loadingLocation: false,
                        debounceTimer: null,
                        searchAddress() {
                            if(this.query.length < 4) { this.showSuggestions = false; return; }
                            clearTimeout(this.debounceTimer);
                            this.debounceTimer = setTimeout(async () => {
                                try {
                                    const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(this.query)}&countrycodes=co&limit=5`);
                                    const data = await res.json();
                                    this.suggestions = data;
                                    this.showSuggestions = data.length > 0;
                                } catch(e) {}
                            }, 600);
                        },
                        selectSuggestion(item) {
                            this.query = item.display_name;
                            this.lat = item.lat;
                            this.lon = item.lon;
                            this.showSuggestions = false;
                        },
                        getLocation() {
                            if(!navigator.geolocation) return alert('GPS no soportado');
                            this.loadingLocation = true;
                            navigator.geolocation.getCurrentPosition(async (pos) => {
                                try {
                                    const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${pos.coords.latitude}&lon=${pos.coords.longitude}&addressdetails=1`);
                                    const data = await res.json();
                                    if(data && data.display_name) {
                                        this.query = data.display_name;
                                        this.lat = pos.coords.latitude;
                                        this.lon = pos.coords.longitude;
                                    }
                                } catch(e) {}
                                this.loadingLocation = false;
                            }, (err) => {
                                this.loadingLocation = false;
                                alert('Error al obtener ubicación');
                            }, { enableHighAccuracy: true });
                        }
                    }" @click.away="showSuggestions = false">
                        <label class="block text-[9px] font-black text-[#5C5246] uppercase tracking-widest mb-3">Dirección</label>
                        <div class="relative">
                            <input type="text" x-model="query" @input="searchAddress" class="w-full pl-6 pr-12 py-4 bg-[#FDFBF7] border-[#2C241B]/10 rounded-2xl text-[#2C241B] placeholder-[#8B8175] focus:ring-2 focus:ring-[#E07A5F] transition-all font-bold text-sm" placeholder="Escribe para buscar o usa el GPS">
                            <button type="button" @click="getLocation" class="absolute right-3 top-1/2 transform -translate-y-1/2 p-2 text-[#E07A5F] hover:bg-[#E07A5F]/10 rounded-lg transition-colors" :class="{'opacity-50 cursor-not-allowed': loadingLocation}" :disabled="loadingLocation">
                                <template x-if="!loadingLocation">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="3 11 22 2 13 21 11 13 3 11"></polygon></svg>
                                </template>
                                <template x-if="loadingLocation">
                                    <svg class="animate-spin" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="2" x2="12" y2="6"></line><line x1="12" y1="18" x2="12" y2="22"></line><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line><line x1="2" y1="12" x2="6" y2="12"></line><line x1="18" y1="12" x2="22" y2="12"></line><line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line><line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line></svg>
                                </template>
                            </button>
                            <ul x-show="showSuggestions" class="absolute z-50 w-full mt-2 bg-white border border-[#2C241B]/10 rounded-xl shadow-2xl max-h-48 overflow-y-auto" style="display: none;">
                                <template x-for="(item, index) in suggestions" :key="index">
                                    <li @click="selectSuggestion(item)" class="px-4 py-3 hover:bg-[#FDFBF7] cursor-pointer text-sm font-medium text-[#2C241B] border-b border-[#2C241B]/5 last:border-0" x-text="item.display_name"></li>
                                </template>
                            </ul>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-5 bg-[#FDFBF7] rounded-2xl border border-[#2C241B]/10"><span class="text-[10px] font-black text-[#2C241B] uppercase tracking-widest">En operación</span><button type="button" @click="$wire.activo = !$wire.activo" class="relative inline-flex h-7 w-12 rounded-full border-2 border-[#2C241B]/10 transition-colors duration-300" :class="$wire.activo ? 'bg-[#E07A5F]' : 'bg-[#E6E2DB]'"><span class="inline-block h-6 w-6 transform rounded-full bg-white transition duration-300 shadow-xl" :class="$wire.activo ? 'translate-x-5' : 'translate-x-0'"></span></button></div>
                </div>
                <div class="flex items-center gap-4 pt-4"><button type="button" @click="showForm = false" class="flex-1 px-6 py-4 bg-[#FDFBF7] text-[#5C5246] font-black rounded-xl transition-all uppercase text-[9px] tracking-widest border border-[#2C241B]/10">Cancelar</button><button type="submit" class="flex-1 px-6 py-4 bg-[#E07A5F] text-white font-black rounded-xl transition-all shadow-xl uppercase text-[9px] tracking-widest">Confirmar</button></div>
            </form>
        </div>
    </div>
</div>
