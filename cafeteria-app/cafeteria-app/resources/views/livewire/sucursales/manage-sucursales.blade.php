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
            <h1 class="text-3xl font-black text-white tracking-tight uppercase">Sucursales</h1>
            <p class="text-stone-500 mt-1 text-base font-medium">Administra tu red operativa</p>
        </div>
        <button @click="showForm = true; $wire.openCreateModal()" class="inline-flex items-center px-6 py-3 bg-[#A85507] hover:bg-[#78350F] text-white font-black rounded-xl transition-all shadow-xl uppercase text-[10px] tracking-widest">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Nueva Sucursal
        </button>
    </div>

    {{-- Stats Grid Fluid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        @foreach(['Total Sucursales' => $stats['total'], 'Activas' => $stats['activas'], 'Inactivas' => $stats['inactivas']] as $label => $value)
            <div class="bg-[#1C1917] rounded-[24px] p-6 border border-[#292524] shadow-xl">
                <p class="text-stone-600 text-[9px] font-black uppercase tracking-widest mb-2">{{ $label }}</p>
                <h3 class="text-3xl font-black {{ $label === 'Activas' ? 'text-emerald-500' : ($label === 'Inactivas' ? 'text-stone-700' : 'text-white') }} tracking-tighter">{{ $value }}</h3>
            </div>
        @endforeach
        <div class="bg-[#1C1917] rounded-[24px] p-6 border border-[#292524] shadow-xl">
            <p class="text-stone-600 text-[9px] font-black uppercase tracking-widest mb-2">Ventas Mes</p>
            <h3 class="text-3xl font-black text-white tracking-tighter">${{ number_format($stats['ventas_mes'], 0, ',', '.') }}</h3>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-[#1C1917] rounded-[24px] p-4 border border-[#292524] shadow-xl mb-8 flex flex-col md:flex-row gap-4 items-center">
        <div class="relative flex-1 w-full">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input wire:model.live.debounce.300ms="search" type="text" 
                class="block w-full pl-12 pr-4 py-3.5 bg-[#0C0A09] border-transparent rounded-xl text-white placeholder-stone-800 focus:ring-2 focus:ring-[#A85507] focus:bg-[#0C0A09] transition-all font-medium text-sm" 
                placeholder="Buscar por nombre o ciudad...">
        </div>

        <div class="flex items-center gap-4 w-full md:w-auto">
            <select wire:model.live="filterStatus" class="bg-[#0C0A09] border-transparent rounded-xl text-stone-300 px-6 py-3.5 focus:ring-2 focus:ring-[#A85507] transition-all cursor-pointer font-bold uppercase text-[10px] tracking-widest min-w-[140px]">
                <option value="todas">Todas</option>
                <option value="activas">Activas</option>
                <option value="inactivas">Inactivas</option>
            </select>

            <div class="flex bg-[#0C0A09] p-1 rounded-xl border border-[#292524]">
                <button wire:click="$set('viewMode', 'grid')" class="p-2.5 rounded-lg transition-all {{ $viewMode === 'grid' ? 'bg-[#A85507] text-white shadow-lg' : 'text-stone-600 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                </button>
                <button wire:click="$set('viewMode', 'list')" class="p-2.5 rounded-lg transition-all {{ $viewMode === 'list' ? 'bg-[#A85507] text-white shadow-lg' : 'text-stone-600 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Grid View Espresso Fluid --}}
    @if($viewMode === 'grid')
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($sucursales as $sucursal)
                <div class="bg-[#1C1917] rounded-[32px] overflow-hidden border border-[#292524] shadow-2xl transition-all hover:border-[#A85507]/30 group relative" wire:key="grid-{{ $sucursal->id }}">
                    <div class="h-1.5 w-full {{ $sucursal->activo ? 'bg-[#A85507]' : 'bg-stone-800' }}"></div>
                    <div class="p-6">
                        <div class="mb-6">
                            <h3 class="text-xl font-black text-white group-hover:text-[#A85507] transition-colors leading-tight tracking-tight uppercase">{{ $sucursal->nombre }}</h3>
                            <p class="text-[#A85507] text-[10px] font-black tracking-widest uppercase opacity-70 mt-1">/s/{{ $sucursal->slug }}</p>
                        </div>

                        <div class="space-y-3 mb-8 text-stone-500">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-stone-900 flex items-center justify-center text-[#A85507]"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
                                <span class="text-xs font-medium truncate">{{ $sucursal->direccion ?: 'Calle 5 #3-15' }}, {{ $sucursal->ciudad }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-stone-900 flex items-center justify-center text-[#A85507]"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg></div>
                                <span class="text-xs font-medium">{{ $sucursal->telefono ?: '3112533941' }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <button wire:click="gestionar('{{ $sucursal->id }}')" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 bg-[#A85507] hover:bg-[#78350F] text-white font-black rounded-xl transition-all shadow-xl uppercase text-[9px] tracking-widest">Gestionar</button>
                            <button @click="$dispatch('open-modal'); $wire.edit('{{ $sucursal->id }}')" class="p-3 bg-stone-900 border border-[#292524] text-stone-600 hover:text-white rounded-xl transition-all"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-20 text-center"><p class="text-stone-600 text-lg font-black uppercase tracking-widest">Sin sucursales</p></div>
            @endforelse
        </div>
    @else
        {{-- List View Fluid --}}
        <div class="bg-[#1C1917] rounded-[32px] border border-[#292524] overflow-hidden shadow-2xl">
            <table class="w-full text-left">
                <thead class="bg-stone-900/50 border-b border-[#292524]">
                    <tr>
                        <th class="px-6 py-4 text-[9px] font-black text-stone-600 uppercase tracking-widest">Sucursal</th>
                        <th class="px-6 py-4 text-[9px] font-black text-stone-600 uppercase tracking-widest">Contacto</th>
                        <th class="px-6 py-4 text-[9px] font-black text-stone-600 uppercase tracking-widest">Estado</th>
                        <th class="px-6 py-4 text-[9px] font-black text-stone-600 uppercase tracking-widest text-center">Ventas</th>
                        <th class="px-6 py-4 text-[9px] font-black text-stone-600 uppercase tracking-widest text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#292524]">
                    @foreach($sucursales as $sucursal)
                        <tr class="hover:bg-stone-900/40 transition-colors group" wire:key="list-{{ $sucursal->id }}">
                            <td class="px-6 py-4">
                                <p class="font-black text-white text-base tracking-tight leading-tight uppercase">{{ $sucursal->nombre }}</p>
                                <p class="text-[#A85507] text-[9px] font-black tracking-widest uppercase opacity-70">/s/{{ $sucursal->slug }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-bold text-white text-xs">{{ $sucursal->telefono ?: '3112533941' }}</p>
                                <p class="text-[10px] text-stone-600 font-medium">{{ $sucursal->direccion ?: 'Calle 5 #3-15' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full {{ $sucursal->activo ? 'bg-emerald-500/10 text-emerald-500' : 'bg-rose-500/10 text-rose-500' }} border {{ $sucursal->activo ? 'border-emerald-500/20' : 'border-rose-500/20' }}">
                                    <div class="w-1.5 h-1.5 rounded-full {{ $sucursal->activo ? 'bg-emerald-500 shadow-[0_0_8px_#10b981]' : 'bg-rose-500 shadow-[0_0_8px_#f43f5e]' }}"></div>
                                    <span class="text-[9px] font-black uppercase tracking-widest">{{ $sucursal->activo ? 'Activa' : 'Inactiva' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="font-black text-white text-lg tracking-tighter">$ {{ number_format($sucursal->ventas_mes_sum ?? 0, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="gestionar('{{ $sucursal->id }}')" class="px-3 py-1.5 bg-[#A85507] hover:bg-[#78350F] text-white font-black rounded-lg transition-all shadow-xl uppercase text-[8px] tracking-widest">Gestionar</button>
                                    <button @click="$dispatch('open-modal'); $wire.edit('{{ $sucursal->id }}')" class="p-2 text-stone-600 hover:text-[#A85507] transition-all bg-stone-900 border border-transparent hover:border-[#A85507]/30 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Modal Espresso Fluid --}}
    <div x-show="showForm" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-[#0C0A09]/95 backdrop-blur-xl" x-transition>
        <div class="bg-[#1C1917] rounded-[40px] shadow-2xl w-full max-w-lg border border-[#292524] overflow-hidden relative" @click.away="showForm = false">
            <div x-show="isFetching" class="absolute inset-0 bg-[#0C0A09]/60 backdrop-blur-md z-10 flex items-center justify-center" x-cloak>
                <div class="flex flex-col items-center gap-4"><div class="w-12 h-12 border-4 border-[#A85507] border-t-transparent rounded-full animate-spin"></div><p class="text-white font-black uppercase text-[10px] tracking-widest animate-pulse">Cargando</p></div>
            </div>
            <div class="px-10 py-8 border-b border-[#292524] flex items-center justify-between bg-stone-900/30">
                <div><h3 class="text-2xl font-black text-white uppercase tracking-tight">{{ $isEditing ? 'Editar' : 'Nueva' }} Sucursal</h3><p class="text-stone-600 text-xs mt-1 font-bold">Datos operativos</p></div>
                <button @click="showForm = false" class="p-3 rounded-xl hover:bg-stone-800 text-stone-600 transition-colors border border-transparent hover:border-[#292524]"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form wire:submit.prevent="save" class="p-10 space-y-8" @submit="$wire.showModal = false">
                <div class="space-y-6">
                    <div><label class="block text-[9px] font-black text-stone-600 uppercase tracking-widest mb-3">Nombre</label><input wire:model.blur="nombre" type="text" class="w-full px-6 py-4 bg-[#0C0A09] border-transparent rounded-2xl text-white placeholder-stone-900 focus:ring-2 focus:ring-[#A85507] transition-all font-bold text-sm"></div>
                    <div class="grid grid-cols-2 gap-6">
                        <div><label class="block text-[9px] font-black text-stone-600 uppercase tracking-widest mb-3">Ciudad</label><input wire:model.defer="ciudad" type="text" class="w-full px-6 py-4 bg-[#0C0A09] border-transparent rounded-2xl text-white placeholder-stone-900 focus:ring-2 focus:ring-[#A85507] transition-all font-bold text-sm"></div>
                        <div><label class="block text-[9px] font-black text-stone-600 uppercase tracking-widest mb-3">Teléfono</label><input wire:model.defer="telefono" type="text" class="w-full px-6 py-4 bg-[#0C0A09] border-transparent rounded-2xl text-white placeholder-stone-900 focus:ring-2 focus:ring-[#A85507] transition-all font-bold text-sm"></div>
                    </div>
                    <div><label class="block text-[9px] font-black text-stone-600 uppercase tracking-widest mb-3">Dirección</label><textarea wire:model.defer="direccion" rows="2" class="w-full px-6 py-4 bg-[#0C0A09] border-transparent rounded-2xl text-white placeholder-stone-900 focus:ring-2 focus:ring-[#A85507] transition-all font-bold text-sm resize-none"></textarea></div>
                    <div class="flex items-center justify-between p-5 bg-[#0C0A09] rounded-2xl border border-[#292524]"><span class="text-[10px] font-black text-white uppercase tracking-widest">En operación</span><button type="button" @click="$wire.activo = !$wire.activo" class="relative inline-flex h-7 w-12 rounded-full border-2 border-transparent transition-colors duration-300" :class="$wire.activo ? 'bg-[#A85507]' : 'bg-stone-800'"><span class="inline-block h-6 w-6 transform rounded-full bg-white transition duration-300 shadow-xl" :class="$wire.activo ? 'translate-x-5' : 'translate-x-0'"></span></button></div>
                </div>
                <div class="flex items-center gap-4 pt-4"><button type="button" @click="showForm = false" class="flex-1 px-6 py-4 bg-stone-900 text-stone-600 font-black rounded-xl transition-all uppercase text-[9px] tracking-widest border border-[#292524]">Cancelar</button><button type="submit" class="flex-1 px-6 py-4 bg-[#A85507] text-white font-black rounded-xl transition-all shadow-xl uppercase text-[9px] tracking-widest">Confirmar</button></div>
            </form>
        </div>
    </div>
</div>
