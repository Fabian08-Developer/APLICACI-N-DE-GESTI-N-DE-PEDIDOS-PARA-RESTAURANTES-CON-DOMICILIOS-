@extends('mesero.layout')

@section('titulo', 'Nueva Reserva')

@section('contenido')
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
<style>
    /* ── Estilos base del Wizard de Reservas ── */
    .wizard-theme {
        --gold:     #E07A5F;
        --gold-light: #e6927a;
        --dark:     #ffffff;
        --surface:  #ffffff;
        --border:   rgba(44,36,27,0.12);
        --text:     #2c241b;
        --text-dim: #64748b;
        --radius:   16px;
        --shadow:   0 4px 16px rgba(0,0,0,0.06);
        --transition: all 0.3s ease;
        font-family: 'DM Sans', sans-serif;
    }

    .wizard-theme-wrapper {
        background-color: var(--dark);
        color: var(--text);
        border-radius: var(--radius);
        padding: 2rem;
    }

    .wizard-theme .serif { font-family: 'DM Serif Display', serif; }

    .wizard-theme .wizard-container {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 2rem;
        align-items: start;
    }

    @media (max-width: 850px) {
        .wizard-theme .wizard-container { grid-template-columns: 1fr; }
    }

    @media (max-width: 600px) {
        .wizard-theme-wrapper { padding: 1rem 0.5rem; border: none; border-radius: 0; box-shadow: none; background: transparent; }
        .wizard-theme .glass-panel { padding: 1.5rem 1rem; }
        .wizard-theme .page-header h1 { font-size: 2rem; }
        .wizard-theme .btn-actions { flex-direction: column-reverse; gap: 1rem; }
        .wizard-theme .btn { width: 100%; }
        .wizard-theme .mesas-grid { grid-template-columns: 1fr; }
    }

    .wizard-theme .glass-panel {
        background: var(--surface);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        padding: 2.5rem;
    }

    .wizard-theme .page-header { margin-bottom: 2rem; }
    .wizard-theme .page-header h1 {
        font-size: 2.5rem;
        color: var(--gold);
        margin-bottom: 0.5rem;
        font-weight: 600;
    }
    .wizard-theme .page-header p { color: var(--text-dim); }

    .wizard-theme .steps {
        display: flex;
        justify-content: space-between;
        margin-bottom: 3rem;
        position: relative;
    }
    .wizard-theme .steps::before {
        content: '';
        position: absolute;
        top: 20px; left: 0; right: 0;
        height: 2px;
        background: var(--border);
        z-index: 0;
    }
    .wizard-theme .step {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
    }
    .wizard-theme .step-num {
        width: 40px; height: 40px;
        border-radius: 50%;
        background: var(--dark);
        border: 2px solid var(--border);
        display: flex; align-items: center; justify-content: center;
        font-weight: 600;
        color: var(--text-dim);
        transition: var(--transition);
    }
    .wizard-theme .step.active .step-num {
        border-color: var(--gold);
        color: var(--gold);
        box-shadow: 0 0 15px rgba(224,122,95,0.3);
    }
    .wizard-theme .step.done .step-num {
        background: var(--gold);
        border-color: var(--gold);
        color: #fff;
    }
    .wizard-theme .step-label { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-dim); }
    .wizard-theme .step.active .step-label { color: var(--gold); }

    .wizard-theme .form-group { margin-bottom: 1.5rem; }
    .wizard-theme .form-label { display: block; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-dim); margin-bottom: 0.5rem; }
    .wizard-theme .form-control {
        width: 100%;
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 1rem;
        color: var(--text);
        font-family: 'DM Sans', sans-serif;
        transition: var(--transition);
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }
    .wizard-theme .form-control:focus { outline: none; border-color: var(--gold); }
    .wizard-theme .form-control[type="date"]::-webkit-calendar-picker-indicator,
    .wizard-theme .form-control[type="time"]::-webkit-calendar-picker-indicator { cursor: pointer; }

    .wizard-theme .slots-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 0.75rem;
        margin-top: 1rem;
    }
    .wizard-theme .slot-btn {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 0.75rem 0.5rem;
        text-align: center;
        cursor: pointer;
        transition: var(--transition);
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }
    .wizard-theme .slot-btn:hover { border-color: var(--gold); }
    .wizard-theme .slot-btn.active { background: rgba(224,122,95,0.08); border-color: var(--gold); box-shadow: inset 0 0 10px rgba(224,122,95,0.1); color: var(--gold); font-weight:600; }
    .wizard-theme .slot-btn.disabled { opacity: 0.4; cursor: not-allowed; text-decoration: line-through; border-color: transparent; }
    
    .wizard-theme .mesas-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    .wizard-theme .mesa-btn {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 1.5rem 1rem;
        text-align: center;
        cursor: pointer;
        transition: var(--transition);
        position: relative;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }
    .wizard-theme .mesa-btn:hover { border-color: var(--gold); transform: translateY(-2px); }
    .wizard-theme .mesa-btn.active { background: rgba(224,122,95,0.05); border-color: var(--gold); box-shadow: 0 5px 15px rgba(224,122,95,0.15); }
    .wizard-theme .mesa-btn.disabled { opacity: 0.55; cursor: not-allowed; border-color: rgba(239,68,68,0.3); background: rgba(239,68,68,0.05); }
    .wizard-theme .mesa-btn.disabled:hover { transform: none; border-color: rgba(239,68,68,0.3); }
    .wizard-theme .mesa-btn .icon { font-size: 2rem; margin-bottom: 0.5rem; display: block; }
    .wizard-theme .mesa-btn .name { font-family: 'DM Serif Display', serif; font-size: 1.2rem; color: var(--text); font-weight:600; }
    .wizard-theme .mesa-btn .check { position: absolute; top: 10px; right: 10px; color: var(--gold); font-size: 1.2rem; display: none; }
    .wizard-theme .mesa-btn.active .check { display: block; }

    .wizard-theme .btn-actions { display: flex; justify-content: space-between; margin-top: 2.5rem; }
    .wizard-theme .btn {
        padding: 1rem 2rem;
        border-radius: 12px;
        font-family: 'DM Sans', sans-serif;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        border: none;
    }
    .wizard-theme .btn-prev { background: #f1f5f9; color: var(--text-dim); border: 1px solid var(--border); }
    .wizard-theme .btn-prev:hover { background: #e2e8f0; color: var(--text); }
    .wizard-theme .btn-next, .wizard-theme .btn-submit { background: var(--gold); color: #fff; }
    .wizard-theme .btn-next:hover, .wizard-theme .btn-submit:hover { background: var(--gold-light); transform: translateY(-2px); box-shadow: 0 5px 15px rgba(224,122,95,0.3); }
    .wizard-theme .btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; box-shadow: none; }

    .wizard-theme .summary-panel { position: sticky; top: 2rem; }
    .wizard-theme .summary-title { font-family: 'DM Serif Display', serif; font-size: 1.5rem; color: var(--text); margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem; }
    .wizard-theme .summary-item { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.2rem; }
    .wizard-theme .summary-icon { width: 40px; height: 40px; border-radius: 10px; background: rgba(224,122,95,0.1); color: var(--gold); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
    .wizard-theme .summary-detail span { display: block; }
    .wizard-theme .summary-label { font-size: 0.75rem; text-transform: uppercase; color: var(--text-dim); }
    .wizard-theme .summary-value { font-size: 1rem; font-weight: 600; color: var(--text); }
    
    .wizard-theme .errors { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); border-radius: 12px; padding: 1rem; margin-bottom: 1.5rem; color: #ef4444; font-size: 0.9rem; }
    [x-cloak] { display: none !important; }

    /* Estilos extra para el checkbox de depósito */
    .wizard-theme .checkbox-label {
        display: flex; align-items: flex-start; gap: 0.75rem; cursor: pointer; font-size: 0.95rem; color: var(--text); line-height: 1.4; font-weight: 500;
        margin-top: 1.5rem; background: #fff; padding: 1rem; border-radius: 12px; border: 1px solid var(--border);
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }
    .wizard-theme .checkbox-label input[type="checkbox"] { margin-top: 0.2rem; width: 1.2rem; height: 1.2rem; accent-color: var(--gold); cursor: pointer; }
</style>

<div class="wizard-theme-wrapper wizard-theme">
    <div class="wizard-container" x-data="reservaWizardMesero()">
        
        <!-- Left: Wizard Form -->
        <div class="glass-panel">
            <div class="page-header">
                <a href="{{ route('mesero.reservas.index') }}" style="color:var(--text-dim); text-decoration:none; font-size:0.9rem; display:inline-block; margin-bottom:1rem;">← Volver a Reservas</a>
                <h1 class="serif">Nueva Reserva</h1>
                <p>Creación de reserva desde el panel del restaurante</p>
            </div>

            <div class="steps">
                <div class="step" :class="{'active': step === 1, 'done': step > 1}">
                    <div class="step-num">1</div><div class="step-label">Horario</div>
                </div>
                <div class="step" :class="{'active': step === 2, 'done': step > 2}">
                    <div class="step-num">2</div><div class="step-label">Mesas</div>
                </div>
                <div class="step" :class="{'active': step === 3, 'done': step > 3}">
                    <div class="step-num">3</div><div class="step-label">Datos</div>
                </div>
            </div>

            @if($errors->any())
                <div class="errors">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('mesero.reservas.store') }}" id="formReserva">
                @csrf
                
                <!-- Hidden inputs to send to server -->
                <input type="hidden" name="fecha_reserva" :value="fecha">
                <input type="hidden" name="numero_personas" :value="personas">
                
                <!-- Para el mesero, permitimos forzar la hora si es necesario -->
                <input type="hidden" name="hora_inicio" :value="horaSeleccionada">
                
                <template x-for="mesaId in mesasSeleccionadas" :key="mesaId">
                    <input type="hidden" name="mesas_ids[]" :value="mesaId">
                </template>

                <!-- STEP 1: Fecha, Personas y Horario -->
                <div x-show="step === 1" x-transition x-cloak>
                    <div class="form-group">
                        <label class="form-label">Fecha</label>
                        <input type="date" class="form-control" x-model="fecha" @change="fetchSlots()">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Número de personas</label>
                        <input type="number" class="form-control" x-model="personas" @change="fetchSlots()" min="1" max="100">
                    </div>

                    <div class="form-group" style="margin-top:2rem;">
                        <label class="form-label">Hora de llegada <span x-show="isLoadingSlots" style="color:var(--gold);">(Cargando sugerencias...)</span></label>
                        
                        <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 1rem;">
                            <span style="font-size: 0.9rem; color: var(--text-dim);">Modo Manual:</span>
                            <input type="time" class="form-control" style="width: auto; padding: 0.5rem;" x-model="horaSeleccionada" @change="validarAnticipacion(); fetchMesasOcupadas();">
                        </div>

                        <label class="form-label">O elige un horario sugerido disponible:</label>
                        <div class="slots-grid">
                            <template x-for="slot in slots" :key="slot.hora_inicio">
                                <div class="slot-btn" 
                                     :class="{'active': horaSeleccionada === slot.hora_inicio, 'disabled': !slot.disponible}"
                                     @click="if(slot.disponible) { horaSeleccionada = slot.hora_inicio; validarAnticipacion(); fetchMesasOcupadas(); }">
                                    <span x-text="slot.hora_inicio.substring(0,5)"></span>
                                </div>
                            </template>
                            <div x-show="slots.length === 0 && !isLoadingSlots" style="grid-column: 1/-1; color: var(--text-dim); font-size:0.9rem;">
                                No hay horarios automáticos para esta fecha. Usa el modo manual.
                            </div>
                        </div>

                        <!-- Alerta en tiempo real de anticipación mínima -->
                        <div x-show="errorHora" x-transition
                             style="margin-top:1rem; padding:0.85rem 1rem; border-radius:10px; background:rgba(239,68,68,0.12); border:1px solid rgba(239,68,68,0.35); display:flex; align-items:center; gap:0.6rem; color:#f87171; font-size:0.88rem;">
                            <span style="display:flex; align-items:center;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                            </span>
                            <span x-text="errorHora"></span>
                        </div>
                    </div>

                    <div class="btn-actions" style="justify-content: flex-end;">
                        <button type="button" class="btn btn-next" @click="step = 2; fetchMesasOcupadas();" :disabled="!fecha || !personas || !horaSeleccionada || !!errorHora">Continuar →</button>
                    </div>
                </div>

                <!-- STEP 2: Selección de Mesa -->
                <div x-show="step === 2" x-transition x-cloak>
                    <p style="color:var(--text-dim); margin-bottom:1.5rem;">Elige las mesas para la reserva (puedes elegir varias). Si no eliges ninguna, el sistema asignará automáticamente entre las disponibles.</p>
                    
                    <div class="mesas-grid">
                        <div class="mesa-btn" :class="{'active': mesasSeleccionadas.length === 0}" @click="mesasSeleccionadas = []">
                            <span class="icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3 7 7 3-7 3-3 7-3-7-7-3 7-3z"/></svg>
                            </span>
                            <span class="name">Automática</span>
                            <span style="display:block; font-size:0.8rem; color:var(--text-dim); margin-top:0.5rem;">Asignación por sistema</span>
                        </div>

                        @foreach($mesas as $mesa)
                        <div class="mesa-btn" 
                             :class="{'active': mesasSeleccionadas.includes('{{ $mesa->id }}'), 'disabled': mesasOcupadas.includes('{{ $mesa->id }}')}" 
                             @click="if(!mesasOcupadas.includes('{{ $mesa->id }}')) { toggleMesa('{{ $mesa->id }}') }">
                            <div class="check">✓</div>
                            <span class="icon">
                                <template x-if="mesasOcupadas.includes('{{ $mesa->id }}')">
                                    <span>🚫</span>
                                </template>
                                <template x-if="!mesasOcupadas.includes('{{ $mesa->id }}')">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/></svg>
                                </template>
                            </span>
                            <span class="name">Mesa {{ $mesa->numero }}</span>
                            <span style="display:block; font-size:0.8rem; color:var(--text-dim); margin-top:0.5rem;">Capacidad: {{ $mesa->capacidad }}</span>
                            <span x-show="mesasOcupadas.includes('{{ $mesa->id }}')" style="display:block; font-size:0.75rem; color:#ef4444; font-weight:600; margin-top:0.4rem; background:rgba(239,68,68,0.12); padding:0.2rem 0.5rem; border-radius:6px;">Reservada en este horario</span>
                        </div>
                        @endforeach
                    </div>

                    <div class="btn-actions">
                        <button type="button" class="btn btn-prev" @click="step = 1">← Atrás</button>
                        <button type="button" class="btn btn-next" @click="step = 3">Continuar →</button>
                    </div>
                </div>

                <!-- STEP 3: Datos de Contacto y Cierre -->
                <div x-show="step === 3" x-transition x-cloak>
                    <div class="form-group">
                        <label class="form-label">Nombre Completo *</label>
                        <input type="text" name="nombre_cliente" class="form-control" value="{{ old('nombre_cliente') }}" required>
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                        <div class="form-group">
                            <label class="form-label">Teléfono *</label>
                            <input type="tel" name="telefono_cliente" class="form-control" value="{{ old('telefono_cliente') }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Correo *</label>
                            <input type="email" name="correo_cliente" class="form-control" value="{{ old('correo_cliente') }}" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Notas del Cliente (Peticiones especiales)</label>
                        <textarea name="notas_cliente" class="form-control" rows="2">{{ old('notas_cliente') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Notas Internas (Visible solo para staff)</label>
                        <textarea name="notas_internas" class="form-control" rows="2" placeholder="Ej: VIP, celebrar cumpleaños, atención rápida...">{{ old('notas_internas') }}</textarea>
                    </div>

                    @if($requiereDeposito && $montoDeposito > 0)
                    <label class="checkbox-label">
                        <input type="checkbox" name="deposito_pagado_efectivo" value="1" {{ old('deposito_pagado_efectivo') ? 'checked' : '' }}>
                        <div>
                            <strong>El cliente pagó el depósito en efectivo (<span x-text="mesasSeleccionadas.length > 1 ? '$' + new Intl.NumberFormat('es-CO').format({{ $montoDeposito }} * mesasSeleccionadas.length) + ' (' + mesasSeleccionadas.length + ' mesas × $' + new Intl.NumberFormat('es-CO').format({{ $montoDeposito }}) + ')' : '${{ number_format($montoDeposito, 0, ',', '.') }} (por mesa)'"></span>) en este momento.</strong><br>
                            <span style="font-size:0.8rem; color:var(--text-dim);">Al marcar esta opción, la reserva quedará Confirmada de inmediato. Si no, se enviará el correo con enlace de pago.</span>
                        </div>
                    </label>
                    @endif

                    <div class="btn-actions">
                        <button type="button" class="btn btn-prev" @click="step = 2">← Atrás</button>
                        <button type="submit" class="btn btn-submit">Crear Reserva Oficial</button>
                    </div>
                </div>

            </form>
        </div>

        <!-- Right: Summary Panel -->
        <div class="glass-panel summary-panel">
            <h3 class="summary-title">Resumen</h3>
            
            <div class="summary-item">
                <div class="summary-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                </div>
                <div class="summary-detail">
                    <span class="summary-label">Fecha</span>
                    <span class="summary-value" x-text="fecha || '-'"></span>
                </div>
            </div>
            <div class="summary-item">
                <div class="summary-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                </div>
                <div class="summary-detail">
                    <span class="summary-label">Personas</span>
                    <span class="summary-value" x-text="personas ? personas + ' personas' : '-'"></span>
                </div>
            </div>
            <div class="summary-item">
                <div class="summary-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </div>
                <div class="summary-detail">
                    <span class="summary-label">Hora</span>
                    <span class="summary-value" x-text="horaSeleccionada ? horaSeleccionada.substring(0,5) : '-'"></span>
                </div>
            </div>
            <div class="summary-item">
                <div class="summary-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/></svg>
                </div>
                <div class="summary-detail">
                    <span class="summary-label">Mesas</span>
                    <span class="summary-value" x-text="mesasSeleccionadas.length === 0 ? 'Automática' : getMesasNombres()"></span>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('reservaWizardMesero', () => ({
        step: 1,
        fecha: '{{ old('fecha_reserva', date('Y-m-d')) }}',
        personas: {{ old('numero_personas', 2) }},
        horaSeleccionada: '{{ old('hora_inicio', '') }}',
        mesasSeleccionadas: @json(old('mesas_ids', [])),
        slots: [],
        isLoadingSlots: false,
        errorHora: '',
        mesasObj: @json($mesas),
        mesasOcupadas: [],

        init() {
            this.fetchSlots();
            if (this.horaSeleccionada) {
                this.fetchMesasOcupadas();
            }
            this.$watch('step', value => {
                if (value === 2) this.fetchMesasOcupadas();
            });
            @if($errors->any())
                this.step = 3;
            @endif
        },

        validarAnticipacion() {
            if (!this.fecha || !this.horaSeleccionada) { this.errorHora = ''; return; }
            const reserva = new Date(this.fecha + 'T' + this.horaSeleccionada);
            const diffMin = (reserva - new Date()) / 60000;
            if (diffMin < 30) {
                const min = Math.ceil(30 - diffMin);
                this.errorHora = `Esta hora no cumple el mínimo de 30 min de anticipación. Elige al menos ${min} min más tarde.`;
            } else {
                this.errorHora = '';
            }
        },

        toggleMesa(id) {
            if (this.mesasSeleccionadas.includes(id)) {
                this.mesasSeleccionadas = this.mesasSeleccionadas.filter(m => m !== id);
            } else {
                this.mesasSeleccionadas.push(id);
            }
        },

        fetchSlots() {
            if (!this.fecha || !this.personas) return;
            
            this.isLoadingSlots = true;
            this.slots = [];
            
            // Usamos la ruta pública del cliente para obtener las franjas.
            // Para eso, el controlador envía el $sucursal en la vista.
            let url = `{{ route('cliente.reservas.slots', $sucursal->slug) }}?fecha=${this.fecha}&numero_personas=${this.personas}`;
            
            if (this.mesasSeleccionadas.length > 0) {
                this.mesasSeleccionadas.forEach(id => {
                    url += `&mesas_ids[]=${id}`;
                });
            }

            fetch(url, {
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                this.slots = data.slots || [];
            })
            .catch(err => console.error(err))
            .finally(() => {
                this.isLoadingSlots = false;
            });
        },

        fetchMesasOcupadas() {
            if (!this.fecha || !this.horaSeleccionada) {
                this.mesasOcupadas = [];
                return;
            }
            let url = `{{ route('cliente.reservas.mesas-ocupadas', $sucursal->slug) }}?fecha=${this.fecha}&hora_inicio=${this.horaSeleccionada}`;
            fetch(url, {
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                this.mesasOcupadas = data.mesas_ocupadas_ids || [];
                // Si alguna de las mesas seleccionadas está ahora ocupada, deseleccionarla
                this.mesasSeleccionadas = this.mesasSeleccionadas.filter(id => !this.mesasOcupadas.includes(id));
            })
            .catch(err => console.error(err));
        },

        getMesasNombres() {
            if (this.mesasSeleccionadas.length === 0) return 'Automática';
            return this.mesasSeleccionadas.map(id => {
                let m = this.mesasObj.find(x => x.id === id);
                return m ? m.numero : id;
            }).join(', ');
        }
    }));
});
</script>
@endsection
