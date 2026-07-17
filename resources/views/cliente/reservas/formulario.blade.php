<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservar Mesa — {{ $sucursal->nombre }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="//unpkg.com/alpinejs" defer></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --gold:     #C48B57;
            --gold-light: #d8aa7e;
            --dark:     #0D0B09;
            --surface:  rgba(25, 20, 16, 0.7);
            --border:   rgba(196,139,87,0.2);
            --text:     #F5EFE6;
            --text-dim: #A0907A;
            --radius:   20px;
            --shadow:   0 15px 40px rgba(0,0,0,0.5);
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--dark);
            color: var(--text);
            min-height: 100vh;
            background-image: radial-gradient(circle at top right, rgba(196,139,87,0.1), transparent 50%),
                              radial-gradient(circle at bottom left, rgba(196,139,87,0.05), transparent 50%);
            background-attachment: fixed;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 3rem 1rem;
        }

        /* ── Typography ── */
        .serif { font-family: 'Playfair Display', serif; }

        /* ── Layout ── */
        .wizard-container {
            width: 100%;
            max-width: 1100px;
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 2rem;
            align-items: start;
        }

        @media (max-width: 850px) {
            .wizard-container { grid-template-columns: 1fr; }
        }

        /* ── Glass Panel ── */
        .glass-panel {
            background: var(--surface);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 2.5rem;
        }

        /* ── Header ── */
        .page-header { margin-bottom: 2rem; }
        .page-header h1 {
            font-size: 2.5rem;
            color: var(--gold);
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        .page-header p { color: var(--text-dim); }

        /* ── Steps Indicator ── */
        .steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3rem;
            position: relative;
        }
        .steps::before {
            content: '';
            position: absolute;
            top: 20px; left: 0; right: 0;
            height: 2px;
            background: var(--border);
            z-index: 0;
        }
        .step {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }
        .step-num {
            width: 40px; height: 40px;
            border-radius: 50%;
            background: var(--dark);
            border: 2px solid var(--border);
            display: flex; align-items: center; justify-content: center;
            font-weight: 600;
            color: var(--text-dim);
            transition: var(--transition);
        }
        .step.active .step-num {
            border-color: var(--gold);
            color: var(--gold);
            box-shadow: 0 0 15px rgba(196,139,87,0.3);
        }
        .step.done .step-num {
            background: var(--gold);
            border-color: var(--gold);
            color: var(--dark);
        }
        .step-label { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-dim); }
        .step.active .step-label { color: var(--gold); }

        /* ── Form Controls ── */
        .form-group { margin-bottom: 1.5rem; }
        .form-label { display: block; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-dim); margin-bottom: 0.5rem; }
        .form-control {
            width: 100%;
            background: rgba(0,0,0,0.3);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1rem;
            color: var(--text);
            font-family: 'Inter', sans-serif;
            transition: var(--transition);
        }
        .form-control:focus { outline: none; border-color: var(--gold); }
        .form-control[type="date"]::-webkit-calendar-picker-indicator { filter: invert(1); cursor: pointer; }

        /* ── Slots ── */
        .slots-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 0.75rem;
            margin-top: 1rem;
        }
        .slot-btn {
            background: rgba(0,0,0,0.3);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 0.75rem 0.5rem;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
        }
        .slot-btn:hover { border-color: var(--gold); }
        .slot-btn.active { background: rgba(196,139,87,0.2); border-color: var(--gold); box-shadow: inset 0 0 10px rgba(196,139,87,0.2); }
        .slot-btn.disabled { opacity: 0.3; cursor: not-allowed; text-decoration: line-through; border-color: transparent; }
        
        /* ── Mesas ── */
        .mesas-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .mesa-btn {
            background: rgba(0,0,0,0.3);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.5rem 1rem;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
        }
        .mesa-btn:hover { border-color: var(--gold); transform: translateY(-2px); }
        .mesa-btn.active { background: rgba(196,139,87,0.1); border-color: var(--gold); box-shadow: 0 5px 15px rgba(196,139,87,0.2); }
        .mesa-btn.disabled { opacity: 0.55; cursor: not-allowed; border-color: rgba(239,68,68,0.3); background: rgba(239,68,68,0.05); }
        .mesa-btn.disabled:hover { transform: none; border-color: rgba(239,68,68,0.3); }
        .mesa-btn .icon { font-size: 2rem; margin-bottom: 0.5rem; display: block; }
        .mesa-btn .name { font-family: 'Playfair Display', serif; font-size: 1.2rem; color: var(--gold); }

        /* ── Buttons ── */
        .btn-actions { display: flex; justify-content: space-between; margin-top: 2.5rem; }
        .btn {
            padding: 1rem 2rem;
            border-radius: 12px;
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            border: none;
        }
        .btn-prev { background: rgba(255,255,255,0.05); color: var(--text); }
        .btn-prev:hover { background: rgba(255,255,255,0.1); }
        .btn-next, .btn-submit { background: var(--gold); color: var(--dark); }
        .btn-next:hover, .btn-submit:hover { background: var(--gold-light); transform: translateY(-2px); box-shadow: 0 5px 15px rgba(196,139,87,0.3); }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; box-shadow: none; }

        /* ── Summary Panel ── */
        .summary-panel { position: sticky; top: 2rem; }
        .summary-title { font-family: 'Playfair Display', serif; font-size: 1.5rem; color: var(--gold); margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem; }
        .summary-item { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.2rem; }
        .summary-icon { width: 40px; height: 40px; border-radius: 10px; background: rgba(196,139,87,0.1); color: var(--gold); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .summary-detail span { display: block; }
        .summary-label { font-size: 0.75rem; text-transform: uppercase; color: var(--text-dim); }
        .summary-value { font-size: 1rem; font-weight: 500; color: var(--text); }
        
        .errors { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); border-radius: 12px; padding: 1rem; margin-bottom: 1.5rem; color: #f87171; font-size: 0.9rem; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body>

<div class="wizard-container" x-data="reservaWizard()">
    
    <!-- Left: Wizard Form -->
    <div class="glass-panel">
        <div class="page-header">
            <h1 class="serif">Reserva tu mesa</h1>
            <p>{{ $sucursal->nombre }}</p>
        </div>

        <div class="steps">
            <div class="step" :class="{'active': step === 1, 'done': step > 1}">
                <div class="step-num">1</div><div class="step-label">Horario</div>
            </div>
            <div class="step" :class="{'active': step === 2, 'done': step > 2}">
                <div class="step-num">2</div><div class="step-label">Mesa</div>
            </div>
            <div class="step" :class="{'active': step === 3, 'done': step > 3}">
                <div class="step-num">3</div><div class="step-label">Tus Datos</div>
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

        <form method="POST" action="{{ route('cliente.reservas.crear', $sucursal->slug) }}" id="formReserva">
            @csrf
            
            <!-- Hidden inputs to send to server -->
            <input type="hidden" name="fecha_reserva" :value="fecha">
            <input type="hidden" name="numero_personas" :value="personas">
            <input type="hidden" name="hora_inicio" :value="horaSeleccionada">
            <template x-if="mesaSeleccionada">
                <input type="hidden" name="mesas_ids[]" :value="mesaSeleccionada">
            </template>

            <!-- STEP 1: Fecha, Personas y Horario -->
            <div x-show="step === 1" x-transition x-cloak>
                <div class="form-group">
                    <label class="form-label">Fecha</label>
                    <input type="date" class="form-control" x-model="fecha" @change="fetchSlots()" min="{{ date('Y-m-d') }}">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Número de personas</label>
                    <input type="number" class="form-control" x-model="personas" @change="fetchSlots()" min="1" max="50">
                </div>

                <div class="form-group" style="margin-top:2rem;">
                    <label class="form-label">Hora de llegada <span x-show="isLoadingSlots" style="color:var(--gold);">(Cargando...)</span></label>
                    <div class="slots-grid">
                        <template x-for="slot in slots" :key="slot.hora_inicio">
                            <div class="slot-btn" 
                                 :class="{'active': horaSeleccionada === slot.hora_inicio, 'disabled': !slot.disponible}"
                                 @click="if(slot.disponible) { horaSeleccionada = slot.hora_inicio; validarAnticipacion(); fetchMesasOcupadas(); }">
                                <span x-text="slot.hora_inicio.substring(0,5)"></span>
                            </div>
                        </template>
                        <div x-show="slots.length === 0 && !isLoadingSlots" style="grid-column: 1/-1; color: var(--text-dim); font-size:0.9rem;">
                            No hay horarios disponibles para la fecha y cantidad de personas indicadas.
                        </div>
                    </div>

                    <!-- Alerta en tiempo real de anticipación mínima -->
                    <div x-show="errorHora" x-transition
                         style="margin-top:1rem; padding:0.85rem 1rem; border-radius:10px; background:rgba(239,68,68,0.12); border:1px solid rgba(239,68,68,0.35); display:flex; align-items:center; gap:0.6rem; color:#f87171; font-size:0.88rem;">
                        <span style="font-size:1.1rem;">⚠️</span>
                        <span x-text="errorHora"></span>
                    </div>
                </div>

                <div class="btn-actions" style="justify-content: flex-end;">
                    <button type="button" class="btn btn-next" @click="step = 2; fetchMesasOcupadas();" :disabled="!fecha || !personas || !horaSeleccionada || !!errorHora">Continuar →</button>
                </div>
            </div>

            <!-- STEP 2: Selección de Mesa -->
            <div x-show="step === 2" x-transition x-cloak>
                <p style="color:var(--text-dim); margin-bottom:1.5rem;">Elige una mesa específica o deja que nosotros te asignemos la mejor disponible entre las no ocupadas.</p>
                
                <div class="mesas-grid">
                    <div class="mesa-btn" :class="{'active': mesaSeleccionada === ''}" @click="mesaSeleccionada = ''">
                        <span class="icon">✨</span>
                        <span class="name">Automática</span>
                        <span style="display:block; font-size:0.8rem; color:var(--text-dim); margin-top:0.5rem;">Cualquier mesa disponible</span>
                    </div>

                    @foreach($mesas as $mesa)
                    <div class="mesa-btn" 
                         :class="{'active': mesaSeleccionada === '{{ $mesa->id }}', 'disabled': mesasOcupadas.includes('{{ $mesa->id }}')}" 
                         @click="if(!mesasOcupadas.includes('{{ $mesa->id }}')) { mesaSeleccionada = '{{ $mesa->id }}' }">
                        <span class="icon" x-text="mesasOcupadas.includes('{{ $mesa->id }}') ? '🚫' : '🪑'">🪑</span>
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

            <!-- STEP 3: Datos de Contacto -->
            <div x-show="step === 3" x-transition x-cloak>
                <div class="form-group">
                    <label class="form-label">Nombre Completo *</label>
                    <input type="text" name="nombre_cliente" class="form-control" value="{{ old('nombre_cliente') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Teléfono *</label>
                    <input type="tel" name="telefono_cliente" class="form-control" value="{{ old('telefono_cliente') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Correo Electrónico *</label>
                    <input type="email" name="correo_cliente" class="form-control" value="{{ old('correo_cliente') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Notas o Peticiones (Opcional)</label>
                    <textarea name="notas_cliente" class="form-control" rows="3">{{ old('notas_cliente') }}</textarea>
                </div>

                <div class="btn-actions">
                    <button type="button" class="btn btn-prev" @click="step = 2">← Atrás</button>
                    <button type="submit" class="btn btn-submit">Confirmar Reserva</button>
                </div>
            </div>

        </form>
    </div>

    <!-- Right: Summary Panel -->
    <div class="glass-panel summary-panel">
        <h3 class="summary-title">Tu Reserva</h3>
        
        <div class="summary-item">
            <div class="summary-icon">📅</div>
            <div class="summary-detail">
                <span class="summary-label">Fecha</span>
                <span class="summary-value" x-text="fecha || '-'"></span>
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-icon">👥</div>
            <div class="summary-detail">
                <span class="summary-label">Personas</span>
                <span class="summary-value" x-text="personas ? personas + ' personas' : '-'"></span>
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-icon">🕒</div>
            <div class="summary-detail">
                <span class="summary-label">Hora</span>
                <span class="summary-value" x-text="horaSeleccionada ? horaSeleccionada.substring(0,5) : '-'"></span>
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-icon">🪑</div>
            <div class="summary-detail">
                <span class="summary-label">Mesa</span>
                <span class="summary-value" x-text="mesaSeleccionada === '' ? 'Automática' : getMesaNombre(mesaSeleccionada)"></span>
            </div>
        </div>

        @if($requiereDeposito && $montoDeposito > 0)
        <div style="margin-top:2rem; padding-top:1.5rem; border-top:1px solid var(--border);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                <span class="summary-label">Depósito de Garantía</span>
                <span class="summary-value" style="color:var(--gold); font-size:1.2rem;">${{ number_format($montoDeposito, 0, ',', '.') }} <span style="font-size:0.8rem; color:var(--text-dim); font-weight:normal;">(por mesa)</span></span>
            </div>
            <p style="font-size:0.8rem; color:var(--text-dim);">Requerido para confirmar (${{ number_format($montoDeposito, 0, ',', '.') }} COP por cada mesa asignada). Se abonará o pagará en el siguiente paso.</p>
        </div>
        @endif
    </div>

</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('reservaWizard', () => ({
        step: 1,
        fecha: '{{ old('fecha_reserva', date('Y-m-d')) }}',
        personas: {{ old('numero_personas', 2) }},
        horaSeleccionada: '{{ old('hora_inicio', '') }}',
        mesaSeleccionada: '{{ old('mesas_ids.0', '') }}',
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
            const ahora    = new Date();
            const [h, m]   = this.horaSeleccionada.split(':');
            const reserva  = new Date(this.fecha + 'T' + this.horaSeleccionada);
            const diffMin  = (reserva - ahora) / 60000;
            if (diffMin < 30) {
                const minFaltantes = Math.ceil(30 - diffMin);
                this.errorHora = `Esta hora no cumple con el mínimo de 30 minutos de anticipación. Elige una hora al menos ${minFaltantes} min más tarde.`;
            } else {
                this.errorHora = '';
            }
        },

        fetchSlots() {
            if (!this.fecha || !this.personas) return;
            
            this.isLoadingSlots = true;
            this.slots = [];
            
            let url = `{{ route('cliente.reservas.slots', $sucursal->slug) }}?fecha=${this.fecha}&numero_personas=${this.personas}`;
            if (this.mesaSeleccionada) url += `&mesas_ids[]=${this.mesaSeleccionada}`;

            fetch(url, {
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                this.slots = data.slots || [];
                let horaAunDisponible = this.slots.find(s => s.hora_inicio === this.horaSeleccionada && s.disponible);
                if (!horaAunDisponible) { this.horaSeleccionada = ''; this.errorHora = ''; }
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
                if (this.mesasOcupadas.includes(this.mesaSeleccionada)) {
                    this.mesaSeleccionada = '';
                }
            })
            .catch(err => console.error(err));
        },

        getMesaNombre(id) {
            let m = this.mesasObj.find(m => m.id === id);
            return m ? 'Mesa ' + m.numero : 'Automática';
        }
    }));
});
</script>

</body>
</html>
