@extends('layouts.cliente')

@section('title', 'Retirar Dinero')

@section('content')
<div class="max-w-lg mx-auto space-y-6 pb-12">

    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white flex items-center gap-2">
                <span>💸</span> Solicitar Retiro
            </h1>
            <p class="text-xs text-slate-400 mt-0.5">Transfiere tus ganancias acumuladas a tu cuenta bancaria o billetera.</p>
        </div>
        <div class="self-start sm:self-auto px-3.5 py-2 bg-slate-900 border border-slate-800 rounded-2xl">
            <span class="text-[10px] text-slate-400 block font-semibold">Ganancias Retirables:</span>
            <p class="text-base sm:text-lg font-black text-emerald-400 font-mono">${{ number_format($withdrawableBalance, 0, ',', '.') }} COP</p>
            @if($uninvestedDeposit > 0)
                <span class="text-[10px] text-amber-400/90 font-medium block mt-0.5">Recarga p/ Planes: ${{ number_format($uninvestedDeposit, 0, ',', '.') }}</span>
            @endif
        </div>
    </div>

    <!-- Estado y Horarios Oficiales de Retiro -->
    <div class="p-4 rounded-3xl border text-xs shadow-lg flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 {{ $isWithdrawalOpen ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300' : 'bg-rose-500/10 border-rose-500/30 text-rose-300' }}">
        <div class="space-y-1">
            <div class="flex items-center gap-2 font-black text-sm {{ $isWithdrawalOpen ? 'text-emerald-400' : 'text-rose-400' }}">
                <span>{{ $isWithdrawalOpen ? '🟢' : '🔴' }}</span>
                <span>{{ $isWithdrawalOpen ? 'Horario de Retiros HABILITADO' : 'Horario de Retiros CERRADO Actualmente' }}</span>
            </div>
            <p class="text-[11px] {{ $isWithdrawalOpen ? 'text-emerald-300/90' : 'text-rose-300/90' }}">
                ⏰ <strong>Horario Oficial de Pagos:</strong> 8:00 AM a 12:00 PM &nbsp;|&nbsp; 2:00 PM a 6:00 PM (Hora Colombia).
            </p>
        </div>
        <div class="px-3 py-1.5 rounded-xl {{ $isWithdrawalOpen ? 'bg-emerald-950/80 border border-emerald-500/30 text-emerald-300' : 'bg-rose-950/80 border border-rose-500/30 text-rose-300' }} text-[11px] font-mono font-bold shrink-0">
            Hora actual: {{ $currentBogotaTime ?? now()->setTimezone('America/Bogota')->format('h:i A') }}
        </div>
    </div>

    @if($totalDeposited < 15000)
        <!-- Alerta de Activación de Cuenta por Recarga Mínima -->
        <div class="p-4 bg-amber-500/10 border border-amber-500/30 rounded-3xl text-xs text-amber-300 space-y-2 shadow-lg">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 font-bold text-amber-200">
                    <span>⚠️</span> Cuenta Pendiente de Activación
                </div>
                <span class="text-[10px] px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-400 font-mono font-bold">
                    Recargado: ${{ number_format($totalDeposited, 0, ',', '.') }} / $15.000 COP
                </span>
            </div>
            <p class="text-[11px] text-amber-300/90 leading-relaxed">
                Para poder solicitar retiros de tus ganancias o comisiones de referidos, es obligatorio haber realizado una <strong>recarga mínima de $15.000 COP</strong> en tu cuenta.
            </p>
            <div class="pt-1">
                <a href="{{ route('cliente.deposits.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-black rounded-xl text-[11px] transition shadow-md cursor-pointer">
                    💳 Realizar Primera Recarga Ahora →
                </a>
            </div>
        </div>
    @endif

    @if($uninvestedDeposit > 0)
        <!-- Aviso explicativo sobre saldo de recarga vs ganancias -->
        <div class="p-4 bg-amber-500/10 border border-amber-500/30 rounded-3xl text-xs text-amber-300 space-y-1.5 shadow-lg">
            <div class="flex items-center gap-2 font-bold text-amber-200">
                <span>🛡️</span> Regla Oficial de Retiros
            </div>
            <p class="text-[11px] text-amber-300/90 leading-relaxed">
                Solo puedes retirar las <strong>ganancias generadas</strong> (rendimientos diarios, comisiones de equipo y premios). Tienes <strong class="text-white font-mono">${{ number_format($uninvestedDeposit, 0, ',', '.') }} COP</strong> en saldo de recarga que debes destinar a <strong>activar Planes VIP</strong> para poner a producir tu dinero.
            </p>
            <div class="pt-1">
                <a href="{{ route('cliente.plans.index') }}" class="inline-flex items-center gap-1 text-[11px] font-extrabold text-emerald-400 hover:text-emerald-300 underline">
                    🚀 Ir a Activar Planes VIP con mi Saldo →
                </a>
            </div>
        </div>
    @endif

    <!-- Formulario de Retiro -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-4 sm:p-7 shadow-2xl space-y-4">
        <form method="POST" action="{{ route('cliente.withdrawals.store') }}" class="space-y-4 text-xs">
            @csrf

            <!-- Monto a Retirar -->
            <div>
                <div class="flex justify-between items-center mb-1">
                    <label class="block font-semibold text-slate-300">Monto a Retirar ($ COP)</label>
                    <span class="text-[10px] text-slate-400">Retirable: <b class="text-emerald-400 font-mono">${{ number_format($withdrawableBalance, 0, ',', '.') }}</b></span>
                </div>
                <div class="relative">
                    <input type="number" id="withdraw_amount_input" step="1000" min="15000" max="{{ $withdrawableBalance }}" name="amount" required placeholder="Ej: 50000" oninput="calcWithdrawalFee()" class="w-full pl-4 pr-24 py-3 bg-slate-950 border border-slate-800 rounded-2xl text-white font-mono text-base focus:outline-none focus:border-cyan-500">
                    <button type="button" onclick="setMaxAmount({{ $withdrawableBalance }})" class="absolute right-3 top-2.5 px-3 py-1 bg-slate-800 hover:bg-slate-700 text-cyan-400 text-xs font-bold rounded-xl transition cursor-pointer">
                        MÁXIMO
                    </button>
                </div>
                <div class="flex justify-between text-[10px] text-slate-500 mt-1">
                    <span class="text-amber-400/90 font-semibold">Mínimo de retiro: $15.000 COP</span>
                    <span class="text-slate-400">Comisión fija: <b class="text-rose-400 font-mono">8%</b></span>
                </div>
            </div>

            <!-- Desglose de Comisión y Neto a Recibir en Tiempo Real -->
            <div class="bg-slate-950 p-3.5 rounded-2xl border border-slate-800/80 space-y-1.5 text-xs">
                <div class="flex justify-between text-slate-400">
                    <span>Monto Solicitado:</span>
                    <span id="preview_requested" class="font-mono text-slate-200 font-bold">$0 COP</span>
                </div>
                <div class="flex justify-between text-slate-400">
                    <span>Comisión por transferencia (8%):</span>
                    <span id="preview_fee" class="font-mono text-rose-400 font-bold">-$0 COP</span>
                </div>
                <div class="pt-1.5 border-t border-slate-800 flex justify-between items-center">
                    <span class="font-bold text-white">Neto que recibirás en tu cuenta:</span>
                    <span id="preview_net" class="font-mono text-emerald-400 font-black text-sm">$0 COP</span>
                </div>
            </div>

            <!-- Método de Recepción Dinámico -->
            <div>
                <label class="block font-semibold text-slate-300 mb-1">Método de Destino</label>
                <select name="payment_method" required class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-2xl text-white focus:outline-none focus:border-cyan-500">
                    @forelse($paymentMethods as $pm)
                        @php
                            $icon = [
                                'purple' => '🟣',
                                'rose' => '🔴',
                                'amber' => '🟡',
                                'emerald' => '🟢',
                                'blue' => '🔵',
                            ][$pm->color_theme] ?? '💳';
                        @endphp
                        <option value="{{ $pm->name }}">{{ $icon }} {{ $pm->name }}</option>
                    @empty
                        <option value="Nequi">🟣 Nequi</option>
                        <option value="Daviplata">🔴 Daviplata</option>
                        <option value="Bancolombia">🟡 Bancolombia</option>
                    @endforelse
                </select>
            </div>

            <!-- Datos de Destino -->
            <div>
                <label class="block font-semibold text-slate-300 mb-1">Número de Celular, Cuenta o Billetera Receptora</label>
                <input type="text" name="wallet_or_account" required placeholder="Ej: 3001234567 o 123-456789-00" class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-2xl text-white font-mono text-sm focus:outline-none focus:border-cyan-500">
            </div>

            <!-- Botón de Envío -->
            @if(!$isWithdrawalOpen)
                <button disabled type="button" class="w-full py-3.5 bg-rose-500/20 border border-rose-500/40 text-rose-300 font-bold rounded-2xl cursor-not-allowed text-xs sm:text-sm">
                    🔒 Retiros Cerrados (Horarios: 8:00 AM - 12:00 PM | 2:00 PM - 6:00 PM)
                </button>
            @elseif($totalDeposited < 15000)
                <a href="{{ route('cliente.deposits.index') }}" class="w-full py-3.5 bg-amber-500/20 border border-amber-500/40 hover:bg-amber-500/30 text-amber-300 font-bold rounded-2xl text-center text-xs sm:text-sm block transition">
                    ⚠️ Requiere primera recarga mín. de $15.000 COP → Recargar Aquí
                </a>
            @elseif($withdrawableBalance >= 15000)
                <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-cyan-500 to-blue-500 hover:from-cyan-600 hover:to-blue-600 text-slate-950 font-black rounded-2xl shadow-lg shadow-cyan-500/25 transition active:scale-95 text-xs sm:text-sm cursor-pointer">
                    ⚡ Solicitar Retiro Inmediato
                </button>
            @else
                <button disabled type="button" class="w-full py-3.5 bg-slate-800 text-slate-500 font-bold rounded-2xl cursor-not-allowed text-xs sm:text-sm">
                    ⚠️ Saldo retirable insuficiente (Mínimo $15.000 COP en ganancias)
                </button>
            @endif
        </form>
    </div>

    <!-- Historial de Retiros del Cliente -->
    <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-5">
        <h3 class="text-xs sm:text-sm font-bold text-white mb-3 flex items-center gap-2">
            <span>📋</span> Historial de tus Retiros
        </h3>

        @if($withdrawals->isEmpty())
            <p class="text-xs text-slate-500 py-6 text-center">Aún no has solicitado ningún retiro.</p>
        @else
            <div class="divide-y divide-slate-800/80">
                @foreach($withdrawals as $with)
                    <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-white font-mono text-sm">${{ number_format($with->amount, 0, ',', '.') }} COP</span>
                                <span class="text-[10px] px-2 py-0.5 rounded-md bg-rose-500/10 text-rose-400 font-mono">Comisión: -${{ number_format($with->fee, 0, ',', '.') }}</span>
                            </div>
                            <div class="text-[11px] text-slate-400 mt-1">
                                <span>Recibes neto: <strong class="text-emerald-400 font-mono font-extrabold">${{ number_format($with->net_amount, 0, ',', '.') }} COP</strong></span>
                                <span class="text-slate-500 block text-[10px] mt-0.5">Destino: {{ $with->wallet_or_account }}</span>
                            </div>
                        </div>
                        <div class="text-right flex sm:flex-col items-center sm:items-end justify-between">
                            @if($with->status === 'pending')
                                <span class="px-2.5 py-0.5 rounded-full bg-amber-500/15 text-amber-400 text-[10px] font-bold uppercase border border-amber-500/30">En Revisión</span>
                            @elseif($with->status === 'approved')
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/15 text-emerald-400 text-[10px] font-bold uppercase border border-emerald-500/30">Pagado</span>
                                @if($with->admin_notes)
                                    <span class="text-[10px] text-emerald-400/80 block mt-0.5">{{ $with->admin_notes }}</span>
                                @endif
                            @else
                                <span class="px-2.5 py-0.5 rounded-full bg-rose-500/15 text-rose-400 text-[10px] font-bold uppercase border border-rose-500/30">Rechazado & Reembolsado</span>
                                @if($with->admin_notes)
                                    <div class="mt-1 text-left sm:text-right">
                                        <span class="text-[10px] text-rose-300 font-medium block max-w-[240px] leading-tight">⚠️ {{ $with->admin_notes }}</span>
                                        <span class="text-[9px] text-emerald-400 font-semibold block mt-0.5">✓ Saldo devuelto a tu cuenta</span>
                                    </div>
                                @endif
                            @endif
                            <span class="block text-[10px] text-slate-500 mt-1">{{ $with->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-3">
                {{ $withdrawals->links() }}
            </div>
        @endif
    </div>

</div>

<script>
    function calcWithdrawalFee() {
        const input = document.getElementById('withdraw_amount_input');
        const val = parseFloat(input.value) || 0;
        const feePercent = 0.08; // 8% de comisión
        const fee = Math.round(val * feePercent);
        const net = Math.max(0, val - fee);

        document.getElementById('preview_requested').innerText = '$' + val.toLocaleString('es-CO') + ' COP';
        document.getElementById('preview_fee').innerText = '-$' + fee.toLocaleString('es-CO') + ' COP (8%)';
        document.getElementById('preview_net').innerText = '$' + net.toLocaleString('es-CO') + ' COP';
    }

    function setMaxAmount(balance) {
        document.getElementById('withdraw_amount_input').value = balance;
        calcWithdrawalFee();
    }

    document.addEventListener('DOMContentLoaded', calcWithdrawalFee);
</script>
@endsection
