@extends('admin.layout')

@section('titulo', 'Reporte de Ventas')

@section('contenido')
<!-- ApexCharts vía CDN -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<!-- AlpineJS para interactividad de modales y sidebar -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<!-- Flatpickr para selector de fechas -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>

<style>
    /* Estilos para el toggle switch moderno */
    .switch { position: relative; display: inline-block; width: 40px; height: 22px; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(255,255,255,0.1); transition: .3s; border: 1px solid var(--border); }
    .slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 2px; bottom: 2px; background-color: var(--text-sec); transition: .3s; }
    input:checked + .slider { background-color: var(--primary); border-color: var(--primary); }
    input:checked + .slider:before { transform: translateX(18px); background-color: #0F172A; }
    .slider.round { border-radius: 22px; }
    .slider.round:before { border-radius: 50%; }

    /* Custom Scrollbar para Sidebars */
    .custom-scroll::-webkit-scrollbar { width: 6px; }
    .custom-scroll::-webkit-scrollbar-track { background: transparent; }
    .custom-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
    
    /* Efecto hover en checkboxes del filtro */
    .filter-check { accent-color: var(--primary); margin-top: 1px; }
    .filter-label { display: flex; align-items: flex-start; gap: 8px; font-size: 0.8rem; margin-bottom: 8px; color: var(--text-sec); cursor: pointer; transition: color 0.2s; }
    .filter-label:hover { color: var(--text-main); }

    /* Estilos Shadcn para Filtros */
    .shadcn-filters-container { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
    .shadcn-filters { display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem; }
    .shadcn-btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 500; border-radius: 0.5rem; border: 1px solid var(--border); background: var(--surface); color: var(--text-main); cursor: pointer; transition: all 0.2s; height: 38px; }
    .shadcn-btn:hover { background: rgba(255,255,255,0.05); }
    .shadcn-select-wrapper { position: relative; }
    .shadcn-select { appearance: none; padding: 0.5rem 2.5rem 0.5rem 1rem; font-size: 0.875rem; border: 1px solid var(--border); border-radius: 0.5rem; background: var(--surface); color: var(--text-main); cursor: pointer; min-width: 140px; height: 38px; font-weight: 500; }
    .shadcn-select-wrapper::after { content: ''; position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); width: 0; height: 0; border-left: 4px solid transparent; border-right: 4px solid transparent; border-top: 4px solid var(--text-sec); pointer-events: none; }
    .shadcn-switch-container { display: flex; align-items: center; gap: 0.5rem; margin-left: auto; }
    .shadcn-switch { position: relative; width: 44px; height: 24px; background: rgba(255,255,255,0.1); border-radius: 12px; cursor: pointer; transition: background 0.2s; border: 1px solid var(--border); }
    .shadcn-switch.active { background: var(--primary); border-color: var(--primary); }
    .shadcn-switch::after { content: ''; position: absolute; top: 1px; left: 2px; width: 20px; height: 20px; background: var(--text-sec); border-radius: 50%; transition: transform 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    .shadcn-switch.active::after { transform: translateX(20px); background: #0F172A; }
    .shadcn-switch-label { font-size: 0.875rem; cursor: pointer; color: var(--text-main); }
    .shadcn-card { background: var(--surface); border: 1px solid var(--border); border-radius: 0.625rem; padding: 1.5rem; color: var(--text-main); }
    .shadcn-checkbox-group { display: flex; flex-direction: column; gap: 0.5rem; }
    .shadcn-checkbox-item { display: flex; align-items: center; gap: 0.5rem; }
    .shadcn-checkbox { width: 1rem; height: 1rem; border: 1px solid var(--border); border-radius: 4px; cursor: pointer; appearance: none; background: rgba(255,255,255,0.05); }
    .shadcn-checkbox:checked { background: var(--primary); border-color: var(--primary); background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230F172A' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='20 6 9 17 4 12'%3E%3C/polyline%3E%3C/svg%3E"); background-size: 10px; background-position: center; background-repeat: no-repeat; }
    .shadcn-checkbox-label { font-size: 0.875rem; cursor: pointer; color: var(--text-main); }

    /* ── Export Sidebar (dark theme) ── */
    .exp-panel { font-family: 'Inter', sans-serif; background: var(--surface); color: var(--text-main); display: flex; flex-direction: column; height: 100%; border-left: 1px solid var(--border); }
    .exp-header { padding: 1.5rem 1.5rem 0; }
    .exp-title { font-size: 1.125rem; font-weight: 600; color: var(--text-main); margin: 0; }
    .exp-desc { font-size: 0.875rem; color: var(--text-sec); margin: 0.25rem 0 0; }
    .exp-body { padding: 1.5rem; overflow-y: auto; flex: 1; }
    .exp-footer { padding: 1rem 1.5rem; display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid var(--border); background: var(--surface); }
    .exp-label { font-size: 0.875rem; font-weight: 500; color: var(--text-main); display: block; margin-bottom: 0.5rem; }
    .exp-tabs { display: grid; grid-template-columns: repeat(3, 1fr); background: rgba(0,0,0,0.2); border-radius: 0.5rem; padding: 0.25rem; margin-bottom: 1rem; }
    .exp-tab { padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 500; color: var(--text-sec); background: none; border: none; border-radius: 0.375rem; cursor: pointer; font-family: inherit; transition: all 0.2s; }
    .exp-tab.active { background: var(--surface); color: var(--text-main); box-shadow: 0 1px 3px rgba(0,0,0,0.3); }
    .exp-tab-content { display: none; } .exp-tab-content.active { display: block; }
    .exp-format-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 1rem; margin-bottom: 1.5rem; }
    .exp-format-opt { display: flex; flex-direction: column; align-items: center; gap: 0.5rem; padding: 1rem; border: 1px solid var(--border); border-radius: 0.5rem; cursor: pointer; transition: all 0.2s; background: rgba(255,255,255,0.02); }
    .exp-format-opt:hover { border-color: var(--text-sec); }
    .exp-format-opt.selected { border-color: var(--primary); background: rgba(255,255,255,0.05); }
    .exp-format-opt input { display: none; }
    .exp-format-opt svg { width: 1.25rem; height: 1.25rem; color: var(--text-sec); }
    .exp-format-opt.selected svg { color: var(--primary); }
    .exp-format-label { font-size: 0.875rem; font-weight: 500; text-transform: uppercase; }
    .exp-sections-grid { display: grid; grid-template-columns: repeat(2,1fr); gap: 0.75rem; margin-bottom: 1.5rem; }
    .exp-section-item { display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.75rem; border: 1px solid var(--border); border-radius: 0.5rem; cursor: pointer; transition: all 0.2s; background: rgba(255,255,255,0.02); }
    .exp-section-item:hover { border-color: var(--text-sec); }
    .exp-section-item.selected { border-color: var(--primary); background: rgba(255,255,255,0.05); }
    .exp-checkbox { width: 1rem; height: 1rem; border: 1px solid var(--border); border-radius: 0.25rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 0.125rem; transition: all 0.2s; }
    .exp-section-item.selected .exp-checkbox { background: var(--primary); border-color: var(--primary); }
    .exp-checkbox svg { width: 0.75rem; height: 0.75rem; color: #0F172A; opacity: 0; }
    .exp-section-item.selected .exp-checkbox svg { opacity: 1; }
    .exp-section-title { font-size: 0.875rem; font-weight: 500; color: var(--text-main); }
    .exp-section-desc { font-size: 0.75rem; color: var(--text-sec); margin-top: 0.125rem; }
    .exp-schedule-card { padding: 1rem; border: 1px solid var(--border); border-radius: 0.5rem; margin-bottom: 1rem; background: rgba(255,255,255,0.01); }
    .exp-schedule-header { display: flex; align-items: center; justify-content: space-between; }
    .exp-switch { position: relative; width: 2.75rem; height: 1.5rem; background: rgba(255,255,255,0.1); border-radius: 9999px; cursor: pointer; transition: background 0.2s; flex-shrink: 0; border: none; }
    .exp-switch.on { background: var(--primary); }
    .exp-switch-thumb { position: absolute; top: 0.125rem; left: 0.125rem; width: 1.25rem; height: 1.25rem; background: white; border-radius: 9999px; transition: transform 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.5); }
    .exp-switch.on .exp-switch-thumb { transform: translateX(1.25rem); }
    .exp-schedule-opts { padding-top: 1rem; margin-top: 1rem; border-top: 1px solid var(--border); display: none; }
    .exp-schedule-opts.visible { display: block; }
    .exp-select-wrap { position: relative; }
    .exp-select { width: 100%; padding: 0.5rem 2rem 0.5rem 0.75rem; font-size: 0.875rem; border: 1px solid var(--border); border-radius: 0.5rem; background: var(--bg-main); color: var(--text-main); font-family: inherit; cursor: pointer; appearance: none; }
    .exp-select:focus { outline: none; border-color: var(--primary); }
    .exp-select-arrow { position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--text-sec); }
    .exp-input { width: 100%; padding: 0.5rem 0.75rem; font-size: 0.875rem; border: 1px solid var(--border); border-radius: 0.5rem; background: var(--bg-main); color: var(--text-main); font-family: inherit; }
    .exp-input:focus { outline: none; border-color: var(--primary); }
    .exp-input::placeholder { color: rgba(255,255,255,0.2); }
    .exp-btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 500; border-radius: 0.5rem; cursor: pointer; font-family: inherit; transition: all 0.2s; border: none; }
    .exp-btn-primary { background: var(--primary); color: #0F172A; } .exp-btn-primary:hover { opacity: 0.9; } .exp-btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }
    .exp-btn-outline { background: transparent; color: var(--text-main); border: 1px solid var(--border); } .exp-btn-outline:hover { background: rgba(255,255,255,0.05); }
    .exp-btn-ghost { background: transparent; color: var(--text-sec); border: none; padding: 0.375rem; } .exp-btn-ghost:hover { background: rgba(255,255,255,0.05); color: var(--text-main); }
    .exp-btn-sm { padding: 0.375rem 0.75rem; font-size: 0.75rem; }
    .exp-recipients { display: flex; flex-direction: column; gap: 0.5rem; }
    .exp-recipient-row { display: flex; gap: 0.5rem; }
    .exp-recipient-row .exp-input { flex: 1; }
    .exp-template-save { display: flex; align-items: center; gap: 0.5rem; }
    .exp-template-save .exp-input { flex: 1; }
    .exp-tpl-item,.exp-hist-item { display: flex; align-items: center; justify-content: space-between; padding: 1rem; border: 1px solid var(--border); border-radius: 0.5rem; margin-bottom: 0.75rem; background: rgba(255,255,255,0.01); }
    .exp-tpl-info,.exp-hist-info { display: flex; align-items: center; gap: 0.75rem; }
    .exp-tpl-icon { color: var(--text-sec); width: 1.25rem; height: 1.25rem; }
    .exp-tpl-name,.exp-hist-name { font-weight: 500; color: var(--text-main); font-size: 0.875rem; }
    .exp-tpl-meta,.exp-hist-meta { font-size: 0.875rem; color: var(--text-sec); }
    .exp-tpl-actions,.exp-hist-actions { display: flex; align-items: center; gap: 0.5rem; }
    .exp-status-icon { width: 2rem; height: 2rem; border-radius: 9999px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .exp-status-icon svg { width: 1rem; height: 1rem; }
    .exp-status-success { background: rgba(52, 211, 153, 0.1); color: #34D399; }
    .exp-status-failed { background: rgba(248, 113, 113, 0.1); color: #F87171; }
    .exp-status-pending { background: rgba(251, 191, 36, 0.1); color: #FBBF24; }
    .exp-badge { display: inline-flex; align-items: center; padding: 0.125rem 0.625rem; font-size: 0.75rem; font-weight: 500; border-radius: 9999px; background: rgba(255,255,255,0.1); color: var(--text-main); }
    .exp-space > * + * { margin-top: 1rem; }
    @keyframes exp-spin { from{transform:rotate(0deg)} to{transform:rotate(360deg)} }
    .exp-spin { animation: exp-spin 1s linear infinite; }
    /* ── Schedule Sidebar (new component) ── */
    .sch-group { margin-bottom: 1.5rem; }
    .sch-label { font-size: 0.875rem; font-weight: 500; color: var(--text-main); display: block; margin-bottom: 0.75rem; }
    .sch-freq-opts { margin-top: 1rem; padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 0.5rem; border: 1px solid var(--border); }
    .sch-days { display: flex; gap: 0.5rem; margin-top: 0.5rem; }
    .sch-day-btn { width: 2.25rem; height: 2.25rem; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 600; border: 1px solid var(--border); border-radius: 0.5rem; background: transparent; color: var(--text-sec); cursor: pointer; transition: all 0.2s; }
    .sch-day-btn:hover { border-color: var(--text-sec); }
    .sch-day-btn.active { background: var(--primary); color: #0F172A; border-color: var(--primary); }
    .sch-month-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.25rem; margin-top: 0.5rem; }
    .sch-month-btn { width: 100%; aspect-ratio: 1; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; border: 1px solid var(--border); border-radius: 0.25rem; background: transparent; color: var(--text-sec); cursor: pointer; transition: all 0.2s; }
    .sch-month-btn:hover { border-color: var(--text-sec); }
    .sch-month-btn.active { background: var(--primary); color: #0F172A; border-color: var(--primary); }
    .sch-methods { display: flex; flex-direction: column; gap: 0.75rem; }
    .sch-method-opt { display: flex; align-items: center; gap: 1rem; padding: 1rem; border: 1px solid var(--border); border-radius: 0.75rem; cursor: pointer; transition: all 0.2s; background: rgba(255,255,255,0.01); }
    .sch-method-opt:hover { border-color: var(--text-sec); }
    .sch-method-opt.active { border-color: var(--primary); background: rgba(255,255,255,0.03); }
    .sch-radio { width: 1.25rem; height: 1.25rem; border: 2px solid var(--border); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .sch-method-opt.active .sch-radio { border-color: var(--primary); }
    .sch-radio-inner { width: 0.625rem; height: 0.625rem; background: var(--primary); border-radius: 50%; transform: scale(0); transition: transform 0.2s; }
    .sch-method-opt.active .sch-radio-inner { transform: scale(1); }
    .sch-method-icon { width: 2.5rem; height: 2.5rem; background: rgba(255,255,255,0.05); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; color: var(--text-sec); }
    .sch-method-opt.active .sch-method-icon { color: var(--primary); background: rgba(59, 130, 246, 0.1); }
    .sch-method-title { font-size: 0.875rem; font-weight: 600; color: var(--text-main); }
    .sch-method-desc { font-size: 0.75rem; color: var(--text-sec); }
    .sch-config-panel { margin-top: 1rem; padding: 1rem; background: rgba(59, 130, 246, 0.05); border-radius: 0.75rem; border: 1px solid rgba(59, 130, 246, 0.2); }
    .sch-tags { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.75rem; }
    .sch-tag { display: flex; align-items: center; gap: 0.5rem; padding: 0.25rem 0.75rem; background: rgba(255,255,255,0.05); border-radius: 2rem; font-size: 0.75rem; color: var(--text-main); border: 1px solid var(--border); }
    .sch-tag-remove { background: none; border: none; color: var(--text-sec); cursor: pointer; padding: 0; display: flex; align-items: center; justify-content: center; }
    .sch-tag-remove:hover { color: #F87171; }
    .sch-summary { margin-top: 1.5rem; padding: 1rem; background: rgba(52, 211, 153, 0.05); border-radius: 0.75rem; border: 1px solid rgba(52, 211, 153, 0.2); }
    .sch-summary-title { font-size: 0.75rem; font-weight: 600; color: #34D399; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.05em; }
    .sch-summary-text { font-size: 0.8rem; color: #A7F3D0; line-height: 1.4; }
    .sch-hint { font-size: 0.7rem; color: var(--text-sec); font-weight: 400; margin-left: 0.25rem; }
</style>


<div x-data="salesReport()">
    {{-- ENCABEZADO Y CONTROLES --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem;">
        <div>
            <h1 style="font-family: 'DM Serif Display', serif; font-size: 1.8rem; margin-bottom: 0.2rem; display: flex; align-items: center; gap: 10px;">
                Reporte de Ventas - Cafetería
                @if($changes['ventasTotales'] >= 0)
                    <span style="font-size: 0.75rem; background: rgba(52, 211, 153, 0.15); color: #34D399; padding: 2px 8px; border-radius: 12px; font-family: 'DM Sans', sans-serif;">
                        ↗ +{{ $changes['ventasTotales'] }}% vs anterior
                    </span>
                @else
                    <span style="font-size: 0.75rem; background: rgba(248, 113, 113, 0.15); color: #F87171; padding: 2px 8px; border-radius: 12px; font-family: 'DM Sans', sans-serif;">
                        ↘ {{ $changes['ventasTotales'] }}% vs anterior
                    </span>
                @endif
            </h1>
            <p style="color: var(--text-sec); font-size: 0.85rem;">{{ \Carbon\Carbon::parse($end)->translatedFormat('l, d \d\e F \d\e Y') }}</p>
        </div>
        
        <div style="display: flex; gap: 10px;">
            <button @click="showScheduleSidebar = true; showExportSidebar = false; showAiAssistant = false;" style="background: var(--surface); border: 1px solid var(--border); color: var(--text-main); padding: 8px 16px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 6px; font-size: 0.85rem; transition: background 0.2s;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                Programar Envío
            </button>
            <button @click="toggleAiAssistant" style="background: var(--surface); border: 1px solid var(--border); color: var(--text-main); padding: 8px 16px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 6px; font-size: 0.85rem; transition: background 0.2s;">
                ✨ Asistente IA
            </button>
            <button @click="showExportSidebar = true" style="background: var(--primary); color: #0F172A; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 0.85rem; display: flex; align-items: center; gap: 6px; transition: opacity 0.2s;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Exportar
            </button>
        </div>
    </div>

    {{-- BARRA DE FILTROS EXPANDIBLE (Shadcn Style) --}}
    <div class="shadcn-filters-container" style="margin-bottom: 2rem;">
        <div class="shadcn-filters">
            <div class="shadcn-select-wrapper">
                <select name="period" form="filterForm" x-model="datePeriod" class="shadcn-select">
                    <option value="hoy">Hoy</option>
                    <option value="semana">Esta semana</option>
                    <option value="mes">Este mes</option>
                    <option value="personalizado">Personalizado</option>
                </select>
            </div>
            
            <button type="button" class="shadcn-btn" @click="$refs.dateRangeInput._flatpickr.open()">
                <svg style="width: 1rem; height: 1rem;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <input type="text" x-ref="dateRangeInput" name="custom_range" placeholder="Seleccionar fechas..." style="background: transparent; border: none; color: inherit; font-size: inherit; font-weight: inherit; outline: none; width: 140px; cursor: pointer; text-align: left; padding: 0;" readonly>
            </button>

            <button type="button" class="shadcn-btn" @click="showFilters = !showFilters">
                <svg style="width: 1rem; height: 1rem;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                Filtros
            </button>

            <input type="hidden" name="start" form="filterForm" id="filterStart" value="{{ $start }}">
            <input type="hidden" name="end" form="filterForm" id="filterEnd" value="{{ $end }}">

            <a href="{{ route('admin.reportes.ventas') }}" class="shadcn-btn" style="margin-left: auto; text-decoration: none; color: var(--text-sec); border-color: transparent; background: transparent; box-shadow: none;" onmouseover="this.style.backgroundColor='rgba(255,255,255,0.05)'; this.style.color='var(--text-main)'" onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--text-sec)'">
                <svg style="width: 1rem; height: 1rem;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 4H8l-7 8 7 8h13a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2z"></path><line x1="18" y1="9" x2="12" y2="15"></line><line x1="12" y1="9" x2="18" y2="15"></line></svg>
                Limpiar Filtros
            </a>
        </div>

        <!-- Filters Panel (Hidden by default) -->
        <div x-show="showFilters" x-collapse style="display: none; margin-top: 1rem; position: relative; z-index: 20;">
            <div class="shadcn-card">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div>
                        <h4 style="font-weight: 500; margin-bottom: 0.5rem; font-size: 0.875rem;">Categorías</h4>
                        <div class="shadcn-checkbox-group">
                            @foreach($categoriasFiltro as $cat)
                                <label class="shadcn-checkbox-item">
                                    <input type="checkbox" name="categorias[]" value="{{ $cat->id }}" class="shadcn-checkbox" form="filterForm" {{ is_array(request('categorias')) && in_array($cat->id, request('categorias')) ? 'checked' : '' }}>
                                    <span class="shadcn-checkbox-label">{{ $cat->nombre }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <h4 style="font-weight: 500; margin-bottom: 0.5rem; font-size: 0.875rem;">Sucursales</h4>
                        <div class="shadcn-checkbox-group">
                            <label class="shadcn-checkbox-item">
                                <input type="checkbox" class="shadcn-checkbox" disabled>
                                <span class="shadcn-checkbox-label" style="color: var(--text-sec);">Local Principal</span>
                            </label>
                            <label class="shadcn-checkbox-item">
                                <input type="checkbox" class="shadcn-checkbox" disabled>
                                <span class="shadcn-checkbox-label" style="color: var(--text-sec);">Sucursal Centro</span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <h4 style="font-weight: 500; margin-bottom: 0.5rem; font-size: 0.875rem;">Método de Pago</h4>
                        <div class="shadcn-checkbox-group">
                            @foreach($metodosPagoFiltro as $metodo)
                                <label class="shadcn-checkbox-item">
                                    <input type="checkbox" name="metodos_pago[]" value="{{ $metodo }}" class="shadcn-checkbox" form="filterForm" {{ is_array(request('metodos_pago')) && in_array($metodo, request('metodos_pago')) ? 'checked' : '' }}>
                                    <span class="shadcn-checkbox-label">{{ ucfirst(strtolower($metodo)) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <h4 style="font-weight: 500; margin-bottom: 0.5rem; font-size: 0.875rem;">Productos Top</h4>
                        <div class="shadcn-checkbox-group">
                            @foreach($productosTopFiltro as $prod)
                                <label class="shadcn-checkbox-item">
                                    <input type="checkbox" name="productos_top[]" value="{{ $prod }}" class="shadcn-checkbox" form="filterForm" {{ is_array(request('productos_top')) && in_array($prod, request('productos_top')) ? 'checked' : '' }}>
                                    <span class="shadcn-checkbox-label">{{ $prod }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div style="margin-top: 1.5rem; display: flex; justify-content: flex-end; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 1rem;">
                    <form id="filterForm" action="{{ route('admin.reportes.ventas') }}" method="GET">
                        <button type="submit" class="shadcn-btn" style="background: var(--primary); color: #0F172A; border-color: var(--primary); height: auto; font-weight: 600;">Aplicar Filtros</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- TARJETAS KPIs --}}
    <div class="grid-tarjetas" style="grid-template-columns: repeat(4, 1fr);">
        <!-- Ventas Totales -->
        <div class="tarjeta-stat" style="position: relative; padding: 1.2rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.8rem;">
                <span class="stat-icono" style="margin:0; font-size: 1.1rem; color: var(--text-sec);">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
                @if($changes['ventasTotales'] >= 0)
                    <span style="font-size: 0.7rem; background: rgba(52, 211, 153, 0.1); color: #34D399; padding: 2px 6px; border-radius: 4px; font-weight: 600;">↗ {{ $changes['ventasTotales'] }}%</span>
                @else
                    <span style="font-size: 0.7rem; background: rgba(248, 113, 113, 0.1); color: #F87171; padding: 2px 6px; border-radius: 4px; font-weight: 600;">↘ {{ abs($changes['ventasTotales']) }}%</span>
                @endif
            </div>
            <div class="stat-label">Ventas Totales</div>
            <div class="stat-numero" style="font-size: 1.8rem;">${{ number_format($currentMetrics['ventasTotales'], 0, ',', '.') }}</div>
        </div>

        <!-- Clientes Atendidos -->
        <div class="tarjeta-stat" style="position: relative; padding: 1.2rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.8rem;">
                <span class="stat-icono" style="margin:0; font-size: 1.1rem; color: var(--text-sec);">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </span>
                @if($changes['clientesAtendidos'] >= 0)
                    <span style="font-size: 0.7rem; background: rgba(52, 211, 153, 0.1); color: #34D399; padding: 2px 6px; border-radius: 4px; font-weight: 600;">↗ {{ $changes['clientesAtendidos'] }}%</span>
                @else
                    <span style="font-size: 0.7rem; background: rgba(248, 113, 113, 0.1); color: #F87171; padding: 2px 6px; border-radius: 4px; font-weight: 600;">↘ {{ abs($changes['clientesAtendidos']) }}%</span>
                @endif
            </div>
            <div class="stat-label">Clientes Atendidos</div>
            <div class="stat-numero" style="font-size: 1.8rem;">{{ number_format($currentMetrics['clientesAtendidos'], 0, ',', '.') }}</div>
        </div>

        <!-- Ticket Promedio -->
        <div class="tarjeta-stat" style="position: relative; padding: 1.2rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.8rem;">
                <span class="stat-icono" style="margin:0; font-size: 1.1rem; color: var(--text-sec);">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"></path></svg>
                </span>
                @if($changes['ticketPromedio'] >= 0)
                    <span style="font-size: 0.7rem; background: rgba(52, 211, 153, 0.1); color: #34D399; padding: 2px 6px; border-radius: 4px; font-weight: 600;">↗ {{ $changes['ticketPromedio'] }}%</span>
                @else
                    <span style="font-size: 0.7rem; background: rgba(248, 113, 113, 0.1); color: #F87171; padding: 2px 6px; border-radius: 4px; font-weight: 600;">↘ {{ abs($changes['ticketPromedio']) }}%</span>
                @endif
            </div>
            <div class="stat-label">Ticket Promedio</div>
            <div class="stat-numero" style="font-size: 1.8rem;">${{ number_format($currentMetrics['ticketPromedio'], 0, ',', '.') }}</div>
        </div>

        <!-- Total Pedidos -->
        <div class="tarjeta-stat" style="position: relative; padding: 1.2rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.8rem;">
                <span class="stat-icono" style="margin:0; font-size: 1.1rem; color: var(--text-sec);">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </span>
                @if($changes['totalPedidos'] >= 0)
                    <span style="font-size: 0.7rem; background: rgba(52, 211, 153, 0.1); color: #34D399; padding: 2px 6px; border-radius: 4px; font-weight: 600;">↗ {{ $changes['totalPedidos'] }}%</span>
                @else
                    <span style="font-size: 0.7rem; background: rgba(248, 113, 113, 0.1); color: #F87171; padding: 2px 6px; border-radius: 4px; font-weight: 600;">↘ {{ abs($changes['totalPedidos']) }}%</span>
                @endif
            </div>
            <div class="stat-label">Total Pedidos</div>
            <div class="stat-numero" style="font-size: 1.8rem;">{{ $currentMetrics['totalPedidos'] }}</div>
        </div>
    </div>

    {{-- GRÁFICAS --}}
    <div class="tarjeta" style="margin-bottom: 2rem;">
        <div class="tarjeta-header" style="border-bottom: none; padding-bottom: 0;">Tendencia de Ventas</div>
        <div id="chart-tendencia" style="min-height: 280px;"></div>
    </div>

    <div class="tarjeta">
        <div class="tarjeta-header" style="border-bottom: none; padding-bottom: 0;">Ventas por Categoría</div>
        <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 2rem; align-items: center; padding: 1rem 1.5rem 2rem;">
            <div id="chart-categorias"></div>
            <div>
                @foreach($categoriasChart as $cat)
                    <div style="margin-bottom: 1rem;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 0.4rem;">
                            <span style="color: var(--text-main);">{{ $cat->nombre }}</span>
                            <div style="display: flex; gap: 15px;">
                                <strong style="color: var(--text-main);">${{ number_format($cat->total, 0, ',', '.') }}</strong>
                                <span style="color: var(--text-sec); font-size: 0.75rem;">{{ $currentMetrics['ventasTotales'] > 0 ? round(($cat->total / $currentMetrics['ventasTotales']) * 100) : 0 }}%</span>
                            </div>
                        </div>
                        <div class="progreso-vacia" style="height: 6px; background: rgba(255,255,255,0.05);">
                            <div class="progreso-llena" style="width: {{ $currentMetrics['ventasTotales'] > 0 ? ($cat->total / $currentMetrics['ventasTotales']) * 100 : 0 }}%; background: var(--primary);"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ASISTENTE IA SIDEBAR (MOCK) --}}
    <div x-show="showAiAssistant" 
         style="display: none; position: fixed; top: 0; right: 0; width: 380px; height: 100vh; background: var(--surface); border-left: 1px solid var(--border); z-index: 400; box-shadow: -10px 0 30px rgba(0,0,0,0.5);"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="transform translate-x-full"
         x-transition:enter-end="transform translate-x-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="transform translate-x-0"
         x-transition:leave-end="transform translate-x-full">
         <div style="display: flex; flex-direction: column; height: 100%;">
            <div style="padding: 1.2rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.02);">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 1.2rem;">✨</span>
                    <strong style="font-size: 1rem; color: var(--text-main);">Asistente de Análisis</strong>
                </div>
                <button @click="toggleAiAssistant" style="background: none; border: none; color: var(--text-sec); cursor: pointer; font-size: 1.2rem; padding: 0;">✕</button>
            </div>

            <div style="flex: 1; padding: 1.5rem; overflow-y: auto; display: flex; flex-direction: column; gap: 1rem;" class="custom-scroll" id="chatContainer">
                <div style="text-align: center; color: var(--text-sec); font-size: 0.8rem; margin-bottom: 1rem;">
                    Pregúntame sobre las ventas de tu cafetería
                </div>
                
                <template x-for="msg in aiMessages" :key="msg.id">
                    <div :style="msg.role === 'user' ? 'align-self: flex-end; background: var(--primary); color: #0F172A; padding: 10px 14px; border-radius: 12px 12px 0 12px; font-size: 0.85rem; max-width: 85%; line-height: 1.4;' : 'align-self: flex-start; background: rgba(255,255,255,0.05); border: 1px solid var(--border); color: var(--text-main); padding: 10px 14px; border-radius: 12px 12px 12px 0; font-size: 0.85rem; max-width: 85%; line-height: 1.4;'" x-text="msg.text">
                    </div>
                </template>
                
                <div x-show="isTyping" style="align-self: flex-start; background: rgba(255,255,255,0.05); border: 1px solid var(--border); color: var(--text-sec); padding: 10px 14px; border-radius: 12px 12px 12px 0; font-size: 0.85rem; display: flex; gap: 4px;">
                    <span style="animation: pulse 1s infinite;">.</span><span style="animation: pulse 1s infinite 0.2s;">.</span><span style="animation: pulse 1s infinite 0.4s;">.</span>
                </div>
            </div>

            <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border); background: var(--bg-main);">
                <form @submit.prevent="sendAiMessage" style="display: flex; gap: 8px;">
                    <input type="text" x-model="aiInput" placeholder="Escribe tu pregunta..." style="flex: 1; background: var(--surface); border: 1px solid var(--border); color: var(--text-main); padding: 10px 12px; border-radius: 8px; font-size: 0.85rem; outline: none;">
                    <button type="submit" style="background: var(--primary); color: #0F172A; border: none; padding: 0 14px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    {{-- SIDEBAR: EXPORTAR REPORTE --}}
    <div x-show="showExportSidebar"
         style="display:none;position:fixed;top:0;right:0;width:480px;height:100vh;z-index:400;box-shadow:-10px 0 30px rgba(0,0,0,0.3);"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="transform translate-x-full"
         x-transition:enter-end="transform translate-x-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="transform translate-x-0"
         x-transition:leave-end="transform translate-x-full">

        <div class="exp-panel" id="expPanel">
            {{-- Hidden form inputs --}}
            <form id="expForm" action="{{ route('admin.reportes.exportar') }}" method="GET" style="display:none;">
                <input type="hidden" name="start" value="{{ $start }}">
                <input type="hidden" name="end" value="{{ $end }}">
                <input type="hidden" name="format" id="expFormatInput" value="pdf">
                <input type="hidden" name="sections[]" value="kpis" id="sec-kpis" checked>
                <input type="hidden" name="sections[]" value="chart" id="sec-chart">
                <input type="hidden" name="sections[]" value="categories" id="sec-categories">
            </form>

            <div class="exp-header">
                <h2 class="exp-title">Exportar Reporte</h2>
                <p class="exp-desc">Configura y descarga tu reporte en el formato deseado</p>
            </div>

            <div class="exp-body" id="expBody">
                {{-- Tabs --}}
                <div class="exp-tabs" id="expTabs">
                    <button class="exp-tab active" data-tab="quick">Exportación rápida</button>
                    <button class="exp-tab" data-tab="templates">Plantillas</button>
                    <button class="exp-tab" data-tab="history">Historial</button>
                </div>

                {{-- Tab: Quick Export --}}
                <div class="exp-tab-content active" id="exp-tab-quick">
                    <div class="exp-space">
                        {{-- Format --}}
                        <div>
                            <label class="exp-label">Formato de exportación</label>
                            <div class="exp-format-grid">
                                <label class="exp-format-opt selected" data-format="pdf">
                                    <input type="radio" name="exp_format" value="pdf" checked>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <span class="exp-format-label">PDF</span>
                                </label>
                                <label class="exp-format-opt" data-format="excel">
                                    <input type="radio" name="exp_format" value="excel">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                                    <span class="exp-format-label">EXCEL</span>
                                </label>
                                <label class="exp-format-opt" data-format="csv">
                                    <input type="radio" name="exp_format" value="csv">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    <span class="exp-format-label">CSV</span>
                                </label>
                            </div>
                        </div>

                        {{-- Sections --}}
                        <div>
                            <label class="exp-label">Secciones a incluir</label>
                            <div class="exp-sections-grid">
                                <div class="exp-section-item selected" data-section="kpis">
                                    <div class="exp-checkbox"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg></div>
                                    <div><div class="exp-section-title">KPIs principales</div><div class="exp-section-desc">Ventas, transacciones, ticket promedio</div></div>
                                </div>
                                <div class="exp-section-item selected" data-section="chart">
                                    <div class="exp-checkbox"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg></div>
                                    <div><div class="exp-section-title">Gráfica de tendencia</div><div class="exp-section-desc">Visualización de ventas en el tiempo</div></div>
                                </div>
                                <div class="exp-section-item selected" data-section="categories">
                                    <div class="exp-checkbox"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg></div>
                                    <div><div class="exp-section-title">Desglose por categorías</div><div class="exp-section-desc">Distribución de ventas</div></div>
                                </div>
                                <div class="exp-section-item" data-section="products">
                                    <div class="exp-checkbox"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg></div>
                                    <div><div class="exp-section-title">Productos destacados</div><div class="exp-section-desc">Top productos y tendencias</div></div>
                                </div>
                                <div class="exp-section-item" data-section="comparison">
                                    <div class="exp-checkbox"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg></div>
                                    <div><div class="exp-section-title">Comparación de períodos</div><div class="exp-section-desc">vs. período anterior</div></div>
                                </div>
                                <div class="exp-section-item" data-section="predictions">
                                    <div class="exp-checkbox"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg></div>
                                    <div><div class="exp-section-title">Proyecciones</div><div class="exp-section-desc">Estimaciones de cierre</div></div>
                                </div>
                            </div>
                        </div>

                        {{-- Schedule --}}
                        <div class="exp-schedule-card">
                            <div class="exp-schedule-header">
                                <div><label class="exp-label" style="margin-bottom:0;">Programar envío automático</label><p style="font-size:0.75rem;color:#64748b;">Recibe el reporte periódicamente por email</p></div>
                                <div class="exp-switch" id="expSwitch"><div class="exp-switch-thumb"></div></div>
                            </div>
                            <div class="exp-schedule-opts" id="expScheduleOpts">
                                <div class="exp-space">
                                    <div>
                                        <label class="exp-label">Frecuencia</label>
                                        <div class="exp-select-wrap">
                                            <select class="exp-select"><option value="daily">Diario</option><option value="weekly" selected>Semanal</option><option value="monthly">Mensual</option></select>
                                            <svg class="exp-select-arrow" style="width:1rem;height:1rem;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="exp-label">Destinatarios</label>
                                        <div class="exp-recipients" id="expRecipients">
                                            <div class="exp-recipient-row"><input type="email" class="exp-input" placeholder="correo@empresa.com"><button type="button" class="exp-btn-ghost" onclick="expRemoveRecipient(this)" style="display:none;border-radius:0.5rem;"><svg style="width:1rem;height:1rem;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></div>
                                        </div>
                                        <button type="button" class="exp-btn exp-btn-outline exp-btn-sm" style="margin-top:0.5rem;" onclick="expAddRecipient()">
                                            <svg style="width:1rem;height:1rem;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            Agregar destinatario
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Template name --}}
                        <div class="exp-template-save">
                            <input type="text" class="exp-input" id="expTplName" placeholder="Nombre de plantilla (opcional)" oninput="document.getElementById('expSaveTplBtn').style.display=this.value?'flex':'none'">
                            <button type="button" class="exp-btn exp-btn-outline exp-btn-sm" id="expSaveTplBtn" style="display:none;">Guardar</button>
                        </div>
                    </div>
                </div>

                {{-- Tab: Templates --}}
                <div class="exp-tab-content" id="exp-tab-templates">
                    <div class="exp-tpl-item">
                        <div class="exp-tpl-info">
                            <svg class="exp-tpl-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <div><div class="exp-tpl-name">Reporte Semanal</div><div class="exp-tpl-meta">4 secciones – Semanal</div></div>
                        </div>
                        <div class="exp-tpl-actions"><button type="button" class="exp-btn exp-btn-outline exp-btn-sm">Usar</button></div>
                    </div>
                    <div class="exp-tpl-item">
                        <div class="exp-tpl-info">
                            <svg class="exp-tpl-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                            <div><div class="exp-tpl-name">Resumen Mensual</div><div class="exp-tpl-meta">6 secciones – Mensual</div></div>
                        </div>
                        <div class="exp-tpl-actions"><button type="button" class="exp-btn exp-btn-outline exp-btn-sm">Usar</button></div>
                    </div>
                </div>

                {{-- Tab: History --}}
                <div class="exp-tab-content" id="exp-tab-history">
                    <div class="exp-hist-item">
                        <div class="exp-hist-info">
                            <div class="exp-status-icon exp-status-success"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></div>
                            <div><div class="exp-hist-name">Reporte Semanal</div><div class="exp-hist-meta">25 Abr 2026, 09:00 – gerente@empresa.com</div></div>
                        </div>
                        <div class="exp-hist-actions"><span class="exp-badge">PDF</span></div>
                    </div>
                    <div class="exp-hist-item">
                        <div class="exp-hist-info">
                            <div class="exp-status-icon exp-status-failed"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></div>
                            <div><div class="exp-hist-name">Resumen Mensual</div><div class="exp-hist-meta">24 Abr 2026, 14:30 – equipo@empresa.com</div></div>
                        </div>
                        <div class="exp-hist-actions"><span class="exp-badge">EXCEL</span><button type="button" class="exp-btn exp-btn-outline exp-btn-sm">Reintentar</button></div>
                    </div>
                    <div class="exp-hist-item">
                        <div class="exp-hist-info">
                            <div class="exp-status-icon exp-status-pending"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                            <div><div class="exp-hist-name">Reporte Diario</div><div class="exp-hist-meta">26 Abr 2026, 08:00 – analista@empresa.com</div></div>
                        </div>
                        <div class="exp-hist-actions"><span class="exp-badge">CSV</span></div>
                    </div>
                </div>
            </div>

            <div class="exp-footer">
                <button type="button" class="exp-btn exp-btn-outline" @click="showExportSidebar = false">Cancelar</button>
                <button type="button" class="exp-btn exp-btn-primary" id="expExportBtn" style="min-width:120px;" onclick="expDoExport()">
                    <svg style="width:1rem;height:1rem;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span id="expBtnLabel">Exportar</span>
                </button>
            </div>
        </div>
    </div>

    {{-- SIDEBAR: PROGRAMAR ENVÍO --}}


    <div x-show="showScheduleSidebar"
         style="display: none; position: fixed; top: 0; right: 0; width: 480px; height: 100vh; background: var(--surface); border-left: 1px solid var(--border); z-index: 400; box-shadow: -10px 0 30px rgba(0,0,0,0.5);"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="transform translate-x-full"
         x-transition:enter-end="transform translate-x-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="transform translate-x-0"
         x-transition:leave-end="transform translate-x-full">
         
         <div x-data="scheduleData()" x-init="init()" style="display: flex; flex-direction: column; height: 100%;">
            <div style="padding: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.25rem; display: flex; align-items: center; gap: 10px;">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Programar Envío
                    </h2>
                    <p style="font-size: 0.85rem; color: var(--text-sec);">Recibe tus reportes automáticamente</p>
                </div>
                <button @click="showScheduleSidebar = false" class="exp-btn-ghost" style="font-size: 1.25rem;">✕</button>
            </div>

            <div style="flex: 1; padding: 1.5rem; overflow-y: auto;" class="custom-scroll">
                
                <!-- PROGRAMACIONES ACTIVAS -->
                <div x-show="activeSchedules.length > 0" style="margin-bottom: 2rem;">
                    <label class="sch-label">Programaciones Activas</label>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <template x-for="sch in activeSchedules" :key="sch.id">
                            <div style="border: 1px solid var(--border); padding: 0.85rem 1rem; border-radius: 10px; display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.02);">
                                <div style="display: flex; gap: 12px; align-items: center;">
                                    <div :class="sch.method === 'email' ? 'exp-status-success' : 'exp-status-pending'" style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <template x-if="sch.method === 'email'">
                                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        </template>
                                        <template x-if="sch.method === 'whatsapp'">
                                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                        </template>
                                    </div>
                                    <div>
                                        <strong style="display: block; font-size: 0.85rem;" x-text="sch.frequency.charAt(0).toUpperCase() + sch.frequency.slice(1) + ' a las ' + sch.time.substring(0, 5)"></strong>
                                        <span style="font-size: 0.75rem; color: var(--text-sec);" x-text="sch.method === 'email' ? (sch.recipients ? sch.recipients[0] : '') : sch.whatsapp_number"></span>
                                    </div>
                                </div>
                                <button @click="deleteSchedule(sch.id)" class="exp-btn-ghost" style="color: #F87171;"><svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </div>
                        </template>
                    </div>
                </div>

                <div style="height: 1px; background: var(--border); margin: 1.5rem 0;" x-show="activeSchedules.length > 0"></div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; padding: 1rem; background: rgba(255,255,255,0.02); border-radius: 0.75rem; border: 1px solid var(--border);">

                <template x-if="scheduleActive">
                    <div x-transition>
                        <!-- Secciones a incluir -->
                        <div class="sch-group">
                            <label class="sch-label">Secciones a incluir en el reporte</label>
                            <div class="exp-sections-grid">
                                <div class="exp-section-item" :class="{ 'selected': selectedSections.includes('kpis') }" @click="toggleSection('kpis')">
                                    <div class="exp-checkbox"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg></div>
                                    <div><div class="exp-section-title">KPIs principales</div><div class="exp-section-desc">Ventas, ticket promedio</div></div>
                                </div>
                                <div class="exp-section-item" :class="{ 'selected': selectedSections.includes('chart') }" @click="toggleSection('chart')">
                                    <div class="exp-checkbox"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg></div>
                                    <div><div class="exp-section-title">Gráfica de tendencia</div><div class="exp-section-desc">Visualización de ventas</div></div>
                                </div>
                                <div class="exp-section-item" :class="{ 'selected': selectedSections.includes('categories') }" @click="toggleSection('categories')">
                                    <div class="exp-checkbox"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg></div>
                                    <div><div class="exp-section-title">Categorías</div><div class="exp-section-desc">Desglose por grupos</div></div>
                                </div>
                                <div class="exp-section-item" :class="{ 'selected': selectedSections.includes('products') }" @click="toggleSection('products')">
                                    <div class="exp-checkbox"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg></div>
                                    <div><div class="exp-section-title">Productos Top</div><div class="exp-section-desc">Ranking de lo más vendido</div></div>
                                </div>
                            </div>
                        </div>

                        <div style="height: 1px; background: var(--border); margin: 1.5rem 0;"></div>

                        <!-- Frecuencia -->
                        <div class="sch-group">
                            <label class="sch-label">Frecuencia de envío</label>
                            <div class="exp-select-wrap">
                                <select class="exp-select" x-model="frequency">
                                    <option value="daily">Diario</option>
                                    <option value="weekly">Semanal</option>
                                    <option value="monthly">Mensual</option>
                                    <option value="custom">Personalizado</option>
                                </select>
                                <svg class="exp-select-arrow" style="width:1rem;height:1rem;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>

                            <!-- Opciones Diario -->
                            <div class="sch-freq-opts" x-show="frequency === 'daily'">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span style="font-size: 0.85rem;">Enviar todos los días a las:</span>
                                    <input type="time" class="exp-input" style="width: 110px;" x-model="time">
                                </div>
                            </div>

                            <!-- Opciones Semanal -->
                            <div class="sch-freq-opts" x-show="frequency === 'weekly'">
                                <span style="font-size: 0.85rem; color: var(--text-sec);">Días de la semana:</span>
                                <div class="sch-days">
                                    <template x-for="day in ['L','M','X','J','V','S','D']">
                                        <button class="sch-day-btn" 
                                                :class="{ 'active': selectedDays.includes(day) }"
                                                @click="toggleDay(day)"
                                                x-text="day"></button>
                                    </template>
                                </div>
                                <div style="margin-top: 1rem; display: flex; align-items: center; gap: 10px;">
                                    <span style="font-size: 0.85rem;">Hora:</span>
                                    <input type="time" class="exp-input" style="width: 110px;" x-model="time">
                                </div>
                            </div>

                            <!-- Opciones Mensual -->
                            <div class="sch-freq-opts" x-show="frequency === 'monthly'">
                                <span style="font-size: 0.85rem; color: var(--text-sec);">Días del mes:</span>
                                <div class="sch-month-grid">
                                    <template x-for="d in 31">
                                        <button class="sch-month-btn" 
                                                :class="{ 'active': selectedMonthDays.includes(d) }"
                                                @click="toggleMonthDay(d)"
                                                x-text="d"></button>
                                    </template>
                                </div>
                                <div style="margin-top: 1rem; display: flex; align-items: center; gap: 10px;">
                                    <span style="font-size: 0.85rem;">Hora:</span>
                                    <input type="time" class="exp-input" style="width: 110px;" x-model="time">
                                </div>
                            </div>

                            <!-- Opciones Personalizado -->
                            <div class="sch-freq-opts" x-show="frequency === 'custom'">
                                <div style="display: flex; flex-direction: column; gap: 12px;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <span style="font-size: 0.85rem; min-width: 50px;">Cada</span>
                                        <input type="number" class="exp-input" style="width: 70px;" x-model="customValue" min="1">
                                        <div class="exp-select-wrap" style="flex: 1;">
                                            <select class="exp-select" x-model="customUnit">
                                                <option value="days">Días</option>
                                                <option value="weeks">Semanas</option>
                                                <option value="months">Meses</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <span style="font-size: 0.85rem; min-width: 50px;">Desde</span>
                                        <input type="date" class="exp-input" x-model="startDate">
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <span style="font-size: 0.85rem; min-width: 50px;">Hasta</span>
                                        <input type="date" class="exp-input" x-model="endDate" placeholder="Sin fin">
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <span style="font-size: 0.85rem; min-width: 50px;">Hora</span>
                                        <input type="time" class="exp-input" style="width: 110px;" x-model="time">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div style="height: 1px; background: var(--border); margin: 1.5rem 0;"></div>

                        <!-- Método de Envío -->
                        <div class="sch-group">
                            <label class="sch-label">Método de envío</label>
                            <div class="sch-methods">
                                <div class="sch-method-opt" :class="{ 'active': method === 'email' }" @click="method = 'email'">
                                    <div class="sch-radio"><div class="sch-radio-inner"></div></div>
                                    <div class="sch-method-icon">
                                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    </div>
                                    <div style="flex: 1;">
                                        <div class="sch-method-title">Correo electrónico</div>
                                        <div class="sch-method-desc">Reporte en PDF/Excel por email</div>
                                    </div>
                                </div>

                                <div class="sch-method-opt" :class="{ 'active': method === 'whatsapp' }" @click="method = 'whatsapp'">
                                    <div class="sch-radio"><div class="sch-radio-inner"></div></div>
                                    <div class="sch-method-icon">
                                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    </div>
                                    <div style="flex: 1;">
                                        <div class="sch-method-title">WhatsApp Business</div>
                                        <div class="sch-method-desc">Notificación directa con enlace</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Paneles de Configuración de Método -->
                            <div class="sch-config-panel" x-show="method === 'whatsapp'" x-transition>
                                <label class="sch-label">Configuración de WhatsApp</label>
                                <div style="display: flex; flex-direction: column; gap: 10px;">
                                    <input type="tel" class="exp-input" placeholder="+57 300 000 0000" x-model="config.whatsappNumber">
                                    <p style="font-size: 0.7rem; color: var(--text-sec);">Se enviará un mensaje con el resumen y link de descarga.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Destinatarios (solo para Email) -->
                        <div class="sch-group" x-show="method === 'email'">
                            <label class="sch-label">Destinatarios <span class="sch-hint">(Enter para agregar)</span></label>
                            <div class="sch-tags">
                                <template x-for="email in recipients" :key="email">
                                    <div class="sch-tag">
                                        <span x-text="email"></span>
                                        <button class="sch-tag-remove" @click="removeRecipient(email)">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                            <div style="display: flex; gap: 8px;">
                                <input type="email" class="exp-input" placeholder="correo@ejemplo.com" 
                                       x-model="recipientInput" 
                                       @keydown.enter.prevent="addRecipient()">
                                <button class="exp-btn exp-btn-outline" @click="addRecipient()">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Resumen -->
                        <div class="sch-summary">
                            <div class="sch-summary-title">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Resumen de Programación
                            </div>
                            <div class="sch-summary-text" x-text="getSummaryText()"></div>
                        </div>
                    </div>
                </template>

                <div x-show="!scheduleActive" style="text-align: center; padding: 3rem 1rem; color: var(--text-sec);">
                    <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-bottom: 1rem; opacity: 0.2;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p>La programación automática está desactivada.</p>
                </div>

            </div>

            <div style="padding: 1.25rem 1.5rem; border-top: 1px solid rgba(255,255,255,0.05); display: flex; justify-content: flex-end; gap: 12px; background: var(--bg-main);">
                <button type="button" class="exp-btn exp-btn-outline" @click="sendTestReport()" :disabled="isSendingTest">
                    <template x-if="!isSendingTest">
                        <span style="display: flex; align-items: center; gap: 6px;">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            Enviar prueba
                        </span>
                    </template>
                    <template x-if="isSendingTest">
                        <span style="display: flex; align-items: center; gap: 6px;">
                            <svg class="exp-spin" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Enviando...
                        </span>
                    </template>
                </button>
                <button type="button" class="exp-btn exp-btn-primary" @click="saveSchedule()">
                    Guardar
                </button>
            </div>
         </div>
    </div>

    {{-- OVERLAY PARA SIDEBARS --}}
    <div x-show="showAiAssistant || showExportSidebar || showScheduleSidebar" 
         @click="showAiAssistant = false; showExportSidebar = false; showScheduleSidebar = false;" 
         style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 399; backdrop-filter: blur(2px);" 
         :style="(showAiAssistant || showExportSidebar || showScheduleSidebar) ? 'display: block;' : 'display: none;'"
         x-transition.opacity></div>

</div>

<script>
    // Componente AlpineJS para Programación de Envío
    document.addEventListener('alpine:init', () => {
        Alpine.data('scheduleData', () => ({
            scheduleActive: true,
            frequency: 'daily',
            time: '09:00',
            selectedDays: ['L', 'X', 'V'],
            selectedMonthDays: [1, 15],
            customValue: 2,
            customUnit: 'days',
            startDate: new Date().toISOString().split('T')[0],
            endDate: '',
            method: 'email',
            recipients: ['admin@cafeteria.com'],
            recipientInput: '',
            config: {
                whatsappNumber: ''
            },
            activeSchedules: [],
            selectedSections: ['kpis', 'chart'],
            isSendingTest: false,
            isLoading: false,

            init() {
                try {
                    this.fetchSchedules();
                } catch (e) {
                    console.error('Initial fetch failed:', e);
                }
            },

            async apiRequest(url, method = 'GET', data = null) {
                const options = {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                };
                if (data) options.body = JSON.stringify(data);
                
                const response = await fetch(url, options);
                if (!response.ok) {
                    const error = await response.json();
                    throw new Error(error.message || 'Error en la petición');
                }
                return response.json();
            },

            async fetchSchedules() {
                try {
                    this.activeSchedules = await this.apiRequest('{{ route("admin.reportes.programacion.index") }}');
                } catch (e) {
                    console.error('Error cargando programaciones:', e);
                }
            },

            toggleSection(section) {
                if (this.selectedSections.includes(section)) {
                    this.selectedSections = this.selectedSections.filter(s => s !== section);
                } else {
                    this.selectedSections.push(section);
                }
            },

            toggleDay(day) {
                if (this.selectedDays.includes(day)) {
                    this.selectedDays = this.selectedDays.filter(d => d !== day);
                } else {
                    this.selectedDays.push(day);
                }
            },

            toggleMonthDay(day) {
                if (this.selectedMonthDays.includes(day)) {
                    this.selectedMonthDays = this.selectedMonthDays.filter(d => d !== day);
                } else {
                    this.selectedMonthDays.push(day);
                }
            },

            addRecipient() {
                const email = this.recipientInput.trim();
                if (email && email.includes('@') && !this.recipients.includes(email)) {
                    this.recipients.push(email);
                    this.recipientInput = '';
                }
            },

            removeRecipient(email) {
                this.recipients = this.recipients.filter(e => e !== email);
            },

            async deleteSchedule(id) {
                if (!confirm('¿Estás seguro de eliminar esta programación?')) return;
                try {
                    await this.apiRequest(`{{ url('admin/reportes/programacion') }}/${id}/eliminar`, 'POST');
                    this.activeSchedules = this.activeSchedules.filter(s => s.id !== id);
                } catch (e) {
                    alert('Error: ' + e.message);
                }
            },

            async sendTestReport() {
                if (this.method === 'email' && this.recipients.length === 0) {
                    alert('Por favor agrega al menos un destinatario.');
                    return;
                }
                if (this.method === 'whatsapp' && !this.config.whatsappNumber) {
                    alert('Por favor ingresa un número de WhatsApp.');
                    return;
                }

                this.isSendingTest = true;
                const target = this.method === 'email' ? this.recipients[0] : this.config.whatsappNumber;

                try {
                    await this.apiRequest('{{ route("admin.reportes.programacion.test") }}', 'POST', {
                        method: this.method,
                        sections: this.selectedSections,
                        recipients: this.recipients,
                        whatsapp_number: this.config.whatsappNumber
                    });
                    alert(`✅ Reporte de prueba enviado exitosamente a ${target}.`);
                } catch (e) {
                    alert('Error enviando prueba: ' + e.message);
                } finally {
                    this.isSendingTest = false;
                }
            },

            getSummaryText() {
                let freqText = '';
                if (this.frequency === 'daily') freqText = 'diariamente';
                else if (this.frequency === 'weekly') freqText = `los días ${this.selectedDays.join(', ')}`;
                else if (this.frequency === 'monthly') freqText = `los días ${this.selectedMonthDays.join(', ')} de cada mes`;
                else if (this.frequency === 'custom') freqText = `cada ${this.customValue} ${this.customUnit}`;

                let methodText = '';
                if (this.method === 'email') methodText = `vía email a ${this.recipients.length} destinatario(s)`;
                else if (this.method === 'whatsapp') methodText = `vía WhatsApp al ${this.config.whatsappNumber}`;

                return `Se enviará ${freqText} a las ${this.time} ${methodText}. Se incluirán las secciones: ${this.selectedSections.join(', ')}.`;
            },

            async saveSchedule() {
                this.isLoading = true;
                try {
                    const data = {
                        active: this.scheduleActive,
                        frequency: this.frequency,
                        time: this.time,
                        days: this.selectedDays,
                        month_days: this.selectedMonthDays,
                        custom_config: { value: this.customValue, unit: this.customUnit, start: this.startDate, end: this.endDate },
                        method: this.method,
                        recipients: this.recipients,
                        whatsapp_number: this.config.whatsappNumber,
                        sections: this.selectedSections
                    };

                    const response = await this.apiRequest('{{ route("admin.reportes.programacion.store") }}', 'POST', data);
                    
                    this.activeSchedules.unshift(response.schedule);
                    alert('¡Programación guardada exitosamente!');
                    
                    // Resetear algunos campos si se desea
                } catch (e) {
                    alert('Error al guardar: ' + e.message);
                } finally {
                    this.isLoading = false;
                }
            }
        }));

        Alpine.data('salesReport', () => ({
            showScheduleSidebar: false,
            showExportSidebar: false,
            showAiAssistant: false,
            showFilters: false,
            aiInput: '',
            isTyping: false,
            aiMessages: [
                { id: 1, role: 'ai', text: '¡Hola! Soy tu asistente de análisis. Pregúntame cuál es la bebida más vendida o las proyecciones de este mes.' }
            ],
            datePeriod: '{{ $period }}',
            fpInstance: null,

            init() {
                // Initialize Flatpickr for custom date ranges
                this.fpInstance = flatpickr(this.$refs.dateRangeInput, {
                    mode: "range",
                    locale: "es",
                    dateFormat: "Y-m-d",
                    altInput: true,
                    altFormat: "d M Y",
                    defaultDate: ["{{ $start }}", "{{ $end }}"],
                    onChange: (selectedDates, dateStr, instance) => {
                        if (selectedDates.length === 2) {
                            let start = flatpickr.formatDate(selectedDates[0], "Y-m-d");
                            let end = flatpickr.formatDate(selectedDates[1], "Y-m-d");
                            document.getElementById('filterStart').value = start;
                            document.getElementById('filterEnd').value = end;
                            this.datePeriod = 'personalizado';
                            this.$nextTick(() => document.getElementById('filterForm').submit());
                        }
                    }
                });

                // Watch for changes in the dropdown to trigger form submission
                this.$watch('datePeriod', (value) => {
                    if (value === 'personalizado') return;
                    this.$nextTick(() => document.getElementById('filterForm').submit());
                });
            },
            
            toggleAiAssistant() {
                this.showAiAssistant = !this.showAiAssistant;
            },

            sendAiMessage() {
                if(this.aiInput.trim() === '') return;
                
                this.aiMessages.push({ id: Date.now(), role: 'user', text: this.aiInput });
                let question = this.aiInput;
                this.aiInput = '';
                this.isTyping = true;
                
                setTimeout(() => {
                    this.$el.querySelector('#chatContainer').scrollTop = this.$el.querySelector('#chatContainer').scrollHeight;
                }, 50);

                // Mock de la IA
                setTimeout(() => {
                    this.isTyping = false;
                    let response = "Analizando tus datos... Las ventas este periodo han sido de $" + {{ $currentMetrics['ventasTotales'] }} + ". Recomiendo promocionar las categorías con menos movimiento para equilibrar.";
                    
                    if(question.toLowerCase().includes('mas vendida') || question.toLowerCase().includes('más vendida')) {
                        response = "De acuerdo a la gráfica, la categoría dominante representa un buen porcentaje de tus ingresos. Es un gran momento para lanzar promociones cruzadas.";
                    }

                    this.aiMessages.push({ 
                        id: Date.now(), 
                        role: 'ai', 
                        text: response 
                    });
                    
                    setTimeout(() => {
                        this.$el.querySelector('#chatContainer').scrollTop = this.$el.querySelector('#chatContainer').scrollHeight;
                    }, 50);
                }, 1500);
            }
        }))
    });

    // Gráficos ApexCharts
    document.addEventListener("DOMContentLoaded", function() {
        // --- Tendencia ---
        var optionsTendencia = {
            series: [{
                name: 'Ventas ($)',
                data: @json($trendTotals)
            }],
            chart: {
                type: 'area',
                height: 280,
                toolbar: { show: false },
                background: 'transparent',
                fontFamily: 'DM Sans, sans-serif',
                parentHeightOffset: 0
            },
            colors: ['#CBD5E1'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.3,
                    opacityTo: 0.05,
                    stops: [0, 90, 100]
                }
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            xaxis: {
                categories: @json($trendDates),
                labels: { style: { colors: '#94A3B8', fontSize: '11px' } },
                axisBorder: { show: false },
                axisTicks: { show: false },
                tooltip: { enabled: false }
            },
            yaxis: {
                labels: {
                    style: { colors: '#94A3B8', fontSize: '11px' },
                    formatter: function (value) { return "$" + value.toLocaleString(); }
                }
            },
            grid: {
                borderColor: 'rgba(255,255,255,0.05)',
                strokeDashArray: 4,
                padding: { top: 0, right: 0, bottom: 0, left: 10 }
            },
            theme: { mode: 'dark' },
            tooltip: {
                theme: 'dark',
                y: { formatter: function (val) { return "$" + val.toLocaleString() } }
            }
        };

        var chartTendencia = new ApexCharts(document.querySelector("#chart-tendencia"), optionsTendencia);
        chartTendencia.render();

        // --- Categorías ---
        var optionsCategorias = {
            series: @json($catTotals->map(fn($v) => (int)$v)),
            labels: @json($catNames),
            chart: {
                type: 'donut',
                height: 280,
                background: 'transparent',
                fontFamily: 'DM Sans, sans-serif'
            },
            colors: ['#CBD5E1', '#94A3B8', '#64748B', '#475569', '#334155', '#1E293B'],
            stroke: { show: true, colors: ['#111827'], width: 3 },
            dataLabels: { enabled: false },
            plotOptions: {
                pie: {
                    donut: { 
                        size: '75%',
                        labels: {
                            show: true,
                            name: { show: false },
                            value: {
                                show: true,
                                fontSize: '1.2rem',
                                color: '#F8FAFC',
                                formatter: function (val) { return "$" + parseInt(val).toLocaleString() }
                            },
                            total: {
                                show: true,
                                showAlways: true,
                                label: 'Total',
                                color: '#94A3B8',
                                formatter: function (w) {
                                    return "$" + w.globals.seriesTotals.reduce((a, b) => { return a + b }, 0).toLocaleString()
                                }
                            }
                        }
                    }
                }
            },
            legend: { show: false },
            theme: { mode: 'dark' },
            tooltip: { theme: 'dark' }
        };

        var chartCategorias = new ApexCharts(document.querySelector("#chart-categorias"), optionsCategorias);
        chartCategorias.render();
    });
</script>

<script>
/* ── Export Sidebar JS ── */
// Tabs
document.querySelectorAll('.exp-tab').forEach(function(t) {
    t.addEventListener('click', function() {
        document.querySelectorAll('.exp-tab').forEach(function(x){ x.classList.remove('active'); });
        document.querySelectorAll('.exp-tab-content').forEach(function(x){ x.classList.remove('active'); });
        t.classList.add('active');
        var c = document.getElementById('exp-tab-' + t.dataset.tab);
        if (c) c.classList.add('active');
    });
});

// Format selection
document.querySelectorAll('.exp-format-opt').forEach(function(opt) {
    opt.addEventListener('click', function() {
        document.querySelectorAll('.exp-format-opt').forEach(function(o){ o.classList.remove('selected'); });
        opt.classList.add('selected');
        var inp = opt.querySelector('input');
        if (inp) inp.checked = true;
        var fi = document.getElementById('expFormatInput');
        if (fi) fi.value = opt.dataset.format;
    });
});

// Sections toggle
document.querySelectorAll('.exp-section-item').forEach(function(item) {
    item.addEventListener('click', function() { item.classList.toggle('selected'); });
});

// Schedule switch
var expSwitch = document.getElementById('expSwitch');
var expOpts   = document.getElementById('expScheduleOpts');
if (expSwitch) {
    expSwitch.addEventListener('click', function() {
        expSwitch.classList.toggle('on');
        if (expOpts) expOpts.classList.toggle('visible');
    });
}

// Recipients
function expAddRecipient() {
    var list = document.getElementById('expRecipients');
    var row = document.createElement('div');
    row.className = 'exp-recipient-row';
    row.innerHTML = '<input type="email" class="exp-input" placeholder="correo@empresa.com"><button type="button" class="exp-btn-ghost" onclick="expRemoveRecipient(this)" style="border-radius:0.5rem;"><svg style="width:1rem;height:1rem;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>';
    list.appendChild(row);
    expUpdateDelBtns();
}
function expRemoveRecipient(btn) { btn.closest('.exp-recipient-row').remove(); expUpdateDelBtns(); }
function expUpdateDelBtns() {
    var rows = document.querySelectorAll('.exp-recipient-row');
    rows.forEach(function(r){ var b=r.querySelector('button'); if(b) b.style.display=rows.length>1?'flex':'none'; });
}

// Export button
function expDoExport() {
    var btn  = document.getElementById('expExportBtn');
    var lbl  = document.getElementById('expBtnLabel');
    var form = document.getElementById('expForm');
    if (!btn || !form) return;

    // Formato seleccionado
    var selFmt = document.querySelector('.exp-format-opt.selected');
    if (selFmt) {
        var fi = document.getElementById('expFormatInput');
        if (fi) fi.value = selFmt.dataset.format;
    }

    // Reconstruir inputs de secciones
    form.querySelectorAll('input[name="sections[]"]').forEach(function(i){ i.remove(); });
    document.querySelectorAll('.exp-section-item.selected').forEach(function(s){
        var inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = 'sections[]'; inp.value = s.dataset.section;
        form.appendChild(inp);
    });

    btn.disabled = true;
    lbl.textContent = 'Exportando...';
    form.submit();

    setTimeout(function(){
        btn.disabled = false;
        lbl.textContent = 'Exportar';
    }, 3000);
}
</script>

@endsection

