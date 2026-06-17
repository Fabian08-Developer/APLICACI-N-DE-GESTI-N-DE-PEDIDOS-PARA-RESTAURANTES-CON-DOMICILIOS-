@extends('mesero.layout')

@section('titulo', 'Nueva Reserva')

@section('contenido')
<div class="page-reservas">
    <div class="reservas-header">
        <div>
            <a href="{{ route('mesero.reservas.index') }}" class="btn-back">← Volver a Reservas</a>
            <h1 class="page-title" style="margin-top: 0.5rem;">Nueva Reserva</h1>
            <p class="page-subtitle">Crear una reserva manualmente</p>
        </div>
    </div>

    <div class="form-container">
        @if($errors->any())
        <div class="alert alert-err">
            <ul style="margin-left: 1.5rem;">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('mesero.reservas.store') }}" class="reserva-form">
            @csrf

            <div class="form-section">
                <h3>1. Datos del Cliente</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Nombre del cliente *</label>
                        <input type="text" name="nombre_cliente" class="form-control" value="{{ old('nombre_cliente') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Teléfono *</label>
                        <input type="tel" name="telefono_cliente" class="form-control" value="{{ old('telefono_cliente') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Correo electrónico *</label>
                        <input type="email" name="correo_cliente" class="form-control" value="{{ old('correo_cliente') }}" required>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3>2. Detalles de la Reserva</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Fecha *</label>
                        <input type="date" name="fecha_reserva" class="form-control" value="{{ old('fecha_reserva', today()->format('Y-m-d')) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Hora de llegada *</label>
                        <input type="time" name="hora_inicio" class="form-control" value="{{ old('hora_inicio') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Número de personas *</label>
                        <input type="number" name="numero_personas" class="form-control" value="{{ old('numero_personas', 2) }}" min="1" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mesas (Puedes seleccionar varias)</label>
                        <select name="mesas_ids[]" class="form-control" multiple style="height: auto; min-height: 100px;">
                            @foreach($mesas as $mesa)
                                <option value="{{ $mesa->id }}" {{ collect(old('mesas_ids'))->contains($mesa->id) ? 'selected' : '' }}>
                                    Mesa #{{ $mesa->numero }} (Capacidad: {{ $mesa->capacidad }} pers.)
                                </option>
                            @endforeach
                        </select>
                        <small style="color: var(--text-muted); margin-top: 0.5rem; display: block;">Mantén presionada la tecla Ctrl (o Cmd) para seleccionar múltiples mesas.</small>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3>3. Notas Adicionales</h3>
                <div class="form-grid" style="grid-template-columns: 1fr;">
                    <div class="form-group">
                        <label class="form-label">Notas del cliente (Peticiones especiales)</label>
                        <textarea name="notas_cliente" class="form-control" rows="2" placeholder="Ej: Cumpleaños, alergias, silla de bebé...">{{ old('notas_cliente') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Notas internas (Solo visible para staff)</label>
                        <textarea name="notas_internas" class="form-control" rows="2" placeholder="Ej: Cliente VIP, requiere atención especial...">{{ old('notas_internas') }}</textarea>
                    </div>
                </div>
            </div>

            @if($requiereDeposito && $montoDeposito > 0)
            <div class="form-section deposito-section">
                <h3>Depósito de Garantía</h3>
                <p class="deposito-info">El restaurante requiere un depósito de <strong>${{ number_format($montoDeposito, 0, ',', '.') }}</strong> para confirmar esta reserva.</p>
                <label class="checkbox-label">
                    <input type="checkbox" name="deposito_pagado_efectivo" value="1" {{ old('deposito_pagado_efectivo') ? 'checked' : '' }}>
                    El cliente pagó el depósito en efectivo en este momento. (La reserva quedará confirmada de inmediato).
                </label>
                <p style="font-size: 0.8rem; color: #a0907a; margin-top: 0.5rem; margin-left: 1.85rem;">
                    Si no marcas esta opción, la reserva quedará "Pendiente de Pago" y el cliente recibirá un correo con el enlace para pagar.
                </p>
            </div>
            @endif

            <div class="form-actions">
                <button type="submit" class="btn-submit">Crear Reserva</button>
            </div>
        </form>
    </div>
</div>

<style>
.page-reservas { width: 100%; }
.reservas-header { margin-bottom: 2rem; }
.btn-back { color: var(--text-muted); text-decoration: none; font-size: 0.9rem; display: inline-block; margin-bottom: 0.5rem; transition: color 0.2s; font-weight: 500; }
.btn-back:hover { color: var(--text-main); }
.page-title { font-size: 1.8rem; font-weight: 700; color: var(--text-main); font-family: 'Playfair Display', serif; }
.page-subtitle { color: var(--text-dim); font-size: 0.9rem; margin-top: 0.2rem; }

.form-container { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 2.5rem; box-shadow: var(--shadow-sm); }
.form-section { margin-bottom: 2.5rem; padding-bottom: 2rem; border-bottom: 1px solid var(--border-light); }
.form-section:last-of-type { border-bottom: none; margin-bottom: 1rem; padding-bottom: 0; }
.form-section h3 { font-family: 'Playfair Display', serif; font-size: 1.2rem; color: var(--primary); margin-bottom: 1.25rem; font-weight: 600; }

.form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; }
.form-group { display: flex; flex-direction: column; gap: 0.5rem; }
.form-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); font-weight: 600; }
.form-control { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 0.8rem 1rem; color: var(--text-main); font-family: inherit; font-size: 0.95rem; width: 100%; transition: all 0.2s; }
.form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 2px var(--accent-bg); }
select.form-control option { background: var(--surface); color: var(--text-main); }

.deposito-section { background: var(--surface-2); border: 1px solid var(--border); border-radius: var(--radius); padding: 1.5rem; margin-bottom: 2.5rem; border-bottom: 1px solid var(--border) !important; }
.deposito-info { font-size: 0.95rem; color: var(--text-main); margin-bottom: 1rem; }
.deposito-info strong { color: var(--primary); font-size: 1.1rem; }
.checkbox-label { display: flex; align-items: flex-start; gap: 0.75rem; cursor: pointer; font-size: 0.95rem; color: var(--text-main); line-height: 1.4; font-weight: 500; }
.checkbox-label input[type="checkbox"] { margin-top: 0.2rem; width: 1.1rem; height: 1.1rem; accent-color: var(--primary); cursor: pointer; }

.form-actions { display: flex; justify-content: flex-end; margin-top: 1.5rem; }
.btn-submit { background: var(--primary); color: #fff; border: none; border-radius: var(--radius-sm); padding: 1rem 2.5rem; font-family: inherit; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.2s; box-shadow: var(--shadow-sm); }
.btn-submit:hover { transform: translateY(-2px); background: #ce6c51; box-shadow: var(--shadow-md); }

.alert { border-radius: var(--radius-sm); padding: 1rem 1.25rem; margin-bottom: 1.5rem; font-size: 0.9rem; border: 1px solid; }
.alert-err { background: rgba(248, 113, 113, 0.1); border-color: rgba(248, 113, 113, 0.3); color: #F87171; }
</style>
@endsection
