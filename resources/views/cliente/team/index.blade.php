@extends('layouts.cliente')

@section('title', 'Mi Equipo y Red')

@section('content')
<div class="space-y-6">

    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white flex items-center gap-2">
                <span>👥</span> Mi Red y Comisiones
            </h1>
            <p class="text-xs text-slate-400 mt-0.5">Gana comisiones en efectivo por cada amigo que recargue y active un Plan VIP.</p>
        </div>
        <div class="self-start sm:self-auto px-4 py-2 bg-gradient-to-r from-emerald-950/80 to-slate-900 border border-emerald-500/40 rounded-2xl flex items-center gap-2.5 shadow-lg">
            <span class="text-xs text-slate-300">Total Comisiones Ganadas:</span>
            <p class="text-base sm:text-lg font-black text-emerald-400 font-mono">${{ number_format($totalCommissions, 0, ',', '.') }} COP</p>
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
            <a href="https://t.me/{{ env('SUPPORT_TELEGRAM', 'SoporteXoficial') }}" target="_blank" class="py-2.5 px-3 bg-cyan-600/20 hover:bg-cyan-600/30 text-cyan-400 border border-cyan-500/30 rounded-xl text-xs font-bold text-center transition flex items-center justify-center gap-2 cursor-pointer">
                <span>✈️</span> Telegram
            </a>
        </div>
    </div>

    <!-- Tarjeta Destacada de Ganancias en Comisiones -->
    <div class="bg-gradient-to-br from-emerald-950/40 via-slate-900 to-slate-900 border border-emerald-500/30 rounded-3xl p-4 sm:p-5 shadow-xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
            <div>
                <span class="text-[10px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                    💸 Dinero en Efectivo
                </span>
                <h3 class="text-base font-extrabold text-white mt-1.5 flex items-center gap-1.5">
                    <span>💰</span> Resumen de Comisiones Ganadas
                </h3>
                <p class="text-[11px] text-slate-400">Todo el dinero ganado por comisiones de red se suma a tu saldo disponible y es <strong>100% retirable</strong>.</p>
            </div>
            <div class="text-left sm:text-right bg-slate-950/80 px-4 py-2.5 rounded-2xl border border-slate-800">
                <span class="text-[10px] text-slate-400 block font-medium">Total Ganado en Red:</span>
                <span class="text-xl sm:text-2xl font-black text-emerald-400 font-mono">${{ number_format($totalCommissions, 0, ',', '.') }} COP</span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1 border-t border-slate-800/80">
            <div class="bg-slate-950/70 p-3 rounded-2xl border border-emerald-500/20 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-white flex items-center gap-1.5">
                        <span class="text-emerald-400">🥇</span> Nivel 1 (Directos - 10%)
                    </span>
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Ganado por tus invitados directos</span>
                </div>
                <div class="text-right">
                    <span class="text-sm sm:text-base font-black text-emerald-400 font-mono">+${{ number_format($commL1, 0, ',', '.') }} COP</span>
                </div>
            </div>

            <div class="bg-slate-950/70 p-3 rounded-2xl border border-cyan-500/20 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-white flex items-center gap-1.5">
                        <span class="text-cyan-400">🥈</span> Nivel 2 (Red Indirecta - 2%)
                    </span>
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Ganado por invitados de tus directos</span>
                </div>
                <div class="text-right">
                    <span class="text-sm sm:text-base font-black text-cyan-400 font-mono">+${{ number_format($commL2, 0, ',', '.') }} COP</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Estructura de tu Red Piramidal -->
    <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-3.5 sm:p-5">
        <h3 class="text-sm font-bold text-white mb-3 sm:mb-4 flex items-center gap-2">
            <span>🔺</span> Estructura de tu Red
        </h3>

        <div class="grid grid-cols-3 gap-2 sm:gap-3">
            <!-- Nivel 1 -->
            <div class="bg-slate-950/80 border border-emerald-500/30 rounded-2xl p-2.5 sm:p-4 text-center">
                <span class="px-1.5 sm:px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 text-[9px] sm:text-[10px] font-extrabold uppercase truncate block">Nivel 1 (10%)</span>
                <h4 class="text-lg sm:text-2xl font-black text-white mt-1.5 sm:mt-2 font-mono">{{ $directReferrals->count() }}</h4>
                <p class="text-[9px] sm:text-[11px] text-slate-400 truncate mt-0.5">Directos</p>
                <div class="mt-1.5 text-[10px] font-mono text-emerald-400 font-bold bg-slate-900/80 px-1 py-0.5 rounded border border-emerald-500/20">
                    +${{ number_format($commL1, 0, ',', '.') }}
                </div>
            </div>

            <!-- Nivel 2 -->
            <div class="bg-slate-950/80 border border-cyan-500/30 rounded-2xl p-2.5 sm:p-4 text-center">
                <span class="px-1.5 sm:px-2.5 py-0.5 rounded-full bg-cyan-500/20 text-cyan-400 text-[9px] sm:text-[10px] font-extrabold uppercase truncate block">Nivel 2 (2%)</span>
                <h4 class="text-lg sm:text-2xl font-black text-white mt-1.5 sm:mt-2 font-mono">{{ $level2Referrals->count() }}</h4>
                <p class="text-[9px] sm:text-[11px] text-slate-400 truncate mt-0.5">De tu Red</p>
                <div class="mt-1.5 text-[10px] font-mono text-cyan-400 font-bold bg-slate-900/80 px-1 py-0.5 rounded border border-cyan-500/20">
                    +${{ number_format($commL2, 0, ',', '.') }}
                </div>
            </div>

            <!-- Nivel 3 -->
            <div class="bg-slate-950/80 border border-purple-500/30 rounded-2xl p-2.5 sm:p-4 text-center">
                <span class="px-1.5 sm:px-2.5 py-0.5 rounded-full bg-purple-500/20 text-purple-400 text-[9px] sm:text-[10px] font-extrabold uppercase truncate block">Nivel 3 (2%)</span>
                <h4 class="text-lg sm:text-2xl font-black text-white mt-1.5 sm:mt-2 font-mono">0</h4>
                <p class="text-[9px] sm:text-[11px] text-slate-400 truncate mt-0.5">Profunda</p>
                <div class="mt-1.5 text-[10px] font-mono text-slate-500 font-bold bg-slate-900/80 px-1 py-0.5 rounded border border-slate-800">
                    $0 COP
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Miembros Directos (Nivel 1) -->
    <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-4 sm:p-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <span>👥</span> Tus Invitados Directos (Nivel 1 - 10%)
            </h3>
            <span class="text-xs text-slate-400 font-mono">{{ $directReferrals->count() }} personas</span>
        </div>

        @if($directReferrals->isEmpty())
            <div class="text-center py-8 bg-slate-950/40 rounded-2xl border border-slate-800/60">
                <span class="text-3xl block mb-2">👥</span>
                <p class="text-xs text-slate-400">Aún no tienes personas registradas con tu enlace.</p>
                <p class="text-[11px] text-slate-500 mt-0.5">¡Comparte tu link de patrocinador por WhatsApp y empieza a ganar comisiones!</p>
            </div>
        @else
            <div class="divide-y divide-slate-800/80">
                @foreach($directReferrals as $ref)
                    <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                        <div class="flex items-start gap-2.5">
                            <div class="w-8 h-8 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-emerald-400 text-xs shrink-0 mt-0.5">
                                {{ strtoupper(substr($ref->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-bold text-white text-sm sm:text-xs">{{ $ref->name }}</p>
                                <p class="text-[11px] text-slate-400 font-mono">{{ $ref->email }}</p>
                                <span class="text-[10px] text-slate-500 mt-0.5 block">Registrado el {{ $ref->created_at->format('d/m/Y') }}</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between sm:justify-end gap-3 sm:text-right pt-2 sm:pt-0 border-t sm:border-0 border-slate-800/60">
                            <div>
                                @if($ref->plans_count > 0)
                                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 font-mono font-bold text-[10px] inline-block border border-emerald-500/30">
                                        ⚡ {{ $ref->plans_count }} plan(es) activo(s)
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full bg-slate-800 text-slate-400 font-mono font-medium text-[10px] inline-block">
                                        0 planes activos
                                    </span>
                                @endif
                            </div>

                            <div class="text-right">
                                @if($ref->commission_generated > 0)
                                    <div class="text-emerald-400 font-mono font-black text-xs sm:text-sm">
                                        +${{ number_format($ref->commission_generated, 0, ',', '.') }} COP
                                    </div>
                                    <span class="text-[9px] text-emerald-500/90 font-medium block">Comisión Ganada (10%)</span>
                                @else
                                    <div class="text-slate-500 font-mono font-medium text-xs">
                                        $0 COP
                                    </div>
                                    <span class="text-[9px] text-slate-500 block">Sin recarga aún</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Fin de Lista de Directos -->
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
