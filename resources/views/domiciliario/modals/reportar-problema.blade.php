<!-- Modal Reportar Problema -->
<div x-show="showProblemaModal" 
     class="fixed inset-0 z-50 flex flex-col justify-end bg-black/60 backdrop-blur-sm sm:items-center sm:justify-center"
     style="display: none;">
    
    <div x-show="showProblemaModal"
         x-transition:enter="modal-enter"
         x-transition:leave="modal-leave"
         @click.away="showProblemaModal = false"
         class="w-full max-w-md bg-dom-surface rounded-t-2xl sm:rounded-2xl p-6 shadow-2xl border-t sm:border border-dom-border relative pb-safe">
        
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-dom-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                Reportar Problema
            </h3>
            <button @click="showProblemaModal = false" class="text-dom-text-muted hover:text-white p-1">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form @submit.prevent="showProblemaModal = false; Swal.fire({title: 'Reporte Enviado', text: 'Central ha sido notificada.', icon: 'success', background: '#141A21', color: '#fff'})">
            
            <p class="text-sm text-dom-text-muted mb-4">Selecciona el problema principal para el pedido <span class="text-white font-bold" x-text="selectedPedido"></span>:</p>
            
            <div class="space-y-2 mb-6">
                <label class="flex items-center gap-3 p-3 rounded-xl border border-dom-border cursor-pointer hover:bg-white/5 transition-colors">
                    <input type="radio" name="motivo" value="cliente_no_contesta" class="w-4 h-4 text-dom-danger bg-dom-bg border-dom-border focus:ring-dom-danger focus:ring-offset-dom-surface">
                    <span class="text-sm text-white">Cliente no contesta</span>
                </label>
                <label class="flex items-center gap-3 p-3 rounded-xl border border-dom-border cursor-pointer hover:bg-white/5 transition-colors">
                    <input type="radio" name="motivo" value="direccion_incorrecta" class="w-4 h-4 text-dom-danger bg-dom-bg border-dom-border focus:ring-dom-danger focus:ring-offset-dom-surface">
                    <span class="text-sm text-white">Dirección incorrecta o incompleta</span>
                </label>
                <label class="flex items-center gap-3 p-3 rounded-xl border border-dom-border cursor-pointer hover:bg-white/5 transition-colors">
                    <input type="radio" name="motivo" value="cliente_cancelo" class="w-4 h-4 text-dom-danger bg-dom-bg border-dom-border focus:ring-dom-danger focus:ring-offset-dom-surface">
                    <span class="text-sm text-white">Cliente canceló en puerta</span>
                </label>
                <label class="flex items-center gap-3 p-3 rounded-xl border border-dom-border cursor-pointer hover:bg-white/5 transition-colors">
                    <input type="radio" name="motivo" value="producto_daniado" class="w-4 h-4 text-dom-danger bg-dom-bg border-dom-border focus:ring-dom-danger focus:ring-offset-dom-surface">
                    <span class="text-sm text-white">Producto dañado / Derramado</span>
                </label>
            </div>

            <div class="mb-6">
                <label class="block text-xs font-semibold tracking-wider text-dom-text-muted uppercase mb-2">Detalles adicionales (Opcional)</label>
                <textarea rows="3" class="w-full bg-dom-bg border border-dom-border rounded-xl p-3 text-sm text-white placeholder-dom-text-muted/50 focus:border-dom-danger focus:ring-1 focus:ring-dom-danger outline-none transition-colors" placeholder="Escribe más detalles sobre el problema..."></textarea>
            </div>

            <button type="submit" class="w-full dom-btn bg-dom-danger hover:bg-dom-danger-hover text-white py-3.5 font-semibold text-sm shadow-[0_0_15px_rgba(255,71,87,0.3)]">
                Enviar Reporte a Central
            </button>
        </form>
    </div>
</div>
