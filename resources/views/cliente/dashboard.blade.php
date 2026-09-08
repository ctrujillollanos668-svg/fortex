@extends('layouts.cliente')

@section('title', 'App VIP')

@section('content')
<div class="max-w-lg mx-auto space-y-4 pb-12">

    <!-- 1. ENCABEZADO DE LA APP: LOGO + SELECTOR + SALDO RÁPIDO -->
    <div class="flex items-center justify-between py-1 px-1">
        <div class="flex items-center gap-2.5">
            <div class="w-10 h-10 rounded-2xl bg-black border border-emerald-500/30 flex items-center justify-center overflow-hidden shadow-lg shadow-emerald-500/25">
                <img src="{{ asset('img/fortex.jpg') }}" alt="FORTEX" class="w-full h-full object-cover">
            </div>
            <div>
                <h1 class="text-base font-black text-white tracking-tight leading-none">FORTEX</h1>
                <span class="text-[9px] text-emerald-400 font-bold uppercase tracking-wider flex items-center gap-1 mt-0.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Servidores Cloud Verificados
                </span>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <!-- Chip Idioma / Moneda -->
            <div class="flex items-center gap-1 px-2.5 py-1 bg-slate-900 border border-slate-800 rounded-xl text-[11px] font-bold text-slate-300">
                <span>🇨🇴</span>
                <span>COP</span>
            </div>

            <!-- Soporte Flotante Directo -->
            <button onclick="openSupportModal()" class="w-8 h-8 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-800 flex items-center justify-center text-slate-300 hover:text-emerald-400 transition cursor-pointer" title="Centro de Ayuda">
                🎧
            </button>
        </div>
    </div>

    <!-- 2. CARRUSEL / BANNER PROMOCIONAL CORPORATIVO (ESTILO APP NATIVA) -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-emerald-950 via-slate-900 to-cyan-950 border border-emerald-500/30 shadow-2xl p-5 min-h-[160px] flex flex-col justify-between">
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-400 text-[10px] font-extrabold border border-emerald-500/30 mb-2">
                <span>⭐</span> COMUNIDAD OFICIAL
            </div>
            <h2 class="text-lg sm:text-xl font-black text-white leading-tight">
                Invierte y gana del <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-cyan-400 font-mono">5% al 7% diario</span>
            </h2>
            <p class="text-[11px] text-slate-300 mt-1 max-w-[280px]">
                Retiros automáticos 24/7 a Nequi, Daviplata y Bancolombia.
            </p>
        </div>

        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-3 border-t border-slate-800/80 mt-2">
            <div class="flex flex-col">
                <div class="flex items-center gap-2">
                    <span class="text-[10px] text-slate-400">Saldo Total:</span>
                    <span class="text-base font-black text-emerald-400 font-mono user-balance-display transition-all duration-300">${{ number_format(Auth::user()->balance, 0, ',', '.') }} COP</span>
                </div>
                @if(Auth::user()->uninvestedDeposit() > 0)
                    <span class="text-[9px] text-cyan-400 font-medium">Retirable (ganancias): ${{ number_format(Auth::user()->withdrawableBalance(), 0, ',', '.') }} COP</span>
                @endif
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <a href="{{ route('cliente.deposits.index') }}" class="flex-1 sm:flex-initial text-center px-3.5 py-1.5 bg-gradient-to-r from-emerald-500 to-teal-400 text-slate-950 font-black rounded-xl text-xs shadow-md transition active:scale-95">
                    ➕ Recargar
                </a>
                <a href="{{ route('cliente.withdrawals.index') }}" class="flex-1 sm:flex-initial text-center px-3.5 py-1.5 bg-slate-900 border border-slate-700 hover:border-cyan-500/40 text-cyan-300 font-bold rounded-xl text-xs transition active:scale-95">
                    💸 Retirar
                </a>
            </div>
        </div>
    </div>

    <!-- 3. BARRA DE AVISOS CON ALTAVOZ (📢 SPEAKER MARQUEE NOTICES) -->
    <div class="flex items-center gap-2.5 bg-slate-900/90 border border-slate-800 rounded-2xl px-3.5 py-2.5 shadow-md overflow-hidden">
        <div class="w-6 h-6 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center text-xs flex-shrink-0">
            📢
        </div>
        <div class="overflow-hidden whitespace-nowrap text-xs text-slate-300 flex-1">
            <div class="animate-marquee inline-block">
                <span>¡Bienvenidos a la plataforma oficial FORTEX! • Servidores de procesamiento y rendimientos en COP • Retiros a Nequi y Daviplata en menos de 15 minutos • Gana 10% directo por cada amigo invitado •</span>
            </div>
        </div>
    </div>

    <!-- 4. REJILLA DE 6 ICONOS DE ACCIÓN PRINCIPALES (3x2 PERFECTAMENTE ALINEADOS) -->
    <div class="grid grid-cols-3 gap-2 sm:gap-2.5 bg-slate-900/70 border border-slate-800/90 rounded-3xl p-2.5 sm:p-3.5 shadow-xl text-center">
        <!-- 1. Retiro -->
        <a href="{{ route('cliente.withdrawals.index') }}" class="group flex flex-col items-center justify-center p-1.5 sm:p-2 rounded-2xl hover:bg-slate-800/60 transition active:scale-95">
            <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-gradient-to-tr from-cyan-500/20 to-blue-500/20 border border-cyan-500/40 text-cyan-300 flex items-center justify-center text-lg sm:text-xl mb-1 shadow-md group-hover:scale-110 transition">
                💸
            </div>
            <span class="text-[11px] sm:text-xs font-bold text-slate-200">Retiro</span>
            <span class="text-[8px] sm:text-[9px] text-slate-500 font-semibold truncate max-w-full px-0.5">Mín $15.000</span>
        </a>

        <!-- 2. Recarga -->
        <a href="{{ route('cliente.deposits.index') }}" class="group flex flex-col items-center justify-center p-1.5 sm:p-2 rounded-2xl hover:bg-slate-800/60 transition active:scale-95">
            <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-gradient-to-tr from-emerald-500/20 to-teal-500/20 border border-emerald-500/40 text-emerald-300 flex items-center justify-center text-lg sm:text-xl mb-1 shadow-md group-hover:scale-110 transition">
                💳
            </div>
            <span class="text-[11px] sm:text-xs font-bold text-slate-200">Recarga</span>
            <span class="text-[8px] sm:text-[9px] text-slate-500 font-semibold truncate max-w-full px-0.5">Nequi / QR</span>
        </a>

        <!-- 3. Planes -->
        <a href="{{ route('cliente.plans.index') }}" class="group flex flex-col items-center justify-center p-1.5 sm:p-2 rounded-2xl hover:bg-slate-800/60 transition active:scale-95">
            <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-gradient-to-tr from-purple-500/20 to-indigo-500/20 border border-purple-500/40 text-purple-300 flex items-center justify-center text-lg sm:text-xl mb-1 shadow-md group-hover:scale-110 transition">
                ⚡
            </div>
            <span class="text-[11px] sm:text-xs font-bold text-slate-200">Planes</span>
            <span class="text-[8px] sm:text-[9px] text-emerald-400 font-semibold truncate max-w-full px-0.5">Rendimientos</span>
        </a>

        <!-- 4. Centro de Ayuda -->
        <button type="button" onclick="openSupportModal()" class="group flex flex-col items-center justify-center p-1.5 sm:p-2 rounded-2xl hover:bg-slate-800/60 transition active:scale-95 cursor-pointer">
            <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-gradient-to-tr from-sky-500/20 to-cyan-500/20 border border-sky-500/40 text-sky-300 flex items-center justify-center text-lg sm:text-xl mb-1 shadow-md group-hover:scale-110 transition">
                👥
            </div>
            <span class="text-[11px] sm:text-xs font-bold text-slate-200">Ayuda</span>
            <span class="text-[8px] sm:text-[9px] text-slate-500 font-semibold truncate max-w-full px-0.5">Soporte 24/7</span>
        </button>

        <!-- 5. Invitar -->
        <a href="{{ route('cliente.team.index') }}" class="group flex flex-col items-center justify-center p-1.5 sm:p-2 rounded-2xl hover:bg-slate-800/60 transition active:scale-95">
            <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-gradient-to-tr from-amber-500/20 to-yellow-500/20 border border-amber-500/40 text-amber-300 flex items-center justify-center text-lg sm:text-xl mb-1 shadow-md group-hover:scale-110 transition">
                🔗
            </div>
            <span class="text-[11px] sm:text-xs font-bold text-slate-200">Invitar</span>
            <span class="text-[8px] sm:text-[9px] text-slate-500 font-semibold truncate max-w-full px-0.5">10% Directo</span>
        </a>

        <!-- 6. Sobre Nosotros -->
        <button type="button" onclick="openAboutModal()" class="group flex flex-col items-center justify-center p-1.5 sm:p-2 rounded-2xl hover:bg-slate-800/60 transition active:scale-95 cursor-pointer">
            <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-gradient-to-tr from-teal-500/20 to-emerald-500/20 border border-teal-500/40 text-teal-300 flex items-center justify-center text-lg sm:text-xl mb-1 shadow-md group-hover:scale-110 transition">
                ℹ️
            </div>
            <span class="text-[11px] sm:text-xs font-bold text-slate-200">Nosotros</span>
            <span class="text-[8px] sm:text-[9px] text-slate-500 font-semibold truncate max-w-full px-0.5">Seguridad</span>
        </button>
    </div>

    <!-- 5. BANNER HORIZONTAL: CERTIFICACIÓN Y SEGURIDAD OFICIAL -->
    <div onclick="openAboutModal()" class="bg-gradient-to-r from-emerald-950/90 via-slate-900 to-teal-950 border border-emerald-500/40 hover:border-emerald-400/80 rounded-2xl p-3 sm:p-3.5 flex items-center justify-between shadow-xl shadow-emerald-950/40 cursor-pointer transition active:scale-[0.98]">
        <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 pr-2">
            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 flex items-center justify-center text-lg sm:text-xl flex-shrink-0 shadow-inner">
                🛡️
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-1.5">
                    <h3 class="text-xs sm:text-sm font-extrabold text-white truncate">Certificado y Licencia de Operación</h3>
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>
                </div>
                <p class="text-[9px] sm:text-[10px] text-slate-300 truncate">Auditoría 2026 • Fondos respaldados</p>
            </div>
        </div>
        <div class="shrink-0 flex items-center gap-1 text-emerald-400 text-[10px] sm:text-xs font-black bg-emerald-500/15 border border-emerald-500/30 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-xl">
            <span>Verificado</span>
            <span class="text-xs sm:text-sm">✓</span>
        </div>
    </div>

    <!-- 6. TARJETAS DE INTERACCIÓN: RULETA DE LA SUERTE & SOBRE ROJO -->
    <div class="grid grid-cols-2 gap-3">
        <!-- Ruleta de la Suerte -->
        <div onclick="openRouletteModal()" class="bg-gradient-to-br from-amber-950/80 via-slate-900 to-slate-900 border border-amber-500/40 rounded-2xl p-3.5 cursor-pointer hover:border-amber-400 transition shadow-lg flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-2xl">🎡</span>
                <span class="text-[9px] px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-400 font-extrabold">DIARIO</span>
            </div>
            <div>
                <h4 class="text-xs font-bold text-white">Ruleta de la suerte &gt;</h4>
                <p class="text-[10px] text-slate-400 mt-0.5">Gana bonos en efectivo</p>
            </div>
        </div>

        <!-- Sobre Rojo / Bono -->
        <div onclick="openRedPacketModal()" class="bg-gradient-to-br from-rose-950/80 via-slate-900 to-slate-900 border border-rose-500/40 rounded-2xl p-3.5 cursor-pointer hover:border-rose-400 transition shadow-lg flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-2xl">🧧</span>
                <span class="text-[9px] px-2 py-0.5 rounded-full bg-rose-500/20 text-rose-400 font-extrabold">REGALO</span>
            </div>
            <div>
                <h4 class="text-xs font-bold text-white">Sobre rojo VIP &gt;</h4>
                <p class="text-[10px] text-slate-400 mt-0.5">Bono de bienvenida</p>
            </div>
        </div>
    </div>

    <!-- 7. ACCESO RÁPIDO A TUS PAQUETES ACTIVOS (SI TIENE PAQUETES COMPRADOS) -->
    @if($userPlans->count() > 0)
        <a href="{{ route('cliente.plans.my-plans') }}" class="block bg-gradient-to-r from-emerald-950/90 via-slate-900 to-cyan-950/90 border border-emerald-500/40 hover:border-emerald-400 rounded-3xl p-4 sm:p-5 shadow-xl transition active:scale-[0.98]">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-[#00E599] flex items-center justify-center text-2xl shadow-inner shrink-0">
                        ⚡
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-[#00E599] text-[10px] font-black uppercase tracking-wider border border-emerald-500/30">
                                {{ $userPlans->count() }} {{ $userPlans->count() == 1 ? 'PAQUETE ACTIVO' : 'PAQUETES ACTIVOS' }}
                            </span>
                            <span class="w-2 h-2 rounded-full bg-[#00E599] animate-pulse"></span>
                        </div>
                        <h4 class="text-sm sm:text-base font-black text-white mt-1">Tus Paquetes Comprados</h4>
                        <p class="text-[11px] text-slate-300">Generando rendimientos diarios • Reclama tu dinero cada 24h</p>
                    </div>
                </div>
                <div class="shrink-0 text-right">
                    <span class="px-3.5 py-2 bg-[#00D287] hover:bg-[#00BF7A] text-slate-950 font-black text-xs rounded-xl shadow-md transition inline-flex items-center gap-1">
                        <span>Ver y Reclamar</span>
                        <span>→</span>
                    </span>
                </div>
            </div>
        </a>
    @endif

    <!-- 8. CATÁLOGO DE PLANES VIP (COMPRA CON SALDO DISPONIBLE) -->
    <div class="space-y-3 pt-2">
        <div class="flex items-center justify-between px-1 mb-1">
            <h3 class="text-sm sm:text-base font-extrabold text-white flex items-center gap-2">
                <span class="text-yellow-400">⭐</span> Membresías VIP Disponibles
            </h3>
            <span class="text-xs text-slate-400 font-medium">Valores en $ COP</span>
        </div>

        <div class="space-y-4">
            @foreach($availablePlans as $plan)
                <div class="bg-[#0b1222]/90 border border-slate-800/90 rounded-[28px] p-5 shadow-2xl space-y-3.5 transition hover:border-slate-700 {{ $plan->isSoldOut() ? 'opacity-60 grayscale' : '' }}">
                    <!-- Top Row: Badge left, Percentage right -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            @php
                                $rawBadge = trim($plan->badge ?? 'BÁSICO');
                                $badgeIcon = '';
                                if(stripos($rawBadge, 'popular') !== false) {
                                    $badgeIcon = '🔥 ';
                                } elseif(stripos($rawBadge, 'recomendado') !== false) {
                                    $badgeIcon = '💎 ';
                                } elseif(stripos($rawBadge, 'exclusivo') !== false || stripos($rawBadge, 'vip') !== false) {
                                    $badgeIcon = '👑 ';
                                }
                            @endphp
                            <span class="px-3.5 py-1 rounded-full bg-[#1e293b]/90 border border-slate-700/60 text-slate-200 text-[10px] sm:text-[11px] font-extrabold uppercase tracking-wider">
                                {{ $badgeIcon }}{{ $rawBadge }}
                            </span>
                            @if($plan->isSoldOut())
                                <span class="px-2.5 py-1 rounded-full bg-rose-500/20 text-rose-400 text-[10px] font-bold uppercase border border-rose-500/30">
                                    🔴 Agotado
                                </span>
                            @elseif($plan->hasStockLimit())
                                <span class="px-2.5 py-1 rounded-full bg-amber-500/20 text-amber-300 text-[10px] font-bold uppercase border border-amber-500/30">
                                    ⚡ Quedan {{ $plan->stock }} cupos
                                </span>
                            @endif
                        </div>
                        <span class="text-sm font-bold text-[#00E599] font-mono tracking-tight">{{ number_format($plan->daily_percentage, 2) }}% diario</span>
                    </div>

                    <!-- Middle Row: Plan Name & Subtitle left, Price & COP right -->
                    <div class="flex items-center justify-between pt-1">
                        <div>
                            <h4 class="text-base sm:text-lg font-black text-white tracking-tight">{{ $plan->name }}</h4>
                            <p class="text-xs text-slate-400 mt-0.5">Paga ${{ number_format(($plan->price * $plan->daily_percentage) / 100, 0, ',', '.') }} COP / día ({{ $plan->duration_days }} días)</p>
                        </div>
                        <div class="text-right">
                            <span class="text-xl sm:text-2xl font-black text-white font-mono tracking-tight">${{ number_format($plan->price, 0, ',', '.') }}</span>
                            <span class="text-[11px] text-slate-400 font-semibold block uppercase tracking-wider">COP</span>
                        </div>
                    </div>

                    <!-- Bottom Row: Full width Mint/Emerald Button -->
                    <div class="pt-1">
                        @if($plan->isSoldOut())
                            <button type="button" disabled class="w-full py-3.5 bg-slate-800 text-slate-500 font-bold rounded-2xl text-xs sm:text-sm cursor-not-allowed border border-slate-700">
                                ❌ Agotado (Sin cupos)
                            </button>
                        @else
                            <button type="button" onclick="openBuyModalDashboard({{ $plan->id }}, '{{ addslashes($plan->name) }}', {{ $plan->price }}, '{{ number_format($plan->price, 0, ',', '.') }}')" class="w-full py-3.5 bg-[#00D287] hover:bg-[#00BF7A] text-slate-950 font-black rounded-2xl text-xs sm:text-sm shadow-lg shadow-[#00D287]/20 transition duration-200 active:scale-[0.98] flex items-center justify-center gap-1.5 cursor-pointer">
                                <span>⚡</span> Activar {{ $plan->name }}
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="pt-2 text-center">
            <a href="{{ route('cliente.plans.index') }}" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-2xl bg-slate-900 hover:bg-slate-800 border border-slate-800 hover:border-emerald-500/40 text-emerald-400 font-bold text-xs transition active:scale-95 shadow-lg">
                <span>⚡ Ver catálogo completo de planes</span>
                <span>→</span>
            </a>
        </div>
    </div>

</div>

<!-- ========================================== -->
<!-- MODALES INTERACTIVOS (SOPORTE, RULETA, SOBRE ROJO, LEGALIDAD) -->
<!-- ========================================== -->

<!-- Modal Selección de Saldo para Comprar Plan (Dashboard) -->
<div id="chooseWalletModalDashboard" class="fixed inset-0 bg-black/85 backdrop-blur-md z-50 hidden flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-md w-full p-4 sm:p-7 shadow-2xl relative text-xs space-y-4 max-h-[94vh] overflow-y-auto">
        <div class="flex items-start justify-between">
            <div>
                <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 text-[10px] font-extrabold uppercase">Activar Membresía</span>
                <h3 id="dashModalPlanName" class="text-lg font-black text-white mt-1">Nombre del Plan</h3>
                <p class="text-xs text-slate-400 mt-0.5">Costo: <strong id="dashModalPlanPrice" class="text-emerald-400 font-mono text-sm">$0 COP</strong></p>
            </div>
            <button type="button" onclick="closeChooseWalletModalDashboard()" class="text-slate-400 hover:text-white text-base font-bold transition cursor-pointer">✕</button>
        </div>

        <p class="text-slate-300 text-xs font-semibold">
            ¿Con qué saldo deseas activar este plan? Elige una opción:
        </p>

        <form id="dashConfirmBuyForm" method="POST" action="">
            @csrf
            <input type="hidden" name="payment_source" id="dashSelectedPaymentSource" value="">

            <div class="space-y-2.5">
                <!-- Opción 1: Saldo de Recargas -->
                <div id="dashCardWalletDeposit" onclick="selectWalletDashboard('deposit')" class="p-3.5 bg-slate-950 border-2 border-slate-800 rounded-2xl cursor-pointer transition flex items-center justify-between hover:border-slate-700">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-500/20 text-blue-400 flex items-center justify-center text-lg">
                            💳
                        </div>
                        <div>
                            <h4 class="font-black text-white text-xs">Saldo de Recargas</h4>
                            <span class="text-[11px] text-slate-400 font-mono block">Disponible: ${{ number_format($rechargeBalance, 0, ',', '.') }} COP</span>
                        </div>
                    </div>
                    <div id="dashBadgeWalletDeposit"></div>
                </div>

                <!-- Opción 2: Saldo de Ganancias -->
                <div id="dashCardWalletEarnings" onclick="selectWalletDashboard('earnings')" class="p-3.5 bg-slate-950 border-2 border-slate-800 rounded-2xl cursor-pointer transition flex items-center justify-between hover:border-slate-700">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-lg">
                            💎
                        </div>
                        <div>
                            <h4 class="font-black text-white text-xs">Saldo de Ganancias (Re-inversión)</h4>
                            <span class="text-[11px] text-emerald-400 font-mono block">Disponible: ${{ number_format($earningsBalance, 0, ',', '.') }} COP</span>
                        </div>
                    </div>
                    <div id="dashBadgeWalletEarnings"></div>
                </div>
            </div>

            <div id="dashWalletErrorMsg" class="hidden mt-3 p-3 bg-rose-500/10 border border-rose-500/30 rounded-xl text-rose-300 text-[11px]"></div>

            <div class="pt-3 space-y-2">
                <button type="submit" id="dashBtnConfirmBuy" disabled class="w-full py-3.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-slate-950 font-black rounded-2xl shadow-lg shadow-emerald-500/25 transition active:scale-95 text-xs sm:text-sm cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                    ⚡ Confirmar y Activar Plan
                </button>
                <button type="button" onclick="closeChooseWalletModalDashboard()" class="w-full py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold rounded-2xl text-xs transition cursor-pointer">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Soporte / Centro de Ayuda -->
<div id="supportModal" class="fixed inset-0 bg-black/85 backdrop-blur-md z-50 hidden flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-6 max-w-sm w-full shadow-2xl relative text-center max-h-[94vh] overflow-y-auto">
        <button onclick="closeSupportModal()" class="absolute right-4 top-4 text-slate-400 hover:text-white text-xl font-bold">✕</button>
        <span class="text-4xl block mb-2">🎧</span>
        <h3 class="text-base font-extrabold text-white">Centro de Ayuda y Quejas VIP</h3>
        <p class="text-xs text-slate-400 mt-1 mb-3">¿Tienes dudas, solicitudes o quejas con tus recargas, planes o retiros? Escríbenos.</p>

        <!-- Horario de Atención Oficial -->
        <div class="p-3 bg-emerald-500/10 border border-emerald-500/30 rounded-2xl text-left mb-3">
            <div class="flex items-center gap-1.5 font-bold text-emerald-400 text-xs mb-1">
                <span>⏰</span> Horario de Atención y Quejas
            </div>
            <p class="text-[11px] text-emerald-200/90 leading-tight">
                <strong>Lunes a Domingo:</strong> 8:00 AM a 8:00 PM (Hora Colombia). Nuestros asesores oficiales responderán tu solicitud a la brevedad.
            </p>
        </div>
        
        <div class="space-y-2.5 text-xs">
            <a href="https://api.whatsapp.com/send?phone={{ env('SUPPORT_WHATSAPP', '573115138588') }}&text={{ urlencode('Hola Soporte FORTEX 🟢, tengo una consulta/queja sobre mi cuenta.') }}" target="_blank" class="w-full py-3 bg-emerald-500/20 hover:bg-emerald-500/30 border border-emerald-500/40 text-emerald-300 font-bold rounded-xl flex items-center justify-center gap-2 transition">
                <span>💬</span> WhatsApp Oficial (8:00 AM - 8:00 PM)
            </a>
            <a href="https://t.me/+{{ env('SUPPORT_TELEGRAM', '573115138588') }}" target="_blank" class="w-full py-3 bg-cyan-500/20 hover:bg-cyan-500/30 border border-cyan-500/40 text-cyan-300 font-bold rounded-xl flex items-center justify-center gap-2 transition">
                <span>✈️</span> Telegram Soporte (8:00 AM - 8:00 PM)
            </a>
        </div>
    </div>
</div>

<!-- Modal Sobre Nosotros, Certificación y Seguridad Oficial -->
<div id="aboutModal" class="fixed inset-0 bg-black/90 backdrop-blur-md z-50 hidden flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 hover:border-emerald-500/40 rounded-3xl p-5 sm:p-7 max-w-md w-full shadow-2xl relative max-h-[92vh] overflow-y-auto">
        <button onclick="closeAboutModal()" class="absolute right-4 top-4 text-slate-400 hover:text-white text-xl font-bold transition cursor-pointer">✕</button>
        
        <!-- Insignia Superior de Verificación -->
        <div class="flex flex-col items-center text-center mb-4">
            <div class="relative mb-2">
                <div class="w-16 h-16 rounded-2xl bg-black border-2 border-emerald-500/50 p-1 flex items-center justify-center shadow-xl shadow-emerald-500/20">
                    <img src="{{ asset('img/fortex.jpg') }}" alt="FORTEX Logo" class="w-full h-full object-cover rounded-xl">
                </div>
                <span class="absolute -bottom-1 -right-1 bg-emerald-500 text-slate-950 text-[11px] font-black w-5 h-5 flex items-center justify-center rounded-full border-2 border-slate-900 shadow">
                    ✓
                </span>
            </div>
            
            <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 text-[10px] font-black tracking-widest uppercase mb-1">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> INFRAESTRUCTURA CLOUD VERIFICADA
            </span>
            <h3 class="text-base sm:text-lg font-black text-white">FORTEX</h3>
            <p class="text-[11px] text-slate-400">Plataforma Oficial de Inversión y Cómputo Cloud</p>
        </div>

        <!-- Recuadro del Certificado Oficial -->
        <div class="bg-slate-950 p-4 rounded-2xl border border-slate-800 space-y-3 mb-4 text-xs">
            <!-- Datos de Red y Servidores -->
            <div class="grid grid-cols-2 gap-2 pb-3 border-b border-slate-800/80 text-[11px]">
                <div>
                    <span class="text-slate-500 text-[10px] block font-semibold">Licencia Operativa:</span>
                    <span class="font-mono font-bold text-emerald-400">FTX-2026-CLOUD</span>
                </div>
                <div>
                    <span class="text-slate-500 text-[10px] block font-semibold">Seguridad de Red:</span>
                    <span class="font-mono font-bold text-white">Tier IV Enterprise</span>
                </div>
                <div>
                    <span class="text-slate-500 text-[10px] block font-semibold">Disponibilidad SLA:</span>
                    <span class="font-bold text-slate-300">99.98% Activo</span>
                </div>
                <div>
                    <span class="text-slate-500 text-[10px] block font-semibold">Protocolo de Cifrado:</span>
                    <span class="font-bold text-amber-400">TLS 1.3 / AES-256</span>
                </div>
            </div>

            <!-- Garantías y Protocolos Clave -->
            <div class="space-y-2.5 text-[11px] text-slate-300">
                <div class="flex items-start gap-2.5">
                    <span class="text-base leading-none">🛡️</span>
                    <div>
                        <strong class="text-white">Garantía de Retiros Automatizados:</strong>
                        <p class="text-slate-400 text-[10px] leading-relaxed mt-0.5">Pagos directos en Colombia a cuentas Bancolombia, Nequi y Daviplata con liquidación prioritaria en menos de 15 minutos.</p>
                    </div>
                </div>

                <div class="flex items-start gap-2.5">
                    <span class="text-base leading-none">🖥️</span>
                    <div>
                        <strong class="text-white">Infraestructura de Servidores FORTEX:</strong>
                        <p class="text-slate-400 text-[10px] leading-relaxed mt-0.5">Tu inversión participa en la capacidad operativa de centros de datos y computación gráfica de alto rendimiento con 99.98% de disponibilidad.</p>
                    </div>
                </div>

                <div class="flex items-start gap-2.5">
                    <span class="text-base leading-none">🔐</span>
                    <div>
                        <strong class="text-white">Seguridad y Cifrado Bancario:</strong>
                        <p class="text-slate-400 text-[10px] leading-relaxed mt-0.5">Conexión cifrada bajo el estándar internacional TLS 1.3 con clave criptográfica AES de 256 bits, garantizando la confidencialidad de tu saldo y transferencias.</p>
                    </div>
                </div>
            </div>

            <!-- Sello Digital de Autenticidad -->
            <div class="pt-2 border-t border-slate-800/80 flex items-center justify-between text-[10px] text-slate-500 font-mono">
                <span>Licencia de Red: #FTX-8840-CO</span>
                <span class="text-emerald-400 font-bold flex items-center gap-1">
                    <span>🔒</span> Servidor Verificado y En Línea
                </span>
            </div>
        </div>

        <!-- Botón de Confirmación Seguro -->
        <button onclick="closeAboutModal()" class="w-full py-3 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-slate-950 font-black rounded-xl text-xs sm:text-sm transition active:scale-95 shadow-lg shadow-emerald-500/25 flex items-center justify-center gap-2 cursor-pointer">
            <span>🛡️</span> Confirmar y Continuar Seguro
        </button>
    </div>
</div>

<!-- Modal Ruleta de la Suerte VIP -->
<div id="rouletteModal" class="fixed inset-0 bg-black/85 backdrop-blur-md z-50 hidden flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-amber-500/30 rounded-3xl p-4 sm:p-6 max-w-sm w-full shadow-2xl relative text-center max-h-[94vh] overflow-y-auto">
        <button onclick="closeRouletteModal()" class="absolute right-4 top-4 text-slate-400 hover:text-white text-xl font-bold cursor-pointer">✕</button>
        
        <div class="flex items-center justify-center gap-2 mb-1">
            <span class="text-2xl">🎡</span>
            <h3 class="text-base font-black text-white">Ruleta de la Suerte VIP</h3>
        </div>
        
        @php
            $userSpins = (Auth::user()->last_spin_at === null && (Auth::user()->roulette_spins === null || Auth::user()->roulette_spins <= 0)) ? 1 : (Auth::user()->roulette_spins ?? 0);
        @endphp

        <!-- Contador de Giros Disponibles -->
        <div class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/40 text-xs font-black mb-3">
            <span>🎟️</span> Giros Disponibles: <span id="spinsLeftBadge" class="text-white font-mono text-sm">{{ $userSpins }}</span>
        </div>

        <!-- RUEDA GIRATORIA VISUAL CON PREMIOS -->
        <div class="relative w-56 h-56 sm:w-64 sm:h-64 mx-auto mb-3 flex items-center justify-center">
            <!-- Puntero Superior Dorado -->
            <div class="absolute -top-3 left-1/2 -translate-x-1/2 z-20 w-0 h-0 border-l-[10px] border-l-transparent border-r-[10px] border-r-transparent border-t-[20px] border-t-amber-400 filter drop-shadow-[0_4px_8px_rgba(245,158,11,0.8)]"></div>

            <!-- Disco Circular de la Ruleta -->
            <div id="rouletteWheel" class="w-full h-full rounded-full border-4 border-amber-400/80 shadow-[0_0_30px_rgba(245,158,11,0.3)] relative overflow-hidden transition-all duration-[4000ms] ease-out flex items-center justify-center select-none" style="background: conic-gradient(#10b981 0deg 45deg, #06b6d4 45deg 90deg, #f59e0b 90deg 135deg, #ec4899 135deg 180deg, #8b5cf6 180deg 225deg, #ef4444 225deg 270deg, #14b8a6 270deg 315deg, #3b82f6 315deg 360deg);">
                
                <!-- Etiquetas de Premios Centradas Radialmente (0 a 7) -->
                <div class="absolute inset-0 text-[10px] font-black text-white pointer-events-none">
                    <!-- Slice 0 (0°-45°) -->
                    <div class="absolute top-0 left-1/2 w-16 -ml-8 h-1/2 pt-2 text-center origin-bottom font-mono font-bold" style="transform: rotate(22.5deg);">$1.000</div>
                    <!-- Slice 1 (45°-90°) -->
                    <div class="absolute top-0 left-1/2 w-16 -ml-8 h-1/2 pt-2 text-center origin-bottom font-mono font-bold" style="transform: rotate(67.5deg);">$2.000</div>
                    <!-- Slice 2 (90°-135°) -->
                    <div class="absolute top-0 left-1/2 w-16 -ml-8 h-1/2 pt-2 text-center origin-bottom font-mono font-bold" style="transform: rotate(112.5deg);">$5.000</div>
                    <!-- Slice 3 (135°-180°) -->
                    <div class="absolute top-0 left-1/2 w-16 -ml-8 h-1/2 pt-2 text-center origin-bottom font-mono font-bold" style="transform: rotate(157.5deg);">$9.000</div>
                    <!-- Slice 4 (180°-225°) -->
                    <div class="absolute top-0 left-1/2 w-16 -ml-8 h-1/2 pt-2 text-center origin-bottom font-mono font-bold" style="transform: rotate(202.5deg);">$500</div>
                    <!-- Slice 5 (225°-270°) Premio Mayor -->
                    <div class="absolute top-0 left-1/2 w-20 -ml-10 h-1/2 pt-1.5 text-center origin-bottom text-yellow-300 font-mono font-black text-[9px] leading-tight" style="transform: rotate(247.5deg);">👑 $13.000</div>
                    <!-- Slice 6 (270°-315°) -->
                    <div class="absolute top-0 left-1/2 w-16 -ml-8 h-1/2 pt-2 text-center origin-bottom font-mono font-bold" style="transform: rotate(292.5deg);">$3.000</div>
                    <!-- Slice 7 (315°-360°) -->
                    <div class="absolute top-0 left-1/2 w-16 -ml-8 h-1/2 pt-2 text-center origin-bottom font-mono font-bold" style="transform: rotate(337.5deg);">$1.000</div>
                </div>

                <!-- Botón Central de Giro -->
                <button id="spinBtn" type="button" onclick="spinRoulette()" class="absolute z-10 w-16 h-16 rounded-full bg-slate-950 border-4 border-amber-400 text-amber-400 font-black text-xs flex flex-col items-center justify-center shadow-2xl hover:scale-105 active:scale-95 transition cursor-pointer">
                    <span>GIRAR</span>
                    <span class="text-[8px] text-slate-400 font-mono" id="spinCenterLabel">{{ $userSpins > 0 ? $userSpins . 'x' : '0x' }}</span>
                </button>
            </div>
        </div>

        <div id="rouletteStatusMessage" class="text-[11px] text-amber-400/90 font-medium mb-3">
            {{ $userSpins > 0 ? '¡Presiona GIRAR para probar tu suerte!' : '¡Invita amigos para ganar más giros!' }}
        </div>

        <!-- Banner Explicativo de Dinámica de Recarga y Giros -->
        <div class="bg-slate-950 p-3 rounded-2xl border border-slate-800 text-left text-[11px] space-y-1.5">
            <div class="flex items-center justify-between text-amber-300 font-bold">
                <span class="flex items-center gap-1.5"><span>⚡</span> Recompensas de la Ruleta</span>
                <span class="text-[9px] px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 font-mono">👑 Hasta $13.000 COP</span>
            </div>
            <p class="text-slate-300 text-[10px] leading-relaxed">
                🎉 <strong>¡Premios en efectivo garantizados en cada giro!</strong> Gira la ruleta y recibe ganancias directas a tu saldo de hasta <strong class="text-emerald-400 font-bold">$13.000 COP</strong>. Recarga tu cuenta para ganar <strong class="text-cyan-300">+3 Giros Gratis</strong> o comparte tu enlace de referido para obtener más oportunidades.
            </p>
            <div class="grid grid-cols-2 gap-2 pt-1">
                <a href="{{ route('cliente.deposits.index') }}" class="py-2 bg-gradient-to-r from-emerald-500 to-teal-400 text-slate-950 font-black text-center rounded-xl text-[11px] shadow-md transition active:scale-95">
                    ➕ Recargar (+3 Giros)
                </a>
                <a href="{{ route('cliente.team.index') }}" class="py-2 bg-slate-800 border border-slate-700 hover:border-amber-500/40 text-amber-300 font-bold text-center rounded-xl text-[11px] transition active:scale-95">
                    🔗 Invitar (+2 Giros)
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Sobre Rojo VIP & Canje de Código -->
<div id="redPacketModal" class="fixed inset-0 bg-black/85 backdrop-blur-md z-50 hidden flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-rose-500/30 rounded-3xl p-4 sm:p-6 max-w-sm w-full shadow-2xl relative text-center max-h-[94vh] overflow-y-auto">
        <button onclick="closeRedPacketModal()" class="absolute right-4 top-4 text-slate-400 hover:text-white text-xl font-bold cursor-pointer">✕</button>
        
        <div class="flex items-center justify-center gap-2 mb-1">
            <span class="text-2xl animate-pulse">🧧</span>
            <h3 class="text-base font-black text-white">Sobre Rojo de Recompensas</h3>
        </div>
        <p class="text-[11px] text-slate-400 mb-4">Abre tu bono de bienvenida sorpresa o canjea un código exclusivo.</p>

        <!-- SOBRE ROJO ANIMADO 3D -->
        <div class="bg-gradient-to-br from-rose-950 via-red-900 to-rose-950 border-2 border-rose-500/50 rounded-3xl p-5 mb-4 shadow-xl relative overflow-hidden group">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-amber-400/20 border border-amber-400/40 text-amber-400 flex items-center justify-center text-3xl mb-2 shadow-inner animate-bounce">
                🧧
            </div>
            <h4 class="text-sm font-black text-white">Bono de Bienvenida VIP</h4>
            <p class="text-[10px] text-rose-200 mt-0.5">¡Reclama tu regalo especial para nuevos miembros!</p>

            <button id="openWelcomePacketBtn" onclick="claimWelcomePacket()" class="mt-3 w-full py-2.5 bg-gradient-to-r from-amber-400 to-yellow-400 hover:from-amber-500 hover:to-yellow-500 text-slate-950 font-black rounded-xl text-xs shadow-lg transition active:scale-95 cursor-pointer">
                🎁 ¡Abrir Mi Sobre Rojo!
            </button>
        </div>

        <!-- SECCIÓN 2: CANJEAR CÓDIGO DE SORTEO O REGALO -->
        <div class="bg-slate-950 p-4 rounded-2xl border border-slate-800 text-left">
            <label class="block text-[11px] font-bold text-slate-300 mb-1.5 flex items-center justify-between">
                <span>¿Tienes un Código de Sorteo?</span>
                <span class="text-[9px] text-rose-400 font-bold">Oficial</span>
            </label>
            
            <div class="flex gap-2">
                <input type="text" id="promoCodeInput" placeholder="EJ: FORTEX-8492" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-white font-mono uppercase text-xs focus:outline-none focus:border-rose-500">
                <button onclick="claimPromoCode()" class="px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs whitespace-nowrap transition cursor-pointer active:scale-95">
                    Canjear
                </button>
            </div>
            <span class="text-[9px] text-slate-500 mt-1.5 block leading-tight">
                Ingresa el código exclusivo entregado por el Administrador en dinámicas y sorteos oficiales de Telegram o WhatsApp.
            </span>
        </div>
    </div>
</div>

<script>
    function openSupportModal() { document.getElementById('supportModal').classList.remove('hidden'); }
    function closeSupportModal() { document.getElementById('supportModal').classList.add('hidden'); }

    function openAboutModal() { document.getElementById('aboutModal').classList.remove('hidden'); }
    function closeAboutModal() { document.getElementById('aboutModal').classList.add('hidden'); }

    let hasSpunInSession = false;

    function openRouletteModal() { document.getElementById('rouletteModal').classList.remove('hidden'); }
    function closeRouletteModal() { 
        document.getElementById('rouletteModal').classList.add('hidden'); 
        if (hasSpunInSession) {
            window.location.reload();
        }
    }

    function openRedPacketModal() { document.getElementById('redPacketModal').classList.remove('hidden'); }
    function closeRedPacketModal() { document.getElementById('redPacketModal').classList.add('hidden'); }

    // ==========================================
    // LÓGICA DE GIRO DE LA RULETA DE LA SUERTE
    // ==========================================
    let isSpinning = false;
    let currentRotation = 0;

    async function spinRoulette() {
        if (isSpinning) return;

        const spinBtn = document.getElementById('spinBtn');
        const wheel = document.getElementById('rouletteWheel');
        const statusMsg = document.getElementById('rouletteStatusMessage');

        isSpinning = true;
        spinBtn.disabled = true;
        spinBtn.classList.add('opacity-50', 'cursor-not-allowed');
        statusMsg.innerText = '⚡ Girando la ruleta de la suerte...';

        try {
            const response = await fetch("{{ route('cliente.rewards.spin') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const data = await response.json().catch(() => ({ success: false, message: 'Respuesta inválida del servidor.' }));

            if (!response.ok || !data.success) {
                isSpinning = false;
                spinBtn.disabled = false;
                spinBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                statusMsg.innerText = data.message || 'Intenta más tarde.';
                
                Swal.fire({
                    icon: 'warning',
                    title: 'Ruleta VIP',
                    text: data.message || 'No fue posible procesar el giro.',
                    customClass: { popup: 'swal-custom-dark' },
                    confirmButtonColor: '#f59e0b'
                });
                return;
            }

            // Cada segmento mide 45 grados (360 / 8)
            const segmentDegrees = 45;
            const targetSegment = data.segment_index;
            
            // Calculamos 5 vueltas completas + el ángulo exacto hacia el puntero superior
            const extraRounds = 360 * 5;
            const targetAngle = 360 - (targetSegment * segmentDegrees) - (segmentDegrees / 2);
            currentRotation += extraRounds + targetAngle;

            wheel.style.transform = `rotate(${currentRotation}deg)`;

            setTimeout(() => {
                isSpinning = false;
                hasSpunInSession = true;

                // Actualizar contadores de giros disponibles en vivo dentro del modal
                const spinsRemaining = data.spins_left ?? 0;
                const spinsBadge = document.getElementById('spinsLeftBadge');
                const centerLabel = document.getElementById('spinCenterLabel');
                if (spinsBadge) spinsBadge.innerText = spinsRemaining;
                if (centerLabel) centerLabel.innerText = spinsRemaining > 0 ? `${spinsRemaining}x` : '0x';

                // Actualizar saldo del usuario en tiempo real en la interfaz
                document.querySelectorAll('.user-balance-display').forEach(el => {
                    el.innerText = data.new_balance_formatted;
                });

                // Estado del botón central de la ruleta según los giros que le queden
                if (spinsRemaining > 0) {
                    spinBtn.disabled = false;
                    spinBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    statusMsg.innerText = `🎉 ¡Ganaste ${data.prize_label}! Te quedan ${spinsRemaining} giro${spinsRemaining > 1 ? 's' : ''}. ¡Presiona GIRAR para continuar!`;
                } else {
                    spinBtn.disabled = true;
                    spinBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    statusMsg.innerText = `🎉 ¡Ganaste ${data.prize_label}! Ya no te quedan más giros. ¡Invita amigos para ganar más!`;
                }

                // Alerta de felicitación que NO cierra la ruleta
                Swal.fire({
                    icon: 'success',
                    title: '¡Felicidades!',
                    html: `Has ganado <b class="text-amber-400 text-lg font-mono">+${data.prize_label}</b><br><br>` +
                          `El dinero ya fue acreditado a tu balance disponible.<br>` +
                          (spinsRemaining > 0 
                            ? `<span class="text-emerald-400 font-bold block mt-2 text-xs">🎟️ ¡Aún tienes ${spinsRemaining} oportunidad${spinsRemaining > 1 ? 'es' : ''} más!</span>` 
                            : `<span class="text-slate-400 text-[11px] block mt-2">¡Recarga o invita amigos con tu link para conseguir más giros!</span>`),
                    customClass: { popup: 'swal-custom-dark' },
                    confirmButtonColor: '#10b981',
                    confirmButtonText: spinsRemaining > 0 ? '⚡ Seguir Girando' : '¡Aceptar!'
                });
            }, 4100);

        } catch (err) {
            isSpinning = false;
            spinBtn.disabled = false;
            spinBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            statusMsg.innerText = 'Ocurrió un error. Intenta nuevamente.';
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un inconveniente al comunicarse con el servidor.',
                customClass: { popup: 'swal-custom-dark' },
                confirmButtonColor: '#ef4444'
            });
        }
    }

    // ==========================================
    // LÓGICA DEL SOBRE ROJO Y CANJE DE CÓDIGOS
    // ==========================================
    async function claimWelcomePacket() {
        try {
            const response = await fetch("{{ route('cliente.rewards.red-packet') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({})
            });

            const data = await response.json().catch(() => ({ success: false, message: 'Respuesta inválida del servidor.' }));

            if (!response.ok || !data.success) {
                Swal.fire({
                    icon: 'info',
                    title: 'Sobre Rojo',
                    text: data.message,
                    customClass: { popup: 'swal-custom-dark' },
                    confirmButtonColor: '#ef4444'
                });
                return;
            }

            closeRedPacketModal();

            Swal.fire({
                icon: 'success',
                title: '¡Sobre Rojo Abierto!',
                html: data.message,
                customClass: { popup: 'swal-custom-dark' },
                confirmButtonColor: '#10b981',
                confirmButtonText: '¡Genial!'
            }).then(() => {
                window.location.reload();
            });

        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un error al procesar el sobre rojo.',
                customClass: { popup: 'swal-custom-dark' },
                confirmButtonColor: '#ef4444'
            });
        }
    }

    async function claimPromoCode() {
        const input = document.getElementById('promoCodeInput');
        const code = input.value.trim();

        if (!code) {
            Swal.fire({
                icon: 'warning',
                title: 'Código Requerido',
                text: 'Por favor escribe un código promocional.',
                customClass: { popup: 'swal-custom-dark' },
                confirmButtonColor: '#ef4444'
            });
            return;
        }

        try {
            const response = await fetch("{{ route('cliente.rewards.red-packet') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ code: code })
            });

            const data = await response.json().catch(() => ({ success: false, message: 'Respuesta inválida del servidor.' }));

            if (!response.ok || !data.success) {
                Swal.fire({
                    icon: 'error',
                    title: 'Código No Válido',
                    text: data.message,
                    customClass: { popup: 'swal-custom-dark' },
                    confirmButtonColor: '#ef4444'
                });
                return;
            }

            closeRedPacketModal();
            input.value = '';

            Swal.fire({
                icon: 'success',
                title: '¡Código Canjeado!',
                html: data.message,
                customClass: { popup: 'swal-custom-dark' },
                confirmButtonColor: '#10b981',
                confirmButtonText: '¡Aceptar!'
            }).then(() => {
                window.location.reload();
            });

        } catch (err) {
            alert('Error al canjear el código.');
        }
    }

    // Reclamar Ganancia Diaria instantáneamente sin alertas molestas
    async function handleClaimDaily(event, planId, url) {
        event.preventDefault();
        const btn = document.getElementById(`btn-claim-${planId}`);
        if (!btn || btn.disabled) return;

        btn.disabled = true;
        btn.classList.add('opacity-75', 'cursor-not-allowed');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span>⏳ Acreditando...</span>';

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({})
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                btn.disabled = false;
                btn.classList.remove('opacity-75', 'cursor-not-allowed');
                btn.innerHTML = originalText;
                Swal.fire({
                    icon: 'warning',
                    title: 'Atención',
                    text: data.message || 'No se pudo reclamar en este momento.',
                    customClass: { popup: 'swal-custom-dark' },
                    confirmButtonColor: '#f59e0b'
                });
                return;
            }

            // 1. Actualizar balances en pantalla instantáneamente
            document.querySelectorAll('.user-balance-value').forEach(el => {
                el.innerText = `$${data.new_balance_formatted}`;
            });
            document.querySelectorAll('.user-balance-display').forEach(el => {
                el.innerText = `$${data.new_balance_formatted} COP`;
            });

            // Pequeño realce visual al actualizar saldo
            document.querySelectorAll('.user-balance-display, .user-balance-value').forEach(el => {
                el.classList.add('scale-105', 'text-emerald-300');
                setTimeout(() => el.classList.remove('scale-105', 'text-emerald-300'), 500);
            });

            // 2. Actualizar acumulado ganado y barra de progreso
            const earnedEl = document.getElementById(`plan-earned-${planId}`);
            if (earnedEl) {
                earnedEl.innerText = `$${data.earned_so_far_formatted}`;
            }
            const progressEl = document.getElementById(`plan-progress-${planId}`);
            if (progressEl) {
                progressEl.style.width = `${data.percent}%`;
            }

            // 3. Reemplazar botón por el temporizador de 24 horas sin ninguna ventana de alerta
            const container = document.getElementById(`plan-action-container-${planId}`);
            if (container) {
                if (data.status === 'completed') {
                    container.innerHTML = `
                        <div class="py-2.5 px-3.5 bg-slate-950 border border-emerald-500/30 rounded-xl flex items-center justify-between text-xs">
                            <span class="text-emerald-400 font-bold">✅ Paquete Completado</span>
                            <span class="font-mono text-emerald-400 text-[10px]">100% Retorno</span>
                        </div>
                    `;
                } else {
                    container.innerHTML = `
                        <div class="py-2.5 px-3.5 bg-slate-950 border border-slate-800 rounded-xl flex items-center justify-between text-xs">
                            <span class="text-slate-400">⏳ Próximo reclamo:</span>
                            <span class="countdown-timer font-mono text-amber-400 font-extrabold" data-seconds="${data.next_seconds}">Calculando...</span>
                        </div>
                    `;
                    startCountdownTimers();
                }
            }

        } catch (err) {
            console.error('Error al reclamar ganancia:', err);
            const form = btn.closest('form');
            if (form) form.submit();
        }
    }

    // Cuenta regresiva de 24 horas para reclamos de paquetes
    function startCountdownTimers() {
        const timers = document.querySelectorAll('.countdown-timer');
        timers.forEach(timer => {
            if (timer.dataset.timerRunning === 'true') return;
            timer.dataset.timerRunning = 'true';

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

    // Lógica del Modal de Selección de Saldo en Dashboard (Recarga vs Ganancias)
    const userDashRechargeBalance = {{ (float) $rechargeBalance }};
    const userDashEarningsBalance = {{ (float) $earningsBalance }};
    let currentDashPlanPrice = 0;

    function openBuyModalDashboard(planId, planName, planPrice, planPriceFormatted) {
        currentDashPlanPrice = parseFloat(planPrice);
        document.getElementById('dashModalPlanName').innerText = planName;
        document.getElementById('dashModalPlanPrice').innerText = '$' + planPriceFormatted + ' COP';
        document.getElementById('dashConfirmBuyForm').action = "{{ url('/plans') }}/" + planId + "/buy";
        document.getElementById('dashSelectedPaymentSource').value = '';
        document.getElementById('dashBtnConfirmBuy').disabled = true;
        document.getElementById('dashWalletErrorMsg').classList.add('hidden');

        // Evaluar disponibilidad de Saldo de Recargas
        const canDeposit = userDashRechargeBalance >= currentDashPlanPrice;
        const badgeDep = document.getElementById('dashBadgeWalletDeposit');
        badgeDep.innerHTML = canDeposit 
            ? '<span class="px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-400 text-[10px] font-extrabold">✅ Disponible</span>'
            : '<span class="px-2.5 py-1 rounded-full bg-rose-500/20 text-rose-400 text-[10px] font-extrabold">❌ Insuficiente</span>';

        // Evaluar disponibilidad de Saldo de Ganancias
        const canEarnings = userDashEarningsBalance >= currentDashPlanPrice;
        const badgeEarn = document.getElementById('dashBadgeWalletEarnings');
        badgeEarn.innerHTML = canEarnings 
            ? '<span class="px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-400 text-[10px] font-extrabold">✅ Disponible</span>'
            : '<span class="px-2.5 py-1 rounded-full bg-rose-500/20 text-rose-400 text-[10px] font-extrabold">❌ Insuficiente</span>';

        resetWalletCardsDashboard();

        // Auto-seleccionar la opción que tenga fondos disponibles
        if (canDeposit) {
            selectWalletDashboard('deposit');
        } else if (canEarnings) {
            selectWalletDashboard('earnings');
        } else {
            document.getElementById('dashWalletErrorMsg').innerText = '⚠️ No tienes saldo suficiente en ninguna de las dos fuentes para este plan ($' + planPriceFormatted + ' COP). Por favor recarga saldo primero.';
            document.getElementById('dashWalletErrorMsg').classList.remove('hidden');
        }

        document.getElementById('chooseWalletModalDashboard').classList.remove('hidden');
    }

    function closeChooseWalletModalDashboard() {
        document.getElementById('chooseWalletModalDashboard').classList.add('hidden');
    }

    function resetWalletCardsDashboard() {
        const cardDep = document.getElementById('dashCardWalletDeposit');
        const cardEarn = document.getElementById('dashCardWalletEarnings');
        if (cardDep) {
            cardDep.className = 'p-3.5 bg-slate-950 border-2 border-slate-800 rounded-2xl cursor-pointer transition flex items-center justify-between hover:border-slate-700';
        }
        if (cardEarn) {
            cardEarn.className = 'p-3.5 bg-slate-950 border-2 border-slate-800 rounded-2xl cursor-pointer transition flex items-center justify-between hover:border-slate-700';
        }
    }

    function selectWalletDashboard(source) {
        const canPay = source === 'deposit' ? (userDashRechargeBalance >= currentDashPlanPrice) : (userDashEarningsBalance >= currentDashPlanPrice);
        const errorDiv = document.getElementById('dashWalletErrorMsg');

        resetWalletCardsDashboard();

        if (!canPay) {
            const label = source === 'deposit' ? 'Saldo de Recargas' : 'Saldo de Ganancias';
            errorDiv.innerText = `⚠️ Tu ${label} no alcanza para activar este plan ($${currentDashPlanPrice.toLocaleString('es-CO')} COP).`;
            errorDiv.classList.remove('hidden');
            document.getElementById('dashSelectedPaymentSource').value = '';
            document.getElementById('dashBtnConfirmBuy').disabled = true;
            return;
        }

        errorDiv.classList.add('hidden');
        document.getElementById('dashSelectedPaymentSource').value = source;
        document.getElementById('dashBtnConfirmBuy').disabled = false;

        if (source === 'deposit') {
            document.getElementById('dashCardWalletDeposit').className = 'p-3.5 bg-slate-950 border-2 border-emerald-500 shadow-lg shadow-emerald-500/10 rounded-2xl cursor-pointer transition flex items-center justify-between';
        } else {
            document.getElementById('dashCardWalletEarnings').className = 'p-3.5 bg-slate-950 border-2 border-emerald-500 shadow-lg shadow-emerald-500/10 rounded-2xl cursor-pointer transition flex items-center justify-between';
        }
    }

    // Cerrar modal al hacer clic en el backdrop
    document.getElementById('chooseWalletModalDashboard')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeChooseWalletModalDashboard();
        }
    });
</script>
@endsection
