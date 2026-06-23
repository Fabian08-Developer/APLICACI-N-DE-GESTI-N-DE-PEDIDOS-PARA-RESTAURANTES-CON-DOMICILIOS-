@section('titulo', 'Centro de Reservas')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>

{{-- ─────────────────────────────────────────────────────────────────────────
     CENTRO DE GESTIÓN DE RESERVAS — Full UX redesign
     ───────────────────────────────────────────────────────────────────────── --}}

<div x-data="{
    showDetail: false,
    showCancel: false,
    activeTab: 'calendario',
    fecha: '{{ $fechaGantt }}',
    filterEstado: '',

    prevDay() { 
        let d = new Date(this.fecha + 'T00:00:00');
        d.setDate(d.getDate() - 1);
        this.fecha = d.toISOString().split('T')[0];
        $wire.setFechaGantt(this.fecha);
    },
    nextDay() { 
        let d = new Date(this.fecha + 'T00:00:00');
        d.setDate(d.getDate() + 1);
        this.fecha = d.toISOString().split('T')[0];
        $wire.setFechaGantt(this.fecha);
    },
    goToday() {
        let today = new Date().toISOString().split('T')[0];
        this.fecha = today;
        $wire.setFechaGantt(today);
    },
    setFecha(val) {
        this.fecha = val;
        $wire.setFechaGantt(val);
    }
}"
 @open-detail-drawer.window="showDetail = true"
 @close-detail-drawer.window="showDetail = false"
>

@vite(['resources/css/pedidos.css'])

<style>
/* ── Reset & Base ── */
.drawer-overlay:not(.show),
.modal-overlay:not(.show) { display: none !important; }

