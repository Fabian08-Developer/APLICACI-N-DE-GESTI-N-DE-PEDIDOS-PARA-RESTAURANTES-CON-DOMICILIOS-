<div class="container-auth relative min-h-screen flex items-center justify-center overflow-hidden" style="background: linear-gradient(135deg, #FDFBF7 0%, #F5F0E6 100%);">
    
    {{-- Decorative Background Elements --}}
    <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-[#E07A5F]/10 rounded-full blur-3xl mix-blend-multiply animate-blob pointer-events-none"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-[#3D5A80]/10 rounded-full blur-3xl mix-blend-multiply animate-blob animation-delay-2000 pointer-events-none"></div>
    <div class="absolute top-[20%] right-[20%] w-[20%] h-[20%] bg-[#81B29A]/10 rounded-full blur-2xl mix-blend-multiply animate-blob animation-delay-4000 pointer-events-none"></div>

    <style>
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        .animate-blob { animation: blob 7s infinite; }
        .animation-delay-2000 { animation-delay: 2s; }
        .animation-delay-4000 { animation-delay: 4s; }
        
        /* Glassmorphism Card */
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 25px 50px -12px rgba(44, 36, 27, 0.15);
        }
        
        .pulse-ring {
            position: relative;
        }
        .pulse-ring::before {
            content: '';
            position: absolute;
            inset: -10px;
            border-radius: 50%;
            border: 2px solid rgba(34, 197, 94, 0.3);
            animation: pulse-ring 2s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
        }
        @keyframes pulse-ring {
            0% { transform: scale(0.8); opacity: 1; }
            100% { transform: scale(1.5); opacity: 0; }
        }
    </style>

    <div class="relative z-10 w-full max-w-lg mx-4">
        
        {{-- Logo Header --}}
        <div class="flex justify-center mb-8">
            <a href="{{ route('home') }}" class="inline-flex flex-col items-center group">
                <div class="w-14 h-14 bg-[#2C241B] rounded-2xl flex items-center justify-center text-white shadow-xl group-hover:-translate-y-1 transition-transform duration-300">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <span class="mt-3 font-black text-xl text-[#2C241B] tracking-tight">SG<span class="text-[#E07A5F]">PD</span></span>
            </a>
        </div>

        {{-- Main Glass Card --}}
        <div class="glass-card rounded-[32px] p-8 md:p-12 text-center transform transition-all hover:scale-[1.01] duration-500">
            
            {{-- Status Icon --}}
            <div class="flex justify-center mb-6">
                <div class="pulse-ring w-20 h-20 bg-emerald-50 rounded-full flex items-center justify-center text-emerald-500 shadow-inner">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            {{-- Typography --}}
            <h1 class="text-3xl font-black text-[#2C241B] tracking-tight mb-3">
                ¡Solicitud Recibida!
            </h1>
            <p class="text-[#5C5246] text-sm font-medium leading-relaxed mb-10 max-w-sm mx-auto">
                Tu registro fue exitoso. Actualmente tu cuenta está en <strong class="text-[#E07A5F]">revisión y auditoría</strong> por parte de la administración central.
            </p>

            {{-- Info Blocks --}}
            <div class="space-y-4 mb-10 text-left">
                
                {{-- Block 1 --}}
                <div class="bg-white/80 rounded-2xl p-4 flex items-start gap-4 border border-white hover:border-[#E07A5F]/30 transition-colors shadow-sm">
                    <div class="w-10 h-10 rounded-xl bg-[#E07A5F]/10 text-[#E07A5F] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-[10px] font-black uppercase tracking-widest text-[#E07A5F] mb-1">Notificación Inmediata</h4>
                        <p class="text-xs text-[#5C5246] font-medium leading-relaxed">Te enviaremos un correo apenas tu cuenta sea verificada y habilitada para operar tu negocio.</p>
                    </div>
                </div>

                {{-- Block 2 --}}
                <div class="bg-white/80 rounded-2xl p-4 flex items-start gap-4 border border-white hover:border-[#3D5A80]/30 transition-colors shadow-sm">
                    <div class="w-10 h-10 rounded-xl bg-[#3D5A80]/10 text-[#3D5A80] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-[10px] font-black uppercase tracking-widest text-[#3D5A80] mb-1">Tiempo de Respuesta</h4>
                        <p class="text-xs text-[#5C5246] font-medium leading-relaxed">El tiempo habitual de validación de identidad y NIT es menor a 24 horas hábiles.</p>
                    </div>
                </div>

            </div>

            {{-- Action Button --}}
            <a href="{{ route('home') }}" class="inline-flex items-center justify-center w-full sm:w-auto px-8 py-4 bg-[#2C241B] hover:bg-[#1A1510] text-white text-xs font-black uppercase tracking-widest rounded-2xl transition-all shadow-xl hover:shadow-2xl hover:-translate-y-1">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver al Inicio
            </a>
            
        </div>
        
        {{-- Footer Note --}}
        <p class="text-center mt-8 text-[11px] font-bold text-[#8B8175] uppercase tracking-widest">
            SGPD Suite © {{ date('Y') }}
        </p>
    </div>
</div>
