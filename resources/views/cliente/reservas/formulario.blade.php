<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservar Mesa — {{ $sucursal->nombre }}</title>
    <meta name="description" content="Reserva tu mesa en {{ $sucursal->nombre }}. Elige fecha, hora y número de personas.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --gold:     #C48B57;
            --gold-dark:#A67040;
            --dark:     #1A1208;
            --surface:  #2C2218;
            --surface2: #3A2D1E;
            --border:   rgba(196,139,87,0.2);
            --text:     #F5EFE6;
            --text-dim: #A0907A;
            --radius:   16px;
            --shadow:   0 20px 60px rgba(0,0,0,0.5);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--dark);
            color: var(--text);
            min-height: 100vh;
            background-image:
                radial-gradient(ellipse at 20% 0%, rgba(196,139,87,0.08) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 100%, rgba(196,139,87,0.05) 0%, transparent 50%);
        }

        /* ── Header ── */
        .header {
            text-align: center;
            padding: 3rem 1.5rem 2rem;
            border-bottom: 1px solid var(--border);
        }
        .header-restaurant {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: var(--gold);
            font-weight: 600;
            margin-bottom: 0.75rem;
        }
        .header h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2rem, 5vw, 3.2rem);
            font-weight: 600;
            color: var(--text);
            line-height: 1.2;
            margin-bottom: 0.5rem;
        }
        .header p {
            color: var(--text-dim);
            font-size: 1rem;
            line-height: 1.6;
        }

        /* ── Main layout ── */
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 2.5rem 1.5rem 4rem;
        }

        /* ── Deposit banner ── */
        .deposit-banner {
            background: linear-gradient(135deg, rgba(196,139,87,0.15), rgba(196,139,87,0.05));
            border: 1px solid var(--border);
            border-left: 4px solid var(--gold);
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .deposit-banner .icon { font-size: 1.5rem; flex-shrink: 0; }
        .deposit-banner p { font-size: 0.9rem; color: var(--text-dim); line-height: 1.5; }
        .deposit-banner strong { color: var(--gold); font-size: 1.05rem; }

        /* ── Form ── */
        .form-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 2.5rem;
            box-shadow: var(--shadow);
        }

        .form-section-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.15rem;
            color: var(--text);
            margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        .form-section-title .icon { color: var(--gold); }

        .form-grid { display: grid; gap: 1.25rem; }
        .form-grid-2 { grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }
        .form-grid-3 { grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); }

        .form-group { display: flex; flex-direction: column; gap: 0.5rem; }
        .form-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-dim);
            font-weight: 600;
        }
        .form-control {
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 0.85rem 1rem;
            color: var(--text);
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            transition: border-color 0.2s, box-shadow 0.2s;
            width: 100%;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(196,139,87,0.12);
        }
        .form-control::placeholder { color: rgba(255,255,255,0.2); }
        .form-control option { background: #2C2218; color: var(--text); }

        /* ── Slots de tiempo ── */
        .slots-container {
            display: none;
            margin-top: 0.5rem;
        }
        .slots-container.visible { display: block; }
        .slots-label {
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-dim);
            font-weight: 600;
            margin-bottom: 0.75rem;
        }
        .slots-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 0.5rem;
        }
        .slot-btn {
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 0.6rem 0.4rem;
            text-align: center;
            cursor: pointer;
            font-size: 0.85rem;
            color: var(--text-dim);
            transition: all 0.2s;
            user-select: none;
        }
        .slot-btn.disponible {
            color: var(--text);
            border-color: rgba(196,139,87,0.3);
            cursor: pointer;
        }
        .slot-btn.disponible:hover {
            background: rgba(196,139,87,0.1);
            border-color: var(--gold);
            color: var(--gold);
        }
        .slot-btn.selected {
            background: rgba(196,139,87,0.2);
            border-color: var(--gold);
            color: var(--gold);
            font-weight: 600;
        }
        .slot-btn.ocupado {
            opacity: 0.35;
            cursor: not-allowed;
            text-decoration: line-through;
        }
        .slots-loading {
            color: var(--text-dim);
            font-size: 0.85rem;
            padding: 1rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .spinner {
            width: 16px; height: 16px;
            border: 2px solid var(--border);
            border-top-color: var(--gold);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        .divider { border: none; border-top: 1px solid var(--border); margin: 2rem 0; }

        /* ── Submit btn ── */
        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 1.1rem 2rem;
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            letter-spacing: 0.03em;
            box-shadow: 0 8px 24px rgba(196,139,87,0.3);
            margin-top: 1.5rem;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(196,139,87,0.4);
        }
        .btn-submit:active { transform: translateY(0); }

        /* ── Mesa selection ── */
        .mesa-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(90px, 1fr)); gap: 0.5rem; }
        .mesa-btn {
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 0.75rem 0.4rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .mesa-btn:hover { border-color: rgba(196,139,87,0.5); background: rgba(196,139,87,0.05); }
        .mesa-btn.selected { border-color: var(--gold); background: rgba(196,139,87,0.12); }
        .mesa-btn .num { font-family: 'Playfair Display', serif; font-size: 1.2rem; color: var(--gold); }
        .mesa-btn .cap { font-size: 0.7rem; color: var(--text-dim); margin-top: 2px; }
        .mesa-auto {
            font-size: 0.85rem;
            color: var(--text-dim);
            padding: 0.75rem 1rem;
            background: rgba(255,255,255,0.02);
            border-radius: 8px;
            border: 1px dashed var(--border);
            cursor: pointer;
            text-align: center;
            transition: all 0.2s;
        }
        .mesa-auto.selected { border-color: var(--gold); color: var(--gold); background: rgba(196,139,87,0.08); }

        /* ── Errors ── */
        .form-error { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); border-radius: 10px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; }
        .form-error ul { list-style: none; }
        .form-error li { color: #f87171; font-size: 0.9rem; padding: 0.2rem 0; }
        .input-error { border-color: rgba(239,68,68,0.5) !important; }

        /* ── Responsive ── */
        @media (max-width: 600px) {
            .form-card { padding: 1.5rem; }
            .container { padding: 1.5rem 1rem 3rem; }
        }
    </style>
</head>
<body>

<div class="header">
    <div class="header-restaurant">{{ $sucursal->nombre }}</div>
    <h1>Reserva tu Mesa</h1>
    <p>Asegura tu lugar y disfruta de una experiencia sin esperas</p>
</div>

<div class="container">

    @if($errors->any())
    <div class="form-error">
        <ul>@foreach($errors->all() as $e)<li>⚠ {{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    @if($requiereDeposito && $montoDeposito > 0)
    <div class="deposit-banner">
        <div class="icon">💳</div>
        <div>
            <p>Se requiere un depósito de garantía de <strong>${{ number_format($montoDeposito, 0, ',', '.') }}</strong> para confirmar tu reserva. Se puede pagar en efectivo, Nequi o transferencia.</p>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('cliente.reservas.crear', $sucursal->slug) }}" id="formReserva">
        @csrf

        {{-- SECCIÓN 1: Datos de la reserva --}}
        <div class="form-card" style="margin-bottom: 1.5rem;">
            <div class="form-section-title">
                <span class="icon">📅</span> Fecha y hora
            </div>

            <div class="form-grid form-grid-3" style="margin-bottom: 1.25rem;">
                <div class="form-group">
                    <label class="form-label">Fecha *</label>
                    <input type="date" name="fecha_reserva" id="fechaReserva" class="form-control @error('fecha_reserva') input-error @enderror"
                        value="{{ old('fecha_reserva', now()->addMinutes($anticipacionMin)->format('Y-m-d')) }}"
                        min="{{ now()->addMinutes($anticipacionMin)->format('Y-m-d') }}"
                        max="{{ now()->addDays($horizonteDias)->format('Y-m-d') }}"
                        required>
                </div>
                <div class="form-group">
                    <label class="form-label">Personas *</label>
                    <input type="number" name="numero_personas" id="nPersonas" class="form-control @error('numero_personas') input-error @enderror"
                        value="{{ old('numero_personas', 2) }}" min="1" max="20" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Duración estimada</label>
                    <input type="text" class="form-control" value="{{ $duracionTurno }} minutos" readonly style="opacity: 0.5; cursor: default;">
                </div>
            </div>

            {{-- Slots de tiempo --}}
            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label">Hora de llegada *</label>
                <input type="hidden" name="hora_inicio" id="horaSeleccionada" value="{{ old('hora_inicio') }}" required>
                <div id="slotsContainer" class="slots-container">
                    <div class="slots-loading" id="slotsLoading" style="display:none;">
                        <div class="spinner"></div> Buscando disponibilidad...
                    </div>
                    <div class="slots-grid" id="slotsGrid"></div>
                </div>
                <p style="font-size:0.82rem;color:var(--text-dim);margin-top:0.4rem;" id="slotsHint">
                    Selecciona fecha y número de personas para ver horarios disponibles.
                </p>
            </div>
        </div>

        {{-- SECCIÓN 2: Selección de mesa --}}
        <div class="form-card" style="margin-bottom: 1.5rem;">
            <div class="form-section-title">
                <span class="icon">🪑</span> Mesa
            </div>
            <p style="font-size:0.85rem;color:var(--text-dim);margin-bottom:1rem;">Puedes escoger una mesa específica o dejar que el sistema te asigne la mejor disponible.</p>

            <input type="hidden" name="mesa_id" id="mesaIdInput" value="{{ old('mesa_id', '') }}">

            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                {{-- Auto-asignación --}}
                <div class="mesa-auto selected" id="mesaAutoBtn" onclick="seleccionarMesa(null, this)">
                    ✨ Asignación automática (recomendado)
                </div>

                {{-- Mesas específicas --}}
                <div class="mesa-grid">
                    @foreach($mesas as $mesa)
                    <div class="mesa-btn" id="mesaBtn-{{ $mesa->id }}" onclick="seleccionarMesa('{{ $mesa->id }}', this)">
                        <div class="num">{{ $mesa->numero }}</div>
                        <div class="cap">{{ $mesa->capacidad }} pers.</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- SECCIÓN 3: Datos personales --}}
        <div class="form-card">
            <div class="form-section-title">
                <span class="icon">👤</span> Tus datos
            </div>

            <div class="form-grid form-grid-2">
                <div class="form-group">
                    <label class="form-label">Nombre completo *</label>
                    <input type="text" name="nombre_cliente" class="form-control @error('nombre_cliente') input-error @enderror"
                        value="{{ old('nombre_cliente') }}" placeholder="Juan Pérez" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Teléfono *</label>
                    <input type="tel" name="telefono_cliente" class="form-control @error('telefono_cliente') input-error @enderror"
                        value="{{ old('telefono_cliente') }}" placeholder="300 000 0000" required>
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="form-label">Correo electrónico *</label>
                    <input type="email" name="correo_cliente" class="form-control @error('correo_cliente') input-error @enderror"
                        value="{{ old('correo_cliente') }}" placeholder="tu@correo.com" required>
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="form-label">Nota especial (opcional)</label>
                    <textarea name="notas_cliente" class="form-control" rows="3"
                        placeholder="Ej: Cumpleaños, silla para bebé, alergias...">{{ old('notas_cliente') }}</textarea>
                </div>
            </div>

            <button type="submit" class="btn-submit" id="btnReservar">
                @if($requiereDeposito && $montoDeposito > 0)
                    Reservar y pagar depósito →
                @else
                    Confirmar Reserva →
                @endif
            </button>
        </div>

    </form>
