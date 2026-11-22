<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>FORTEX ⚡ | Plataforma Oficial de Cómputo y Rendimientos</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('img/fortex.jpg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        /* Reglas críticas de seguridad visual para conexiones móviles lentas */
        img { max-width: 100%; height: auto; }
        header img { width: 48px !important; height: 48px !important; object-fit: cover; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #030712;
            -webkit-tap-highlight-color: transparent;
        }
        /* Prevenir auto-zoom en iOS Safari */
        @media screen and (max-width: 768px) {
            input:not([type="checkbox"]):not([type="radio"]), select, textarea {
                font-size: 16px !important;
            }
        }
        .glow-emerald {
            box-shadow: 0 0 45px -5px rgba(16, 185, 129, 0.4);
        }
        .glow-cyan {
            box-shadow: 0 0 45px -5px rgba(6, 182, 212, 0.4);
        }
        .glow-gold {
            box-shadow: 0 0 45px -5px rgba(245, 158, 11, 0.4);
        }
        .gradient-text {
            background: linear-gradient(135deg, #10b981 0%, #06b6d4 50%, #f59e0b 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        @keyframes marquee {
            0% { transform: translateX(0%); }
            100% { transform: translateX(-50%); }
        }
        .animate-marquee {
            display: flex;
            width: 200%;
            animation: marquee 28s linear infinite;
        }
        .pyramid-card:hover {
            transform: translateY(-4px) scale(1.02);
        }
        .swal2-popup.swal-custom-dark {
            background: #090d16 !important;
            border: 1px solid #1e293b !important;
            border-radius: 1.5rem !important;
            color: #f1f5f9 !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.75) !important;
        }
        .swal2-title {
            color: #ffffff !important;
            font-weight: 800 !important;
        }
        .swal2-html-container {
            color: #94a3b8 !important;
            font-size: 0.875rem !important;
        }
        .bottom-nav-blur {
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
    </style>
</head>
<body class="text-slate-100 selection:bg-emerald-500 selection:text-black overflow-x-hidden @auth pb-20 md:pb-0 @endauth">

    <!-- Luces Ambientales de Fondo -->
    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-[900px] h-[550px] bg-emerald-500/15 rounded-full blur-[150px] pointer-events-none -z-10"></div>
    <div class="fixed top-1/2 -right-48 w-[700px] h-[700px] bg-cyan-500/10 rounded-full blur-[180px] pointer-events-none -z-10"></div>
    <div class="fixed bottom-0 -left-48 w-[700px] h-[700px] bg-amber-500/10 rounded-full blur-[180px] pointer-events-none -z-10"></div>

    <!-- Ticker de Pagos y Notificaciones en Vivo -->
    <div class="bg-slate-950/90 border-b border-slate-800/80 py-2.5 overflow-hidden text-xs backdrop-blur-md sticky top-0 z-50">
        <div class="animate-marquee whitespace-nowrap flex items-center gap-10 text-slate-400">
            <span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span> <strong class="text-white">@carlos_m</strong> retiró <strong class="text-emerald-400 font-mono">$150.000 COP</strong> a Nequi <span class="text-slate-500 text-[10px]">hace 2 min</span></span>
            <span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-cyan-400"></span> <strong class="text-white">@juan_vip</strong> activó el paquete <strong class="text-cyan-400">VIP 2 - Plata</strong> ($50.000 COP)</span>
            <span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span> <strong class="text-white">@elena_m</strong> ganó comisión de Nivel 1 <strong class="text-amber-400 font-mono">+$25.000 COP</strong></span>
            <span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span> <strong class="text-white">@david_pro</strong> retiró <strong class="text-emerald-400 font-mono">$78.000 COP</strong> a Bancolombia <span class="text-slate-500 text-[10px]">hace 5 min</span></span>
            <span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-purple-400"></span> ⚡ Nuevo usuario registrado desde Telegram</span>
            <!-- Repetición para efecto continuo -->
            <span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span> <strong class="text-white">@carlos_m</strong> retiró <strong class="text-emerald-400 font-mono">$150.000 COP</strong> a Nequi <span class="text-slate-500 text-[10px]">hace 2 min</span></span>
            <span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-cyan-400"></span> <strong class="text-white">@juan_vip</strong> activó el paquete <strong class="text-cyan-400">VIP 2 - Plata</strong></span>
        </div>
    </div>

    <!-- Barra de Navegación Principal -->
    <header class="bg-slate-950/80 border-b border-slate-800/80 backdrop-blur-xl sticky top-[41px] z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <!-- Logo Oficial -->
            <a href="/" class="flex items-center gap-3 group">
                <div class="w-12 h-12 rounded-2xl bg-black border border-emerald-500/30 flex items-center justify-center overflow-hidden shadow-lg shadow-emerald-500/25 group-hover:scale-105 transition-transform" style="width:48px;height:48px;min-width:48px;min-height:48px;max-width:48px;max-height:48px;border-radius:16px;overflow:hidden;display:flex;align-items:center;justify-content:center;">
                    <img src="{{ asset('img/fortex.jpg') }}" alt="FORTEX" width="48" height="48" style="width:48px;height:48px;max-width:48px;max-height:48px;object-fit:cover;border-radius:14px;" class="w-full h-full object-cover">
                </div>
                <div>
                    <span class="text-xl font-extrabold tracking-tight text-white block leading-none">FORTEX</span>
                    <span class="text-[10px] uppercase tracking-widest text-emerald-400/90 font-bold flex items-center gap-1 mt-0.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Plataforma Verificada
                    </span>
                </div>
            </a>

            <!-- Menú Central -->
            <nav class="hidden lg:flex items-center gap-8 text-sm font-semibold text-slate-300">
                <a href="#como-funciona" class="hover:text-emerald-400 transition">Cómo Funciona</a>
                <a href="#piramide" class="hover:text-emerald-400 transition">Pirámide de Red</a>
                <a href="#planes" class="hover:text-emerald-400 transition">Membresías VIP</a>
                <a href="#calculadora" class="hover:text-emerald-400 transition">Calculadora</a>
                <a href="#faq" class="hover:text-emerald-400 transition">Preguntas</a>
            </nav>

            <!-- Menú de Acciones: Autenticado vs Invitado -->
            <div class="flex items-center gap-3">
                @auth
                    <!-- Cápsula de Saldo y Recarga Rápida -->
                    <div class="flex items-center bg-slate-900/90 border border-slate-800/90 rounded-2xl p-1 shadow-lg">
                        <a href="{{ route('cliente.deposits.index') }}" title="Tu saldo disponible" class="flex items-center gap-2 px-3 py-1.5 hover:bg-slate-800/60 rounded-xl transition">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span class="text-emerald-400 font-mono font-extrabold text-xs sm:text-sm">${{ number_format(Auth::user()->balance, 0, ',', '.') }}</span>
                            <span class="text-[10px] text-slate-500 font-bold hidden sm:inline">COP</span>
                        </a>

                        <a href="{{ route('cliente.deposits.index') }}" class="px-3 py-1.5 bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-600 hover:to-teal-500 text-slate-950 font-black rounded-xl text-xs shadow-md transition active:scale-95 flex items-center gap-1">
                            <span>➕</span> <span class="hidden sm:inline">Recargar</span>
                        </a>
                    </div>

                    <!-- Menú Desplegable de Usuario VIP -->
                    <div class="relative" id="userMenuDropdownContainer">
                        <button type="button" onclick="toggleUserDropdown()" class="flex items-center gap-2 p-1.5 sm:px-3 sm:py-2 bg-slate-900 hover:bg-slate-800 border border-slate-800 hover:border-slate-700 rounded-2xl transition cursor-pointer">
                            <div class="w-7 h-7 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center text-slate-950 font-black text-xs">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <span class="text-xs font-bold text-slate-200 hidden md:inline truncate max-w-[100px]">{{ Auth::user()->name }}</span>
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- Panel Flotante del Menú -->
                        <div id="userDropdownMenu" class="hidden absolute right-0 mt-2 w-52 bg-slate-900/95 border border-slate-800 rounded-2xl shadow-2xl backdrop-blur-xl p-2 z-50 space-y-1 text-xs">
                            <div class="px-3 py-2 border-b border-slate-800/80 mb-1">
                                <p class="font-bold text-white truncate">{{ Auth::user()->name }}</p>
                                <p class="text-[10px] text-slate-400 font-mono truncate">{{ Auth::user()->email }}</p>
                            </div>

                            <a href="{{ route('cliente.withdrawals.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/80 transition font-semibold">
                                <span class="text-sm">💸</span> Solicitar Retiro
                            </a>

                            <a href="{{ route('cliente.team.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/80 transition font-semibold">
                                <span class="text-sm">👥</span> Mi Red de Referidos
                            </a>

                            <a href="#planes" onclick="toggleUserDropdown()" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/80 transition font-semibold">
                                <span class="text-sm">⚡</span> Catálogo de Planes
                            </a>

                            <div class="pt-1 border-t border-slate-800/80">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-rose-400 hover:bg-rose-500/15 transition font-semibold cursor-pointer text-left">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        <span>Cerrar Sesión</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <button onclick="openAuthModal('login')" class="px-4 py-2 text-xs sm:text-sm font-semibold text-slate-300 hover:text-white transition cursor-pointer">
                        Iniciar Sesión
                    </button>
                    <button onclick="openAuthModal('register')" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white text-xs sm:text-sm font-bold shadow-lg shadow-emerald-500/25 transition active:scale-95 cursor-pointer">
                        Registrarse Gratis
                    </button>
                @endauth
            </div>
        </div>
    </header>

    <!-- ========================================== -->
    <!-- HERO SECTION & PANEL VIP DEL CLIENTE -->
    <!-- ========================================== -->
    <section class="relative pt-10 pb-16 md:pt-16 md:pb-24 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
        <div class="text-center max-w-4xl mx-auto">
            <!-- Sello de Confianza y Auditoría -->
            <div class="inline-flex items-center gap-2.5 px-4 py-2 rounded-full bg-slate-900/90 border border-emerald-500/30 text-xs font-semibold text-emerald-400 mb-6 shadow-lg shadow-emerald-500/10">
                <span class="flex h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>🛡️ Protocolo Seguro • Retiros a Nequi, Daviplata y Bancolombia 24/7</span>
            </div>

            <!-- Título Principal Impactante -->
            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-white leading-[1.1]">
                Genera rendimientos diarios y escala en la <span class="gradient-text">Pirámide VIP</span>
            </h1>

            <p class="mt-4 text-sm sm:text-base text-slate-300 leading-relaxed max-w-2xl mx-auto font-normal">
                Plataforma líder en distribución de beneficios por membresías en Pesos Colombianos. Obtén hasta un <strong>7% diario</strong> y genera hasta un <strong>10% directo</strong> por cada invitado.
            </p>

            <!-- PANEL DE CONTROL DEL CLIENTE (VISIBLE CUANDO ESTÁ AUTENTICADO) -->
            @auth
                <div class="mt-8 text-left bg-gradient-to-br from-slate-900 via-slate-900/95 to-emerald-950/40 border border-emerald-500/30 rounded-3xl p-6 sm:p-8 shadow-2xl relative overflow-hidden">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-800">
                        <div class="flex items-center gap-3.5">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center text-slate-950 font-black text-xl shadow-lg shadow-emerald-500/30">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h2 class="text-lg font-black text-white">{{ Auth::user()->name }}</h2>
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 text-[10px] font-extrabold border border-emerald-500/30">
                                        💎 MIEMBRO VIP
                                    </span>
                                </div>
                                <p class="text-xs text-slate-400 font-mono">{{ Auth::user()->email }}</p>
                            </div>
                        </div>

                        <div class="sm:text-right">
                            <span class="text-[11px] text-slate-400 block">Saldo Disponible</span>
                            <div class="text-3xl sm:text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 via-teal-300 to-cyan-400 font-mono">
                                ${{ number_format(Auth::user()->balance, 0, ',', '.') }} <span class="text-xs font-bold text-slate-400">COP</span>
                            </div>
                        </div>
                    </div>

                    <!-- 4 Botones de Acción Neón -->
                    <div class="grid grid-cols-4 gap-2.5 sm:gap-4 mt-6">
                        <a href="{{ route('cliente.deposits.index') }}" class="group flex flex-col items-center justify-center p-3 sm:p-4 bg-slate-950/80 hover:bg-emerald-500/10 border border-slate-800 hover:border-emerald-500/40 rounded-2xl transition active:scale-95 text-center">
                            <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-lg mb-1.5 group-hover:scale-110 transition">➕</div>
                            <span class="text-xs font-bold text-white">Recargar</span>
                        </a>

                        <a href="{{ route('cliente.withdrawals.index') }}" class="group flex flex-col items-center justify-center p-3 sm:p-4 bg-slate-950/80 hover:bg-cyan-500/10 border border-slate-800 hover:border-cyan-500/40 rounded-2xl transition active:scale-95 text-center">
                            <div class="w-10 h-10 rounded-xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center text-lg mb-1.5 group-hover:scale-110 transition">💸</div>
                            <span class="text-xs font-bold text-white">Retirar</span>
                        </a>

                        <a href="#planes" class="group flex flex-col items-center justify-center p-3 sm:p-4 bg-slate-950/80 hover:bg-purple-500/10 border border-slate-800 hover:border-purple-500/40 rounded-2xl transition active:scale-95 text-center">
                            <div class="w-10 h-10 rounded-xl bg-purple-500/20 text-purple-400 flex items-center justify-center text-lg mb-1.5 group-hover:scale-110 transition">⚡</div>
                            <span class="text-xs font-bold text-white">Planes</span>
                        </a>

                        <a href="{{ route('cliente.team.index') }}" class="group flex flex-col items-center justify-center p-3 sm:p-4 bg-slate-950/80 hover:bg-amber-500/10 border border-slate-800 hover:border-amber-500/40 rounded-2xl transition active:scale-95 text-center">
                            <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-lg mb-1.5 group-hover:scale-110 transition">👥</div>
                            <span class="text-xs font-bold text-white">Mi Red</span>
                        </a>
                    </div>

                    <!-- Planes Activos & Reclamo Diario -->
                    @if(isset($userPlans) && $userPlans->count() > 0)
                        <div class="mt-6 pt-6 border-t border-slate-800">
                            <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-300 mb-3 flex items-center gap-1.5">
                                <span>⚡</span> Tus Inversiones Activas:
                            </h3>
                            <div class="space-y-3">
                                @foreach($userPlans as $up)
                                    <div class="bg-slate-950/90 border border-slate-800 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 text-[10px] font-extrabold uppercase">
                                                    {{ $up->plan->name }}
                                                </span>
                                                <span class="text-xs text-white font-mono font-bold">${{ number_format($up->invested_amount, 0, ',', '.') }} COP</span>
                                            </div>
                                            <div class="text-[11px] text-slate-400 mt-1">
                                                Ganado: <strong class="text-emerald-400 font-mono">${{ number_format($up->earned_so_far, 0, ',', '.') }}</strong> / Tope: <strong class="text-amber-400 font-mono">${{ number_format($up->max_earning, 0, ',', '.') }} COP</strong>
                                            </div>
                                        </div>

                                        @if(!$up->canClaim())
                                            <div class="flex items-center gap-2 px-3.5 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-slate-400">
                                                <span>⏳ Próximo reclamo:</span>
                                                <span class="countdown-timer font-mono text-amber-400 font-extrabold" data-seconds="{{ $up->secondsUntilNextClaim() }}">Calculando...</span>
                                            </div>
                                        @else
                                            <form method="POST" action="{{ route('cliente.plans.claim', $up->id) }}">
                                                @csrf
                                                <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-slate-950 font-black rounded-xl text-xs shadow-lg shadow-emerald-500/25 transition active:scale-95 cursor-pointer animate-pulse">
                                                    🎁 Reclamar (+${{ number_format($up->daily_earning, 0, ',', '.') }} COP)
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Enlace de Referidos Rápido -->
                    <div class="mt-6 pt-6 border-t border-slate-800">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-slate-300">🔗 Tu Enlace de Invitación VIP:</span>
                            <span class="text-[10px] text-emerald-400 font-bold">10% de Comisión Directa</span>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-2">
                            <input id="heroRefInput" type="text" readonly value="{{ url('/?ref=' . Auth::user()->referral_code) }}" class="flex-1 px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs font-mono text-emerald-400 select-all focus:outline-none">
                            <button type="button" onclick="navigator.clipboard.writeText('{{ url('/?ref=' . Auth::user()->referral_code) }}'); notifyCopied('¡Enlace de referido copiado!')" class="px-5 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold rounded-xl text-xs transition cursor-pointer flex items-center justify-center gap-1">
                                📋 Copiar
                            </button>
                        </div>
                    </div>
                </div>
            @else
                <!-- Botones CTA para Invitados -->
                <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <button onclick="openAuthModal('register')" class="w-full sm:w-auto px-9 py-4 bg-gradient-to-r from-emerald-500 via-teal-500 to-cyan-500 hover:from-emerald-600 hover:to-cyan-600 text-white font-extrabold rounded-2xl shadow-xl shadow-emerald-500/30 transition-all duration-300 transform hover:-translate-y-1 text-center text-base flex items-center justify-center gap-2 cursor-pointer">
                        <span>⚡</span> Abrir Mi Cuenta Ahora (Gratis)
                    </button>
                    <a href="#calculadora" class="w-full sm:w-auto px-8 py-4 bg-slate-900/90 hover:bg-slate-800 border border-slate-800 hover:border-slate-700 text-slate-200 font-bold rounded-2xl transition text-center text-base flex items-center justify-center gap-2">
                        <span>🧮</span> Simular Ganancias
                    </a>
                </div>
            @endauth

            <!-- 4 Pilares de Confianza / Métricas -->
            <div class="mt-12 grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-slate-900/70 border border-slate-800/80 rounded-2xl p-5 backdrop-blur-sm hover:border-emerald-500/40 transition">
                    <p class="text-3xl font-extrabold text-white font-mono">5% - 7%</p>
                    <p class="text-xs text-emerald-400 font-semibold mt-1">Rendimiento Diario Fijo</p>
                </div>
                <div class="bg-slate-900/70 border border-slate-800/80 rounded-2xl p-5 backdrop-blur-sm hover:border-cyan-500/40 transition">
                    <p class="text-3xl font-extrabold text-white font-mono">3 Niveles</p>
                    <p class="text-xs text-cyan-400 font-semibold mt-1">Comisiones de Red</p>
                </div>
                <div class="bg-slate-900/70 border border-slate-800/80 rounded-2xl p-5 backdrop-blur-sm hover:border-amber-500/40 transition">
                    <p class="text-3xl font-extrabold text-white font-mono">100%</p>
                    <p class="text-xs text-amber-400 font-semibold mt-1">Retiros Rápidos en COP</p>
                </div>
                <div class="bg-slate-900/70 border border-slate-800/80 rounded-2xl p-5 backdrop-blur-sm hover:border-purple-500/40 transition">
                    <p class="text-3xl font-extrabold text-white font-mono">$10.000</p>
                    <p class="text-xs text-purple-400 font-semibold mt-1">Mínimo de Entrada COP</p>
                </div>
            </div>
        </div>
    </section>

    <!-- SECCIÓN: CÓMO FUNCIONA EN 3 PASOS -->
    <section id="como-funciona" class="py-20 bg-slate-950/90 border-t border-slate-900">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="px-3.5 py-1 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-full text-xs font-bold uppercase tracking-wider">
                    Sencillo y Transparente
                </span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-white mt-3">
                    Empieza a ganar en <span class="gradient-text">3 Simples Pasos</span>
                </h2>
                <p class="text-slate-400 text-sm mt-2">Todo el proceso es automatizado y toma menos de 2 minutos.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Paso 1 -->
                <div class="bg-slate-900/50 border border-slate-800 rounded-3xl p-7 relative hover:border-emerald-500/40 transition duration-300">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 flex items-center justify-center text-2xl font-extrabold mb-5">
                        1
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Crea tu Cuenta Gratis</h3>
                    <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                        Regístrate con tu correo o número de celular en 30 segundos y activa tu código de invitación.
                    </p>
                </div>

                <!-- Paso 2 -->
                <div class="bg-slate-900/50 border border-slate-800 rounded-3xl p-7 relative hover:border-cyan-500/40 transition duration-300">
                    <div class="w-14 h-14 rounded-2xl bg-cyan-500/10 border border-cyan-500/30 text-cyan-400 flex items-center justify-center text-2xl font-extrabold mb-5">
                        2
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Elige tu Paquete VIP</h3>
                    <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                        Recarga fácilmente por Nequi o Daviplata (desde $10.000 COP) y activa tu membresía para ganar todos los días.
                    </p>
                </div>

                <!-- Paso 3 -->
                <div class="bg-slate-900/50 border border-slate-800 rounded-3xl p-7 relative hover:border-amber-500/40 transition duration-300">
                    <div class="w-14 h-14 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-400 flex items-center justify-center text-2xl font-extrabold mb-5">
                        3
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Cobra y Retira a tu Nequi</h3>
                    <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                        Reclama tus ganancias cada 24 horas y solicita tus retiros directos a tu cuenta cuando quieras.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- SECCIÓN VISUAL DE LA PIRÁMIDE (ESTRUCTURA DE RED) -->
    <section id="piramide" class="py-24 bg-gradient-to-b from-slate-950 via-slate-900/50 to-slate-950 border-y border-slate-900 relative">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="px-3.5 py-1 bg-cyan-500/10 border border-cyan-500/30 text-cyan-400 rounded-full text-xs font-bold uppercase tracking-wider">
                    Sistema de Referidos
                </span>
                <h2 class="text-3xl sm:text-5xl font-extrabold text-white mt-3">
                    Tu <span class="gradient-text">Pirámide de Comisiones</span>
                </h2>
                <p class="text-slate-400 text-sm sm:text-base mt-3">
                    Construye un equipo y multiplica tus ganancias con nuestro árbol de 3 niveles continuos.
                </p>
            </div>

            <!-- Gráfico Escalonado de la Pirámide -->
            <div class="max-w-3xl mx-auto space-y-4">
                <!-- Nivel Cúspide: TÚ -->
                <div class="pyramid-card mx-auto w-11/12 sm:w-2/5 bg-gradient-to-r from-amber-400 via-amber-500 to-yellow-500 text-slate-950 p-5 rounded-2xl glow-gold text-center font-extrabold cursor-pointer transition-all duration-300">
                    <div class="text-2xl mb-1">👑</div>
                    <div class="text-lg uppercase tracking-wider font-black">TÚ (Líder / Cúspide)</div>
                    <div class="text-xs font-semibold opacity-95">Cobras de los 3 niveles inferiores en tiempo real</div>
                </div>

                <!-- Nivel 1: Directos -->
                <div class="pyramid-card mx-auto w-full sm:w-3/5 bg-gradient-to-r from-emerald-600 via-emerald-500 to-teal-500 text-white p-5 rounded-2xl glow-emerald text-center font-bold cursor-pointer transition-all duration-300">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs uppercase tracking-widest bg-black/40 px-3 py-1 rounded-full font-extrabold">Nivel 1</span>
                        <span class="text-xl font-extrabold bg-white/20 px-3.5 py-1 rounded-xl">10% Directo</span>
                    </div>
                    <div class="text-base sm:text-lg font-extrabold">Tus Invitados Directos</div>
                    <div class="text-xs text-emerald-100 font-normal mt-0.5">Recibes el 10% instantáneo de cada recarga que realicen con tu link.</div>
                </div>

                <!-- Nivel 2: Indirectos -->
                <div class="pyramid-card mx-auto w-full sm:w-4/5 bg-gradient-to-r from-cyan-600 via-cyan-500 to-blue-600 text-white p-5 rounded-2xl glow-cyan text-center font-bold cursor-pointer transition-all duration-300">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs uppercase tracking-widest bg-black/40 px-3 py-1 rounded-full font-extrabold">Nivel 2</span>
                        <span class="text-xl font-extrabold bg-white/20 px-3.5 py-1 rounded-xl">5% Comisión</span>
                    </div>
                    <div class="text-base sm:text-lg font-extrabold">Invitados de tus Invitados</div>
                    <div class="text-xs text-cyan-100 font-normal mt-0.5">Ganas el 5% de cada persona que tus amigos inviten sin esfuerzo extra.</div>
                </div>

                <!-- Nivel 3: Base de la red -->
                <div class="pyramid-card mx-auto w-full bg-gradient-to-r from-purple-700 via-indigo-600 to-blue-700 text-white p-5 rounded-2xl shadow-xl shadow-purple-500/20 text-center font-bold cursor-pointer transition-all duration-300">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs uppercase tracking-widest bg-black/40 px-3 py-1 rounded-full font-extrabold">Nivel 3</span>
                        <span class="text-xl font-extrabold bg-white/20 px-3.5 py-1 rounded-xl">2% Comisión</span>
                    </div>
                    <div class="text-base sm:text-lg font-extrabold">Comunidad y Red Profunda</div>
                    <div class="text-xs text-purple-100 font-normal mt-0.5">El volumen masivo de toda la base te sigue pagando comisiones acumulativas.</div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECCIÓN DE PLANES / PAQUETES VIP -->
    <section id="planes" class="py-24 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <span class="px-3.5 py-1 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-full text-xs font-bold uppercase tracking-wider">
                Membresías Oficiales
            </span>
            <h2 class="text-3xl sm:text-5xl font-extrabold text-white mt-3">
                Selecciona tu <span class="gradient-text">Paquete VIP</span>
            </h2>
            <p class="text-slate-400 text-sm sm:text-base mt-3">
                Rendimientos fijos acreditados cada 24 horas directamente a tu cuenta en Pesos Colombianos.
            </p>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-3 gap-2.5 sm:gap-6 items-stretch">
            @if(isset($plans) && $plans->count() > 0)
                @foreach($plans as $plan)
                    <div class="bg-slate-900/80 border {{ $plan->badge ? 'border-emerald-500 shadow-emerald-500/20' : 'border-slate-800' }} hover:border-emerald-500/50 rounded-2xl sm:rounded-3xl p-3 sm:p-7 flex flex-col justify-between transition-all duration-300 hover:-translate-y-1 relative overflow-hidden group shadow-xl {{ $plan->isSoldOut() ? 'opacity-60 grayscale' : '' }}">
                        @if($plan->badge)
                            <div class="hidden sm:block absolute -right-12 top-7 bg-gradient-to-r from-emerald-500 to-teal-400 text-slate-950 text-[10px] font-extrabold uppercase py-1 px-12 rotate-45 shadow-md">
                                {{ $plan->badge }}
                            </div>
                        @endif

                        <div>
                            <div class="flex items-center gap-1 sm:gap-2 flex-wrap mb-1.5 sm:mb-2">
                                <span class="px-2 sm:px-3 py-0.5 sm:py-1 rounded-full bg-slate-800 text-slate-300 text-[9px] sm:text-xs font-bold truncate max-w-full">
                                    {{ $plan->badge ?? 'Membresía VIP' }}
                                </span>
                                @if($plan->isSoldOut())
                                    <span class="px-1.5 sm:px-2.5 py-0.5 sm:py-1 rounded-full bg-rose-500/20 text-rose-400 text-[8px] sm:text-[10px] font-black uppercase border border-rose-500/40">
                                        🔴 Agotado
                                    </span>
                                @elseif($plan->hasStockLimit())
                                    <span class="px-1.5 sm:px-2.5 py-0.5 sm:py-1 rounded-full bg-amber-500/20 text-amber-400 text-[8px] sm:text-[10px] font-black uppercase border border-amber-500/40">
                                        ⚡ Quedan {{ $plan->stock }} cupos
                                    </span>
                                @endif
                            </div>
                            <h3 class="text-sm sm:text-2xl font-black text-white mt-0.5 sm:mt-1 leading-snug">{{ $plan->name }}</h3>
                            <p class="text-[10px] sm:text-xs text-slate-400 mt-0.5 sm:mt-1 line-clamp-2 sm:min-h-[32px]">{{ $plan->description ?? 'Rendimiento diario fijo garantizado en COP.' }}</p>

                            <div class="my-2.5 sm:my-6 py-2 sm:py-4 border-y border-slate-800">
                                <div class="text-base sm:text-4xl font-extrabold text-white font-mono leading-none">
                                    ${{ number_format($plan->price, 0, ',', '.') }} <span class="text-[9px] sm:text-xs font-normal text-emerald-400 font-sans">COP</span>
                                </div>
                                <p class="text-[10px] sm:text-xs text-emerald-400 font-semibold mt-1">
                                    {{ $plan->daily_percentage }}% Diario <span class="hidden sm:inline">(${{ number_format(($plan->price * $plan->daily_percentage) / 100, 0, ',', '.') }} COP / día)</span>
                                </p>
                            </div>

                            <ul class="space-y-1 sm:space-y-3 text-[10px] sm:text-xs text-slate-300">
                                <li class="flex items-center gap-1 sm:gap-2"><span class="text-emerald-400">✓</span> <span>Duración: <strong>{{ $plan->duration_days }} Días</strong></span></li>
                                <li class="flex items-center gap-1 sm:gap-2"><span class="text-emerald-400">✓</span> <span>Tope: <strong class="text-amber-400 font-mono">${{ number_format($plan->max_return, 0, ',', '.') }}</strong></span></li>
                                <li class="flex items-center gap-1 sm:gap-2"><span class="text-emerald-400">✓</span> <span>Comisión Red: <strong>10% + 2%</strong></span></li>
                                <li class="flex items-center gap-1 sm:gap-2"><span class="text-emerald-400">✓</span> <span>Nequi / Bancolombia</span></li>
                            </ul>
                        </div>

                        @if($plan->isSoldOut())
                            <button type="button" disabled class="mt-3 sm:mt-8 w-full py-2.5 sm:py-4 bg-slate-800 text-slate-500 font-bold text-[10px] sm:text-xs rounded-xl text-center cursor-not-allowed border border-slate-700">
                                ❌ Agotado
                            </button>
                        @else
                            @auth
                                <a href="{{ route('cliente.plans.index') }}" class="mt-3 sm:mt-8 block w-full py-2.5 sm:py-4 {{ $plan->badge ? 'bg-gradient-to-r from-emerald-500 to-teal-500 text-slate-950 font-black shadow-lg shadow-emerald-500/25' : 'bg-slate-800 hover:bg-slate-700 text-white font-bold' }} text-[11px] sm:text-xs rounded-xl text-center transition cursor-pointer active:scale-95">
                                    <span class="sm:hidden">⚡ Activar</span>
                                    <span class="hidden sm:inline">⚡ Activar {{ $plan->name }}</span>
                                </a>
                            @else
                                <button onclick="openAuthModal('register')" class="mt-3 sm:mt-8 w-full py-2.5 sm:py-4 {{ $plan->badge ? 'bg-gradient-to-r from-emerald-500 to-teal-500 text-white shadow-lg shadow-emerald-500/25' : 'bg-slate-800 hover:bg-slate-700 text-white' }} font-extrabold text-[11px] sm:text-xs rounded-xl text-center transition cursor-pointer">
                                    <span class="sm:hidden">Activar ⚡</span>
                                    <span class="hidden sm:inline">Activar {{ $plan->name }} ⚡</span>
                                </button>
                            @endauth
                        @endif
                    </div>
                @endforeach
            @else
                <div class="col-span-2 lg:col-span-3 text-center py-12 text-slate-500">
                    <p>No hay ofertas de planes disponibles en este momento.</p>
                </div>
            @endif
        </div>
    </section>

    <!-- CALCULADORA INTERACTIVA DE GANANCIAS -->
    <section id="calculadora" class="py-20 bg-slate-950/80 border-t border-slate-900">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            <div class="bg-gradient-to-br from-slate-900 via-slate-900 to-slate-950 border border-slate-800 rounded-3xl p-8 sm:p-12 shadow-2xl relative">
                <div class="text-center max-w-xl mx-auto mb-8">
                    <span class="text-3xl">🧮</span>
                    <h2 class="text-2xl sm:text-4xl font-extrabold text-white mt-2">Calculadora de Ganancias COP</h2>
                    <p class="text-xs sm:text-sm text-slate-400 mt-1">Mueve la barra y calcula exactamente cuánto dinero generarás cada día.</p>
                </div>

                <!-- Slider -->
                <div class="space-y-4">
                    <div class="flex justify-between items-center text-sm font-semibold">
                        <span class="text-slate-400">Monto de Inversión:</span>
                        <span id="amountDisplay" class="text-2xl sm:text-3xl font-extrabold text-emerald-400 font-mono">$50.000 COP</span>
                    </div>

                    <input id="calcSlider" type="range" min="10000" max="500000" step="10000" value="50000"
                        class="w-full h-3 bg-slate-800 rounded-lg appearance-none cursor-pointer accent-emerald-500">
                    
                    <div class="flex justify-between text-[11px] text-slate-500 font-mono">
                        <span>$10.000</span>
                        <span>$100.000</span>
                        <span>$250.000</span>
                        <span>$500.000</span>
                    </div>
                </div>

                <!-- Resultados en Tarjetas -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-8 pt-8 border-t border-slate-800">
                    <div class="bg-slate-950/60 p-5 rounded-2xl border border-slate-800 text-center">
                        <p class="text-xs text-slate-400 uppercase font-semibold">Ganancia Diaria</p>
                        <p id="dailyResult" class="text-2xl sm:text-3xl font-extrabold text-white mt-1 font-mono">$3.000 COP</p>
                        <span class="text-[10px] text-emerald-400 font-semibold">6% cada 24 horas</span>
                    </div>
                    <div class="bg-slate-950/60 p-5 rounded-2xl border border-slate-800 text-center">
                        <p class="text-xs text-slate-400 uppercase font-semibold">Ganancia en 30 Días</p>
                        <p id="monthlyResult" class="text-2xl sm:text-3xl font-extrabold text-cyan-400 mt-1 font-mono">$90.000 COP</p>
                        <span class="text-[10px] text-cyan-400 font-semibold">Retorno Total</span>
                    </div>
                    <div class="bg-slate-950/60 p-5 rounded-2xl border border-slate-800 text-center">
                        <p class="text-xs text-slate-400 uppercase font-semibold">Porcentaje de Retorno</p>
                        <p id="roiResult" class="text-2xl sm:text-3xl font-extrabold text-amber-400 mt-1 font-mono">180%</p>
                        <span class="text-[10px] text-amber-400 font-semibold">Tope Máximo Garantizado</span>
                    </div>
                </div>

                <div class="mt-8 text-center">
                    @auth
                        <a href="#planes" class="inline-block px-9 py-4 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-slate-950 font-black rounded-2xl text-sm shadow-lg shadow-emerald-500/25 transition cursor-pointer">
                            Activar un Plan con este Retorno 🚀
                        </a>
                    @else
                        <button onclick="openAuthModal('register')" class="px-9 py-4 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-bold rounded-2xl text-sm shadow-lg shadow-emerald-500/25 transition cursor-pointer">
                            Comenzar con este Retorno 🚀
                        </button>
                    @endauth
                </div>
            </div>
        </div>
    </section>

    <!-- PREGUNTAS FRECUENTES (FAQ) -->
    <section id="faq" class="py-20 max-w-4xl mx-auto px-4 sm:px-6">
        <div class="text-center mb-12">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-white">Preguntas Frecuentes</h2>
            <p class="text-slate-400 text-xs sm:text-sm mt-1">Todo lo que necesitas saber antes de comenzar.</p>
        </div>

        <div class="space-y-3.5">
            <details class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 cursor-pointer group">
                <summary class="font-bold text-sm sm:text-base text-white flex items-center justify-between">
                    <span>¿Cómo se acreditan mis ganancias diarias?</span>
                    <span class="text-emerald-400 text-xl group-open:rotate-45 transition-transform">+</span>
                </summary>
                <p class="text-xs sm:text-sm text-slate-400 mt-3 leading-relaxed">
                    Cada 24 horas después de activar tu membresía, tu rendimiento se acredita a tu balance. Puedes entrar a tu panel de usuario y hacer clic en *"Reclamar Ganancia de Hoy"* para recibirlo al instante.
                </p>
            </details>

            <details class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 cursor-pointer group">
                <summary class="font-bold text-sm sm:text-base text-white flex items-center justify-between">
                    <span>¿Cómo gano comisiones con mi enlace de referidos?</span>
                    <span class="text-emerald-400 text-xl group-open:rotate-45 transition-transform">+</span>
                </summary>
                <p class="text-xs sm:text-sm text-slate-400 mt-3 leading-relaxed">
                    Al registrarte obtienes un código de invitación único. Ganas el <strong>10%</strong> en Nivel 1 (invitados directos), <strong>5%</strong> en Nivel 2 y <strong>2%</strong> en Nivel 3 por cada recarga que ellos hagan.
                </p>
            </details>

            <details class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 cursor-pointer group">
                <summary class="font-bold text-sm sm:text-base text-white flex items-center justify-between">
                    <span>¿Cuál es el monto mínimo para solicitar retiros?</span>
                    <span class="text-emerald-400 text-xl group-open:rotate-45 transition-transform">+</span>
                </summary>
                <p class="text-xs sm:text-sm text-slate-400 mt-3 leading-relaxed">
                    El mínimo de retiro es de <strong>$15.000 COP</strong> y los pagos se envían a tu cuenta de Nequi, Daviplata o Bancolombia.
                </p>
            </details>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="border-t border-slate-900 bg-slate-950 py-12 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <img src="{{ asset('img/fortex.jpg') }}" alt="FORTEX" class="w-6 h-6 rounded-md object-cover border border-slate-800">
                <span class="font-bold text-slate-200">FORTEX</span>
                <span>— Sistema Oficial de Rendimientos de Cómputo en COP</span>
            </div>
            <p>© 2026 FORTEX. Todos los derechos reservados.</p>
        </div>
    </footer>

    <!-- BARRA INFERIOR FLOTANTE PARA MÓVILES (CUANDO EL CLIENTE ESTÁ AUTENTICADO) -->
    @auth
        <nav class="fixed bottom-0 left-0 right-0 z-50 bg-slate-950/90 border-t border-slate-800/90 bottom-nav-blur lg:hidden">
            <div class="max-w-md mx-auto px-6 h-16 flex items-center justify-between text-center">
                <a href="/" class="flex flex-col items-center gap-1 text-emerald-400">
                    <span class="text-lg">🏠</span>
                    <span class="text-[10px] font-bold">Inicio</span>
                </a>
                <a href="{{ route('cliente.plans.index') }}" class="flex flex-col items-center gap-1 text-slate-400 hover:text-slate-200">
                    <span class="text-lg">⚡</span>
                    <span class="text-[10px] font-bold">Planes</span>
                </a>
                <a href="{{ route('cliente.deposits.index') }}" class="-mt-5 w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 text-slate-950 flex items-center justify-center text-xl font-extrabold shadow-lg shadow-emerald-500/30 active:scale-95 transition">
                    ➕
                </a>
                <a href="{{ route('cliente.team.index') }}" class="flex flex-col items-center gap-1 text-slate-400 hover:text-slate-200">
                    <span class="text-lg">👥</span>
                    <span class="text-[10px] font-bold">Equipo</span>
                </a>
                <a href="{{ route('cliente.withdrawals.index') }}" class="flex flex-col items-center gap-1 text-cyan-400">
                    <span class="text-lg">💸</span>
                    <span class="text-[10px] font-bold">Retirar</span>
                </a>
            </div>
        </nav>
    @endauth

    <!-- ========================================== -->
    <!-- MODAL UNIFICADO DE AUTENTICACIÓN (LOGIN & REGISTRO) -->
    <!-- ========================================== -->
    <div id="authModal" class="fixed inset-0 bg-black/85 backdrop-blur-md z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-900/95 border border-slate-800 rounded-3xl p-4 sm:p-8 max-w-md w-full shadow-2xl relative max-h-[92vh] overflow-y-auto">
            
            <!-- Botón Cerrar -->
            <button onclick="closeAuthModal()" class="absolute right-4 top-4 sm:right-5 sm:top-5 text-slate-400 hover:text-white text-2xl font-bold transition cursor-pointer">
                ✕
            </button>

            <!-- Logo Superior -->
            <div class="text-center mb-5">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 text-white font-black text-2xl shadow-lg shadow-emerald-500/25 mb-2">
                    💎
                </div>
                <h3 id="modalTitle" class="text-xl font-extrabold text-white">Acceso a la Plataforma</h3>
                <p id="modalSubtitle" class="text-xs text-slate-400 mt-0.5">Ingresa o crea tu cuenta en segundos</p>
            </div>

            <!-- Pestañas de Selección: Login / Registro -->
            <div class="grid grid-cols-2 p-1 bg-slate-950 rounded-2xl border border-slate-800 mb-5 text-xs font-bold">
                <button type="button" id="tabLoginBtn" onclick="switchAuthTab('login')" class="py-2.5 rounded-xl transition cursor-pointer text-slate-400">
                    🔑 Iniciar Sesión
                </button>
                <button type="button" id="tabRegisterBtn" onclick="switchAuthTab('register')" class="py-2.5 rounded-xl transition cursor-pointer text-slate-400">
                    🚀 Registrarse
                </button>
            </div>

            @if ($errors->any())
                <div class="mb-4 p-3.5 bg-rose-500/15 border border-rose-500/30 rounded-2xl text-rose-300 text-xs">
                    <div class="flex items-center gap-2 font-bold mb-1.5 text-rose-400">
                        <span>⚠️</span> Atención:
                    </div>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- FORMULARIO 1: INICIAR SESIÓN -->
            <div id="loginFormSection">
                <form method="POST" action="{{ route('login') }}" class="space-y-4 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Correo Electrónico</label>
                        <input id="modal_login_email" type="email" name="email" value="{{ old('email') }}" required placeholder="usuario@ejemplo.com" class="w-full px-4 py-3 bg-slate-950/70 border border-slate-800 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Contraseña</label>
                        <input id="modal_login_pass" type="password" name="password" required placeholder="••••••••" class="w-full px-4 py-3 bg-slate-950/70 border border-slate-800 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500">
                    </div>

                    <div class="flex items-center justify-between text-[11px] text-slate-400">
                        <div class="flex items-center gap-1.5">
                            <input type="checkbox" name="remember" id="modal_remember" class="rounded bg-slate-950 border-slate-800 text-emerald-500 focus:ring-emerald-500">
                            <label for="modal_remember" class="cursor-pointer">Recordarme</label>
                        </div>
                        <button type="button" onclick="openWelcomeForgotModal()" class="text-emerald-400 hover:text-emerald-300 font-bold transition hover:underline cursor-pointer">
                            ¿Olvidaste tu contraseña?
                        </button>
                    </div>

                    <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-bold rounded-xl shadow-lg shadow-emerald-500/25 transition active:scale-95 text-xs sm:text-sm cursor-pointer">
                        Iniciar Sesión
                    </button>
                </form>
            </div>

            <!-- FORMULARIO 2: REGISTRO -->
            <div id="registerFormSection" class="hidden">
                <form method="POST" action="{{ route('register') }}" class="space-y-3.5 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1">Nombre Completo</label>
                        <input type="text" name="name" required placeholder="Ej: Juan Pérez" class="w-full px-4 py-2.5 bg-slate-950/70 border border-slate-800 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1">Correo Electrónico</label>
                        <input type="email" name="email" required placeholder="tu@correo.com" class="w-full px-4 py-2.5 bg-slate-950/70 border border-slate-800 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1">Número de Celular (Nequi / Daviplata)</label>
                        <input type="text" name="phone" required placeholder="Ej: 3001234567" class="w-full px-4 py-2.5 bg-slate-950/70 border border-slate-800 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1">Código de Referido / Patrocinador</label>
                        <input id="modalRefInput" type="text" name="referral_code" value="{{ old('referral_code', request('ref') ?: request('referral_code') ?: request('referred_by')) }}" placeholder="Opcional (Ej: VIP-777)" class="w-full px-4 py-2.5 bg-slate-950/70 border border-slate-800 rounded-xl text-emerald-400 font-mono uppercase focus:outline-none focus:border-emerald-500">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1">Contraseña</label>
                            <input type="password" name="password" required placeholder="••••••••" class="w-full px-4 py-2.5 bg-slate-950/70 border border-slate-800 rounded-xl text-white focus:outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1">Confirmar</label>
                            <input type="password" name="password_confirmation" required placeholder="••••••••" class="w-full px-4 py-2.5 bg-slate-950/70 border border-slate-800 rounded-xl text-white focus:outline-none focus:border-emerald-500">
                        </div>
                    </div>

                    <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-emerald-500 via-teal-500 to-cyan-500 hover:from-emerald-600 hover:to-cyan-600 text-white font-extrabold rounded-xl shadow-lg shadow-emerald-500/25 transition active:scale-95 text-xs sm:text-sm cursor-pointer mt-2">
                        Crear Mi Cuenta VIP 🚀
                    </button>
                </form>
            </div>

        </div>
    </div>

    <!-- Modal Recuperar Contraseña por WhatsApp desde la Bienvenida -->
    <div id="welcomeForgotModal" class="fixed inset-0 bg-black/90 backdrop-blur-md z-[70] hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-sm w-full p-4 sm:p-6 shadow-2xl relative text-xs max-h-[92vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center font-bold text-sm">
                        💬
                    </div>
                    <h3 class="text-sm font-black text-white">Recuperar Contraseña</h3>
                </div>
                <button type="button" onclick="closeWelcomeForgotModal()" class="text-slate-400 hover:text-white text-base font-bold transition cursor-pointer">✕</button>
            </div>

            <p class="text-slate-400 text-[11px] mb-4 leading-relaxed">
                Ingresa tu <strong>número de celular / WhatsApp</strong> registrado. Te conectaremos con el <strong>Soporte Oficial de FORTEX</strong> para validar tu cuenta y entregarte una nueva contraseña al instante.
            </p>

            <div class="space-y-3">
                <div>
                    <label class="block font-semibold text-slate-300 uppercase tracking-wider text-[10px] mb-1">Tu Número de Celular / WhatsApp</label>
                    <div class="flex items-center bg-slate-950/80 border border-slate-800 rounded-xl focus-within:border-emerald-500 overflow-hidden">
                        <select id="welcomeForgotCountryCode" class="bg-slate-900 text-white text-xs font-semibold px-2 sm:px-3 py-2.5 border-r border-slate-800 focus:outline-none cursor-pointer">
                            <option value="+57" selected>🇨🇴 +57 (CO)</option>
                            <option value="+52">🇲🇽 +52 (MX)</option>
                            <option value="+51">🇵🇪 +51 (PE)</option>
                            <option value="+593">🇪🇨 +593 (EC)</option>
                            <option value="+58">🇻🇪 +58 (VE)</option>
                            <option value="+54">🇦🇷 +54 (AR)</option>
                            <option value="+56">🇨🇱 +56 (CL)</option>
                            <option value="+591">🇧🇴 +591 (BO)</option>
                            <option value="+1">🇺🇸 +1 (US)</option>
                            <option value="+34">🇪🇸 +34 (ES)</option>
                            <option value="+507">🇵🇦 +507 (PA)</option>
                            <option value="+506">🇨🇷 +506 (CR)</option>
                            <option value="+1">🇩🇴 +1 (DO)</option>
                            <option value="+502">🇬🇹 +502 (GT)</option>
                            <option value="+504">🇭🇳 +504 (HN)</option>
                            <option value="+503">🇸🇻 +503 (SV)</option>
                            <option value="+505">🇳🇮 +505 (NI)</option>
                            <option value="+595">🇵🇾 +595 (PY)</option>
                            <option value="+598">🇺🇾 +598 (UY)</option>
                            <option value="+55">🇧🇷 +55 (BR)</option>
                        </select>
                        <input type="tel" id="welcomeForgotPhone" placeholder="Ej: 3222216725" class="w-full bg-transparent text-white placeholder-slate-600 focus:outline-none font-mono text-xs px-3 py-2.5">
                    </div>
                    <span class="text-[10px] text-slate-500 mt-1 block">Toca la bandera para cambiar el código de país.</span>
                </div>

                <button type="button" onclick="sendWelcomeWhatsAppRecovery()" class="w-full py-3 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-black rounded-xl shadow-lg shadow-emerald-500/25 transition flex items-center justify-center gap-2 text-xs cursor-pointer active:scale-95">
                    <span>💬</span> Solicitar Clave por WhatsApp
                </button>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-800/80 text-[10px] text-slate-500 text-center">
                🔒 Atención humana y segura 24/7 sin intermediarios
            </div>
        </div>
    </div>

    <!-- SCRIPTS INTERACTIVOS -->
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true,
            customClass: { popup: 'swal-custom-dark' }
        });

        function notifyCopied(text = '¡Enlace copiado al portapapeles!') {
            Toast.fire({
                icon: 'success',
                title: text
            });
        }

        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: '¡Operación Exitosa!',
                text: "{{ session('success') }}",
                customClass: { popup: 'swal-custom-dark' },
                confirmButtonColor: '#10b981',
                confirmButtonText: '¡Excelente!'
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Atención',
                text: "{{ session('error') }}",
                customClass: { popup: 'swal-custom-dark' },
                confirmButtonColor: '#f43f5e',
                confirmButtonText: 'Entendido'
            });
        @endif

        @if ($errors->any())
            document.addEventListener('DOMContentLoaded', function() {
                openAuthModal('{{ old("name") || old("phone") || old("referral_code") || old("referred_by") ? "register" : "login" }}');
            });
        @elseif (request('ref') || request('referral_code') || request('auth') === 'register')
            document.addEventListener('DOMContentLoaded', function() {
                openAuthModal('register');
            });
        @elseif (request('auth') === 'login')
            document.addEventListener('DOMContentLoaded', function() {
                openAuthModal('login');
            });
        @endif

        // Client-side fallback to read URL params if needed
        document.addEventListener('DOMContentLoaded', function() {
            const params = new URLSearchParams(window.location.search);
            const refParam = params.get('ref') || params.get('referral_code') || params.get('referred_by');
            if (refParam) {
                const refInput = document.getElementById('modalRefInput');
                if (refInput && !refInput.value) {
                    refInput.value = refParam;
                }
                openAuthModal('register');
            }
        });

        // Dropdown de Usuario VIP
        function toggleUserDropdown() {
            const menu = document.getElementById('userDropdownMenu');
            if (menu) {
                menu.classList.toggle('hidden');
            }
        }

        document.addEventListener('click', function(e) {
            const container = document.getElementById('userMenuDropdownContainer');
            const menu = document.getElementById('userDropdownMenu');
            if (container && menu && !container.contains(e.target)) {
                menu.classList.add('hidden');
            }
        });

        // Modal Auth
        function openAuthModal(tab = 'login') {
            document.getElementById('authModal').classList.remove('hidden');
            switchAuthTab(tab);
        }

        function closeAuthModal() {
            document.getElementById('authModal').classList.add('hidden');
        }

        function switchAuthTab(tab) {
            const loginSection = document.getElementById('loginFormSection');
            const registerSection = document.getElementById('registerFormSection');
            const tabLoginBtn = document.getElementById('tabLoginBtn');
            const tabRegisterBtn = document.getElementById('tabRegisterBtn');

            if (tab === 'login') {
                loginSection.classList.remove('hidden');
                registerSection.classList.add('hidden');

                tabLoginBtn.className = 'py-2.5 rounded-xl transition cursor-pointer bg-emerald-500 text-slate-950 font-extrabold shadow-md';
                tabRegisterBtn.className = 'py-2.5 rounded-xl transition cursor-pointer text-slate-400 hover:text-white';
            } else {
                loginSection.classList.add('hidden');
                registerSection.classList.remove('hidden');

                tabRegisterBtn.className = 'py-2.5 rounded-xl transition cursor-pointer bg-emerald-500 text-slate-950 font-extrabold shadow-md';
                tabLoginBtn.className = 'py-2.5 rounded-xl transition cursor-pointer text-slate-400 hover:text-white';
            }
        }

        document.getElementById('authModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAuthModal();
            }
        });

        function openWelcomeForgotModal() {
            closeAuthModal();
            document.getElementById('welcomeForgotModal').classList.remove('hidden');
        }

        function closeWelcomeForgotModal() {
            document.getElementById('welcomeForgotModal').classList.add('hidden');
        }

        function sendWelcomeWhatsAppRecovery() {
            const countryCode = document.getElementById('welcomeForgotCountryCode').value;
            const phone = document.getElementById('welcomeForgotPhone').value.trim();
            if (!phone) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atención',
                    text: 'Por favor ingresa tu número de celular o WhatsApp',
                    customClass: { popup: 'swal-custom-dark' },
                    confirmButtonColor: '#f59e0b'
                });
                return;
            }
            const fullPhone = `${countryCode} ${phone}`;
            const supportNumber = "{{ env('SUPPORT_WHATSAPP', '573115138588') }}";
            const msg = encodeURIComponent(`Hola Soporte FORTEX 🟢, solicito recuperar la contraseña de mi cuenta registrada con el celular: ${fullPhone}`);
            window.open(`https://api.whatsapp.com/send?phone=${supportNumber}&text=${msg}`, '_blank');
        }

        // Calculadora
        const slider = document.getElementById('calcSlider');
        const amountDisplay = document.getElementById('amountDisplay');
        const dailyResult = document.getElementById('dailyResult');
        const monthlyResult = document.getElementById('monthlyResult');
        const roiResult = document.getElementById('roiResult');

        if (slider) {
            slider.addEventListener('input', function() {
                const amount = parseInt(this.value);
                const daily = amount * 0.06;
                const monthly = daily * 30;

                amountDisplay.innerText = `$${amount.toLocaleString('es-CO')} COP`;
                dailyResult.innerText = `$${daily.toLocaleString('es-CO')} COP`;
                monthlyResult.innerText = `$${monthly.toLocaleString('es-CO')} COP`;
                roiResult.innerText = '180%';
            });
        }

        // Contador de 24 Horas en Vivo
        function startCountdownTimers() {
            const timers = document.querySelectorAll('.countdown-timer');
            timers.forEach(timer => {
                let seconds = parseInt(timer.getAttribute('data-seconds'), 10);
                if (isNaN(seconds) || seconds <= 0) {
                    timer.innerText = "¡Listo para reclamar!";
                    return;
                }

                const updateTimer = () => {
                    if (seconds <= 0) {
                        timer.innerText = "¡Listo para reclamar!";
                        setTimeout(() => window.location.reload(), 1500);
                        return;
                    }
                    const h = Math.floor(seconds / 3600);
                    const m = Math.floor((seconds % 3600) / 60);
                    const s = seconds % 60;
                    timer.innerText = `${h.toString().padStart(2, '0')}h ${m.toString().padStart(2, '0')}m ${s.toString().padStart(2, '0')}s`;
                    seconds--;
                    setTimeout(updateTimer, 1000);
                };
                updateTimer();
            });
        }
        document.addEventListener('DOMContentLoaded', startCountdownTimers);
    </script>
</body>
</html>
