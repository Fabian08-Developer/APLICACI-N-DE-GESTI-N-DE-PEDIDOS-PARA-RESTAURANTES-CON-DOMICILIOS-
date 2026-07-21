<div class="w-full pb-10"
    x-data="{
        showForm: @entangle('showModal'),
        deleteModal: @entangle('deleteModal'),
        isFetching: false
    }"
    @open-modal.window="showForm = true; isFetching = true"
    @data-loaded.window="isFetching = false"
>
    <script src="{{ asset('js/colombia.js') }}"></script>
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

    {{-- Stats --}}
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
                class="block w-full pl-12 pr-4 py-3.5 bg-[#FDFBF7] border-[#2C241B]/10 rounded-xl text-[#2C241B] placeholder-[#8B8175] focus:ring-2 focus:ring-[#E07A5F] transition-all font-medium text-sm"
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

    {{-- Grid View --}}
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
                                <span class="text-xs font-medium truncate">{{ $sucursal->direccion ?: 'Sin dirección' }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-[#FDFBF7] flex items-center justify-center text-[#E07A5F]"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg></div>
                                <span class="text-xs font-medium">{{ $sucursal->telefono ?: 'Sin teléfono' }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button wire:click="gestionar('{{ $sucursal->id }}')" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 bg-[#E07A5F] hover:bg-[#C8644A] text-white font-black rounded-xl transition-all shadow-xl uppercase text-[9px] tracking-widest">Gestionar</button>
                            <button @click="$wire.edit('{{ $sucursal->id }}')" class="p-3 bg-[#FDFBF7] border border-[#2C241B]/10 text-[#5C5246] hover:text-[#2C241B] rounded-xl transition-all"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                            <button wire:click="confirmDelete('{{ $sucursal->id }}')" class="p-3 bg-[#FDFBF7] border border-[#2C241B]/10 text-rose-500 hover:text-white hover:bg-rose-500 hover:border-rose-500 rounded-xl transition-all"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-20 text-center"><p class="text-[#5C5246] text-lg font-black uppercase tracking-widest">Sin sucursales</p></div>
            @endforelse
        </div>
    @else
        {{-- List View --}}
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
                <tbody class="divide-y divide-[#2C241B]/10">
                    @foreach($sucursales as $sucursal)
                        <tr class="hover:bg-[#FDFBF7]/40 transition-colors group" wire:key="list-{{ $sucursal->id }}">
                            <td class="px-6 py-4">
                                <p class="font-black text-[#2C241B] text-base tracking-tight leading-tight uppercase">{{ $sucursal->nombre }}</p>
                                <p class="text-[#E07A5F] text-[9px] font-black tracking-widest uppercase opacity-70">/s/{{ $sucursal->slug }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-bold text-[#2C241B] text-xs">{{ $sucursal->telefono ?: 'Sin teléfono' }}</p>
                                <p class="text-[10px] text-[#5C5246] font-medium">{{ $sucursal->direccion ?: 'Sin dirección' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full {{ $sucursal->activo ? 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20' : 'bg-rose-500/10 text-rose-500 border-rose-500/20' }} border">
                                    <div class="w-1.5 h-1.5 rounded-full {{ $sucursal->activo ? 'bg-emerald-500' : 'bg-rose-500' }}"></div>
                                    <span class="text-[9px] font-black uppercase tracking-widest">{{ $sucursal->activo ? 'Activa' : 'Inactiva' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="font-black text-[#2C241B] text-lg tracking-tighter">$ {{ number_format($sucursal->ventas_mes_sum ?? 0, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="gestionar('{{ $sucursal->id }}')" class="px-3 py-1.5 bg-[#E07A5F] hover:bg-[#C8644A] text-white font-black rounded-lg transition-all uppercase text-[8px] tracking-widest">Gestionar</button>
                                    <button @click="$wire.edit('{{ $sucursal->id }}')" class="p-2 text-[#5C5246] hover:text-[#E07A5F] transition-all bg-[#FDFBF7] border border-[#2C241B]/10 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                                    <button wire:click="confirmDelete('{{ $sucursal->id }}')" class="p-2 text-rose-500 hover:text-white transition-all bg-[#FDFBF7] hover:bg-rose-500 hover:border-rose-500 border border-[#2C241B]/10 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Modal --}}
    <div x-show="showForm" x-cloak
         class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-6"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="fixed inset-0 bg-[#2C241B]/40 backdrop-blur-md" @click="showForm = false"></div>

        <div class="bg-white rounded-[32px] shadow-2xl w-full max-w-lg border border-[#2C241B]/5 overflow-hidden relative z-10"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-8 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-8 scale-95">

            <div class="px-8 py-6 border-b border-[#2C241B]/5 bg-gradient-to-r from-[#FDFBF7] to-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-[#E07A5F]/10 flex items-center justify-center text-[#E07A5F]">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-[#2C241B] uppercase tracking-tight">{{ $isEditing ? 'Editar' : 'Nueva' }} Sucursal</h3>
                            <p class="text-[#8B8175] text-[11px] mt-1 font-medium">Configuración de sede operativa</p>
                        </div>
                    </div>
                    <button type="button" @click="showForm = false" class="p-2 rounded-xl text-[#8B8175] hover:text-[#2C241B] hover:bg-[#2C241B]/5 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <form wire:submit.prevent="save" class="p-8 space-y-5 max-h-[70vh] overflow-y-auto">

                {{-- Nombre --}}
                <div>
                    <label class="block text-[10px] font-black text-[#2C241B] uppercase tracking-widest mb-2 pl-1">Nombre Comercial <span class="text-[#E07A5F]">*</span></label>
                    <input wire:model.live="nombre" type="text" placeholder="Ej. Sede Centro"
                           class="w-full px-5 py-3.5 bg-white border border-[#2C241B]/10 rounded-2xl text-[#2C241B] placeholder-[#8B8175]/50 focus:border-[#E07A5F] focus:ring-4 focus:ring-[#E07A5F]/10 transition-all font-bold text-sm shadow-sm outline-none">
                    @error('nombre') <span class="text-rose-500 text-[9px] font-black uppercase mt-1 block pl-1">{{ $message }}</span> @enderror
                </div>

                {{-- Departamento + Ciudad + Teléfono --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4"
                     x-data="{
                         departments: Object.keys(window.colombiaData || {}).sort(),
                         cities: [],
                         selectedDept: '',
                         init() {
                             const cur = $wire.ciudad;
                             if (cur) {
                                 for (let d in window.colombiaData) {
                                     if (window.colombiaData[d].includes(cur)) {
                                         this.selectedDept = d;
                                         this.cities = window.colombiaData[d].sort();
                                         break;
                                     }
                                 }
                             }
                             this.$watch('$wire.ciudad', (newVal) => {
                                 if (!newVal) return;
                                 for (let d in window.colombiaData) {
                                     if (window.colombiaData[d].includes(newVal)) {
                                         if (this.selectedDept !== d) {
                                             this.selectedDept = d;
                                             this.cities = window.colombiaData[d].sort();
                                         }
                                         break;
                                     }
                                 }
                             });
                         }
                     }">
                    <div>
                        <label class="block text-[10px] font-black text-[#2C241B] uppercase tracking-widest mb-2 pl-1">Departamento <span class="text-[#E07A5F]">*</span></label>
                        <select x-model="selectedDept"
                                @change="cities = (window.colombiaData[selectedDept] || []).sort(); $wire.set('ciudad', '')"
                                class="w-full px-5 py-3.5 bg-white border border-[#2C241B]/10 rounded-2xl text-[#2C241B] focus:border-[#E07A5F] focus:ring-4 focus:ring-[#E07A5F]/10 transition-all font-bold text-sm shadow-sm outline-none appearance-none cursor-pointer">
                            <option value="">Seleccione...</option>
                            <template x-for="dept in departments" :key="dept">
                                <option :value="dept" x-text="dept"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-[#2C241B] uppercase tracking-widest mb-2 pl-1">Ciudad <span class="text-[#E07A5F]">*</span></label>
                        <select @change="$wire.set('ciudad', $event.target.value)"
                                :disabled="cities.length === 0"
                                class="w-full px-5 py-3.5 bg-white border border-[#2C241B]/10 rounded-2xl text-[#2C241B] focus:border-[#E07A5F] focus:ring-4 focus:ring-[#E07A5F]/10 transition-all font-bold text-sm shadow-sm outline-none appearance-none cursor-pointer disabled:opacity-40">
                            <option value="">Seleccione...</option>
                            <template x-for="city in cities" :key="city">
                                <option :value="city" x-text="city" :selected="city === $wire.ciudad"></option>
                            </template>
                        </select>
                        @error('ciudad') <span class="text-rose-500 text-[9px] font-black uppercase mt-1 block pl-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-[#2C241B] uppercase tracking-widest mb-2 pl-1">Teléfono</label>
                        <input wire:model.live="telefono" type="text" placeholder="Ej. 3112533941"
                               class="w-full px-5 py-3.5 bg-white border border-[#2C241B]/10 rounded-2xl text-[#2C241B] placeholder-[#8B8175]/50 focus:border-[#E07A5F] focus:ring-4 focus:ring-[#E07A5F]/10 transition-all font-bold text-sm shadow-sm outline-none">
                        @error('telefono') <span class="text-rose-500 text-[9px] font-black uppercase mt-1 block pl-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Dirección --}}
                <div>
                    <label class="block text-[10px] font-black text-[#2C241B] uppercase tracking-widest mb-2 pl-1">Dirección</label>
                    <input wire:model.live="direccion" type="text" placeholder="Ej. Calle 5 #3-15"
                           class="w-full px-5 py-3.5 bg-white border border-[#2C241B]/10 rounded-2xl text-[#2C241B] placeholder-[#8B8175]/50 focus:border-[#E07A5F] focus:ring-4 focus:ring-[#E07A5F]/10 transition-all font-bold text-sm shadow-sm outline-none">
                </div>

                {{-- Estado --}}
                <div class="flex items-center justify-between p-5 bg-[#FDFBF7] rounded-2xl border border-[#2C241B]/5">
                    <div>
                        <span class="block text-[10px] font-black text-[#2C241B] uppercase tracking-widest">En Operación</span>
                        <span class="block text-[10px] text-[#8B8175] font-medium mt-0.5">La sucursal está visible y activa</span>
                    </div>
                    <button type="button" @click="$wire.set('activo', !$wire.activo)"
                            class="relative inline-flex h-7 w-12 rounded-full border-2 transition-colors duration-300 focus:outline-none"
                            :class="$wire.activo ? 'bg-[#E07A5F] border-[#E07A5F]' : 'bg-[#E6E2DB] border-[#2C241B]/10'">
                        <span class="inline-block h-6 w-6 transform rounded-full bg-white transition-transform duration-300 shadow-md"
                              :class="$wire.activo ? 'translate-x-5' : 'translate-x-0'"></span>
                    </button>
                </div>

                {{-- Botones --}}
                <div class="flex items-center gap-4 pt-2">
                    <button type="button" @click="showForm = false"
                            class="w-1/3 px-6 py-4 bg-white text-[#5C5246] hover:text-[#2C241B] hover:bg-[#FDFBF7] font-black rounded-2xl transition-all uppercase text-[10px] tracking-widest border border-[#2C241B]/10">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="w-2/3 flex items-center justify-center gap-2 px-6 py-4 bg-[#E07A5F] hover:bg-[#C8644A] text-white font-black rounded-2xl transition-all shadow-[0_8px_20px_rgba(224,122,95,0.25)] active:scale-[0.98] uppercase text-[10px] tracking-widest">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete Modal --}}
    <div x-show="deleteModal" x-cloak
         class="fixed inset-0 z-[70] flex items-center justify-center p-4 sm:p-6"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="fixed inset-0 bg-[#2C241B]/60 backdrop-blur-sm" @click="deleteModal = false"></div>

        <div class="bg-white rounded-[32px] shadow-2xl w-full max-w-sm border border-[#2C241B]/5 overflow-hidden relative z-10"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-8 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-8 scale-95">

            <div class="px-8 py-8 flex flex-col items-center text-center">
                <div class="w-16 h-16 rounded-full bg-rose-500/10 flex items-center justify-center text-rose-500 mb-6">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-xl font-black text-[#2C241B] uppercase tracking-tight mb-2">Eliminar Sucursal</h3>
                <p class="text-[#8B8175] text-xs font-medium mb-6">Esta acción no se puede deshacer. Por favor ingresa tu contraseña para confirmar la eliminación.</p>

                <form wire:submit.prevent="deleteSucursal" class="w-full">
                    <div class="mb-6">
                        <input wire:model="passwordVerification" type="password" placeholder="Tu contraseña..."
                               class="w-full px-5 py-3.5 bg-[#FDFBF7] border border-[#2C241B]/10 rounded-xl text-[#2C241B] placeholder-[#8B8175]/50 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 transition-all font-bold text-sm text-center outline-none">
                        @error('passwordVerification') <span class="text-rose-500 text-[10px] font-black uppercase mt-2 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center gap-3 w-full">
                        <button type="button" @click="deleteModal = false"
                                class="flex-1 px-4 py-3.5 bg-white text-[#5C5246] hover:text-[#2C241B] hover:bg-[#FDFBF7] font-black rounded-xl transition-all uppercase text-[10px] tracking-widest border border-[#2C241B]/10">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-3.5 bg-rose-500 hover:bg-rose-600 text-white font-black rounded-xl transition-all shadow-[0_8px_20px_rgba(244,63,94,0.25)] uppercase text-[10px] tracking-widest">
                            Eliminar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
