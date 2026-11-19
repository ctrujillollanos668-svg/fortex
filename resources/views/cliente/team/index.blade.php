@extends('layouts.cliente')

@section('title', 'Mi Equipo y Red')

@section('content')
<div class="space-y-6">

    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white flex items-center gap-2">
                <span>👥</span> Mi Red y Equipo
            </h1>
            <p class="text-xs text-slate-400 mt-0.5">Gana comisiones pasivas de hasta 3 niveles por cada persona invitada.</p>
        </div>
        <div class="self-start sm:self-auto px-3.5 py-1.5 bg-slate-900 border border-slate-800 rounded-2xl flex items-center gap-2">
            <span class="text-[11px] text-slate-400">Total Comisiones:</span>
            <p class="text-sm sm:text-base font-extrabold text-emerald-400 font-mono">${{ number_format($totalCommissions, 0, ',', '.') }} COP</p>
        </div>
    </div>

    <!-- Tarjeta del Enlace de Invitación -->
    <div class="bg-gradient-to-br from-slate-900 via-slate-900 to-cyan-950/40 border border-slate-800 rounded-3xl p-4 sm:p-6 shadow-2xl relative overflow-hidden">
        <h3 class="text-sm font-bold text-white mb-1">🔗 Tu Enlace Único de Patrocinador</h3>
        <p class="text-xs text-slate-400 mb-3">Las personas que se registren con este link quedarán vinculadas directamente bajo tu red.</p>

        <div class="flex items-center gap-2 bg-slate-950 p-2 rounded-2xl border border-slate-800">
            <input id="refInput" type="text" readonly value="{{ url('/register?ref=' . $user->referral_code) }}" class="bg-transparent border-0 text-xs font-mono text-emerald-400 px-2 flex-1 min-w-0 truncate focus:outline-none select-all">
            <button onclick="copyRefLink()" class="shrink-0 px-3 sm:px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold rounded-xl text-xs transition cursor-pointer">
                📋 Copiar Link
            </button>
        </div>

        <!-- Botones de Redes Sociales -->
        <div class="grid grid-cols-2 gap-3 mt-4">
            @php
                $shareText = urlencode("¡Únete a mi equipo en FORTEX y gana rendimientos diarios en pesos colombianos! Regístrate aquí: " . url('/register?ref=' . $user->referral_code));
            @endphp
            <a href="https://api.whatsapp.com/send?text={{ $shareText }}" target="_blank" class="py-2.5 px-3 bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-400 border border-emerald-500/30 rounded-xl text-xs font-bold text-center transition flex items-center justify-center gap-2">
                <span>💬</span> WhatsApp
            </a>
            <a href="https://t.me/share/url?url={{ urlencode(url('/register?ref=' . $user->referral_code)) }}&text={{ urlencode('¡Gana rendimientos diarios con tu membresía VIP!') }}" target="_blank" class="py-2.5 px-3 bg-cyan-600/20 hover:bg-cyan-600/30 text-cyan-400 border border-cyan-500/30 rounded-xl text-xs font-bold text-center transition flex items-center justify-center gap-2">
                <span>✈️</span> Telegram
            </a>
        </div>
    </div>

    <!-- Estructura de tu Pirámide (Tus Niveles) -->
    <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-3.5 sm:p-5">
        <h3 class="text-sm font-bold text-white mb-3 sm:mb-4 flex items-center gap-2">
            <span>🔺</span> Estructura de tu Red Piramidal
        </h3>

        <div class="grid grid-cols-3 gap-2 sm:gap-3">
            <!-- Nivel 1 -->
            <div class="bg-slate-950/80 border border-emerald-500/30 rounded-2xl p-2.5 sm:p-4 text-center">
                <span class="px-1.5 sm:px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 text-[9px] sm:text-[10px] font-extrabold uppercase truncate block">Nivel 1 (10%)</span>
                <h4 class="text-lg sm:text-2xl font-black text-white mt-1.5 sm:mt-2 font-mono">{{ $directReferrals->count() }}</h4>
                <p class="text-[9px] sm:text-[11px] text-slate-400 truncate mt-0.5">Directos</p>
            </div>

            <!-- Nivel 2 -->
            <div class="bg-slate-950/80 border border-cyan-500/30 rounded-2xl p-2.5 sm:p-4 text-center">
                <span class="px-1.5 sm:px-2.5 py-0.5 rounded-full bg-cyan-500/20 text-cyan-400 text-[9px] sm:text-[10px] font-extrabold uppercase truncate block">Nivel 2 (2%)</span>
                <h4 class="text-lg sm:text-2xl font-black text-white mt-1.5 sm:mt-2 font-mono">{{ $level2Referrals->count() }}</h4>
                <p class="text-[9px] sm:text-[11px] text-slate-400 truncate mt-0.5">De tu Red</p>
            </div>

            <!-- Nivel 3 -->
            <div class="bg-slate-950/80 border border-purple-500/30 rounded-2xl p-2.5 sm:p-4 text-center">
                <span class="px-1.5 sm:px-2.5 py-0.5 rounded-full bg-purple-500/20 text-purple-400 text-[9px] sm:text-[10px] font-extrabold uppercase truncate block">Nivel 3 (2%)</span>
                <h4 class="text-lg sm:text-2xl font-black text-white mt-1.5 sm:mt-2 font-mono">0</h4>
                <p class="text-[9px] sm:text-[11px] text-slate-400 truncate mt-0.5">Profunda</p>
            </div>
        </div>
    </div>

    <!-- Lista de Miembros Directos -->
    <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-5">
        <h3 class="text-sm font-bold text-white mb-3 flex items-center gap-2">
            <span>👥</span> Tus Invitados Directos (Nivel 1)
        </h3>

        @if($directReferrals->isEmpty())
            <p class="text-xs text-slate-500 py-6 text-center">Aún no tienes personas registradas con tu enlace. Comparte tu link para empezar a ganar.</p>
        @else
            <div class="divide-y divide-slate-800/80">
                @foreach($directReferrals as $ref)
                    <div class="py-3 flex items-center justify-between text-xs">
                        <div>
                            <p class="font-bold text-white">{{ $ref->name }}</p>
                            <p class="text-[11px] text-slate-400 font-mono">{{ $ref->email }}</p>
                        </div>
                        <div class="text-right">
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 font-mono font-bold text-[10px]">
                                {{ $ref->userPlans->count() }} plan(es) activos
                            </span>
                            <span class="block text-[10px] text-slate-500 mt-0.5">Unido el {{ $ref->created_at->format('d/m/Y') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>

<script>
    function copyRefLink() {
        const input = document.getElementById('refInput');
        input.select();
        navigator.clipboard.writeText(input.value);
        notifyCopied('¡Enlace de referido copiado al portapapeles!');
    }
</script>
@endsection
