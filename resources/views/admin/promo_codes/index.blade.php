@extends('layouts.admin')

@section('title', 'Códigos de Regalo y Sorteos')

@section('content')
<div class="space-y-6">

    <!-- Encabezado con Botón para Crear Nuevo Código -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-white flex items-center gap-2">
                <span>🎁</span> Códigos de Regalo y Sorteos
            </h1>
            <p class="text-xs text-slate-400 mt-1">Crea códigos exclusivos para ganadores de sorteos en Telegram/WhatsApp o promociones VIP con límite de canjes.</p>
        </div>

        <button onclick="openCreateModal()" class="px-5 py-3 bg-gradient-to-r from-rose-500 to-amber-500 hover:from-rose-600 hover:to-amber-600 text-white font-black rounded-2xl shadow-lg shadow-rose-500/20 text-xs sm:text-sm transition flex items-center justify-center gap-2 cursor-pointer">
            <span>➕</span> Crear Nuevo Código de Regalo
        </button>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-500/15 border border-emerald-500/30 rounded-2xl text-emerald-300 text-xs sm:text-sm flex items-center gap-3 shadow-lg shadow-emerald-500/10">
            <span class="text-xl">✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 bg-rose-500/15 border border-rose-500/30 rounded-2xl text-rose-300 text-xs space-y-1">
            @foreach ($errors->all() as $error)
                <p>• {{ $error }}</p>
            @endforeach
        </div>
    @endif

    <!-- Tarjetas de Estadísticas -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4">
            <span class="text-[11px] text-slate-400 font-semibold block">Códigos Totales</span>
            <p class="text-xl sm:text-2xl font-black text-white mt-1 font-mono">{{ $totalCodes }}</p>
        </div>
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4">
            <span class="text-[11px] text-slate-400 font-semibold block">Códigos Activos</span>
            <p class="text-xl sm:text-2xl font-black text-emerald-400 mt-1 font-mono">{{ $activeCodes }}</p>
        </div>
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4">
            <span class="text-[11px] text-slate-400 font-semibold block">Total Canjeado</span>
            <p class="text-xl sm:text-2xl font-black text-rose-400 mt-1 font-mono">${{ number_format($totalRedeemedAmount, 0, ',', '.') }} COP</p>
        </div>
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4">
            <span class="text-[11px] text-slate-400 font-semibold block">Canjes Realizados</span>
            <p class="text-xl sm:text-2xl font-black text-amber-400 mt-1 font-mono">{{ $totalRedemptionsCount }}</p>
        </div>
    </div>

    <!-- Lista de Códigos Creados -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <span>🏷️</span> Códigos Disponibles y de Sorteo
            </h3>
            <span class="text-xs text-slate-400">{{ $promoCodes->total() }} registrados</span>
        </div>

        @if($promoCodes->isEmpty())
            <div class="py-12 text-center text-slate-500 text-xs">
                <span class="text-4xl block mb-2">🎁</span>
                No hay códigos de regalo creados aún. Haz clic en <strong>"Crear Nuevo Código"</strong> para generar el primero.
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($promoCodes as $code)
                    @php
                        $isExhausted = $code->max_uses > 0 && $code->used_count >= $code->max_uses;
                    @endphp
                    <div class="bg-slate-950 border {{ $isExhausted ? 'border-slate-800 opacity-60' : ($code->status ? 'border-rose-500/30' : 'border-amber-500/30') }} rounded-2xl p-4 flex flex-col justify-between space-y-3 relative">
                        
                        <div>
                            <!-- Header de Tarjeta -->
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <span class="px-3 py-1 bg-rose-500/20 text-rose-300 border border-rose-500/30 rounded-xl text-xs font-mono font-black select-all flex items-center gap-1.5">
                                    <span>🎟️</span> {{ $code->code }}
                                </span>
                                
                                @if($isExhausted)
                                    <span class="px-2 py-0.5 rounded-full bg-slate-800 text-slate-400 text-[10px] font-extrabold uppercase">Agotado</span>
                                @elseif($code->status)
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 text-[10px] font-extrabold uppercase">🟢 Activo</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-400 text-[10px] font-extrabold uppercase">⏸️ Pausado</span>
                                @endif
                            </div>

                            <!-- Monto del Premio -->
                            <div class="my-2">
                                <span class="text-[10px] text-slate-400 uppercase font-semibold">Premio al Canjear:</span>
                                <p class="text-xl font-black text-emerald-400 font-mono">${{ number_format($code->reward_amount, 0, ',', '.') }} <span class="text-xs text-slate-500">COP</span></p>
                            </div>

                            <!-- Límite de Usos -->
                            <div class="bg-slate-900 p-2.5 rounded-xl border border-slate-800/80 text-xs space-y-1">
                                <div class="flex justify-between text-[11px] text-slate-400">
                                    <span>Usos canjeados:</span>
                                    <span class="font-mono font-bold text-white">{{ $code->used_count }} / {{ $code->max_uses }}</span>
                                </div>
                                <div class="w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                    @php $pct = min(100, round(($code->used_count / max(1, $code->max_uses)) * 100)); @endphp
                                    <div class="bg-rose-500 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                                @if($code->description)
                                    <p class="text-[10px] text-slate-400 truncate pt-1">📝 {{ $code->description }}</p>
                                @endif
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="flex items-center gap-2 pt-2 border-t border-slate-800/80">
                            <!-- Copiar Código -->
                            <button onclick="copyCode('{{ $code->code }}')" class="flex-1 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl transition cursor-pointer flex items-center justify-center gap-1">
                                📋 Copiar
                            </button>

                            <!-- Toggle Activar/Pausar -->
                            <form method="POST" action="{{ route('admin.promo-codes.toggle', $code->id) }}" class="inline">
                                @csrf
                                <button type="submit" title="{{ $code->status ? 'Pausar código' : 'Activar código' }}" class="px-2.5 py-1.5 {{ $code->status ? 'bg-amber-500/20 text-amber-300 hover:bg-amber-500/30' : 'bg-emerald-500/20 text-emerald-300 hover:bg-emerald-500/30' }} text-xs font-bold rounded-xl transition cursor-pointer">
                                    {{ $code->status ? '⏸️' : '▶️' }}
                                </button>
                            </form>

                            <!-- Eliminar -->
                            <form method="POST" action="{{ route('admin.promo-codes.destroy', $code->id) }}" onsubmit="return confirm('¿Seguro que deseas eliminar el código {{ $code->code }}?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Eliminar código" class="px-2.5 py-1.5 bg-rose-500/20 hover:bg-rose-500/30 text-rose-400 text-xs font-bold rounded-xl transition cursor-pointer">
                                    🗑️
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $promoCodes->links() }}
            </div>
        @endif
    </div>

    <!-- Historial de Últimos Canjes Realizados por Clientes -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl">
        <h3 class="text-base font-bold text-white mb-3 flex items-center gap-2">
            <span>📜</span> Últimos Ganadores que Canjearon Códigos
        </h3>

        @if($recentRedemptions->isEmpty())
            <p class="text-xs text-slate-500 py-6 text-center">Ningún cliente ha canjeado códigos todavía.</p>
        @else
            <div class="divide-y divide-slate-800/80">
                @foreach($recentRedemptions as $redemption)
                    <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                        <div class="flex items-center gap-3">
                            <span class="text-xl">🎉</span>
                            <div>
                                <p class="font-bold text-white">{{ $redemption->user->name ?? 'Usuario Eliminado' }} <span class="text-slate-500 font-mono text-[11px]">({{ $redemption->user->email ?? 'N/A' }})</span></p>
                                <p class="text-[11px] text-slate-400">Canjeó el código: <strong class="text-rose-400 font-mono font-bold">{{ $redemption->promoCode->code ?? 'N/A' }}</strong></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="font-mono font-black text-emerald-400 text-sm block">+${{ number_format($redemption->reward_amount, 0, ',', '.') }} COP</span>
                            <span class="text-[10px] text-slate-500">{{ $redemption->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>

<!-- ========================================== -->
<!-- MODAL PARA CREAR NUEVO CÓDIGO DE REGALO -->
<!-- ========================================== -->
<div id="createPromoModal" class="fixed inset-0 bg-black/85 backdrop-blur-md z-50 hidden flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-7 max-w-md w-full shadow-2xl relative max-h-[94vh] overflow-y-auto">
        <!-- Cerrar -->
        <button onclick="closeCreateModal()" class="absolute right-4 top-4 text-slate-400 hover:text-white text-xl font-bold cursor-pointer">✕</button>

        <h3 class="text-lg font-black text-white mb-1 flex items-center gap-2">
            <span>🎁</span> Crear Código de Sorteo / Regalo
        </h3>
        <p class="text-xs text-slate-400 mb-4">Define el código, el valor del premio y cuántos clientes podrán canjearlo.</p>

        <form method="POST" action="{{ route('admin.promo-codes.store') }}" class="space-y-4 text-xs">
            @csrf

            <!-- Código Personalizado o Generado -->
            <div>
                <div class="flex justify-between items-center mb-1">
                    <label class="block font-semibold text-slate-300">Código del Sorteo</label>
                    <button type="button" onclick="generateRandomCode()" class="text-[10px] text-rose-400 hover:text-rose-300 font-bold underline cursor-pointer">
                        🎲 Generar al Azar
                    </button>
                </div>
                <input type="text" id="promo_code_input" name="code" placeholder="Ej: SORTEO50K o FORTEX-WIN" class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-2xl text-white font-mono text-sm uppercase focus:outline-none focus:border-rose-500">
                <span class="text-[10px] text-slate-500 mt-0.5 block">Déjalo en blanco si deseas que el sistema genere uno automático.</span>
            </div>

            <!-- Monto del Premio en COP -->
            <div>
                <label class="block font-semibold text-slate-300 mb-1">Premio que Acredita al Saldo ($ COP)</label>
                <div class="relative">
                    <input type="number" step="any" min="100" name="reward_amount" required placeholder="Ej: 20000" class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-2xl text-white font-mono text-sm focus:outline-none focus:border-emerald-500">
                    <span class="absolute right-4 top-3.5 text-xs text-emerald-400 font-bold">COP</span>
                </div>
            </div>

            <!-- Límite de Usos -->
            <div>
                <label class="block font-semibold text-slate-300 mb-1">Límite de Canjes (¿Cuántos ganadores?)</label>
                <input type="number" min="1" value="1" name="max_uses" required class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-2xl text-white font-mono text-sm focus:outline-none focus:border-rose-500">
                <span class="text-[10px] text-slate-500 mt-0.5 block">Pon <strong>1</strong> para un solo ganador de sorteo, o <strong>5 / 10</strong> para premiar a los primeros en canjearlo.</span>
            </div>

            <!-- Descripción / Motivo -->
            <div>
                <label class="block font-semibold text-slate-300 mb-1">Nota o Motivo (Opcional)</label>
                <input type="text" name="description" placeholder="Ej: Ganador sorteo en vivo Telegram 07 Septiembre" class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-2xl text-white text-xs focus:outline-none focus:border-slate-600">
            </div>

            <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-rose-500 to-amber-500 hover:from-rose-600 hover:to-amber-600 text-white font-black rounded-2xl shadow-lg shadow-rose-500/25 transition active:scale-95 text-xs sm:text-sm cursor-pointer">
                🚀 Crear Código de Sorteo
            </button>
        </form>
    </div>
</div>

<script>
    function openCreateModal() {
        document.getElementById('createPromoModal').classList.remove('hidden');
    }

    function closeCreateModal() {
        document.getElementById('createPromoModal').classList.add('hidden');
    }

    function generateRandomCode() {
        const words = ['SORTEO', 'FORTEX', 'REGALO', 'PREMIO', 'VIP', 'PROMO'];
        const randomWord = words[Math.floor(Math.random() * words.length)];
        const randomNum = Math.floor(1000 + Math.random() * 9000);
        document.getElementById('promo_code_input').value = randomWord + '-' + randomNum;
    }

    function copyCode(code) {
        navigator.clipboard.writeText(code);
        alert('¡Código [' + code + '] copiado al portapapeles! Ya puedes enviarlo por Telegram o WhatsApp.');
    }

    document.getElementById('createPromoModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeCreateModal();
        }
    });
</script>
@endsection
