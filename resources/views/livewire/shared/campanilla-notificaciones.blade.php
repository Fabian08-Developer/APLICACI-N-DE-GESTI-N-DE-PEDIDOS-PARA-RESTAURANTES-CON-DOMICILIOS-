<div x-data="{ abierto: @entangle('panelAbierto') }"
     style="position: relative; display: flex; align-items: center;"
     @click.outside="abierto = false; $wire.panelAbierto = false">

    {{-- Botón campanilla --}}
    <button
        wire:click="togglePanel"
        title="Notificaciones"
        style="position: relative; background: none; border: none; cursor: pointer; padding: 0.35rem; border-radius: 0.5rem; color: var(--text-sec, #6B7280); transition: all 0.2s; display: flex; align-items: center; justify-content: center;"
        onmouseover="this.style.background='rgba(0,0,0,0.06)'"
        onmouseout="this.style.background='none'">

        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none"
             viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>

        {{-- Badge contador --}}
        @if($noLeidas > 0)
        <span style="
            position: absolute; top: 0; right: 0;
            background: #E07A5F; color: #fff;
            font-size: 0.6rem; font-weight: 800;
            min-width: 16px; height: 16px;
            border-radius: 8px; padding: 0 3px;
            display: flex; align-items: center; justify-content: center;
            border: 1.5px solid #fff;
            line-height: 1;
        ">{{ $noLeidas > 9 ? '9+' : $noLeidas }}</span>
        @endif
    </button>

    {{-- Panel desplegable --}}
    <div x-show="abierto"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="
            position: absolute; top: calc(100% + 10px); right: 0;
            width: 340px; max-height: 420px;
            background: #fff; border: 1px solid rgba(0,0,0,0.08);
            border-radius: 1rem; overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.12);
            z-index: 9999;
         ">

        {{-- Cabecera del panel --}}
        <div style="
            padding: 0.9rem 1.1rem;
            display: flex; align-items: center; justify-content: space-between;
            border-bottom: 1px solid rgba(0,0,0,0.06);
            background: linear-gradient(to bottom, rgba(224,122,95,0.04), transparent);
        ">
            <span style="font-weight: 700; font-size: 0.85rem; color: #2C241B;">
                Notificaciones
                @if($noLeidas > 0)
                    <span style="background: rgba(224,122,95,0.12); color: #E07A5F; font-size: 0.7rem; font-weight: 800; padding: 1px 7px; border-radius: 10px; margin-left: 4px;">
                        {{ $noLeidas }} nuevas
                    </span>
                @endif
            </span>
            @if($noLeidas > 0)
            <button wire:click="marcarTodas"
                    style="font-size: 0.7rem; color: #E07A5F; background: none; border: none; cursor: pointer; font-weight: 600; padding: 2px 6px; border-radius: 6px; transition: all 0.2s;"
                    onmouseover="this.style.background='rgba(224,122,95,0.08)'"
                    onmouseout="this.style.background='none'">
                Marcar todas leídas
            </button>
            @endif
        </div>

        {{-- Lista de notificaciones --}}
        <div style="overflow-y: auto; max-height: 340px;">
            @forelse($notificaciones as $noti)
            <div wire:click="marcarLeida('{{ $noti['id'] }}')"
                 style="
                    padding: 0.85rem 1.1rem;
                    border-bottom: 1px solid rgba(0,0,0,0.04);
                    cursor: pointer;
                    transition: background 0.15s;
                    background: {{ !$noti['leida'] ? 'rgba(224,122,95,0.04)' : 'transparent' }};
                    display: flex; gap: 0.75rem; align-items: flex-start;
                 "
                 onmouseover="this.style.background='rgba(0,0,0,0.03)'"
                 onmouseout="this.style.background='{{ !$noti['leida'] ? 'rgba(224,122,95,0.04)' : 'transparent' }}'">

                {{-- Indicador no leída --}}
                <div style="flex-shrink: 0; padding-top: 4px;">
                    @if(!$noti['leida'])
                    <div style="width: 8px; height: 8px; background: #E07A5F; border-radius: 50%;"></div>
                    @else
                    <div style="width: 8px; height: 8px;"></div>
                    @endif
                </div>

                <div style="flex: 1; min-width: 0;">
                    <p style="font-size: 0.8rem; font-weight: {{ !$noti['leida'] ? '700' : '500' }}; color: #2C241B; margin: 0 0 2px; line-height: 1.3;">
                        {{ $noti['titulo'] }}
                    </p>
                    <p style="font-size: 0.75rem; color: #6B7280; margin: 0; line-height: 1.4; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        {{ $noti['mensaje'] }}
                    </p>
                    <p style="font-size: 0.68rem; color: #9CA3AF; margin: 4px 0 0; font-weight: 500;">
                        {{ $noti['hace'] }}
                    </p>
                </div>
            </div>
            @empty
            <div style="padding: 2.5rem 1rem; text-align: center; color: #9CA3AF;">
                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1" style="margin: 0 auto 0.75rem; display: block; opacity: 0.4;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <p style="font-size: 0.8rem; margin: 0; font-weight: 500;">Sin notificaciones por ahora</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Indicador de carga de Livewire --}}
    <div wire:loading wire:target="marcarLeida,marcarTodas,togglePanel"
         style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; pointer-events: none;">
        <div style="width: 14px; height: 14px; border: 2px solid rgba(224,122,95,0.3); border-top-color: #E07A5F; border-radius: 50%; animation: spin 0.7s linear infinite;"></div>
    </div>
</div>