</div>

<script>
const SLOTS_URL  = "{{ route('cliente.reservas.slots', $sucursal->slug) }}";
const CSRF_TOKEN = "{{ csrf_token() }}";

let slotActivo = null;

// ── Cargar slots al cambiar fecha o personas ────────────────────
function cargarSlots() {
    const fecha    = document.getElementById('fechaReserva').value;
    const personas = document.getElementById('nPersonas').value;
    const mesaId   = document.getElementById('mesaIdInput').value;

    if (!fecha || !personas) return;

    document.getElementById('slotsContainer').classList.add('visible');
    document.getElementById('slotsGrid').innerHTML = '';
    document.getElementById('slotsLoading').style.display = 'flex';
    document.getElementById('slotsHint').style.display = 'none';
    document.getElementById('horaSeleccionada').value = '';
    slotActivo = null;

    const params = new URLSearchParams({ fecha, numero_personas: personas });
    if (mesaId) params.append('mesa_id', mesaId);

    fetch(`${SLOTS_URL}?${params}`, {
        headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('slotsLoading').style.display = 'none';
        renderSlots(data.slots || []);
    })
    .catch(() => {
        document.getElementById('slotsLoading').style.display = 'none';
        document.getElementById('slotsGrid').innerHTML = '<p style="color:#f87171;font-size:.85rem">Error al cargar horarios. Intenta de nuevo.</p>';
    });
}

