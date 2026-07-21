@extends('mesero.layout')

@section('titulo', 'Reservas del Día')

@section('contenido')
<div class="page-reservas">

    {{-- Header --}}
    <div class="reservas-header">
        <div>
            <h1 class="page-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: text-bottom; margin-right: 4px; color: var(--primary);"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                Reservas
            </h1>
            <p class="page-subtitle">Gestión de reservas del día</p>
        </div>
        <div class="header-actions">
            <form method="GET" action="{{ route('mesero.reservas.index') }}" class="date-form">
                <input type="date" name="fecha" value="{{ $fecha }}"
                       class="date-input" onchange="this.form.submit()" {{ (isset($alertaSucursal) && $alertaSucursal) ? 'disabled' : '' }}>
            </form>
            @if(isset($alertaSucursal) && $alertaSucursal)
            <button class="btn-nueva-reserva" disabled style="opacity: 0.5; cursor: not-allowed;">
                + Nueva Reserva
            </button>
            @else
            <a href="{{ route('mesero.reservas.crear') }}" class="btn-nueva-reserva">
                + Nueva Reserva
            </a>
            @endif
        </div>
    </div>

    {{-- Alertas --}}
    @if(isset($alertaSucursal) && $alertaSucursal)
    <div class="empty-state" style="margin-top: 4rem; border-color: rgba(245, 158, 11, 0.3); background: rgba(245, 158, 11, 0.02);">
        <div class="empty-icon" style="color: #f59e0b; opacity: 1;">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><path d="M12 9v4"></path><path d="M12 17h.01"></path></svg>
        </div>
        <h3 style="margin-bottom: 0.75rem; font-size: 1.5rem; font-family: 'Playfair Display', serif; color: var(--text-main);">Acceso Restringido</h3>
        <p style="font-size: 1rem; max-width: 500px; margin: 0 auto; line-height: 1.5; color: var(--text-muted);">{{ $alertaSucursal }}</p>
    </div>
    @endif
    
    @if(session('exito'))
    <div class="modern-alert modern-alert-success">
        <div class="modern-alert-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
        </div>
        <div class="modern-alert-content">
            <p class="modern-alert-message">{{ session('exito') }}</p>
        </div>
    </div>
    @endif
    
    @if(session('error'))
    <div class="modern-alert modern-alert-error">
        <div class="modern-alert-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
        </div>
        <div class="modern-alert-content">
            <p class="modern-alert-message">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    {{-- KPIs --}}
    @if(!isset($alertaSucursal) || !$alertaSucursal)
    <div class="kpi-row">
        <div class="kpi-card">
            <div class="kpi-val">{{ $resumen['total'] }}</div>
            <div class="kpi-lbl">Total</div>
        </div>
        <div class="kpi-card kpi-green">
            <div class="kpi-val">{{ $resumen['confirmadas'] }}</div>
            <div class="kpi-lbl">Confirmadas</div>
        </div>
        <div class="kpi-card kpi-amber">
            <div class="kpi-val">{{ $resumen['pendientes'] }}</div>
            <div class="kpi-lbl">Pendientes</div>
        </div>
        <div class="kpi-card kpi-blue">
            <div class="kpi-val">{{ $resumen['llegaron'] }}</div>
            <div class="kpi-lbl">Llegaron</div>
        </div>
    </div>
    @endif

    {{-- Lista de reservas --}}
    @if(!isset($alertaSucursal) || !$alertaSucursal)
        @if($reservas->isEmpty())
        <div class="empty-state">
            <div class="empty-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"></path><path d="M7 2v20"></path><path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"></path></svg>
            </div>
            <p>No hay reservas para el {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</p>
        </div>
        @else
    <div class="reservas-list">
        @foreach($reservas as $reserva)
        <div class="reserva-card estado-{{ $reserva->estado->value }}">

            {{-- Tiempo y mesa --}}
            <div class="rc-time">
                <div class="rc-hora">{{ $reserva->hora_inicio }}</div>
                <div class="rc-mesa">{{ $reserva->mesas->count() > 0 ? 'Mesas: ' . $reserva->mesas->pluck('numero')->join(', ') : '—' }}</div>
            </div>

            {{-- Info --}}
            <div class="rc-info">
                <div class="rc-nombre">{{ $reserva->nombre_cliente }}</div>
                <div class="rc-meta">
                    <span style="display: flex; align-items: center; gap: 6px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        {{ $reserva->numero_personas }} personas
                    </span>
                    <span class="rc-code">{{ $reserva->codigo_reserva }}</span>
                </div>
                @if($reserva->notas_cliente)
                <div class="rc-nota" style="display: flex; align-items: flex-start; gap: 6px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-top: 2px; flex-shrink: 0;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    <span>{{ $reserva->notas_cliente }}</span>
                </div>
                @endif

                {{-- Depósito --}}
                @if($reserva->monto_deposito > 0)
                <div class="rc-deposito {{ $reserva->deposito_pagado ? 'pagado' : 'pendiente' }}" style="display: inline-flex; align-items: center; gap: 4px;">
                    @if($reserva->deposito_pagado)
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        Depósito pagado
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        Depósito pendiente
                    @endif
                    · ${{ number_format($reserva->monto_deposito, 0, ',', '.') }}
                </div>
                @endif
            </div>

            {{-- Estado y acciones --}}
            <div class="rc-actions">
                <span class="estado-badge estado-{{ $reserva->estado->value }}">
                    {{ $reserva->estado->etiqueta() }}
                </span>

                <div class="action-btns">

                    {{-- Aprobar depósito --}}
                    @if(!$reserva->deposito_pagado && $reserva->monto_deposito > 0)
                    <button class="btn-accion btn-green" onclick="abrirModalDeposito('{{ $reserva->id }}', '{{ $reserva->codigo_reserva }}')" style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                        Aprobar pago
                    </button>
                    @endif

                    {{-- Confirmar --}}
                    @if($reserva->estado->value === 'pendiente')
                    <form method="POST" action="{{ route('mesero.reservas.confirmar', $reserva->id) }}">
                        @csrf
                        <button type="submit" class="btn-accion btn-blue" style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"></path></svg>
                            Confirmar
                        </button>
                    </form>
                    @endif

                    {{-- Check-in --}}
                    @if($reserva->estado->value === 'confirmada')
                    <form method="POST" action="{{ route('mesero.reservas.check-in', $reserva->id) }}" style="width: 100%;">
                        @csrf
                        <button type="submit" class="btn-accion btn-green" style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><polyline points="17 11 19 13 23 9"></polyline></svg>
                            Check-in
                        </button>
                    </form>
                    @endif

                    {{-- Cancelar --}}
                    @if(!$reserva->estado->esFinal())
                    <button class="btn-accion btn-red" onclick="abrirModalCancelar('{{ $reserva->id }}', '{{ $reserva->codigo_reserva }}')" style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        Cancelar
                    </button>
                    @endif

                </div>
            </div>
        </div>
        @endforeach
    </div>
        @endif
    @endif
</div>

{{-- Modal: Cancelar --}}
<div class="modal-overlay" id="modalCancelar" style="display:none" onclick="cerrarModal('modalCancelar')">
    <div class="modal-box" onclick="event.stopPropagation()">
        <h3 class="modal-title">Cancelar Reserva</h3>
        <p class="modal-sub" id="modalCancelarCodigo"></p>
        <form method="POST" id="formCancelar">
            @csrf
            <div class="form-group">
                <label class="form-label">Motivo de cancelación *</label>
                <textarea name="motivo" class="form-control" rows="3" required
                    placeholder="Ej: Mesa no disponible, error del cliente..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="cerrarModal('modalCancelar')">Volver</button>
                <button type="submit" class="btn-modal-confirm btn-red-solid">Cancelar Reserva</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Aprobar Depósito --}}
