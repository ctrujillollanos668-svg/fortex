@extends('layouts.cliente')

@section('title', 'Recargar Saldo')

@section('content')
<div class="space-y-6">

    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white flex items-center gap-2">
                <span>➕</span> Recargar Saldo
            </h1>
            <p class="text-xs text-slate-400 mt-0.5">Transfiere escaneando el código QR oficial y reporta tu comprobante.</p>
        </div>
        <div class="self-start sm:self-auto px-3.5 py-1.5 bg-slate-900 border border-slate-800 rounded-2xl flex items-center gap-2">
            <span class="text-[11px] text-slate-400">Saldo Actual:</span>
            <p class="text-sm sm:text-base font-extrabold text-emerald-400 font-mono">${{ number_format($user->balance, 0, ',', '.') }} COP</p>
        </div>
    </div>

    <!-- Aviso de Tiempo de Acreditación (10 Minutos) -->
    <div class="p-4 rounded-3xl bg-cyan-500/10 border border-cyan-500/30 text-cyan-300 text-xs shadow-lg flex items-start gap-3">
        <span class="text-2xl shrink-0">⏳</span>
        <div class="space-y-1">
            <div class="font-extrabold text-white text-xs sm:text-sm">
                Tiempo estimado de acreditación: ~10 Minutos
            </div>
            <p class="text-[11px] text-cyan-200/90 leading-relaxed">
                Una vez realices la transferencia y subas tu comprobante, nuestro sistema y equipo administrativo validarán tu pago. <strong>Por favor espera aproximadamente 10 minutos</strong> para que tu saldo se refleje automáticamente en tu cuenta.
            </p>
        </div>
    </div>

    <!-- Cuentas Oficiales para Transferir y Escanear QR -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        @forelse($paymentMethods as $pm)
            @php
                $colorTheme = [
                    'purple' => ['badge' => 'bg-purple-500/20 text-purple-400 border-purple-500/30', 'icon' => '🟣', 'btn' => 'bg-purple-500/20 hover:bg-purple-500/30 border-purple-500/40 text-purple-300'],
                    'rose' => ['badge' => 'bg-rose-500/20 text-rose-400 border-rose-500/30', 'icon' => '🔴', 'btn' => 'bg-rose-500/20 hover:bg-rose-500/30 border-rose-500/40 text-rose-300'],
                    'amber' => ['badge' => 'bg-amber-500/20 text-amber-400 border-amber-500/30', 'icon' => '🟡', 'btn' => 'bg-amber-500/20 hover:bg-amber-500/30 border-amber-500/40 text-amber-300'],
                    'emerald' => ['badge' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30', 'icon' => '🟢', 'btn' => 'bg-emerald-500/20 hover:bg-emerald-500/30 border-emerald-500/40 text-emerald-300'],
                    'blue' => ['badge' => 'bg-cyan-500/20 text-cyan-400 border-cyan-500/30', 'icon' => '🔵', 'btn' => 'bg-cyan-500/20 hover:bg-cyan-500/30 border-cyan-500/40 text-cyan-300'],
                ][$pm->color_theme] ?? ['badge' => 'bg-slate-800 text-slate-300 border-slate-700', 'icon' => '💳', 'btn' => 'bg-slate-800 hover:bg-slate-700 text-white'];
            @endphp

            <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-5 relative overflow-hidden shadow-xl flex flex-col justify-between items-center text-center">
                <div class="w-full">
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-2.5 py-0.5 rounded-full {{ $colorTheme['badge'] }} border text-[10px] font-extrabold uppercase">
                            {{ $pm->name }}
                        </span>
                        <span class="text-2xl">{{ $colorTheme['icon'] }}</span>
                    </div>

                    <!-- Código QR Centrado -->
                    <div onclick="showQrModal('QR {{ $pm->name }}', '{{ $pm->qr_image ? asset('storage/' . $pm->qr_image) : '' }}', '{{ $pm->account_holder ?? $pm->name }}')" class="bg-white p-3 rounded-2xl inline-block shadow-lg cursor-pointer hover:scale-105 transition-transform my-2 border border-slate-700">
                        @if($pm->qr_image)
                            <img src="{{ asset('storage/' . $pm->qr_image) }}" alt="QR {{ $pm->name }}" class="w-28 h-28 object-contain">
                        @else
                            <div class="w-28 h-28 flex flex-col items-center justify-center text-slate-800">
                                <span class="text-3xl mb-1">📱</span>
                                <span class="text-[10px] font-bold">QR Oficial</span>
                            </div>
                        @endif
                    </div>

                    @if($pm->account_holder)
                        <div class="mt-2 text-center">
                            <span class="text-[10px] text-slate-400 block">Titular de la cuenta:</span>
                            <p class="text-xs sm:text-sm font-bold text-white mt-0.5">{{ $pm->account_holder }}</p>
                        </div>
                    @endif
                </div>

                <!-- Botón para ver y escanear QR grande -->
                <button type="button" onclick="showQrModal('QR {{ $pm->name }}', '{{ $pm->qr_image ? asset('storage/' . $pm->qr_image) : '' }}', '{{ $pm->account_holder ?? $pm->name }}')" class="mt-4 w-full py-2.5 {{ $colorTheme['btn'] }} border font-bold rounded-xl text-xs flex items-center justify-center gap-1.5 transition cursor-pointer">
                    <span>📸</span> Escanear Código QR
                </button>
            </div>
        @empty
            <div class="col-span-3 bg-slate-900/40 border border-slate-800 rounded-3xl p-6 text-center text-slate-400 text-xs">
                No hay métodos de pago activos en este momento. Contacta al soporte.
            </div>
        @endforelse
    </div>

    <!-- Formulario para Reportar el Pago -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-4 sm:p-7 shadow-2xl">
        <h3 class="text-base font-bold text-white mb-4 flex items-center gap-2">
            <span>📝</span> Reportar Comprobante de Transferencia
        </h3>

        <form method="POST" action="{{ route('cliente.deposits.store') }}" enctype="multipart/form-data" class="space-y-4 text-xs">
            @csrf

            <!-- Monto -->
            <div>
                <label class="block font-semibold text-slate-300 mb-1">Monto Transferido ($ COP)</label>
                <div class="relative">
                    <input type="number" step="1000" min="10000" name="amount" required placeholder="Ej: 30000" class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-2xl text-white font-mono text-sm focus:outline-none focus:border-emerald-500">
                    <span class="absolute right-4 top-3.5 text-xs text-emerald-400 font-bold">COP</span>
                </div>
                <span class="text-[10px] text-slate-500 mt-1 block">Mínimo de recarga: $10.000 COP</span>
            </div>

            <!-- Método de Pago Seleccionado -->
            <div>
                <label class="block font-semibold text-slate-300 mb-1">Método de Pago Utilizado</label>
                <select name="payment_method" required class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-2xl text-white focus:outline-none focus:border-emerald-500">
                    @foreach($paymentMethods as $pm)
                        <option value="{{ $pm->name }}">{{ $pm->name }} @if($pm->account_holder) (Titular: {{ $pm->account_holder }}) @endif</option>
                    @endforeach
                </select>
            </div>

            <!-- Número de Comprobante / Referencia -->
            <div>
                <label class="block font-semibold text-slate-300 mb-1">Número de Aprobación / Referencia / Celular que envía</label>
                <input type="text" name="transaction_hash" required placeholder="Ej: M1234567 o 3001234567" class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-2xl text-white focus:outline-none focus:border-emerald-500">
            </div>

            <!-- Subir Foto del Comprobante (Obligatorio) -->
            <div>
                <label class="block font-semibold text-slate-300 mb-1 flex items-center justify-between">
                    <span>Adjuntar Captura de Pago <span class="text-rose-400 font-bold">* (Obligatorio)</span></span>
                    <span class="text-[10px] text-slate-500 font-normal">JPG, PNG, WEBP</span>
                </label>
                <input type="file" name="proof_image" required accept="image/*" class="w-full text-xs text-slate-400 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-500 file:text-slate-950 hover:file:bg-emerald-400 cursor-pointer bg-slate-950 border border-slate-800 rounded-2xl p-2">
                <span class="text-[10px] text-slate-500 mt-1 block">Sube el pantallazo o comprobante emitido por tu banco/Nequi para que el administrador verifique y apruebe tu saldo.</span>
            </div>

            <!-- Botón de Envío -->
            <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-bold rounded-2xl shadow-lg shadow-emerald-500/25 transition active:scale-95 text-xs sm:text-sm cursor-pointer">
                🚀 Enviar Reporte de Recarga
            </button>
        </form>
    </div>

    <!-- Historial de Recargas del Cliente -->
    <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-5">
        <h3 class="text-xs sm:text-sm font-bold text-white mb-3 flex items-center gap-2">
            <span>📋</span> Historial de tus Recargas
        </h3>

        @if($deposits->isEmpty())
            <p class="text-xs text-slate-500 py-6 text-center">No has realizado ninguna recarga todavía.</p>
        @else
            <div class="divide-y divide-slate-800/80">
                @foreach($deposits as $dep)
                    <div class="py-3 flex items-center justify-between text-xs">
                        <div>
                            <p class="font-bold text-white font-mono">${{ number_format($dep->amount, 0, ',', '.') }} COP</p>
                            <p class="text-[11px] text-slate-400">{{ $dep->payment_method }} • Ref: {{ $dep->transaction_hash }}</p>
                        </div>
                        <div class="text-right">
                            @if($dep->status === 'pending')
                                <span class="px-2.5 py-0.5 rounded-full bg-amber-500/15 text-amber-400 text-[10px] font-bold uppercase">En Revisión</span>
                            @elseif($dep->status === 'approved')
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/15 text-emerald-400 text-[10px] font-bold uppercase">Aprobado</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full bg-rose-500/15 text-rose-400 text-[10px] font-bold uppercase">Rechazado</span>
                                @if($dep->admin_notes)
                                    <p class="text-[10px] text-rose-300 mt-1 max-w-[200px] leading-tight">⚠️ {{ $dep->admin_notes }}</p>
                                @endif
                            @endif
                            <span class="block text-[10px] text-slate-500 mt-0.5">{{ $dep->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-3">
                {{ $deposits->links() }}
            </div>
        @endif
    </div>

</div>

<!-- ========================================== -->
<!-- MODAL EMERGENTE PARA VER Y ESCANEAR EL QR -->
<!-- ========================================== -->
<div id="qrModal" class="fixed inset-0 bg-black/85 backdrop-blur-md z-50 hidden flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 sm:p-8 max-w-sm w-full shadow-2xl relative text-center max-h-[94vh] overflow-y-auto">
        <!-- Cerrar -->
        <button onclick="closeQrModal()" class="absolute right-4 top-4 text-slate-400 hover:text-white text-2xl font-bold transition cursor-pointer">
            ✕
        </button>

        <h3 id="qrModalTitle" class="text-lg font-black text-white mb-1">Código QR Oficial</h3>
        <p class="text-xs text-slate-400 mb-4">Abre tu app bancaria y escanea este código para transferir</p>

        <!-- Contenedor del QR -->
        <div class="bg-white p-3 sm:p-4 rounded-2xl inline-block shadow-xl mb-4 border border-slate-200">
            <div id="qrImageContainer" class="w-52 h-52 sm:w-60 sm:h-60 flex items-center justify-center">
                <!-- Se inyecta dinámicamente -->
            </div>
        </div>

        <!-- Solo el Titular / Nombre (sin número por seguridad) -->
        <div class="bg-slate-950 p-3.5 rounded-xl border border-slate-800 text-xs mb-4 text-center">
            <span class="text-[11px] text-slate-400 block mb-0.5">Titular de la cuenta:</span>
            <span id="qrModalHolder" class="font-bold text-emerald-400 text-sm sm:text-base"></span>
        </div>

        <button type="button" onclick="closeQrModal()" class="w-full py-3 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-xl text-xs transition cursor-pointer">
            Listo, ya transferí
        </button>
    </div>
</div>

<script>
    function showQrModal(title, qrUrl, holder) {
        document.getElementById('qrModalTitle').innerText = title;
        document.getElementById('qrModalHolder').innerText = holder;

        const container = document.getElementById('qrImageContainer');
        if (qrUrl && qrUrl.trim() !== '') {
            container.innerHTML = `<img src="${qrUrl}" alt="${title}" class="w-full h-full object-contain">`;
        } else {
            container.innerHTML = `<div class="text-center p-4 text-slate-700 font-bold text-xs"><span class="text-4xl block mb-2">📸</span>Escanea el QR oficial desde tu app</div>`;
        }

        document.getElementById('qrModal').classList.remove('hidden');
    }

    function closeQrModal() {
        document.getElementById('qrModal').classList.add('hidden');
    }

    document.getElementById('qrModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeQrModal();
        }
    });
</script>
@endsection
