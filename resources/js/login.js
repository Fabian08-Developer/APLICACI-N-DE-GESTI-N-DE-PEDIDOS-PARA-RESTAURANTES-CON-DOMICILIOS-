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