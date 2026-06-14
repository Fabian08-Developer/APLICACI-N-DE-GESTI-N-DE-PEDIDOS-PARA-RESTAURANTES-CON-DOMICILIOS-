<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-[#2C241B] uppercase tracking-tighter">Usuarios Globales</h2>
            <p class="text-sm text-[#5C5246] font-medium">Administración de todos los usuarios registrados en el ecosistema</p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-[#141210] border border-[#2C241B]/10 rounded-2xl p-4 flex flex-col md:flex-row gap-4">
        <div class="relative flex-1">
            <input type="text" wire:model.live="search" 
                   class="w-full bg-white border border-[#44403C] rounded-xl px-12 py-3 text-[#2C241B] text-sm focus:border-[#E07A5F] focus:ring-0 transition-all"
                   placeholder="Buscar por nombre o correo...">
            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-[#5C5246]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </div>
        <div class="w-full md:w-48">
            <select wire:model.live="roleFilter" class="w-full bg-white border border-[#44403C] rounded-xl px-4 py-3 text-[#2C241B] text-sm focus:border-[#E07A5F] focus:ring-0 outline-none">
                <option value="">Todos los Roles</option>
                <option value="gerente">Gerente</option>
                <option value="admin">Administrador</option>
                <option value="vendedor">Vendedor</option>
            </select>
        </div>
    </div>

    <div class="bg-[#141210] border border-[#2C241B]/10 rounded-[32px] overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-white border-b border-[#2C241B]/10">
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[#5C5246]">Usuario</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[#5C5246]">Empresa</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[#5C5246]">Rol</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[#5C5246]">Estado</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[#5C5246] text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#2C241B]/10]">
                @forelse($users as $user)
                    <tr class="hover:bg-white transition-colors" wire:key="{{ $user->id }}">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-[#E07A5F]/20 text-[#E07A5F] flex items-center justify-center font-black text-xs">
                                    {{ substr($user->nombre, 0, 1) }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-[#2C241B]">{{ $user->nombre }}</span>
                                    <span class="text-[11px] text-[#5C5246]">{{ $user->correo }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs text-[#8B8175] font-medium">
                                {{ $user->empresa->nombre ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 rounded bg-[#E6E2DB] text-[#8B8175] text-[9px] font-black uppercase tracking-widest">
                                {{ $user->rol?->nombre ?? 'Sin Rol' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($user->activo)
                                <span class="px-2 py-1 rounded-full bg-green-500/10 text-green-500 text-[9px] font-black uppercase tracking-widest">Activo</span>
                            @else
                                <span class="px-2 py-1 rounded-full bg-rose-500/10 text-rose-500 text-[9px] font-black uppercase tracking-widest">Bloqueado</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                {{-- Toggle Status --}}
                                <button wire:click="toggleStatus('{{ $user->id }}')" 
                                        class="p-2 rounded-xl {{ $user->activo ? 'text-amber-500 bg-amber-500/10 hover:bg-amber-500 hover:text-[#2C241B]' : 'text-green-500 bg-green-500/10 hover:bg-green-500 hover:text-[#2C241B]' }} transition-all"
                                        title="{{ $user->activo ? 'Desactivar' : 'Activar' }}">
                                    @if($user->activo)
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @endif
                                </button>

                                {{-- Delete --}}
                                <button wire:click="confirmDelete('{{ $user->id }}')"
                                        class="p-2 rounded-xl text-rose-500 bg-rose-500/10 hover:bg-rose-500 hover:text-[#2C241B] transition-all"
                                        title="Eliminar usuario">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-[#5C5246] uppercase text-[10px] font-black tracking-widest">No se encontraron usuarios</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="px-6 py-4 border-t border-[#2C241B]/10">
            {{ $users->links() }}
        </div>
    </div>

    {{-- Modal de Confirmación de Eliminación --}}
    @if($confirmingDelete)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#2C241B]/80 backdrop-blur-sm">
            <div class="bg-white border border-rose-500/30 p-8 rounded-[28px] max-w-md w-full shadow-2xl shadow-rose-900/20">
                <div class="w-14 h-14 rounded-full bg-rose-500/10 flex items-center justify-center mb-5">
                    <svg class="w-7 h-7 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>

                <h3 class="text-xl font-black text-[#2C241B] mb-1 tracking-tight">¿Eliminar usuario?</h3>
                <p class="text-[#8B8175] text-sm mb-6 leading-relaxed">
                    Esta acción eliminará permanentemente al usuario y todos sus datos. Ingresa tu contraseña de Super Admin para confirmar.
                </p>

                <div class="mb-6">
                    <label class="block text-[9px] font-black text-[#5C5246] uppercase tracking-widest mb-2">Tu contraseña de Super Admin</label>
                    <input type="password" wire:model="deletePassword"
                           wire:keydown.enter="deleteUser"
                           placeholder="••••••••"
                           class="w-full bg-[#FDFBF7] border border-[#2C241B]/10 rounded-xl py-3 px-4 text-[#2C241B] placeholder-[#8B8175] focus:border-rose-500 focus:ring-0 outline-none transition-all">
                    @error('deletePassword')
                        <span class="text-rose-500 text-[10px] font-bold uppercase mt-1 block tracking-widest">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex gap-3">
                    <button wire:click="cancelDelete"
                            class="flex-1 py-3 text-[#8B8175] font-bold text-xs uppercase tracking-widest hover:text-[#2C241B] hover:bg-[#E6E2DB] rounded-xl transition-all">
                        Cancelar
                    </button>
                    <button wire:click="deleteUser"
                            class="flex-1 bg-rose-600 hover:bg-rose-700 text-white py-3 rounded-xl font-black text-xs uppercase tracking-widest shadow-lg shadow-rose-900/30 transition-all">
                        Sí, Eliminar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
