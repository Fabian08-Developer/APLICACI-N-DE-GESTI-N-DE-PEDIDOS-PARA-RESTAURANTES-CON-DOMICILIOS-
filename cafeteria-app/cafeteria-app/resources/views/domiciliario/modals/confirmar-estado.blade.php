<!-- Modal Confirmar Estado -->
<div x-show="showConfirmStateModal" 
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
     style="display: none;">
    
    <div x-show="showConfirmStateModal"
         x-transition:enter="fade-enter"
         x-transition:leave="fade-leave"
         @click.away="showConfirmStateModal = false"
         class="w-full max-w-sm bg-dom-surface rounded-2xl p-6 shadow-2xl border border-dom-border">
        
        <div class="w-12 h-12 rounded-full bg-[#00A8B5]/10 text-[#00A8B5] flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
            </svg>
        </div>

        <h3 class="text-lg font-bold text-white text-center mb-2">¿Actualizar estado?</h3>
        <p class="text-sm text-dom-text-muted text-center mb-6">
            El pedido <span class="text-white font-bold" x-text="selectedPedido"></span> pasará al siguiente estado: <br>
            <strong class="text-[#00A8B5] mt-1 inline-block" x-text="nextStateText"></strong>
        </p>

        <div class="flex gap-3">
            <button @click="showConfirmStateModal = false" class="flex-1 dom-btn-secondary py-3">
                Cancelar
            </button>
            <button @click="confirmStateChange()" class="flex-1 dom-btn-primary !py-3 shadow-[0_0_15px_rgba(0,168,181,0.3)]">
                Confirmar
            </button>
        </div>
    </div>
</div>