/* ── Badges ── */
.badge-pago-pendiente { background:#fef08a; color:#854d0e; }
.badge-pendiente { background:#fed7aa; color:#9a3412; }
.badge-confirmada { background:#bbf7d0; color:#166534; }
.badge-llegada { background:#bfdbfe; color:#1e3a8a; }
.badge-completada { background:#e5e7eb; color:#374151; }
.badge-cancelada { background:#fecaca; color:#991b1b; }
.badge-no-show { background:#fca5a5; color:#7f1d1d; }
.badge { padding:4px 10px; border-radius:9999px; font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; display:inline-flex; align-items:center; gap:4px; }

/* ── KPI Cards ── */
.kpi-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-bottom:1.5rem; }
@media(max-width:768px){ .kpi-grid{ grid-template-columns:repeat(2,1fr); } }
.kpi-card { background:#fff; border-radius:14px; padding:1.1rem 1.25rem; box-shadow:0 1px 4px rgba(0,0,0,.07); border:1px solid rgba(44,36,27,.06); display:flex; align-items:center; gap:.9rem; transition:box-shadow .2s; }
.kpi-card:hover { box-shadow:0 4px 16px rgba(0,0,0,.1); }
.kpi-icon { width:42px; height:42px; border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.kpi-num { font-family:'DM Serif Display',serif; font-size:1.7rem; line-height:1; color:#2c241b; }
.kpi-label { font-size:.72rem; color:#94a3b8; text-transform:uppercase; letter-spacing:.05em; margin-top:2px; }

/* ── Date Command Bar ── */
.date-command-bar { display:flex; align-items:center; gap:.75rem; background:#fff; border-radius:14px; padding:.8rem 1.2rem; box-shadow:0 1px 4px rgba(0,0,0,.07); border:1px solid rgba(44,36,27,.06); margin-bottom:1.5rem; flex-wrap:wrap; }
.dcb-nav-btn { width:34px; height:34px; border-radius:8px; border:1px solid rgba(44,36,27,.12); background:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#2c241b; transition:all .15s; }
.dcb-nav-btn:hover { background:#fdf6f0; border-color:#E07A5F; }
.dcb-today-btn { padding:6px 14px; border-radius:8px; border:1px solid #E07A5F; background:#fff; color:#E07A5F; font-size:.8rem; font-weight:600; cursor:pointer; transition:all .15s; }
.dcb-today-btn:hover { background:#E07A5F; color:#fff; }
.dcb-date-input { border:1px solid rgba(44,36,27,.12); border-radius:8px; padding:6px 12px; font-size:.85rem; color:#2c241b; font-family:'DM Sans',sans-serif; }
.dcb-date-label { font-size:.95rem; font-weight:600; color:#2c241b; font-family:'DM Serif Display',serif; }
.legend-chips { display:flex; gap:.5rem; flex-wrap:wrap; margin-left:auto; }
.chip { display:flex; align-items:center; gap:5px; padding:4px 10px; border-radius:9999px; font-size:.72rem; font-weight:600; cursor:pointer; border:2px solid transparent; transition:all .15s; user-select:none; }
.chip-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
.chip-confirmada { background:#dcfce7; color:#166534; }
.chip-pendiente { background:#fed7aa; color:#9a3412; }
.chip-pago { background:#fef08a; color:#854d0e; }
.chip-llego { background:#bfdbfe; color:#1e3a8a; }
.chip.active { border-color:currentColor; }

/* ── Gantt de Mesas ── */
.gantt-container { background:#fff; border-radius:16px; box-shadow:0 1px 4px rgba(0,0,0,.07); border:1px solid rgba(44,36,27,.06); overflow:hidden; margin-bottom:1.5rem; }
.gantt-title { padding:1rem 1.25rem; border-bottom:1px solid rgba(44,36,27,.08); display:flex; align-items:center; gap:.5rem; }
.gantt-title h3 { margin:0; font-family:'DM Serif Display',serif; color:#2c241b; font-size:1.1rem; }
.gantt-scroll { overflow-x:auto; }
.gantt-grid { display:grid; min-width:900px; }
.gantt-header-row { display:flex; border-bottom:2px solid rgba(44,36,27,.08); }
.gantt-mesa-col { width:90px; flex-shrink:0; padding:.6rem .8rem; background:#faf9f6; font-size:.72rem; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.04em; border-right:1px solid rgba(44,36,27,.06); }
.gantt-hours-header { flex:1; display:flex; background:#faf9f6; }
.gantt-hour-slot-header { flex:1; text-align:center; font-size:.68rem; font-weight:600; color:#94a3b8; padding:.5rem .2rem; border-right:1px solid rgba(44,36,27,.05); white-space:nowrap; }
.gantt-row { display:flex; border-bottom:1px solid rgba(44,36,27,.05); min-height:52px; position:relative; }
.gantt-row:last-child { border-bottom:none; }
.gantt-row:hover { background:rgba(224,122,95,.02); }
.gantt-mesa-label { width:90px; flex-shrink:0; padding:.6rem .8rem; border-right:1px solid rgba(44,36,27,.06); display:flex; flex-direction:column; justify-content:center; }
.gantt-mesa-num { font-weight:700; color:#2c241b; font-size:.85rem; }
.gantt-mesa-cap { font-size:.68rem; color:#94a3b8; }
.gantt-track { flex:1; position:relative; display:flex; align-items:center; }
.gantt-track-bg { position:absolute; inset:0; display:flex; pointer-events:none; }
.gantt-slot-bg { flex:1; border-right:1px solid rgba(44,36,27,.04); }
.gantt-slot-bg:nth-child(even) { background:rgba(44,36,27,.01); }
.gantt-now-line { position:absolute; top:0; bottom:0; width:2px; background:#ef4444; opacity:.8; z-index:10; pointer-events:none; }
.gantt-now-line::before { content:''; position:absolute; top:4px; left:50%; transform:translateX(-50%); width:8px; height:8px; border-radius:50%; background:#ef4444; }
.gantt-block { position:absolute; top:6px; bottom:6px; border-radius:8px; cursor:pointer; display:flex; align-items:center; padding:0 8px; overflow:hidden; transition:filter .15s, transform .1s; box-shadow:0 2px 6px rgba(0,0,0,.15); z-index:5; }
.gantt-block:hover { filter:brightness(1.1); transform:translateY(-1px); box-shadow:0 4px 12px rgba(0,0,0,.2); }
.gantt-block-text { font-size:.72rem; font-weight:600; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.gantt-empty { padding:2rem; text-align:center; color:#94a3b8; font-size:.85rem; }
.gantt-legend-row { display:flex; gap:.5rem; padding:.75rem 1.25rem; background:#faf9f6; border-top:1px solid rgba(44,36,27,.06); flex-wrap:wrap; }
.gantt-legend-item { display:flex; align-items:center; gap:5px; font-size:.72rem; color:#64748b; }
.gantt-legend-dot { width:10px; height:10px; border-radius:3px; }

/* ── Tooltip ── */
.gantt-tooltip { position:fixed; background:#1e293b; color:#fff; border-radius:10px; padding:.75rem 1rem; font-size:.78rem; z-index:9999; pointer-events:none; max-width:220px; box-shadow:0 8px 24px rgba(0,0,0,.25); line-height:1.5; }
.gantt-tooltip-title { font-weight:700; font-size:.85rem; margin-bottom:.3rem; }
.gantt-tooltip-row { display:flex; gap:.4rem; color:#94a3b8; }
.gantt-tooltip-row span:last-child { color:#e2e8f0; }

/* ── FullCalendar Custom Styling ── */
.fc { font-family:'DM Sans',sans-serif; }
.fc-theme-standard .fc-scrollgrid { border-radius:0; border:none; }
.fc-col-header-cell { background:#faf9f6; }
.fc-col-header-cell-cushion { color:#2c241b; font-weight:600; font-size:.8rem; padding:.5rem; text-decoration:none; }
.fc-daygrid-day-number { color:#2c241b; text-decoration:none; font-weight:500; font-size:.85rem; }
.fc-timegrid-slot-label { font-size:.72rem; color:#94a3b8; }
.fc-toolbar-title { font-family:'DM Serif Display',serif; color:#2c241b; font-size:1.3rem !important; }
.fc-button-primary { background:var(--primary,#A85507) !important; border-color:var(--primary,#A85507) !important; border-radius:8px !important; font-size:.8rem !important; }
.fc-button-primary:hover { background:#8c4605 !important; border-color:#8c4605 !important; }
.fc-button-primary:not(:disabled):active, .fc-button-primary:not(:disabled).fc-button-active { background:#7a3d04 !important; border-color:#7a3d04 !important; }
.fc-event { border:none !important; border-radius:6px !important; }
.fc-event-title { font-weight:600; font-size:.75rem; }
.fc-event-time { font-size:.7rem; font-weight:400; }
.fc-now-indicator { border-color:#ef4444 !important; }
.fc-now-indicator-arrow { border-top-color:#ef4444 !important; }
.fc-bg-event { opacity:.15 !important; }
.cal-wrapper { background:#fff; border-radius:16px; box-shadow:0 1px 4px rgba(0,0,0,.07); border:1px solid rgba(44,36,27,.06); padding:1.25rem; }

/* ── Tooltip FullCalendar ── */
.fc-popover-custom { background:#1e293b; border-radius:10px; padding:.75rem 1rem; font-size:.78rem; color:#fff; box-shadow:0 8px 24px rgba(0,0,0,.25); max-width:240px; }

/* ── Historial ── */
.historial-search { width:100%; padding:.6rem 1rem; border:1px solid rgba(44,36,27,.12); border-radius:10px; font-size:.85rem; font-family:'DM Sans',sans-serif; color:#2c241b; margin-bottom:1rem; }
</style>

{{-- ─── HEADER con alertas ─── --}}
<div class="pagina-header">
    <h1>Centro de Reservas</h1>
    <p>Gestiona mesas, horarios y estado de las reservas en tiempo real.</p>
</div>

@if (session()->has('success'))
    <div class="alerta alerta-success"><span class="alerta-icon">✓</span><span class="alerta-message">{{ session('success') }}</span></div>
@endif
@if (session()->has('error'))
    <div class="alerta alerta-danger"><span class="alerta-icon">✕</span><span class="alerta-message">{{ session('error') }}</span></div>
@endif

{{-- ─── TABS ─── --}}
<div class="tabs-container">
    <button @click="activeTab = 'calendario'" type="button" class="tab-btn" :class="{ 'activo': activeTab === 'calendario' }">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:inline;margin-right:4px;vertical-align:-3px"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        Calendario
    </button>
    <button @click="activeTab = 'historial'" type="button" class="tab-btn" :class="{ 'activo': activeTab === 'historial' }">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:inline;margin-right:4px;vertical-align:-3px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Historial
    </button>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     TAB: CALENDARIO
══════════════════════════════════════════════════════════════════════ --}}
<div x-show="activeTab === 'calendario'" x-transition.opacity>

    {{-- ─── KPI Cards ─── --}}
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:#eff6ff;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#3b82f6" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <div class="kpi-num">{{ $kpis['total_hoy'] }}</div>
                <div class="kpi-label">Reservas hoy</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon" style="background:#f0fdf4;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div>
                <div class="kpi-num">{{ $kpis['confirmadas'] }}</div>
                <div class="kpi-label">Confirmadas</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon" style="background:#eff6ff;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#3b82f6" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div>
                <div class="kpi-num">{{ $kpis['clientes_aca'] }}</div>
                <div class="kpi-label">Clientes en sitio</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon" style="background:#fef9c3;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#eab308" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="kpi-num">{{ $kpis['pendientes_pago'] }}</div>
                <div class="kpi-label">Pend. de pago</div>
            </div>
        </div>
    </div>

    {{-- ─── Barra de Fecha / Leyenda ─── --}}
    <div class="date-command-bar">
        <button class="dcb-nav-btn" @click="prevDay()" title="Día anterior">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M15 19l-7-7 7-7"/></svg>
        </button>
        <button class="dcb-today-btn" @click="goToday()">Hoy</button>
        <button class="dcb-nav-btn" @click="nextDay()" title="Día siguiente">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 5l7 7-7 7"/></svg>
        </button>

        <input type="date" class="dcb-date-input" :value="fecha" @change="setFecha($event.target.value)" wire:ignore>

        <span class="dcb-date-label" x-text="(() => { let d=new Date(fecha+'T00:00:00'); return d.toLocaleDateString('es-ES',{weekday:'long',day:'numeric',month:'long'}); })()"></span>

        <div class="legend-chips">
            <div class="chip chip-confirmada" :class="{active: filterEstado==='confirmada'}" @click="filterEstado = filterEstado==='confirmada' ? '' : 'confirmada'">
                <span class="chip-dot" style="background:#10b981;"></span> Confirmada
            </div>
            <div class="chip chip-pendiente" :class="{active: filterEstado==='pendiente'}" @click="filterEstado = filterEstado==='pendiente' ? '' : 'pendiente'">
                <span class="chip-dot" style="background:#f59e0b;"></span> Pendiente
            </div>
            <div class="chip chip-pago" :class="{active: filterEstado==='pendiente_pago'}" @click="filterEstado = filterEstado==='pendiente_pago' ? '' : 'pendiente_pago'">
                <span class="chip-dot" style="background:#eab308;"></span> Pago pendiente
            </div>
            <div class="chip chip-llego" :class="{active: filterEstado==='cliente_llego'}" @click="filterEstado = filterEstado==='cliente_llego' ? '' : 'cliente_llego'">
                <span class="chip-dot" style="background:#3b82f6;"></span> Llegó
            </div>
        </div>
    </div>

    {{-- ─── GANTT DE MESAS ─── --}}
    @php
        $ganttStart = 10; // 10:00
        $ganttEnd   = 24; // 00:00 (cierre medianoche)
        $totalSlots = ($ganttEnd - $ganttStart) * 2; // slots de 30 min
    @endphp

    <div class="gantt-container" x-data="{
        tooltip: null,
        tooltipX: 0,
        tooltipY: 0,
        showTooltip(evt, data) {
            this.tooltip = data;
            this.tooltipX = evt.clientX + 14;
            this.tooltipY = evt.clientY - 10;
        },
        hideTooltip() { this.tooltip = null; }
    }">
        {{-- Tooltip flotante --}}
        <template x-if="tooltip">
            <div class="gantt-tooltip"
                 :style="`left:${tooltipX}px; top:${tooltipY}px;`"
                 x-cloak>
                <div class="gantt-tooltip-title" x-text="tooltip.cliente"></div>
                <div class="gantt-tooltip-row">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:2px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span x-text="tooltip.hora_inicio + ' → ' + tooltip.hora_fin"></span>
                </div>
                <div class="gantt-tooltip-row">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:2px"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span x-text="tooltip.personas + ' personas'"></span>
                </div>
                <div class="gantt-tooltip-row" style="margin-top:.35rem; padding-top:.35rem; border-top:1px solid rgba(255,255,255,.1)">
                    <span x-text="tooltip.estado"></span>
                </div>
                <div class="gantt-tooltip-row" style="font-size:.68rem; color:#64748b;" x-text="tooltip.codigo"></div>
            </div>
        </template>

        <div class="gantt-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#A85507" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM4 14a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2z"/></svg>
            <h3>Ocupación de Mesas por Hora</h3>
            <span style="margin-left:.5rem; font-size:.75rem; color:#94a3b8; font-weight:400;" x-text="`${fecha}`"></span>
        </div>

        <div class="gantt-scroll">
            {{-- Header de horas --}}
            <div class="gantt-header-row">
                <div class="gantt-mesa-col">Mesa</div>
                <div class="gantt-hours-header">
                    @for ($h = $ganttStart; $h < $ganttEnd; $h++)
                        <div class="gantt-hour-slot-header" style="flex:2">
                            {{ str_pad($h, 2, '0', STR_PAD_LEFT) }}:00
                        </div>
                    @endfor
                </div>
            </div>

            {{-- Filas de mesas --}}
            @forelse($ganttData as $mesaInfo)
            @php
                // Filtrar reservas por estado si se pasa el filtro del chip (lo hacemos desde el backend en el render)
                $reservasMesa = $mesaInfo['reservas'];
            @endphp
            <div class="gantt-row" x-show="filterEstado === '' || {{ json_encode(collect($mesaInfo['reservas'])->pluck('estado_value')->toArray()) }}.includes(filterEstado)">
                <div class="gantt-mesa-label">
                    <span class="gantt-mesa-num">Mesa {{ $mesaInfo['numero'] }}</span>
                    <span class="gantt-mesa-cap">Cap. {{ $mesaInfo['capacidad'] ?? '?' }}</span>
                </div>
                <div class="gantt-track">
                    {{-- Fondo de slots --}}
                    <div class="gantt-track-bg">
                        @for ($i = 0; $i < $totalSlots; $i++)
                            <div class="gantt-slot-bg"></div>
                        @endfor
                    </div>

                    {{-- Línea de hora actual (solo si es hoy) --}}
                    @php
                        $fechaHoy = \Carbon\Carbon::today()->format('Y-m-d');
                    @endphp
                    @if($fechaGantt === $fechaHoy)
                    <div class="gantt-now-line" wire:ignore
                         style="left: {{ max(0, min(100, ((\Carbon\Carbon::now()->hour * 60 + \Carbon\Carbon::now()->minute) - $ganttStart * 60) / (($ganttEnd - $ganttStart) * 60) * 100)) }}%">
                    </div>
                    @endif

                    {{-- Bloques de reserva --}}
                    @foreach($reservasMesa as $r)
                    @php
                        [$hI, $mI] = explode(':', $r['hora_inicio']);
                        [$hF, $mF] = explode(':', $r['hora_fin']);
                        $minInicio = (int)$hI * 60 + (int)$mI;
                        $minFin    = (int)$hF * 60 + (int)$mF;
                        $ganttMin  = $ganttStart * 60;
                        $totalMin  = ($ganttEnd - $ganttStart) * 60;
                        $left  = max(0, ($minInicio - $ganttMin) / $totalMin * 100);
                        $width = max(2, ($minFin - $minInicio) / $totalMin * 100);
                    @endphp
                    <div class="gantt-block"
                         style="left:{{ $left }}%; width:{{ $width }}%; background:{{ $r['color'] }};"
                         @mouseenter="showTooltip($event, {{ json_encode($r) }})"
                         @mouseleave="hideTooltip()"
                         wire:click="openDetailModal('{{ $r['id'] }}')"
                         x-show="filterEstado === '' || filterEstado === '{{ $r['estado_value'] }}'">
                        <span class="gantt-block-text">{{ $r['cliente'] }} · {{ $r['hora_inicio'] }}-{{ $r['hora_fin'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @empty
            <div class="gantt-empty">No hay mesas configuradas para esta sucursal.</div>
            @endforelse
        </div>

        {{-- Leyenda inferior del Gantt --}}
        <div class="gantt-legend-row">
            <span style="font-size:.72rem; color:#94a3b8; font-weight:600; text-transform:uppercase; letter-spacing:.04em; margin-right:.25rem;">Referencia:</span>
            <div class="gantt-legend-item"><div class="gantt-legend-dot" style="background:#10b981;"></div> Confirmada</div>
            <div class="gantt-legend-item"><div class="gantt-legend-dot" style="background:#f59e0b;"></div> Pendiente</div>
            <div class="gantt-legend-item"><div class="gantt-legend-dot" style="background:#eab308;"></div> Pend. de pago</div>
            <div class="gantt-legend-item"><div class="gantt-legend-dot" style="background:#3b82f6;"></div> Cliente en sitio</div>
            <div class="gantt-legend-item" style="margin-left:auto;"><div style="width:12px;height:2px;background:#ef4444;display:inline-block;vertical-align:middle;margin-right:4px;"></div> Hora actual</div>
        </div>
    </div>

    {{-- ─── FULLCALENDAR (vista semana con horas) ─── --}}
    <div class="cal-wrapper"
         x-data="{
            calendar: null,
            init() {
                let el = document.getElementById('reservas-calendar');
                this.calendar = new FullCalendar.Calendar(el, {
                    initialView: 'timeGridWeek',
                    locale: 'es',
                    height: 600,
                    slotMinTime: '09:00:00',
                    slotMaxTime: '24:00:00',
                    slotDuration: '00:30:00',
                    nowIndicator: true,
                    allDaySlot: false,
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'timeGridWeek,timeGridDay,dayGridMonth'
                    },
                    buttonText: { today:'Hoy', week:'Semana', day:'Día', month:'Mes' },
                    events: @js(json_decode($eventosCalendario ?? '[]')),
                    eventDidMount(info) {
                        let p = info.event.extendedProps;
                        info.el.title = [
                            info.event.title,
                            `Estado: ${p.estado}`,
                            `${p.personas} personas`,
                            p.mesas ? `Mesa(s): ${p.mesas}` : '',
                            `Depósito: ${p.deposito}`,
                        ].filter(Boolean).join('\n');
                    },
                    eventClick(info) {
                        $wire.openDetailModal(info.event.id);
                    },
                    dateClick(info) {
                        /* future: pre-fill new reservation */
                    },
                    datesSet(info) {
                        /* sync with gantt when user navigates */
                    }
                });
                this.calendar.render();

                window.addEventListener('update-calendar-events', evt => {
                    if (this.calendar) {
                        this.calendar.removeAllEventSources();
                        let events = typeof evt.detail.events === 'string' ? JSON.parse(evt.detail.events) : evt.detail.events;
                        this.calendar.addEventSource(events);
                    }
                });
            }
         }">
        <div wire:ignore>
            <div id="reservas-calendar"></div>
        </div>
    </div>

</div>{{-- end tab calendario --}}

{{-- ══════════════════════════════════════════════════════════════════════
     TAB: HISTORIAL
══════════════════════════════════════════════════════════════════════ --}}
<div x-show="activeTab === 'historial'" style="display:none;" x-transition.opacity>

    {{-- Filtros del historial --}}
    <div class="elegant-filter-card" style="margin-bottom:1rem;">
        <div class="filter-header">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
            FILTROS DEL HISTORIAL
        </div>
        <div class="elegant-filter-grid">
            <div class="elegant-group">
                <label>BUSCAR</label>
                <input type="text" wire:model.live.debounce.300ms="busquedaHistorial" placeholder="Cliente, código, teléfono…">
            </div>
            <div class="elegant-group">
                <label>DESDE</label>
                <input type="date" wire:model.live="filtroFechaInicio">
            </div>
            <div class="elegant-group">
                <label>HASTA</label>
                <input type="date" wire:model.live="filtroFechaFin">
            </div>
            <div class="elegant-group">
                <label>ESTADO</label>
                <select wire:model.live="filtroEstado">
                    <option value="">Todos</option>
                    <option value="completada">Completada</option>
                    <option value="cancelada">Cancelada</option>
                    <option value="no_show">No se presentó</option>
                </select>
            </div>
            <div class="elegant-group">
                <label>MESA</label>
                <select wire:model.live="filtroMesa">
                    <option value="">Todas</option>
                    @foreach($mesas as $mesa)
                        <option value="{{ $mesa->id }}">Mesa {{ $mesa->numero }}</option>
                    @endforeach
                </select>
            </div>
            <div class="elegant-actions">
                <button type="button" wire:click="limpiarFiltros" class="btn-limpiar" title="Limpiar filtros">✕</button>
            </div>
        </div>
    </div>

    <div class="tarjeta">
        <div class="tarjeta-header" style="display:flex;align-items:center;justify-content:space-between;">
            <span>Historial · {{ $reservasHistorial->count() }} resultado(s)</span>
        </div>

        @if($reservasHistorial->isEmpty())
            <div class="vacio">No se encontraron registros con los criterios indicados.</div>
        @else
        <table>
            <thead>
                <tr>
                    <th>Fecha y Hora</th>
                    <th>Cliente</th>
                    <th>Mesas</th>
                    <th>Pers.</th>
                    <th>Depósito</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reservasHistorial as $reserva)
                <tr>
                    <td style="white-space:nowrap;">
                        <strong>{{ \Carbon\Carbon::parse($reserva->fecha_reserva)->format('d/m/Y') }}</strong><br>
                        <span class="texto-gris">{{ substr($reserva->hora_inicio,0,5) }} – {{ substr($reserva->hora_fin,0,5) }}</span>
                    </td>
                    <td>
                        <div class="cliente-info-col">
                            <span class="cliente-nombre">{{ $reserva->nombre_cliente }}</span>
                            <span class="cliente-tel">{{ $reserva->telefono_cliente }}</span>
                        </div>
                    </td>
                    <td>
                        @if($reserva->mesas->count() > 0)
                            <span class="meta-badge">M: {{ $reserva->mesas->pluck('numero')->join(', ') }}</span>
                        @else
                            <span class="texto-gris">—</span>
                        @endif
                    </td>
                    <td class="texto-gris">{{ $reserva->numero_personas }}</td>
                    <td>
                        @if($reserva->monto_deposito > 0)
                            ${{ number_format($reserva->monto_deposito,0) }}<br>
                            <small class="texto-gris">{{ $reserva->deposito_pagado ? 'Pagado' : 'Pend.' }}</small>
                        @else
                            <span class="texto-gris">—</span>
                        @endif
                    </td>
                    <td><span class="badge {{ $reserva->estado->colorClase() }}">{{ $reserva->estado->etiqueta() }}</span></td>
                    <td>
                        <button type="button" class="btn-ver" wire:click="openDetailModal('{{ $reserva->id }}')">Ver detalles</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     DRAWER DE DETALLE
══════════════════════════════════════════════════════════════════════ --}}
<div class="drawer-overlay" :class="{ 'show': showDetail }" @click="showDetail = false; $wire.closeDetailModal()" wire:ignore.self>
    <div class="drawer-content" :class="{ 'show': showDetail }" @click.stop style="background:#ffffff;">
        @if($selectedReserva)
            <div class="drawer-header" style="background:#fdfbf7; border-bottom:1px solid rgba(44,36,27,.08);">
                <div>
                    <h2 style="color:#2c241b; font-size:1.4rem; font-family:'DM Serif Display',serif; margin:0;">
                        Reserva {{ $selectedReserva->codigo_reserva }}
                    </h2>
                    <span style="font-size:.8rem; color:#94a3b8; display:flex; align-items:center; gap:4px; margin-top:4px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Creada: {{ $selectedReserva->creado_en?->format('d/m/Y H:i') }}
                    </span>
                </div>
                <button type="button" @click="showDetail = false; $wire.closeDetailModal()" style="background:rgba(44,36,27,.05); border:none; border-radius:50%; width:32px; height:32px; display:flex; align-items:center; justify-content:center; color:#2c241b; cursor:pointer; font-size:1rem;">✕</button>
            </div>

            <div class="drawer-body" style="padding:1.5rem 2rem;">

                {{-- Info General --}}
                <div class="drawer-section" style="border:1px solid rgba(44,36,27,.08); border-radius:12px; padding:1.25rem; background:#faf9f6; margin-bottom:1.5rem;">
                    <h3 style="display:flex; align-items:center; gap:8px; color:#2c241b; margin-top:0; font-size:1rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Información de la Reserva
                    </h3>
                    <div class="info-grid" style="gap:1.25rem;">
                        <div><span class="info-label">Cliente:</span><span style="font-weight:600;color:#2c241b;font-size:.9rem;">{{ $selectedReserva->nombre_cliente }}</span></div>
                        <div><span class="info-label">Teléfono:</span><span style="color:#2c241b;font-size:.9rem;">{{ $selectedReserva->telefono_cliente }}</span></div>
                        <div><span class="info-label">Fecha:</span><span style="color:#2c241b;font-size:.9rem;">{{ \Carbon\Carbon::parse($selectedReserva->fecha_reserva)->format('d/m/Y') }}</span></div>
                        <div><span class="info-label">Horario:</span><span style="color:#2c241b;font-size:.9rem;">{{ substr($selectedReserva->hora_inicio,0,5) }} – {{ substr($selectedReserva->hora_fin,0,5) }}</span></div>
                        <div>
                            <span class="info-label">Mesas:</span>
                            <span style="color:#2c241b;font-size:.9rem;font-weight:600;">
                                {{ $selectedReserva->mesas->count() > 0 ? 'Mesa(s) ' . $selectedReserva->mesas->pluck('numero')->join(', ') : '—' }}
                            </span>
                        </div>
                        <div><span class="info-label">Personas:</span><span style="color:#2c241b;font-size:.9rem;">{{ $selectedReserva->numero_personas }} personas</span></div>
                        <div class="full-width">
                            <span class="info-label">Notas:</span>
                            <span style="background:rgba(201,168,76,.1);padding:.75rem;border-radius:8px;border-left:3px solid #E07A5F;font-size:.85rem;display:block;margin-top:4px;color:#5a4b3c;">
                                {{ $selectedReserva->notas_cliente ?: 'Sin notas adicionales.' }}
                            </span>
                        </div>
                        <div class="full-width" style="margin-top:.5rem;display:flex;align-items:center;justify-content:space-between;padding-top:1rem;border-top:1px dashed rgba(44,36,27,.1);">
                            <span class="info-label" style="margin-bottom:0;">Estado:</span>
                            <span class="badge {{ $selectedReserva->estado->colorClase() }}" style="font-size:.8rem;padding:6px 12px;">{{ $selectedReserva->estado->etiqueta() }}</span>
                        </div>
                    </div>
                </div>

                {{-- Depósito --}}
                <div class="drawer-section" style="border:1px solid rgba(44,36,27,.08); border-radius:12px; padding:1.25rem; margin-bottom:1.5rem;">
                    <h3 style="display:flex;align-items:center;gap:8px;color:#2c241b;margin-top:0;font-size:1rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Depósito
                    </h3>
                    <div class="info-grid">
                        <div style="background:rgba(44,36,27,.03);padding:1rem;border-radius:8px;">
                            <span class="info-label">Monto requerido:</span>
                            <span style="font-size:1.2rem;font-weight:700;color:#2c241b;">${{ number_format($selectedReserva->monto_deposito,0) }}</span>
                        </div>
                        <div style="background:{{ $selectedReserva->deposito_pagado ? 'rgba(16,185,129,.1)' : 'rgba(245,158,11,.1)' }};padding:1rem;border-radius:8px;">
                            <span class="info-label" style="color:{{ $selectedReserva->deposito_pagado ? '#059669' : '#d97706' }}">Estado de Pago:</span>
                            <span style="font-size:1.1rem;font-weight:600;color:{{ $selectedReserva->deposito_pagado ? '#10b981' : '#f59e0b' }}">{{ $selectedReserva->deposito_pagado ? '✓ Pagado' : '⚠ Pendiente' }}</span>
                        </div>
                    </div>
                    @if($selectedReserva->pagosDeposito && $selectedReserva->pagosDeposito->count() > 0)
                    <div style="margin-top:1.5rem;">
                        <strong style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:#64748b;">Transacciones:</strong>
                        <div style="display:flex;flex-direction:column;gap:8px;margin-top:.75rem;">
                            @foreach($selectedReserva->pagosDeposito as $pago)
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:.75rem;border:1px solid rgba(44,36,27,.06);border-radius:8px;background:#faf9f6;">
                                <div>
                                    <div style="font-weight:600;color:#2c241b;">${{ number_format($pago->monto,0) }} <span style="font-weight:400;color:#64748b;font-size:.8rem;">vía {{ ucfirst($pago->metodo) }}</span></div>
                                    @if($pago->referencia)<div style="font-size:.72rem;color:#94a3b8;font-family:monospace;">Ref: {{ $pago->referencia }}</div>@endif
                                </div>
                                <div style="text-align:right;">
                                    <span class="badge {{ $pago->estado === 'aprobado' ? 'badge-confirmada' : 'badge-pendiente' }}">{{ $pago->estado }}</span>
                                    <div style="font-size:.7rem;color:#94a3b8;margin-top:4px;">{{ $pago->creado_en->format('d/m H:i') }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Acciones de Admin --}}
                @if(!$selectedReserva->estado->esFinal())
                <div class="drawer-section" style="border:none; padding:0;">
                    <h3 style="display:flex;align-items:center;gap:8px;color:#2c241b;margin-top:0;margin-bottom:1rem;font-size:1rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                        Acciones de Administrador
                    </h3>
                    <div style="display:flex;flex-direction:column;gap:10px;">

                        @if($selectedReserva->estado->value === 'pendiente_pago')
                        <button type="button"
                                wire:click="cambiarEstado('{{ $selectedReserva->id }}', 'confirmada')"
                                wire:confirm="¿Confirmar reserva manualmente sin depósito vía sistema?"
                                style="background:#10b981;color:#fff;border:none;padding:1rem;border-radius:8px;font-weight:600;display:flex;align-items:center;justify-content:center;gap:8px;cursor:pointer;font-size:.9rem;transition:background .2s;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Aprobar Manualmente
                        </button>
                        <p style="font-size:.72rem;color:#94a3b8;text-align:center;margin:0;">Usa esto si el cliente pagó fuera del sistema (ej: efectivo).</p>
                        @endif

                        @if($selectedReserva->estado->value === 'pendiente')
                        <button type="button"
                                wire:click="cambiarEstado('{{ $selectedReserva->id }}', 'confirmada')"
                                style="background:#10b981;color:#fff;border:none;padding:1rem;border-radius:8px;font-weight:600;display:flex;align-items:center;justify-content:center;gap:8px;cursor:pointer;font-size:.9rem;transition:background .2s;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Confirmar Reserva
                        </button>
                        @endif

                        @if($selectedReserva->estado->value === 'confirmada')
                        <button type="button"
                                wire:click="cambiarEstado('{{ $selectedReserva->id }}', 'cliente_llego')"
                                style="background:#3b82f6;color:#fff;border:none;padding:1rem;border-radius:8px;font-weight:600;display:flex;align-items:center;justify-content:center;gap:8px;cursor:pointer;font-size:.9rem;transition:background .2s;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            ✓ Registrar Llegada del Cliente
                        </button>
                        @endif

                        @if($selectedReserva->estado->value === 'cliente_llego')
                        <button type="button"
                                wire:click="cambiarEstado('{{ $selectedReserva->id }}', 'completada')"
                                wire:confirm="¿Marcar esta reserva como completada?"
                                style="background:#6366f1;color:#fff;border:none;padding:1rem;border-radius:8px;font-weight:600;display:flex;align-items:center;justify-content:center;gap:8px;cursor:pointer;font-size:.9rem;">
                            Marcar como Completada
                        </button>
                        @endif

                        @if(in_array($selectedReserva->estado->value, ['pendiente_pago', 'pendiente', 'confirmada']))
                        <button type="button" @click="showCancel = true"
                                style="background:transparent;color:#ef4444;border:1px solid #ef4444;padding:1rem;border-radius:8px;font-weight:600;display:flex;align-items:center;justify-content:center;gap:8px;cursor:pointer;font-size:.9rem;transition:all .2s;margin-top:.25rem;"
                                onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            Cancelar Reserva
                        </button>
                        @endif
                    </div>
                </div>
                @endif

                @if($selectedReserva->estado->value === 'cancelada')
                <div style="background:rgba(239,68,68,.05);border:1px solid rgba(239,68,68,.2);border-left:4px solid #ef4444;padding:1.25rem;border-radius:8px;margin-top:1rem;">
                    <h3 style="color:#b91c1c;margin-top:0;font-size:.9rem;">Motivo de Cancelación</h3>
                    <p style="color:#991b1b;margin-bottom:0;font-size:.875rem;line-height:1.5;">"{{ $selectedReserva->motivo_cancelacion ?? 'Sin motivo especificado.' }}"</p>
                </div>
                @endif

            </div>
        @endif
    </div>
</div>

{{-- ─── MODAL CANCELACIÓN ─── --}}
<div class="modal-overlay" :class="{ 'show': showCancel }" @click="showCancel = false; $wire.closeCancelModal()" wire:ignore.self>
    <div class="modal-content" @click.stop wire:ignore.self>
        <div class="modal-header">
            <h3>Cancelar Reserva</h3>
            <button type="button" class="btn-close-modal" @click="showCancel = false; $wire.closeCancelModal()">✕</button>
        </div>
        <div class="modal-body">
            <p>Ingresa el motivo. Se enviará al cliente por correo.</p>
            <textarea wire:model="motivoCancelacion" class="form-control" rows="3" placeholder="Ej: Sin disponibilidad, mantenimiento del local…" style="width:100%;margin-top:10px;"></textarea>
            @error('motivoCancelacion')<span style="color:#ef4444;font-size:.8rem;">{{ $message }}</span>@enderror
            <div style="margin-top:1rem;text-align:right;">
                <button type="button" class="btn-cancel" wire:click="cancelarReserva" wire:loading.attr="disabled">Confirmar Cancelación</button>
            </div>
        </div>
    </div>
</div>

</div>{{-- end root --}}
