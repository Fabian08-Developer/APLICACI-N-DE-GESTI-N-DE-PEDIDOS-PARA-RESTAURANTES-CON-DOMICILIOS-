<div x-data="{
    activeTab: 'pedidos',
    disponible: true,
    showConfirmStateModal: $wire.entangle('showConfirmStateModal'),
    showProblemaModal: $wire.entangle('showProblemaModal'),
    nextStateText: '',
    
    init() {
        this.disponible = '{{ $domiciliario['estado'] }}' === 'disponible';
        
        window.addEventListener('estado-actualizado', event => {
            Swal.fire({
                title: '¡Actualizado!',
                text: 'El estado del pedido ha sido actualizado.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false,
                background: '#141A21',
                color: '#F8FAFC'
            });
        });

        window.addEventListener('error-distancia', event => {
            Swal.fire({
                title: 'Estás muy lejos',
                text: 'Debes acercarte a menos de 50 metros del cliente para confirmar la entrega.',
                icon: 'error',
                background: '#141A21',
                color: '#F8FAFC'
            });
        });

        // GEOLOCATION TRACKING
        if (navigator.geolocation && this.disponible) {
            navigator.geolocation.watchPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    $wire.actualizarUbicacion(lat, lng);
                },
                (error) => {
                    console.error('Error obteniendo ubicación', error);
                },
                { enableHighAccuracy: true, maximumAge: 10000, timeout: 10000 }
            );
        }
    },

    setTab(tab) {
        this.activeTab = tab;
    },

    toggleDisponibilidad() {
        this.disponible = !this.disponible;
        $wire.toggleDisponibilidad();
    },

    openConfirmModal(pedidoId, currentState) {
        switch(currentState) {
            case 'PENDIENTE_PAGO':
            case 'CREADO':
            case 'EN_PREPARACION':
            case 'LISTO':
            case 'ASIGNADO': this.nextStateText = 'Iniciar Entrega'; break;
            case 'EN_CAMINO': this.nextStateText = 'Confirmar Entrega'; break;
            default: this.nextStateText = 'Avanzar Estado'; break;
        }
        $wire.openConfirmModal(pedidoId);
    },

    closeConfirmModal() {
        $wire.closeConfirmModal();
    },

    openProblemaModal(pedidoId) {
        this.showProblemaModal = true;
    },
    
    actionCall(phone) {
        window.open(`tel:${phone}`);
    },
    
    actionWhatsApp(phone, mensaje) {
        window.open(`https://wa.me/57${phone}?text=${encodeURIComponent(mensaje)}`);
    },
    
    actionNavigate(address) {
        const query = `${address}, Neiva, Huila, Colombia`;
        window.open(`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(query)}`);
    }
}" class="min-h-screen w-full relative" x-cloak>
    
    <!-- Header -->
    @include('domiciliario.partials.header')

    <!-- Tabs Content -->
    <div class="w-full pb-20 overflow-y-auto no-scrollbar">
        <!-- Tab: Pedidos -->
        <div x-show="activeTab === 'pedidos'"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-4"
             x-transition:enter-end="opacity-100 translate-x-0">
            @include('domiciliario.tabs.pedidos')
        </div>

        <!-- Tab: Historial -->
        <div x-show="activeTab === 'historial'"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-4"
             x-transition:enter-end="opacity-100 translate-x-0"
             style="display: none;">
            @include('domiciliario.tabs.historial')
        </div>

        <!-- Tab: Perfil -->
        <div x-show="activeTab === 'perfil'"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-4"
             x-transition:enter-end="opacity-100 translate-x-0"
             style="display: none;">
            @include('domiciliario.tabs.perfil')
        </div>
    </div>

    <!-- Bottom Navigation -->
    <nav class="fixed bottom-0 left-0 right-0 z-40 bg-[#0B1015]/95 backdrop-blur-xl border-t border-dom-border h-[68px] pb-safe shadow-[0_-10px_40px_rgba(0,0,0,0.5)]">
        <div class="max-w-md mx-auto h-full flex items-center justify-around px-2">
            <!-- Tab: Pedidos -->
            <button @click="setTab('pedidos')" 
                    class="bottom-nav-item"
                    :class="{'active': activeTab === 'pedidos'}">
                <div class="relative">
                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    @if(count($pedidos) > 0)
                    <span class="absolute -top-1 -right-2 bg-[#00A8B5] text-white text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center shadow-sm">
                        {{ count($pedidos) }}
                    </span>
                    @endif
                </div>
                <span class="text-[10px] font-medium tracking-wide">Pedidos</span>
            </button>

            <!-- Tab: Historial -->
            <button @click="setTab('historial')" 
                    class="bottom-nav-item"
                    :class="{'active': activeTab === 'historial'}">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                <span class="text-[10px] font-medium tracking-wide">Historial</span>
            </button>

            <!-- Tab: Perfil -->
            <button @click="setTab('perfil')" 
                    class="bottom-nav-item"
                    :class="{'active': activeTab === 'perfil'}">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span class="text-[10px] font-medium tracking-wide">Perfil</span>
            </button>
        </div>
    </nav>

    <!-- Modals -->
    @include('domiciliario.modals.reportar-problema')

    <!-- Modal Confirmar Estado -->
    <div x-show="showConfirmStateModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
         style="display: none;"
         x-cloak>
        
        <div x-show="showConfirmStateModal"
             x-transition:enter="modal-enter"
             x-transition:leave="modal-leave"
             @click.away="closeConfirmModal"
             class="bg-dom-surface w-full max-w-sm rounded-2xl shadow-2xl border border-dom-border overflow-hidden">
            
            <div class="p-6 text-center">
                <div class="w-16 h-16 bg-[#00A8B5]/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-[#00A8B5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                
                <h3 class="text-xl font-bold text-white mb-2">Confirmar Acción</h3>
                <p class="text-dom-text-muted mb-6" x-text="'¿Estás seguro de ' + nextStateText + '?'"></p>
                
                <div class="flex gap-3">
                    <button @click="closeConfirmModal" class="dom-btn-secondary flex-1">
                        Cancelar
                    </button>
                    <!-- Trigger Livewire method on click -->
                    <button wire:click="confirmarEstado" class="dom-btn-primary flex-1">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
