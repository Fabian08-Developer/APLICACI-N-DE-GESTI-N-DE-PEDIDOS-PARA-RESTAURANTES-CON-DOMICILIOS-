<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cafe Bambu — Acceso</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/login.css'])
</head>
<body>

<div class="container" id="contenedor">

    {{-- PANEL IMAGEN --}}
    <div class="panel-imagen" id="panelImagen">
        <span class="flotante">🍷</span>
        <span class="flotante">🫕</span>
        <span class="flotante">🥩</span>

        <div class="panel-contenido">
            <div class="top-bar">
                <span class="top-bar-label">Cafe Bambu</span>
                <div class="nav-tabs">
                    <button class="nav-tab activo" id="btn-login"    onclick="cambiarModo('login')">Ingresar</button>
                    <button class="nav-tab"        id="btn-register" onclick="cambiarModo('register')">Registro</button>
                </div>
            </div>

            <div class="panel-bottom">
                <p class="panel-eyebrow">Sistema de gestión</p>
                <h1 class="panel-titulo" id="panel-titulo">
                    El arte de<br><em>servir bien</em><br>comienza aquí
                </h1>
                <div class="panel-profile">
                    <div class="panel-avatar">🍴</div>
                    <div>
                        <div class="panel-nombre">Cafe Bambu</div>
                        <div class="panel-rol">Gestión de restaurante</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FORM LOGIN (derecha) --}}
    <div class="form-login" id="formLogin">
        <div class="form-inner">
            <div class="logo">
                <span class="logo-icono">🍴</span>
                <span class="logo-nombre">Cafe <span>Bambu</span></span>
            </div>
            <h2 class="form-titulo">Bienvenido</h2>
            <p class="form-sub">Ingresa tus credenciales para continuar</p>

            @if(session('error'))
                <div class="alerta-error">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="campo">
                    <input type="email" name="email" placeholder="Correo electrónico"
                        value="{{ old('email') }}" autocomplete="email" required>
                    @error('email') <p class="campo-error">{{ $message }}</p> @enderror
                </div>
                <div class="campo">
                    <div class="campo-rel">
                        <input type="password" id="pass-login" name="password"
                            placeholder="Contraseña" autocomplete="current-password" required>
                        <button type="button" class="toggle-pass" onclick="togglePass('pass-login')">👁</button>
                    </div>
                    @error('password') <p class="campo-error">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="btn-submit">Ingresar al sistema</button>
            </form>
            <p class="form-footer">Cafe Bambu · {{ date('Y') }}</p>
        </div>
    </div>

    {{-- FORM REGISTER (izquierda) --}}
    <div class="form-register" id="formRegister">
        <div class="form-inner">
            <div class="logo">
                <span class="logo-icono">🍴</span>
                <span class="logo-nombre">Cafe <span>Bambu</span></span>
            </div>
            <h2 class="form-titulo">Crear cuenta</h2>
            <p class="form-sub">Completa los datos para registrarte</p>

            @if($errors->any() && old('_form') === 'register')
                <div class="alerta-error">Revisa los campos e intenta de nuevo.</div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf
                <input type="hidden" name="_form" value="register">
                <div class="campo">
                    <input type="text" name="nombre" placeholder="Nombre completo"
                        value="{{ old('nombre') }}" required>
                    @error('nombre') <p class="campo-error">{{ $message }}</p> @enderror
                </div>
                <div class="campo">
                    <input type="email" name="email" placeholder="Correo electrónico"
                        value="{{ old('email') }}" autocomplete="email" required>
                    @error('email') <p class="campo-error">{{ $message }}</p> @enderror
                </div>
                <div class="grupo">
            <select id="rol_id" name="rol_id" required>
                <option value="">— Selecciona un rol —</option>
                {{-- $roles viene desde el controlador --}}
                @foreach($roles as $rol)
                    <option value="{{ $rol->id }}" {{ old('rol_id') == $rol->id ? 'selected' : '' }}>
                        {{ $rol->nombre }}
                    </option>
                @endforeach
            </select>
            @error('rol_id')
                <div class="error-campo">{{ $message }}</div>
            @enderror
        </div>
                <div class="campo">
                    <div class="campo-rel">
                        <input type="password" id="pass-reg" name="password"
                            placeholder="Contraseña" required>
                        <button type="button" class="toggle-pass" onclick="togglePass('pass-reg')">👁</button>
                    </div>
                    @error('password') <p class="campo-error">{{ $message }}</p> @enderror
                </div>
                <div class="campo">
                    <input type="password" name="password_confirmation"
                        placeholder="Confirmar contraseña" required>
                </div>
                <button type="submit" class="btn-submit">Crear cuenta</button>
            </form>
            <p class="form-footer">Cafe Bambu · {{ date('Y') }}</p>
        </div>
    </div>

</div>

<script>
    // ── Limpiar token de pestaña al llegar desde logout ─────────────────
    // El logout redirige a /login?_clear=1 — aquí limpiamos sessionStorage
    (function() {
        const url = new URL(window.location);
        if (url.searchParams.has('_clear')) {
            sessionStorage.removeItem('staff_token');
            url.searchParams.delete('_clear');
            window.history.replaceState(null, '', url.pathname + url.search + url.hash);
        }
    })();

    let modoActual = 'login';

    function cambiarModo(modo) {
        if (modo === modoActual) return;
        modoActual = modo;

        const contenedor = document.getElementById('contenedor');
        const btnLogin    = document.getElementById('btn-login');
        const btnRegister = document.getElementById('btn-register');
        const titulo      = document.getElementById('panel-titulo');

        if (modo === 'register') {
            contenedor.classList.add('modo-register');
            btnLogin.classList.remove('activo');
            btnRegister.classList.add('activo');
            titulo.style.opacity = '0';
            setTimeout(() => {
                titulo.innerHTML = 'Únete a<br><em>la experiencia</em><br>culinaria';
                titulo.style.opacity = '1';
            }, 300);
        } else {
            contenedor.classList.remove('modo-register');
            btnLogin.classList.add('activo');
            btnRegister.classList.remove('activo');
            titulo.style.opacity = '0';
            setTimeout(() => {
                titulo.innerHTML = 'El arte de<br><em>servir bien</em><br>comienza aquí';
                titulo.style.opacity = '1';
            }, 300);
        }
    }

    function togglePass(id) {
        const input = document.getElementById(id);
        input.type = input.type === 'password' ? 'text' : 'password';
    }

    @if(old('_form') === 'register' || ($errors->any() && old('_form') === 'register'))
        document.addEventListener('DOMContentLoaded', () => cambiarModo('register'));
    @endif
</script>

</body>
</html>