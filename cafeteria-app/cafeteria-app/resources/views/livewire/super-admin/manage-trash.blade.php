<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-white uppercase tracking-tighter">Papelera de Reciclaje</h2>
            <p class="text-sm text-stone-500 font-medium">Administra los gerentes en estado de recuperación por 30 días</p>
        </div>
    </div>

    {{-- Filtro de Búsqueda --}}
    <div class="bg-[#141210] border border-[#292524] rounded-2xl p-4">
        <div class="relative">
            <input type="text" wire:model.live="search" 
                   class="w-full bg-[#1C1917] border border-[#44403C] rounded-xl px-12 py-3 text-white text-sm focus:border-[#A85507] focus:ring-0 transition-all"
                   placeholder="Buscar por nombre o correo de gerente...">
            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-stone-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </div>
    </div>

    {{-- Tabla de Cuentas en Recuperación --}}
    <div class="bg-[#141210] border border-[#292524] rounded-[32px] overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-[#1C1917]/50 border-b border-[#292524]">
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-stone-500">Gerente</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-stone-500">Negocio / Empresa</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-stone-500">Fecha Eliminación</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-stone-500">Días Restantes</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-stone-500 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#292524]">
                @forelse($users as $user)
                    @php
                        $deletedAt = \Carbon\Carbon::parse($user->eliminado_en);
                        $daysPassed = (int) $deletedAt->diffInDays(now());
                        $daysRemaining = (int) max(0, 30 - $daysPassed);
                        
                        // Determinar color de la alerta del contador
                        if ($daysRemaining > 15) {
                            $badgeColor = 'bg-emerald-500/10 text-emerald-500';
                            $barColor = 'bg-emerald-500';
                        } elseif ($daysRemaining > 5) {
                            $badgeColor = 'bg-amber-500/10 text-amber-500';
                            $barColor = 'bg-amber-500';
                        } else {
                            $badgeColor = 'bg-rose-500/10 text-rose-500';
                            $barColor = 'bg-rose-500';
                        }
                    @endphp
                    <tr class="hover:bg-[#1C1917]/30 transition-colors" wire:key="{{ $user->id }}">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-[#A85507]/20 text-[#A85507] flex items-center justify-center font-black text-xs">
                                    {{ substr($user->nombre, 0, 1) }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-white">{{ $user->nombre }}</span>
                                    <span class="text-[11px] text-stone-500">{{ $user->correo }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs text-stone-400 font-medium">
                                {{ $user->empresa->nombre ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs text-stone-400">
                                {{ $deletedAt->format('d/m/Y h:i A') }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col gap-1 w-32">
                                <div class="flex justify-between items-center text-[10px] font-bold">
                                    <span class="px-2 py-0.5 rounded-full {{ $badgeColor }} uppercase tracking-wider">
                                        {{ $daysRemaining }} días
                                    </span>
                                </div>
                                <div class="w-full bg-[#1C1917] h-1.5 rounded-full overflow-hidden border border-stone-800">
                                    <div class="h-full {{ $barColor }} transition-all" style="width: {{ ($daysRemaining / 30) * 100 }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                {{-- Reenviar Correo --}}
                                <button wire:click="resendRecoveryEmail('{{ $user->id }}')" 
                                        class="p-2 rounded-xl text-sky-500 bg-sky-500/10 hover:bg-sky-500 hover:text-white transition-all"
                                        title="Reenviar correo de recuperación">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L22 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </button>

                                {{-- Restaurar --}}
                                <button wire:click="restoreUser('{{ $user->id }}')" 
                                        class="p-2 rounded-xl text-emerald-500 bg-emerald-500/10 hover:bg-emerald-500 hover:text-white transition-all"
                                        title="Restaurar gerente y negocio">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 6H16"/>
                                    </svg>
                                </button>

                                {{-- Eliminar Permanentemente --}}
                                <button wire:click="confirmForceDelete('{{ $user->id }}')"
                                        class="p-2 rounded-xl text-rose-500 bg-rose-500/10 hover:bg-rose-500 hover:text-white transition-all"
                                        title="Eliminar permanentemente">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-stone-600 uppercase text-[10px] font-black tracking-widest">La papelera está vacía</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="px-6 py-4 border-t border-[#292524]">
            {{ $users->links() }}
        </div>
    </div>

    {{-- Modal de Confirmación de Eliminación Permanente --}}
    @if($confirmingForceDelete)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
            <div class="bg-[#1C1917] border border-rose-500/30 p-8 rounded-[28px] max-w-md w-full shadow-2xl shadow-rose-900/20">
                <div class="w-14 h-14 rounded-full bg-rose-500/10 flex items-center justify-center mb-5">
                    <svg class="w-7 h-7 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>

                <h3 class="text-xl font-black text-white mb-1 tracking-tight">¿Eliminar permanentemente?</h3>
                <p class="text-stone-400 text-sm mb-6 leading-relaxed">
                    Esta acción es irreversible y **eliminará de forma definitiva** al gerente, su empresa, todas sus sucursales y sus registros. Ingresa tu contraseña de Super Admin para confirmar.
                </p>

                <div class="mb-6">
                    <label class="block text-[9px] font-black text-stone-500 uppercase tracking-widest mb-2">Tu contraseña de Super Admin</label>
                    <input type="password" wire:model="deletePassword"
                           wire:keydown.enter="forceDeleteUser"
                           placeholder="••••••••"
                           class="w-full bg-[#0C0A09] border border-stone-800 rounded-xl py-3 px-4 text-white placeholder-stone-600 focus:border-rose-500 focus:ring-0 outline-none transition-all">
                    @error('deletePassword')
                        <span class="text-rose-500 text-[10px] font-bold uppercase mt-1 block tracking-widest">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex gap-3">
                    <button wire:click="cancelForceDelete"
                            class="flex-1 py-3 text-stone-400 font-bold text-xs uppercase tracking-widest hover:text-white hover:bg-stone-800 rounded-xl transition-all">
                        Cancelar
                    </button>
                    <button wire:click="forceDeleteUser"
                            class="flex-1 bg-rose-600 hover:bg-rose-700 text-white py-3 rounded-xl font-black text-xs uppercase tracking-widest shadow-lg shadow-rose-900/30 transition-all">
                        Sí, Eliminar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
