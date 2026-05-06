{{--
    Token Bridge — página puente de autenticación por pestaña

    Cuando el middleware no encuentra un token en el request (ej: al recargar la página),
    en vez de redirigir directo al login, muestra esta mini-página que:
    1. Revisa sessionStorage (que es por pestaña) buscando el token
    2. Si lo encuentra → redirige a la misma URL con ?_st=TOKEN
    3. Si no lo encuentra → redirige al login

    Esto resuelve el problema de "recargar pierde la sesión" porque sessionStorage
    persiste mientras la pestaña esté abierta, pero no se puede enviar al servidor
    automáticamente sin JavaScript.
--}}
<!DOCTYPE html>
<html><head><meta charset="UTF-8"></head>
<body>
<script>
(function() {
    var token = sessionStorage.getItem('staff_token');
    if (token) {
        var url = new URL(window.location);
        url.searchParams.set('_st', token);
        window.location.replace(url.toString());
    } else {
        window.location.replace('/login');
    }
})();
</script>
</body>
</html>
