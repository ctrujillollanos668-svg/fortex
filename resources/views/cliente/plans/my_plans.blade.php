@extends('layouts.cliente')

@section('title', 'Mis Planes Comprados')

@section('content')
<div class="space-y-6 pb-12">

    <!-- Selector de Pestañas: Catálogo vs Mis Planes -->
    <div class="grid grid-cols-2 p-1 bg-slate-900/90 border border-slate-800 rounded-2xl gap-1">
        <a href="{{ route('cliente.plans.index') }}" class="py-2.5 px-3 rounded-xl text-slate-400 hover:text-white font-bold text-xs sm:text-sm text-center transition flex items-center justify-center gap-1.5 hover:bg-slate-800/60">
            <span>⚡</span> Catálogo de Planes
        </a>
        <a href="{{ route('cliente.plans.my-plans') }}" class="py-2.5 px-3 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 text-slate-950 font-black text-xs sm:text-sm text-center shadow-md flex items-center justify-center gap-1.5">
            <span>📦</span> Mis Planes Comprados
        </a>
    </div>

    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white flex items-center gap-2">
                <span>📦</span> Mis Planes Comprados (Rendimientos)
            </h1>
            <p class="text-xs text-slate-400 mt-0.5">Monitorea tus inversiones activas y reclama tu ganancia diaria cada 24 horas.</p>
        </div>
    </div>

    <!-- Resumen de Saldos Disponibles -->
    <div class="grid grid-cols-2 sm:grid-cols-2 gap-3">
        <div class="p-4 bg-slate-900/90 border border-slate-800 rounded-3xl">
            <span class="text-[10px] text-slate-400 uppercase tracking-wider block font-bold">💳 Saldo Recargas</span>
            <p class="text-base sm:text-lg font-black text-white font-mono mt-0.5">${{ number_format($rechargeBalance, 0, ',', '.') }} <span class="text-[10px] text-slate-500 font-normal font-sans">COP</span></p>
            <span class="text-[10px] text-slate-500 block mt-0.5">Exclusivo para activar planes</span>
        </div>
        <div class="p-4 bg-slate-900/90 border border-emerald-500/30 rounded-3xl">
            <span class="text-[10px] text-emerald-400 uppercase tracking-wider block font-bold">💎 Saldo Ganancias</span>
            <p class="text-base sm:text-lg font-black text-[#00E599] font-mono mt-0.5">${{ number_format($earningsBalance, 0, ',', '.') }} <span class="text-[10px] text-slate-500 font-normal font-sans">COP</span></p>
            <span class="text-[10px] text-emerald-400/80 block mt-0.5">Retirable o re-invertible</span>
        </div>
    </div>

    <!-- 1. Tus Planes Activos Comprados -->
    <div class="space-y-4">
        <div class="flex items-center justify-between px-1">
            <h2 class="text-sm font-extrabold text-white flex items-center gap-2">
                <span>🔥</span> Planes en Producción ({{ $activePlans->count() }})
            </h2>
            <span class="text-xs text-[#00E599] font-bold font-mono">{{ $activePlans->count() }} Activos</span>
        </div>

        @if($activePlans->isEmpty())
            <div class="bg-gradient-to-br from-slate-900/80 via-slate-900/60 to-slate-950 border border-slate-800 rounded-3xl p-8 text-center space-y-3">
                <div class="w-16 h-16 rounded-3xl bg-emerald-500/10 border border-emerald-500/20 text-3xl flex items-center justify-center mx-auto text-[#00E599] shadow-inner">
                    📦
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-white">No tienes planes activos en este momento</h3>
                    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">Activa tu primera membresía VIP en el catálogo de planes para comenzar a recibir rendimientos diarios del 5% al 7% en automático.</p>
                </div>
                <div class="pt-2">
                    <a href="{{ route('cliente.plans.index') }}" class="inline-flex items-center gap-2 px-6 py-3.5 bg-[#00D287] hover:bg-[#00BF7A] text-slate-950 font-black rounded-2xl text-xs sm:text-sm shadow-xl shadow-[#00D287]/25 transition active:scale-95">
                        <span>⚡</span> Ir a Comprar Planes VIP →
                    </a>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($activePlans as $up)
                    <div class="bg-[#0b1222]/90 border border-slate-800/90 rounded-[28px] p-5 shadow-2xl space-y-4">
                        <!-- Top Row: Badge ACTIVO left, Rendimiento right -->
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="px-3.5 py-1 rounded-full bg-emerald-500/20 text-[#00E599] text-[10px] sm:text-[11px] font-black uppercase tracking-wider border border-emerald-500/30">
                                    ACTIVO
                                </span>
                                <h4 class="text-base sm:text-lg font-black text-white tracking-tight mt-2">{{ $up->plan->name }}</h4>
                            </div>
                            <div class="text-right">
                                <span class="text-xs text-slate-400 font-medium block">Rendimiento</span>
                                <span class="text-sm sm:text-base font-extrabold text-[#00E599] font-mono">+${{ number_format($up->daily_earning, 0, ',', '.') }} COP / día</span>
                            </div>
                        </div>

                        <!-- Middle Row: Progreso del Tope -->
                        <div class="space-y-1.5">
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-400 font-medium">Progreso del Tope:</span>
                                <span class="font-bold text-slate-200 font-mono text-xs sm:text-sm" id="plan-progress-text-{{ $up->id }}">${{ number_format($up->earned_so_far, 0, ',', '.') }} / ${{ number_format($up->max_earning, 0, ',', '.') }} COP</span>
                            </div>
                            @php
                                $percent = $up->max_earning > 0 ? min(100, round(($up->earned_so_far / $up->max_earning) * 100)) : 0;
                            @endphp
                            <div class="w-full bg-slate-950 rounded-full h-2.5 overflow-hidden border border-slate-800">
                                <div id="plan-progress-bar-{{ $up->id }}" class="bg-gradient-to-r from-[#00E599] to-teal-400 h-full rounded-full transition-all duration-500" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>

                        <!-- Bottom Row: Countdown Pill or Claim Button -->
                        <div id="plan-action-container-{{ $up->id }}" class="pt-1">
                            @if(!$up->canClaim())
                                <div class="py-3 px-4 bg-slate-950 border border-slate-800/90 rounded-2xl flex items-center justify-between text-xs sm:text-sm">
                                    <span class="text-slate-300 font-medium flex items-center gap-1.5">
                                        <span>⏳</span> Próximo reclamo:
                                    </span>
                                    <span class="countdown-timer font-mono text-amber-400 font-black text-xs sm:text-sm tracking-wide" data-seconds="{{ $up->secondsUntilNextClaim() }}">Calculando...</span>
                                </div>
                            @else
                                <form method="POST" action="{{ route('cliente.plans.claim', $up->id) }}" onsubmit="handleClaimDaily(event, {{ $up->id }}, '{{ route('cliente.plans.claim', $up->id) }}')">
                                    @csrf
                                    <button type="submit" id="btn-claim-{{ $up->id }}" class="w-full py-3.5 bg-gradient-to-r from-[#00D287] via-emerald-400 to-[#00D287] hover:brightness-110 text-slate-950 font-black rounded-2xl text-xs sm:text-sm shadow-lg shadow-[#00D287]/25 transition duration-200 active:scale-[0.98] cursor-pointer animate-pulse flex items-center justify-center gap-2">
                                        <span>🎁</span> Reclamar Ganancia de Hoy (+${{ number_format($up->daily_earning, 0, ',', '.') }} COP)
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- 2. Planes Completados (Historial) -->
    @if(isset($completedPlans) && $completedPlans->count() > 0)
        <div class="space-y-3 pt-4 border-t border-slate-800/80">
            <div class="flex items-center justify-between px-1">
                <h2 class="text-xs sm:text-sm font-bold text-slate-400 flex items-center gap-2">
                    <span>🏁</span> Planes Finalizados ({{ $completedPlans->count() }})
                </h2>
                <span class="text-[11px] text-slate-500 font-mono">100% Retorno Cumplido</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach($completedPlans as $cp)
                    <div class="bg-slate-900/50 border border-slate-800/70 rounded-2xl p-4 flex items-center justify-between text-xs opacity-75">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-white">{{ $cp->plan->name }}</span>
                                <span class="px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 text-[9px] font-extrabold uppercase border border-emerald-500/20">Completado</span>
                            </div>
                            <span class="text-[11px] text-slate-400 block mt-1">Ganancia Total: <strong class="text-emerald-400 font-mono font-bold">${{ number_format($cp->earned_so_far, 0, ',', '.') }} COP</strong></span>
                        </div>
                        <div class="text-right">
                            <span class="text-emerald-400 font-black text-sm">✓ Finalizado</span>
                            <span class="text-[10px] text-slate-500 block">{{ $cp->updated_at->format('d/m/Y') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>

<script>
    async function handleClaimDaily(event, planId, url) {
        event.preventDefault();
        const btn = document.getElementById(`btn-claim-${planId}`);
        if (!btn || btn.disabled) return;

        btn.disabled = true;
        btn.classList.add('opacity-75', 'cursor-not-allowed');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span>⏳ Acreditando saldo...</span>';

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
                    text: data.message || 'No se pudo procesar el reclamo.',
                    customClass: { popup: 'swal-custom-dark' },
                    confirmButtonColor: '#f59e0b'
                });
                return;
            }

            // 1. Actualizar balance en el encabezado
            document.querySelectorAll('.user-balance-value').forEach(el => {
                el.innerText = `$${data.new_balance_formatted}`;
            });

            // 2. Actualizar progreso
            const progressText = document.getElementById(`plan-progress-text-${planId}`);
            if (progressText) {
                progressText.innerText = `$${data.earned_so_far_formatted} / $${data.max_earning_formatted} COP`;
            }
            const progressBar = document.getElementById(`plan-progress-bar-${planId}`);
            if (progressBar) {
                progressBar.style.width = `${data.percent}%`;
            }

            // 3. Reemplazar botón por temporizador sin alerta
            const container = document.getElementById(`plan-action-container-${planId}`);
            if (container) {
                if (data.status === 'completed') {
                    container.innerHTML = `
                        <div class="w-full mt-4 py-3 bg-slate-950 border border-emerald-500/30 rounded-2xl flex items-center justify-between px-4 text-xs">
                            <span class="text-[#00E599] font-bold">✅ Paquete Completado</span>
                            <span class="font-mono text-[#00E599] text-[10px]">100% Retorno</span>
                        </div>
                    `;
                } else {
                    container.innerHTML = `
                        <div class="py-3 px-4 bg-slate-950 border border-slate-800/90 rounded-2xl flex items-center justify-between text-xs sm:text-sm">
                            <span class="text-slate-300 font-medium flex items-center gap-1.5">
                                <span>⏳</span> Próximo reclamo:
                            </span>
                            <span class="countdown-timer font-mono text-amber-400 font-black text-xs sm:text-sm tracking-wide" data-seconds="${data.next_seconds}">Calculando...</span>
                        </div>
                    `;
                    startCountdownTimers();
                }
            }

        } catch (err) {
            console.error('Error al reclamar:', err);
            const form = btn.closest('form');
            if (form) form.submit();
        }
    }

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
</script>
@endsection
