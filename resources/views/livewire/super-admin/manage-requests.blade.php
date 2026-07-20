<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-[#2C241B] uppercase tracking-tighter">Solicitudes de Registro</h2>
            <p class="text-sm text-[#5C5246] font-medium">Gestiona las nuevas cuentas de gerentes y sus negocios</p>
        </div>
    </div>

    <div class="bg-[#141210] border border-[#2C241B]/10 rounded-[32px] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[700px]">
                <thead>
                    <tr class="bg-white border-b border-[#2C241B]/10">
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[#5C5246]">Negocio / Gerente</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[#5C5246]">Documento</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[#5C5246]">Fecha Solicitud</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[#5C5246] text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#2C241B]/10]">
                    @forelse($requests as $request)
                        <tr class="hover:bg-white transition-colors" wire:key="{{ $request->id }}">
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-[#2C241B]">{{ $request->empresa->nombre ?? 'Sin Empresa' }}</span>
                                    <span class="text-[11px] text-[#5C5246]">NIT: {{ $request->empresa->nit ?? 'N/A' }}</span>
                                    <span class="text-[11px] text-[#E07A5F] mt-1">{{ $request->nombre }} ({{ $request->correo }})</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($request->empresa && $request->empresa->documento_path)
                                    <button wire:click="downloadDoc('{{ $request->empresa->id }}')" class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-blue-400 hover:text-blue-300 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        Ver Documento
                                    </button>
                                @else
                                    <span class="text-[10px] font-bold text-[#5C5246] uppercase">Sin Doc</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs text-[#8B8175] font-medium">
                                {{ $request->creado_en?->format('d/m/Y H:i') ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button 
                                        wire:click="approve('{{ $request->id }}')"
                                        wire:confirm="¿Estás seguro de aprobar esta cuenta?"
                                        class="px-4 py-1.5 rounded-xl bg-green-500/10 text-green-500 hover:bg-green-500 hover:text-[#2C241B] text-[10px] font-black uppercase tracking-widest transition-all"
                                    >
                                        Aprobar
                                    </button>
                                    <button 
                                        wire:click="reject('{{ $request->id }}')"
                                        wire:confirm="¿Estás seguro de rechazar y eliminar esta solicitud?"
                                        class="px-4 py-1.5 rounded-xl bg-rose-500/10 text-rose-500 hover:bg-rose-500 hover:text-[#2C241B] text-[10px] font-black uppercase tracking-widest transition-all"
                                    >
                                        Rechazar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center text-[#5C5246]">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                    </div>
                                    <p class="text-xs font-bold text-[#5C5246] uppercase tracking-widest">No hay solicitudes pendientes</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($requests->hasPages())
            <div class="px-6 py-4 bg-white border-t border-[#2C241B]/10">
                {{ $requests->links() }}
            </div>
        @endif
    </div>

    <!-- Separador Premium -->
    <div class="border-t border-[#2C241B]/10 my-8"></div>

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-[#2C241B] uppercase tracking-tighter">Solicitudes de Actualización de NIT</h2>
            <p class="text-sm text-[#5C5246] font-medium">Gestiona los cambios y actualizaciones de NIT de los negocios activos</p>
        </div>
    </div>

    <div class="bg-[#141210] border border-[#2C241B]/10 rounded-[32px] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[750px]">
                <thead>
                    <tr class="bg-white border-b border-[#2C241B]/10">
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[#5C5246]">Negocio / Gerente</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[#5C5246]">Documento Actual</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[#5C5246]">Documento Nuevo</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[#5C5246] text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#2C241B]/10]">
                    @forelse($nitRequests as $nitRequest)
                        @php
                            $managerUser = $nitRequest->usuarios->first();
                        @endphp
                        <tr class="hover:bg-white transition-colors" wire:key="nit-{{ $nitRequest->id }}">
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-[#2C241B]">{{ $nitRequest->nombre }}</span>
                                    <span class="text-[11px] text-[#5C5246]">NIT: {{ $nitRequest->nit }}</span>
                                    @if($managerUser)
                                        <span class="text-[11px] text-[#E07A5F] mt-1">{{ $managerUser->nombre }} ({{ $managerUser->correo }})</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($nitRequest->documento_path)
                                    <button type="button" wire:click="downloadDoc('{{ $nitRequest->id }}')" class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-[#E07A5F] hover:text-[#78350F] transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        NIT Original
                                    </button>
                                @else
                                    <span class="text-[10px] font-bold text-[#5C5246] uppercase">Sin NIT</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($nitRequest->documento_pendiente_path)
                                    <button type="button" wire:click="downloadPendingDoc('{{ $nitRequest->id }}')" class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-blue-400 hover:text-blue-300 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        Ver Propuesto
                                    </button>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button 
                                        type="button"
                                        wire:click="approveNitUpdate('{{ $nitRequest->id }}')"
                                        wire:confirm="¿Estás seguro de aprobar esta actualización de NIT?"
                                        class="px-4 py-1.5 rounded-xl bg-green-500/10 text-green-500 hover:bg-green-500 hover:text-[#2C241B] text-[10px] font-black uppercase tracking-widest transition-all"
                                    >
                                        Aprobar
                                    </button>
                                    <button 
                                        type="button"
                                        wire:click="openRejectModal('{{ $nitRequest->id }}')"
                                        class="px-4 py-1.5 rounded-xl bg-rose-500/10 text-rose-500 hover:bg-rose-500 hover:text-[#2C241B] text-[10px] font-black uppercase tracking-widest transition-all"
                                    >
                                        Rechazar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center text-[#5C5246]">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                    </div>
                                    <p class="text-xs font-bold text-[#5C5246] uppercase tracking-widest">No hay solicitudes de NIT en trámite</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($nitRequests->hasPages())
            <div class="px-6 py-4 bg-white border-t border-[#2C241B]/10">
                {{ $nitRequests->links() }}
            </div>
        @endif
    </div>

    <!-- Modal de Rechazo de NIT (Con Motivo) -->
    @if($showRejectModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#2C241B]/80 backdrop-blur-sm animate-in fade-in duration-300">
            <div class="bg-white border border-rose-500/30 p-5 sm:p-8 rounded-[32px] max-w-md w-full shadow-2xl shadow-rose-900/20 relative overflow-hidden max-h-[90vh] overflow-y-auto">
                
                {{-- Accent Top Bar --}}
                <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-red-600 to-rose-800"></div>

                <div class="w-16 h-16 rounded-full bg-rose-950/30 border border-rose-500/30 flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>

                <h3 class="text-xl font-black text-[#2C241B] mb-2 tracking-tight uppercase">Rechazar Cambio de NIT</h3>
                <p class="text-[#8B8175] text-xs mb-6 leading-relaxed font-medium">
                    Por favor, escribe el motivo del rechazo. Este mensaje será enviado directamente al correo electrónico del gerente del negocio para que pueda corregirlo.
                </p>
                
                <form wire:submit.prevent="rejectNitUpdate" class="space-y-4">
                    <div>
                        <label class="block text-[9px] font-black text-[#5C5246] uppercase tracking-widest mb-3">Explicación del Motivo</label>
                        <textarea wire:model="motivoRechazo" rows="4" placeholder="Ej. El documento no es legible, o el NIT no coincide con el registro fiscal de la empresa..." 
                                  class="w-full bg-[#FDFBF7] border border-[#2C241B]/10 rounded-2xl py-4 px-6 text-[#2C241B] text-xs placeholder-[#8B8175] focus:border-rose-500 focus:ring-0 outline-none transition-colors resize-none font-sans leading-relaxed"></textarea>
                        
                        @error('motivoRechazo') 
                            <span class="text-rose-500 text-[10px] font-black uppercase mt-2 block tracking-widest">{{ $message }}</span> 
                        @enderror
                    </div>
                    
                    <div class="flex flex-col sm:flex-row gap-3 pt-2">
                        <button type="button" wire:click="closeRejectModal" class="flex-1 py-3 text-[#8B8175] font-black text-[10px] uppercase tracking-widest hover:text-[#2C241B] hover:bg-[#E6E2DB] rounded-2xl transition-all">
                            Cancelar
                        </button>
                        <button type="submit" class="flex-1 bg-rose-600 hover:bg-rose-700 text-white py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-lg shadow-rose-900/30 transition-all active:scale-95">
                            Rechazar y Notificar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
