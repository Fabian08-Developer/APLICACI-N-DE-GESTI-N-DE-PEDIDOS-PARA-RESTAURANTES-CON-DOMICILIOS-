<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifica tu correo | SGPD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Outfit:wght@300;400;500;600;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #0C0A09; }
        .glass { background: rgba(28, 25, 23, 0.6); backdrop-filter: blur(20px); border: 1px solid rgba(68, 64, 60, 0.3); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6 text-[#F5F5F4] relative overflow-hidden">
    {{-- Decoración de Fondo estilo Home --}}
    <div class="absolute top-0 left-0 w-full h-full opacity-20 pointer-events-none">
        <div class="absolute top-[-10%] right-[-10%] w-[50%] h-[50%] bg-[#A85507]/20 blur-[120px] rounded-full"></div>
        <div class="absolute bottom-[-10%] left-[-10%] w-[50%] h-[50%] bg-[#A85507]/10 blur-[120px] rounded-full"></div>
    </div>

    <div class="max-w-md w-full glass rounded-[40px] p-10 md:p-14 shadow-2xl relative z-10">
        <div class="text-center">
            <div class="w-20 h-20 bg-gradient-to-tr from-[#A85507] to-[#78350F] rounded-[24px] flex items-center justify-center mx-auto mb-10 shadow-2xl shadow-[#A85507]/30">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>

            <h1 class="font-['Cormorant_Garamond'] text-4xl font-light text-[#F5F5F4] italic leading-tight mb-4">Verifica tu correo</h1>
            <p class="text-stone-400 text-sm leading-relaxed mb-10">
                Gracias por unirte a SGPD. Hemos enviado un enlace de activación a tu bandeja de entrada. Por favor, confírmalo para empezar a operar.
            </p>

            @if (session('warning'))
                <div class="mb-8 p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-500 text-xs font-bold uppercase tracking-tight">
                    {{ session('warning') }}
                </div>
            @endif

            <div class="space-y-6">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="w-full py-5 rounded-2xl bg-[#A85507] hover:bg-[#78350F] text-white font-black text-xs uppercase tracking-[0.2em] transition-all shadow-xl shadow-[#A85507]/20 active:scale-95">
                        Reenviar enlace
                    </button>
                </form>

                <form method="POST" action="{{ route('home') }}">
                    @csrf
                    <button type="submit" class="w-full text-stone-500 hover:text-[#A85507] font-black text-[10px] uppercase tracking-[0.3em] transition-colors">
                        ← Volver al inicio
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