<div class="modal-overlay" id="modalDeposito" style="display:none" onclick="cerrarModal('modalDeposito')">
    <div class="modal-box" onclick="event.stopPropagation()">
        <h3 class="modal-title">Aprobar Depósito</h3>
        <p class="modal-sub" id="modalDepositoCodigo"></p>
        <form method="POST" id="formDeposito">
            @csrf
            <div class="form-group">
                <label class="form-label">Referencia / Comprobante *</label>
                <input type="text" name="referencia" class="form-control"
                    placeholder="Número de comprobante o 'Efectivo'" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="cerrarModal('modalDeposito')">Volver</button>
                <button type="submit" class="btn-modal-confirm btn-green-solid">Aprobar Pago</button>
            </div>
        </form>
    </div>
</div>

<style>
.page-reservas { width: 100%; }
.reservas-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; gap: 1rem; flex-wrap: wrap; }
.page-title { font-size: 1.8rem; font-weight: 700; color: var(--text-main); font-family: 'Playfair Display', serif; }
.page-subtitle { color: var(--text-dim); font-size: .875rem; margin-top: .2rem; }
.header-actions { display: flex; gap: .75rem; align-items: center; }
.date-input { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: .6rem .9rem; color: var(--text-main); font-size: .875rem; font-family: inherit; }
.btn-nueva-reserva { background: var(--primary); color: #fff; border: none; border-radius: var(--radius-sm); padding: .65rem 1.25rem; font-size: .875rem; font-weight: 600; cursor: pointer; text-decoration: none; white-space: nowrap; transition: 0.2s; box-shadow: var(--shadow-sm); }
.btn-nueva-reserva:hover { background: #ce6c51; transform: translateY(-1px); box-shadow: var(--shadow-md); }
.kpi-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
.kpi-card { background: var(--surface); border: 1px solid var(--border-light); border-radius: var(--radius); padding: 1.25rem; text-align: center; box-shadow: var(--shadow-sm); }
.kpi-card.kpi-green { border-color: rgba(16,185,129,.25); }
.kpi-card.kpi-amber { border-color: rgba(224,122,95,.3); }
.kpi-card.kpi-blue  { border-color: rgba(59,130,246,.3); }
.kpi-val { font-size: 2rem; font-weight: 700; color: var(--text-main); font-family: 'Playfair Display', serif; }
.kpi-lbl { font-size: .75rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: .05em; margin-top: .4rem; font-weight: 600; }
.empty-state { text-align: center; padding: 4rem 2rem; color: var(--text-muted); background: var(--surface); border-radius: var(--radius); border: 1px dashed var(--border); }
.empty-icon { font-size: 3rem; margin-bottom: 1rem; opacity: 0.5; }
.reservas-list { display: flex; flex-direction: column; gap: 1rem; }
.reserva-card { background: var(--surface); border: 1px solid var(--border-light); border-radius: var(--radius); padding: 1.25rem 1.5rem; display: grid; grid-template-columns: 80px 1fr auto; gap: 1.5rem; align-items: start; border-left-width: 4px; box-shadow: var(--shadow-sm); transition: 0.2s; }
.reserva-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); border-color: var(--border); }
.reserva-card.estado-confirmada { border-left-color: var(--status-success); }
.reserva-card.estado-pendiente { border-left-color: var(--status-warning); }
.reserva-card.estado-pendiente_pago { border-left-color: var(--primary); }
.reserva-card.estado-cliente_llego { border-left-color: var(--status-info); }
.reserva-card.estado-completada { border-left-color: var(--text-dim); opacity: .7; }
.reserva-card.estado-cancelada, .reserva-card.estado-no_show { border-left-color: var(--status-error); opacity: .6; }
.rc-time { text-align: center; border-right: 1px solid var(--border-light); padding-right: 1.5rem; }
.rc-hora { font-size: 1.3rem; font-weight: 700; color: var(--text-main); font-family: 'Playfair Display', serif; }
.rc-mesa { font-size: .75rem; color: var(--text-dim); margin-top: .3rem; font-weight: 500; }
.rc-nombre { font-weight: 600; font-size: 1.05rem; margin-bottom: .4rem; color: var(--text-main); }
.rc-meta { display: flex; gap: 1rem; font-size: .8rem; color: var(--text-muted); margin-bottom: .4rem; }
.rc-code { font-family: monospace; background: var(--surface-2); padding: 2px 6px; border-radius: 4px; }
.rc-nota { font-size: .8rem; color: var(--text-dim); font-style: italic; margin-top: .4rem; background: var(--surface-2); padding: 0.5rem 0.8rem; border-radius: 6px; }
.rc-deposito { display: inline-block; padding: .25rem .7rem; border-radius: 6px; font-size: .75rem; font-weight: 600; margin-top: .5rem; }
.rc-deposito.pagado { background: rgba(16,185,129,.1); color: #059669; }
.rc-deposito.pendiente { background: var(--accent-bg); color: var(--primary); }
.rc-actions { display: flex; flex-direction: column; gap: .75rem; align-items: flex-end; }
.estado-badge { display: inline-block; padding: .3rem .8rem; border-radius: 100px; font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; white-space: nowrap; border: 1px solid transparent; }
.estado-badge.estado-confirmada { background: rgba(16,185,129,.1); color: #059669; border-color: rgba(16,185,129,.2); }
.estado-badge.estado-pendiente { background: rgba(242,204,143,.2); color: #B48A36; border-color: rgba(242,204,143,.4); }
.estado-badge.estado-pendiente_pago { background: var(--accent-bg); color: var(--primary); border-color: rgba(224,122,95,.2); }
.estado-badge.estado-cliente_llego { background: rgba(61,90,128,.1); color: var(--status-info); border-color: rgba(61,90,128,.2); }
.estado-badge.estado-cancelada { background: rgba(224,122,95,.1); color: var(--status-error); border-color: rgba(224,122,95,.2); }
.estado-badge.estado-no_show { background: rgba(224,122,95,.08); color: var(--status-error); }
.estado-badge.estado-completada { background: var(--surface-2); color: var(--text-dim); border-color: var(--border); }
.action-btns { display: flex; flex-direction: column; gap: .5rem; width: 100%; }
.btn-accion { border: 1px solid transparent; border-radius: var(--radius-sm); padding: .5rem .85rem; font-size: .8rem; font-weight: 600; cursor: pointer; white-space: nowrap; transition: all .2s; font-family: inherit; width: 100%; }
.btn-blue { background: var(--surface); color: var(--status-info); border-color: rgba(61,90,128,.2); }
.btn-blue:hover { background: rgba(61,90,128,.05); border-color: var(--status-info); }
.btn-green { background: var(--surface); color: #059669; border-color: rgba(16,185,129,.2); }
.btn-green:hover { background: rgba(16,185,129,.05); border-color: #059669; }
.btn-red { background: var(--surface); color: var(--status-error); border-color: rgba(224,122,95,.2); }
.btn-red:hover { background: var(--accent-bg); border-color: var(--status-error); }
/* Modals */
.modal-overlay { position: fixed; inset: 0; background: rgba(44,36,27,.4); backdrop-filter: blur(4px); z-index: 2000; display: flex; align-items: center; justify-content: center; padding: 1rem; }
.modal-box { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 2rem; max-width: 440px; width: 100%; box-shadow: var(--shadow-md); }
.modal-title { font-size: 1.3rem; font-weight: 700; margin-bottom: .35rem; color: var(--text-main); font-family: 'Playfair Display', serif; }
.modal-sub { color: var(--text-dim); font-size: .85rem; margin-bottom: 1.5rem; }
.form-group { margin-bottom: 1.25rem; }
.form-label { display: block; font-size: .75rem; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); font-weight: 600; margin-bottom: .5rem; }
.form-control { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: .75rem 1rem; color: var(--text-main); font-family: inherit; font-size: .9rem; width: 100%; transition: 0.2s; }
.form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px var(--accent-bg); }
.modal-footer { display: flex; gap: .75rem; justify-content: flex-end; margin-top: 1.5rem; }
.btn-modal-cancel { background: var(--surface-2); border: 1px solid var(--border-light); border-radius: var(--radius-sm); padding: .65rem 1.25rem; color: var(--text-main); font-size: .875rem; cursor: pointer; font-weight: 600; }
.btn-modal-confirm { border: none; border-radius: var(--radius-sm); padding: .65rem 1.25rem; font-weight: 600; font-size: .875rem; cursor: pointer; }
.btn-red-solid { background: var(--status-error); color: #fff; }
.btn-red-solid:hover { background: #ce6c51; }
.btn-green-solid { background: var(--status-success); color: #fff; }
.btn-green-solid:hover { background: #6da289; }

/* Modern Alerts */
.modern-alert {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem 1.25rem;
    border-radius: var(--radius-sm, 12px);
    margin-bottom: 1.5rem;
    border-left: 4px solid transparent;
    box-shadow: 0 2px 10px rgba(0,0,0,0.02);
    animation: fadeIn 0.3s ease-out forwards;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-5px); }
    to { opacity: 1; transform: translateY(0); }
}
.modern-alert-icon {
    flex-shrink: 0;
    margin-top: 2px;
}
.modern-alert-content {
    flex-grow: 1;
}
.modern-alert-title {
    margin: 0 0 0.35rem 0;
    font-size: 1rem;
    font-weight: 700;
}
.modern-alert-message {
    margin: 0;
    font-size: 0.9rem;
    line-height: 1.5;
}

/* Warning Alert */
.modern-alert-warning {
    background-color: rgba(245, 158, 11, 0.05);
    border: 1px solid rgba(245, 158, 11, 0.2);
    border-left: 4px solid #f59e0b;
}
.modern-alert-warning .modern-alert-icon { color: #f59e0b; }
.modern-alert-warning .modern-alert-title { color: #b45309; }
.modern-alert-warning .modern-alert-message { color: #d97706; }

/* Success Alert */
.modern-alert-success {
    background-color: rgba(16, 185, 129, 0.05);
    border: 1px solid rgba(16, 185, 129, 0.2);
    border-left: 4px solid #10b981;
}
.modern-alert-success .modern-alert-icon { color: #10b981; }
.modern-alert-success .modern-alert-message { color: #047857; font-weight: 500; }

/* Error Alert */
.modern-alert-error {
    background-color: rgba(239, 68, 68, 0.05);
    border: 1px solid rgba(239, 68, 68, 0.2);
    border-left: 4px solid #ef4444;
}
.modern-alert-error .modern-alert-icon { color: #ef4444; }
.modern-alert-error .modern-alert-message { color: #b91c1c; font-weight: 500; }
</style>


<script>
function abrirModalCancelar(id, codigo) {
    document.getElementById('formCancelar').action = `/mesero/reservas/${id}/cancelar`;
    document.getElementById('modalCancelarCodigo').textContent = `Reserva: ${codigo}`;
    document.getElementById('modalCancelar').style.display = 'flex';
}
function abrirModalDeposito(id, codigo) {
    document.getElementById('formDeposito').action = `/mesero/reservas/${id}/aprobar-deposito`;
    document.getElementById('modalDepositoCodigo').textContent = `Reserva: ${codigo}`;
    document.getElementById('modalDeposito').style.display = 'flex';
}
function cerrarModal(id) {
    document.getElementById(id).style.display = 'none';
}
</script>
@endsection
