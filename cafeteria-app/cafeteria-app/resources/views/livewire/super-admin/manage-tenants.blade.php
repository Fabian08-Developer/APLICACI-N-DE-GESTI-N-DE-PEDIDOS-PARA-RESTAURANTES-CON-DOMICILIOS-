<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-white uppercase tracking-tighter">Gestionar Tenants</h2>
            <p class="text-sm text-stone-500 font-medium">Control global de todas las empresas registradas en la plataforma</p>
        </div>
    </div>

    {{-- Buscador --}}
    <div class="bg-[#141210] border border-[#292524] rounded-2xl p-4">
        <div class="relative">
            <input type="text" wire:model.live="search" 
                   class="w-full bg-[#1C1917] border border-[#44403C] rounded-xl px-12 py-3 text-white text-sm focus:border-[#A85507] focus:ring-0 transition-all"
                   placeholder="Buscar por nombre o NIT...">
            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-stone-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </div>
    </div>

    <div class="bg-[#141210] border border-[#292524] rounded-[32px] overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-[#1C1917]/50 border-b border-[#292524]">
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-stone-500">Empresa</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-stone-500">Usuarios</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-stone-500">Estado</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-stone-500 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#292524]">
                @forelse($tenants as $tenant)
                    <tr class="hover:bg-[#1C1917]/30 transition-colors" wire:key="{{ $tenant->id }}">
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-white">{{ $tenant->nombre }}</span>
                                <span class="text-[11px] text-stone-500">NIT: {{ $tenant->nit }}</span>
                                <span class="text-[11px] text-stone-600">Creado: {{ $tenant->creado_en?->format('d/m/Y') ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-stone-400 font-bold">
                            {{ $tenant->usuarios_count }}
                        </td>
                        <td class="px-6 py-4">
                            @if($tenant->activo)
                                <span class="px-3 py-1 rounded-full bg-green-500/10 text-green-500 text-[10px] font-black uppercase tracking-widest">Activo</span>
                            @else
                                <span class="px-3 py-1 rounded-full bg-rose-500/10 text-rose-500 text-[10px] font-black uppercase tracking-widest">Inactivo</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button wire:click="toggleStatus('{{ $tenant->id }}')" 
                                        class="p-2 rounded-xl {{ $tenant->activo ? 'text-amber-500 bg-amber-500/10 hover:bg-amber-500 hover:text-white' : 'text-green-500 bg-green-500/10 hover:bg-green-500 hover:text-white' }} transition-all">
                                    @if($tenant->activo)
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @endif
                                </button>
                                <button wire:click="deleteEmpresa('{{ $tenant->id }}')" 
                                        wire:confirm="¿Estás seguro de eliminar esta empresa y TODOS sus usuarios? Esta acción no se puede deshacer."
                                        class="p-2 rounded-xl text-rose-500 bg-rose-500/10 hover:bg-rose-500 hover:text-white transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-stone-600 uppercase text-[10px] font-black tracking-widest">No se encontraron empresas</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="px-6 py-4 border-t border-[#292524]">
            {{ $tenants->links() }}
        </div>
    </div>
</div>
