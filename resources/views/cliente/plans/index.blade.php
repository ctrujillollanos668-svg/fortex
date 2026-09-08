@extends('layouts.cliente')

@section('title', 'Planes VIP Disponibles')

@section('content')
<div class="space-y-6 pb-12">

    <!-- Selector de Pestañas: Catálogo vs Mis Planes -->
    <div class="grid grid-cols-2 p-1 bg-slate-900/90 border border-slate-800 rounded-2xl gap-1">
        <a href="{{ route('cliente.plans.index') }}" class="py-2.5 px-3 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 text-slate-950 font-black text-xs sm:text-sm text-center shadow-md flex items-center justify-center gap-1.5">
            <span>⚡</span> Catálogo de Planes
        </a>
        <a href="{{ route('cliente.plans.my-plans') }}" class="py-2.5 px-3 rounded-xl text-slate-400 hover:text-white font-bold text-xs sm:text-sm text-center transition flex items-center justify-center gap-1.5 hover:bg-slate-800/60">
            <span>📦</span> Mis Planes Comprados
        </a>
    </div>

    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white flex items-center gap-2">
                <span>⚡</span> Planes VIP Disponibles para Comprar
            </h1>
            <p class="text-xs text-slate-400 mt-0.5">Elige tu membresía y comienza a generar rendimientos fijos diarios en Pesos Colombianos ($ COP).</p>
        </div>
    </div>

    <!-- Resumen de Saldos para Comprar -->
    <div class="grid grid-cols-2 sm:grid-cols-2 gap-3">
        <div class="p-4 bg-slate-900/90 border border-slate-800 rounded-3xl">
            <span class="text-[10px] text-slate-400 uppercase tracking-wider block font-bold">💳 Saldo Recargas</span>
            <p class="text-base sm:text-lg font-black text-white font-mono mt-0.5">${{ number_format($rechargeBalance, 0, ',', '.') }} <span class="text-[10px] text-slate-500 font-normal font-sans">COP</span></p>
            <span class="text-[10px] text-slate-500 block mt-0.5">Exclusivo para activar planes</span>
        </div>
        <div class="p-4 bg-slate-900/90 border border-emerald-500/30 rounded-3xl">
            <span class="text-[10px] text-emerald-400 uppercase tracking-wider block font-bold">💎 Saldo Ganancias</span>
            <p class="text-base sm:text-lg font-black text-[#00E599] font-mono mt-0.5">${{ number_format($earningsBalance, 0, ',', '.') }} <span class="text-[10px] text-slate-500 font-normal font-sans">COP</span></p>
            <span class="text-[10px] text-emerald-400/80 block mt-0.5">Disponible para re-invertir</span>
        </div>
    </div>

    <!-- Catálogo de Planes VIP -->
    <div class="space-y-4">
        <div class="flex items-center justify-between px-1">
            <h2 class="text-sm sm:text-base font-extrabold text-white flex items-center gap-2">
                <span class="text-yellow-400">⭐</span> Membresías VIP Disponibles
            </h2>
            <span class="text-xs text-slate-400 font-medium">Valores en $ COP</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($availablePlans as $plan)
                <div class="bg-[#0b1222]/90 border border-slate-800/90 rounded-[28px] p-5 shadow-2xl space-y-3.5 flex flex-col justify-between transition hover:border-slate-700 {{ $plan->isSoldOut() ? 'opacity-60 grayscale' : '' }}">
                    <div class="space-y-3">
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
                                <h3 class="text-base sm:text-lg font-black text-white tracking-tight">{{ $plan->name }}</h3>
                                <p class="text-xs text-slate-400 mt-0.5">Paga ${{ number_format(($plan->price * $plan->daily_percentage) / 100, 0, ',', '.') }} COP / día ({{ $plan->duration_days }} días)</p>
                            </div>
                            <div class="text-right">
                                <span class="text-xl sm:text-2xl font-black text-white font-mono tracking-tight">${{ number_format($plan->price, 0, ',', '.') }}</span>
                                <span class="text-[11px] text-slate-400 font-semibold block uppercase tracking-wider">COP</span>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Row: Full width Mint/Emerald Button -->
                    <div class="pt-2">
                        @if($plan->isSoldOut())
                            <button type="button" disabled class="w-full py-3.5 bg-slate-800 text-slate-500 font-bold rounded-2xl text-xs sm:text-sm cursor-not-allowed border border-slate-700">
                                ❌ Agotado (Sin cupos)
                            </button>
                        @else
                            <button type="button" onclick="openBuyModalCatalog({{ $plan->id }}, '{{ addslashes($plan->name) }}', {{ $plan->price }}, '{{ number_format($plan->price, 0, ',', '.') }}')" class="w-full py-3.5 bg-[#00D287] hover:bg-[#00BF7A] text-slate-950 font-black rounded-2xl text-xs sm:text-sm shadow-lg shadow-[#00D287]/20 transition duration-200 active:scale-[0.98] flex items-center justify-center gap-1.5 cursor-pointer">
                                <span>⚡</span> Activar {{ $plan->name }}
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-3 bg-slate-900/40 border border-slate-800 rounded-3xl p-8 text-center text-slate-400 text-xs">
                    No hay planes VIP disponibles en este momento.
                </div>
            @endforelse
        </div>
    </div>