function renderSlots(slots) {
    const grid = document.getElementById('slotsGrid');
    if (!slots.length) {
        grid.innerHTML = '<p style="color:var(--text-dim);font-size:.85rem">No hay horarios disponibles para esta fecha.</p>';
        return;
    }

    grid.innerHTML = slots.map(s => `
        <div class="slot-btn ${s.disponible ? 'disponible' : 'ocupado'}"
             onclick="${s.disponible ? `seleccionarSlot('${s.hora_inicio}', this)` : ''}"
             title="${s.disponible ? `${s.hora_inicio} - ${s.hora_fin}` : 'No disponible'}">
            <div style="font-weight:600">${s.hora_inicio}</div>
            <div style="font-size:.7rem;opacity:.7">${s.hora_fin}</div>
        </div>
    `).join('');
}

function seleccionarSlot(hora, el) {
    document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('horaSeleccionada').value = hora;
    slotActivo = hora;
}

// ── Selección de mesa ────────────────────────────────────────────
function seleccionarMesa(mesaId, el) {
    document.querySelectorAll('.mesa-btn, .mesa-auto').forEach(b => b.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('mesaIdInput').value = mesaId || '';
    cargarSlots(); // Recargar slots para la mesa seleccionada
}

// ── Eventos ──────────────────────────────────────────────────────
document.getElementById('fechaReserva').addEventListener('change', cargarSlots);
document.getElementById('nPersonas').addEventListener('change', cargarSlots);

// Preselección si hay valor previo (after error)
@if(old('mesa_id'))
    document.getElementById('mesaIdInput').value = "{{ old('mesa_id') }}";
    const btn = document.getElementById('mesaBtn-{{ old('mesa_id') }}');
    if (btn) {
        document.querySelectorAll('.mesa-btn, .mesa-auto').forEach(b => b.classList.remove('selected'));
        btn.classList.add('selected');
        document.getElementById('mesaAutoBtn').classList.remove('selected');
    }
@endif

// Cargar slots iniciales si ya hay fecha
if (document.getElementById('fechaReserva').value) cargarSlots();

// Validar que se haya seleccionado hora antes de enviar
document.getElementById('formReserva').addEventListener('submit', function(e) {
    if (!document.getElementById('horaSeleccionada').value) {
        e.preventDefault();
        alert('Por favor selecciona una hora de llegada.');
    }
});
</script>

</body>
</html>
