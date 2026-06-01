<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Domicilio — {{ $sucursal->nombre }}</title>
    <meta name="description" content="Regístrate para realizar tu pedido a domicilio en {{ $sucursal->nombre }}.">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/acceso.css'])
    <style>
        .form-grupo {
            width: 100%;
            margin-bottom: 1.25rem;
            text-align: left;
        }
        .form-label {
            display: block;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--latte);
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        .form-control {
            width: 100%;
            padding: 1rem 1.2rem;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(193, 127, 62, 0.25);
            border-radius: 12px;
            color: var(--crema);
            font-family: 'Outfit', sans-serif;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            outline: none;
        }
        .form-control:focus {
            border-color: var(--caramelo);
            background: rgba(255, 255, 255, 0.06);
            box-shadow: 0 0 12px rgba(193, 127, 62, 0.25);
        }
        .form-control::placeholder {
            color: rgba(242, 232, 217, 0.3);
        }
        .btn-submit {
            width: 100%;
            padding: 1.1rem 1.4rem;
            background: linear-gradient(135deg, rgba(193, 127, 62, 0.2), rgba(193, 127, 62, 0.05));
            border: 1px solid rgba(193, 127, 62, 0.4);
            border-radius: 18px;
            cursor: pointer;
            font-family: 'Outfit', sans-serif;
            font-weight: 500;
            color: var(--crema);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            transition: all 0.3s ease;
            margin-top: 1.5rem;
        }
        .btn-submit:hover {
            background: linear-gradient(135deg, rgba(193, 127, 62, 0.3), rgba(193, 127, 62, 0.1));
            border-color: rgba(193, 127, 62, 0.7);
            transform: translateY(-2px);
            box-shadow: 0 8px 32px rgba(193, 127, 62, 0.2);
        }
        .btn-submit:active {
            transform: translateY(0);
        }
        .form-card {
            width: 100%;
            background: rgba(31, 16, 8, 0.45);
            border: 1px solid rgba(193, 127, 62, 0.15);
            border-radius: 24px;
            padding: 2rem 1.75rem;
            backdrop-filter: blur(12px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body>

    {{-- Fondo con textura y glow --}}
    <div class="fondo-grano"></div>
    <div class="fondo-glow"></div>

    <div class="contenedor">
        
        {{-- Logo --}}
        <div class="logo">
            <div class="logo-icono">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M12 2v9"></path>
                    <path d="M8 5h8"></path>
                </svg>
            </div>
            <span class="logo-nombre">{{ $sucursal->nombre }}</span>
        </div>

        <div class="linea-deco">
            <span></span>
            <span class="punto"></span>
            <span></span>
        </div>

        {{-- Bienvenida --}}
        <div class="bienvenida">
            <p class="saludo">Pedido a Domicilio</p>
            <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 2.2rem; font-weight: 500; color: var(--crema); margin-bottom: 0.5rem;">Identificación</h2>
            <p style="font-size: 0.85rem; color: var(--tenue); max-width: 280px; margin: 0 auto 1.5rem;">
                Por favor, ingresa tus datos para continuar al menú digital y realizar tu pedido.
            </p>
        </div>

        {{-- Alertas de error --}}
        @if ($errors->any())
            <div class="alerta-error" style="opacity: 1; transform: none; margin-bottom: 1rem;">
                <ul style="list-style: none; padding: 0; margin: 0;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Formulario --}}
        <div class="form-card">
            <form action="{{ route('cliente.domicilio.registro', ['sucursal_slug' => $sucursal->slug]) }}" method="POST">
                @csrf
                
                <div class="form-grupo">
                    <label for="nombre_cliente" class="form-label">Nombre Completo</label>
                    <input type="text" id="nombre_cliente" name="nombre_cliente" class="form-control" placeholder="Ej. Juan Pérez" value="{{ old('nombre_cliente') }}" required>
                </div>

                <div class="form-grupo">
                    <label for="telefono_cliente" class="form-label">Teléfono / Celular</label>
                    <input type="tel" id="telefono_cliente" name="telefono_cliente" class="form-control" placeholder="Ej. 3001234567" value="{{ old('telefono_cliente') }}" required>
                </div>

                <div class="form-grupo">
                    <label for="direccion_cliente" class="form-label">Dirección de Entrega</label>
                    <input type="text" id="direccion_cliente" name="direccion_cliente" class="form-control" placeholder="Ej. Calle 10 # 5-20, Apto 402" value="{{ old('direccion_cliente') }}" required>
                </div>

                <button type="submit" class="btn-submit">
                    <span>Ver Menú Digital</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </button>
            </form>
        </div>

        {{-- Pie --}}
        <div class="pie">
            <p>SGPD © {{ date('Y') }}</p>
        </div>

    </div>

</body>
</html>