</div>

<!-- Modal Selección de Saldo para Comprar Plan (Catálogo) -->
<div id="chooseWalletModalCatalog" class="fixed inset-0 bg-black/85 backdrop-blur-md z-50 hidden flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-md w-full p-4 sm:p-7 shadow-2xl relative text-xs space-y-4 max-h-[94vh] overflow-y-auto">
        <div class="flex items-start justify-between">
            <div>
                <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 text-[10px] font-extrabold uppercase">Activar Membresía</span>
                <h3 id="catModalPlanName" class="text-lg font-black text-white mt-1">Nombre del Plan</h3>
                <p class="text-xs text-slate-400 mt-0.5">Costo: <strong id="catModalPlanPrice" class="text-emerald-400 font-mono text-sm">$0 COP</strong></p>
            </div>
            <button type="button" onclick="closeChooseWalletModalCatalog()" class="text-slate-400 hover:text-white text-base font-bold transition cursor-pointer">✕</button>
        </div>

        <p class="text-slate-300 text-xs font-semibold">
            ¿Con qué saldo deseas activar este plan? Elige una opción:
        </p>

        <form id="catConfirmBuyForm" method="POST" action="">
            @csrf
            <input type="hidden" name="payment_source" id="catSelectedPaymentSource" value="">

            <div class="space-y-2.5">
                <!-- Opción 1: Saldo de Recargas -->
                <div id="catCardWalletDeposit" onclick="selectWalletCatalog('deposit')" class="p-3.5 bg-slate-950 border-2 border-slate-800 rounded-2xl cursor-pointer transition flex items-center justify-between hover:border-slate-700">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-500/20 text-blue-400 flex items-center justify-center text-lg">
                            💳
                        </div>
                        <div>
                            <h4 class="font-black text-white text-xs">Saldo de Recargas</h4>
                            <span class="text-[11px] text-slate-400 font-mono block">Disponible: ${{ number_format($rechargeBalance, 0, ',', '.') }} COP</span>
                        </div>
                    </div>
                    <div id="catBadgeWalletDeposit"></div>
                </div>

                <!-- Opción 2: Saldo de Ganancias -->
                <div id="catCardWalletEarnings" onclick="selectWalletCatalog('earnings')" class="p-3.5 bg-slate-950 border-2 border-slate-800 rounded-2xl cursor-pointer transition flex items-center justify-between hover:border-slate-700">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-lg">
                            💎
                        </div>
                        <div>
                            <h4 class="font-black text-white text-xs">Saldo de Ganancias (Re-inversión)</h4>
                            <span class="text-[11px] text-emerald-400 font-mono block">Disponible: ${{ number_format($earningsBalance, 0, ',', '.') }} COP</span>
                        </div>
                    </div>
                    <div id="catBadgeWalletEarnings"></div>
                </div>
            </div>

            <div id="catWalletErrorMsg" class="hidden mt-3 p-3 bg-rose-500/10 border border-rose-500/30 rounded-xl text-rose-300 text-[11px]"></div>

            <div class="pt-3 space-y-2">
                <button type="submit" id="catBtnConfirmBuy" disabled class="w-full py-3.5 bg-gradient-to-r from-[#00D287] via-emerald-400 to-[#00D287] hover:brightness-110 text-slate-950 font-black rounded-2xl shadow-lg shadow-[#00D287]/25 transition active:scale-95 text-xs sm:text-sm cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                    ⚡ Confirmar y Activar Plan
                </button>
                <button type="button" onclick="closeChooseWalletModalCatalog()" class="w-full py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold rounded-2xl text-xs transition cursor-pointer">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const userRechargeBalance = {{ (float) $rechargeBalance }};
    const userEarningsBalance = {{ (float) $earningsBalance }};
    let currentPlanPrice = 0;

    function openBuyModalCatalog(planId, planName, planPrice, planPriceFormatted) {
        currentPlanPrice = parseFloat(planPrice);
        document.getElementById('catModalPlanName').innerText = planName;
        document.getElementById('catModalPlanPrice').innerText = '$' + planPriceFormatted + ' COP';
        document.getElementById('catConfirmBuyForm').action = "{{ url('/plans') }}/" + planId + "/buy";
        document.getElementById('catSelectedPaymentSource').value = '';
        document.getElementById('catBtnConfirmBuy').disabled = true;
        document.getElementById('catWalletErrorMsg').classList.add('hidden');

        // Evaluar disponibilidad de Saldo de Recargas
        const canDeposit = userRechargeBalance >= currentPlanPrice;
        const badgeDep = document.getElementById('catBadgeWalletDeposit');
        badgeDep.innerHTML = canDeposit 
            ? '<span class="px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-400 text-[10px] font-extrabold">✅ Disponible</span>'
            : '<span class="px-2.5 py-1 rounded-full bg-rose-500/20 text-rose-400 text-[10px] font-extrabold">❌ Insuficiente</span>';

        // Evaluar disponibilidad de Saldo de Ganancias
        const canEarnings = userEarningsBalance >= currentPlanPrice;
        const badgeEarn = document.getElementById('catBadgeWalletEarnings');
        badgeEarn.innerHTML = canEarnings 
            ? '<span class="px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-400 text-[10px] font-extrabold">✅ Disponible</span>'
            : '<span class="px-2.5 py-1 rounded-full bg-rose-500/20 text-rose-400 text-[10px] font-extrabold">❌ Insuficiente</span>';

        resetWalletCardsCatalog();

        // Auto-seleccionar la opción disponible que tenga fondos
        if (canDeposit) {
            selectWalletCatalog('deposit');
        } else if (canEarnings) {
            selectWalletCatalog('earnings');
        } else {
            document.getElementById('catWalletErrorMsg').innerText = '⚠️ No tienes saldo suficiente en ninguna de las dos fuentes para este plan ($' + planPriceFormatted + ' COP). Por favor recarga saldo primero.';
            document.getElementById('catWalletErrorMsg').classList.remove('hidden');
        }

        document.getElementById('chooseWalletModalCatalog').classList.remove('hidden');
    }

    function closeChooseWalletModalCatalog() {
        document.getElementById('chooseWalletModalCatalog').classList.add('hidden');
    }

    function resetWalletCardsCatalog() {
        const cardDep = document.getElementById('catCardWalletDeposit');
        const cardEarn = document.getElementById('catCardWalletEarnings');
        if (cardDep) {
            cardDep.className = 'p-3.5 bg-slate-950 border-2 border-slate-800 rounded-2xl cursor-pointer transition flex items-center justify-between hover:border-slate-700';
        }
        if (cardEarn) {
            cardEarn.className = 'p-3.5 bg-slate-950 border-2 border-slate-800 rounded-2xl cursor-pointer transition flex items-center justify-between hover:border-slate-700';
        }
    }

    function selectWalletCatalog(source) {
        const canPay = source === 'deposit' ? (userRechargeBalance >= currentPlanPrice) : (userEarningsBalance >= currentPlanPrice);
        const errorDiv = document.getElementById('catWalletErrorMsg');

        resetWalletCardsCatalog();

        if (!canPay) {
            const label = source === 'deposit' ? 'Saldo de Recargas' : 'Saldo de Ganancias';
            errorDiv.innerText = `⚠️ Tu ${label} no alcanza para activar este plan ($${currentPlanPrice.toLocaleString('es-CO')} COP).`;
            errorDiv.classList.remove('hidden');
            document.getElementById('catSelectedPaymentSource').value = '';
            document.getElementById('catBtnConfirmBuy').disabled = true;
            return;
        }

        errorDiv.classList.add('hidden');
        document.getElementById('catSelectedPaymentSource').value = source;
        document.getElementById('catBtnConfirmBuy').disabled = false;

        if (source === 'deposit') {
            document.getElementById('catCardWalletDeposit').className = 'p-3.5 bg-slate-950 border-2 border-emerald-500 shadow-lg shadow-emerald-500/10 rounded-2xl cursor-pointer transition flex items-center justify-between';
        } else {
            document.getElementById('catCardWalletEarnings').className = 'p-3.5 bg-slate-950 border-2 border-emerald-500 shadow-lg shadow-emerald-500/10 rounded-2xl cursor-pointer transition flex items-center justify-between';
        }
    }

    // Cerrar modal al hacer clic en el backdrop
    document.getElementById('chooseWalletModalCatalog')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeChooseWalletModalCatalog();
        }
    });
</script>
@endsection
