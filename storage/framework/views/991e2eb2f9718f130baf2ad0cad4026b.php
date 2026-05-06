
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
<?php /**PATH C:\laragon\www\Cafeteria Vs.2 - PWA\cafeteria-web\resources\views/partials/token-bridge.blade.php ENDPATH**/ ?>