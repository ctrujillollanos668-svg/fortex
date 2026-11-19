@extends('layouts.admin')

@section('title', 'Gestión de Depósitos y Recargas')

@section('content')
<div class="space-y-6">
    <!-- Encabezado y Filtros -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-white flex items-center gap-2">
                <span>📥</span> Gestión de Depósitos / Recargas
            </h1>
            <p class="text-slate-400 text-xs sm:text-sm mt-0.5">Aprueba o rechaza los comprobantes de pago de los clientes en Pesos Colombianos ($ COP).</p>
        </div>

        <!-- Filtros por Estado -->
        <div class="flex items-center gap-2 bg-slate-900 p-1.5 rounded-2xl border border-slate-800 text-xs">
            <a href="{{ route('admin.deposits.index') }}" class="px-3 py-1.5 rounded-xl font-semibold transition {{ empty($status) ? 'bg-emerald-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white' }}">
                Todos
            </a>
            <a href="{{ route('admin.deposits.index', ['status' => 'pending']) }}" class="px-3 py-1.5 rounded-xl font-semibold transition flex items-center gap-1.5 {{ $status === 'pending' ? 'bg-amber-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white' }}">
                <span>Pendientes</span>
                @if($pendingCount > 0)
                    <span class="px-1.5 py-0.2 rounded-full bg-slate-950 text-white text-[10px]">{{ $pendingCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.deposits.index', ['status' => 'approved']) }}" class="px-3 py-1.5 rounded-xl font-semibold transition {{ $status === 'approved' ? 'bg-emerald-500 text-slate-950' : 'text-slate-400 hover:text-white' }}">
                Aprobados
            </a>
            <a href="{{ route('admin.deposits.index', ['status' => 'rejected']) }}" class="px-3 py-1.5 rounded-xl font-semibold transition {{ $status === 'rejected' ? 'bg-rose-500 text-white font-bold' : 'text-slate-400 hover:text-white' }}">
                Rechazados
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-500/15 border border-emerald-500/30 rounded-2xl text-emerald-300 text-xs sm:text-sm flex items-center gap-3 shadow-lg shadow-emerald-500/10">
            <span class="text-xl">✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Tabla de Depósitos -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
        @if($deposits->isEmpty())
            <div class="p-12 text-center text-slate-500 text-xs">
                <span class="text-3xl block mb-2">📭</span>
                No hay solicitudes de recarga que coincidan con el filtro seleccionado.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 bg-slate-950/60 border-b border-slate-800">
                            <th class="py-3.5 px-4 font-semibold">ID</th>
                            <th class="py-3.5 px-4 font-semibold">Cliente</th>
                            <th class="py-3.5 px-4 font-semibold">Monto ($ COP)</th>
                            <th class="py-3.5 px-4 font-semibold">Método & Ref</th>
                            <th class="py-3.5 px-4 font-semibold text-center">Foto Comprobante</th>
                            <th class="py-3.5 px-4 font-semibold">Estado / Motivo</th>
                            <th class="py-3.5 px-4 font-semibold">Fecha</th>
                            <th class="py-3.5 px-4 font-semibold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/80">
                        @foreach($deposits as $dep)
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="py-4 px-4 font-mono text-slate-400">#{{ $dep->id }}</td>
                                <td class="py-4 px-4">
                                    <div class="font-bold text-white">{{ $dep->user->name ?? 'Usuario Eliminado' }}</div>
                                    <div class="text-slate-400 text-[11px] font-mono">{{ $dep->user->email ?? '' }}</div>
                                    @if($dep->user->phone ?? false)
                                        <div class="text-emerald-400 text-[10px]">📱 {{ $dep->user->phone }}</div>
                                    @endif
                                </td>
                                <td class="py-4 px-4">
                                    <span class="text-emerald-400 font-black font-mono text-sm">${{ number_format($dep->amount, 0, ',', '.') }}</span>
                                    <span class="text-[10px] text-slate-500 block">COP</span>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="px-2.5 py-1 rounded-lg bg-slate-800 text-slate-200 font-bold">{{ $dep->payment_method }}</span>
                                    @if($dep->transaction_hash)
                                        <p class="text-[11px] text-amber-400 font-mono mt-1 font-semibold">Ref: {{ $dep->transaction_hash }}</p>
                                    @endif
                                </td>

                                <!-- Columna de la Foto del Comprobante -->
                                <td class="py-4 px-4 text-center">
                                    @if($dep->encoded_image)
                                        <div class="inline-flex flex-col items-center gap-1.5">
                                            <button type="button" onclick="openProofModal('{{ $dep->encoded_image }}', '{{ $dep->user->name ?? 'Cliente' }}', '${{ number_format($dep->amount, 0, ',', '.') }} COP', '{{ $dep->payment_method }}', '{{ $dep->transaction_hash }}')" class="group relative block w-14 h-14 rounded-xl overflow-hidden border border-slate-700 hover:border-emerald-500 transition shadow-md cursor-pointer">
                                                <img src="{{ $dep->encoded_image }}" alt="Comprobante de Pago" class="w-full h-full object-cover group-hover:scale-110 transition duration-300">
                                                <div class="absolute inset-0 bg-black/40 group-hover:bg-black/10 flex items-center justify-center text-white text-xs font-bold transition">
                                                    🔍
                                                </div>
                                            </button>
                                            <span class="text-[10px] text-emerald-400 font-semibold cursor-pointer" onclick="openProofModal('{{ $dep->encoded_image }}', '{{ $dep->user->name ?? 'Cliente' }}', '${{ number_format($dep->amount, 0, ',', '.') }} COP', '{{ $dep->payment_method }}', '{{ $dep->transaction_hash }}')">Ver Captura</span>
                                        </div>
                                    @elseif($dep->proof_image)
                                        <div class="inline-flex flex-col items-center gap-1.5">
                                            <button type="button" onclick="openProofModal('{{ route('admin.deposits.image', $dep->id) }}', '{{ $dep->user->name ?? 'Cliente' }}', '${{ number_format($dep->amount, 0, ',', '.') }} COP', '{{ $dep->payment_method }}', '{{ $dep->transaction_hash }}')" class="group relative block w-14 h-14 rounded-xl overflow-hidden border border-slate-700 hover:border-emerald-500 transition shadow-md cursor-pointer">
                                                <img src="{{ route('admin.deposits.image', $dep->id) }}" alt="Comprobante de Pago" class="w-full h-full object-cover group-hover:scale-110 transition duration-300">
                                                <div class="absolute inset-0 bg-black/40 group-hover:bg-black/10 flex items-center justify-center text-white text-xs font-bold transition">
                                                    🔍
                                                </div>
                                            </button>
                                            <span class="text-[10px] text-emerald-400 font-semibold cursor-pointer" onclick="openProofModal('{{ route('admin.deposits.image', $dep->id) }}', '{{ $dep->user->name ?? 'Cliente' }}', '${{ number_format($dep->amount, 0, ',', '.') }} COP', '{{ $dep->payment_method }}', '{{ $dep->transaction_hash }}')">Ver Captura</span>
                                        </div>
                                    @else
                                        <span class="text-slate-500 text-[11px] italic">Sin foto adjunta</span>
                                    @endif
                                </td>

                                <td class="py-4 px-4">
                                    @if($dep->status === 'pending')
                                        <span class="px-2.5 py-1 rounded-full bg-amber-500/15 border border-amber-500/30 text-amber-400 text-[10px] font-bold uppercase">Pendiente</span>
                                    @elseif($dep->status === 'approved')
                                        <span class="px-2.5 py-1 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-[10px] font-bold uppercase">Aprobado</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full bg-rose-500/15 border border-rose-500/30 text-rose-400 text-[10px] font-bold uppercase">Rechazado</span>
                                        @if($dep->admin_notes)
                                            <p class="text-[10px] text-rose-300 mt-1 max-w-[170px] leading-tight">⚠️ {{ $dep->admin_notes }}</p>
                                        @endif
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-slate-400 text-[11px]">
                                    {{ $dep->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="py-4 px-4 text-right">
                                    @if($dep->status === 'pending')
                                        <div class="flex items-center justify-end gap-2">
                                            <!-- Formulario Aprobar -->
                                            <form id="approve-form-{{ $dep->id }}" method="POST" action="{{ route('admin.deposits.approve', $dep->id) }}">
                                                @csrf
                                                <button type="button" onclick="confirmCustomAction({
                                                    title: '¿Aprobar Recarga?',
                                                    html: '¿Confirmas acreditar <b class=\'text-emerald-400\'>${{ number_format($dep->amount, 0, ',', '.') }} COP</b> a la cuenta de <b>{{ $dep->user->name }}</b>?',
                                                    icon: 'success',
                                                    confirmText: '✓ Sí, Aprobar y Acreditar',
                                                    confirmColor: '#10b981',
                                                    formId: 'approve-form-{{ $dep->id }}'
                                                })" class="px-3 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-black rounded-xl text-xs transition cursor-pointer shadow-md">
                                                    ✓ Aprobar
                                                </button>
                                            </form>

                                            <!-- Botón Rechazar que Abre Modal de Motivo -->
                                            <button type="button" onclick="openRejectModal({{ $dep->id }}, '{{ $dep->user->name ?? 'Cliente' }}', '${{ number_format($dep->amount, 0, ',', '.') }} COP', '{{ $dep->payment_method }}', '{{ $dep->transaction_hash }}', '{{ $dep->encoded_image ?? ($dep->proof_image ? route('admin.deposits.image', $dep->id) : '') }}')" class="px-3 py-1.5 bg-rose-500/15 hover:bg-rose-500/25 border border-rose-500/30 text-rose-400 font-bold rounded-xl text-xs transition cursor-pointer">
                                                ✕ Rechazar
                                            </button>

                                            <!-- Formulario Eliminar -->
                                            <form method="POST" action="{{ route('admin.deposits.destroy', $dep->id) }}" onsubmit="return confirm('¿Seguro que deseas eliminar este comprobante?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Eliminar registro" class="p-1.5 text-slate-500 hover:text-rose-400 hover:bg-rose-500/15 rounded-lg transition cursor-pointer">
                                                    🗑️
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <div class="flex items-center justify-end gap-2">
                                            <span class="text-slate-500 text-[11px]">Procesado</span>
                                            <form method="POST" action="{{ route('admin.deposits.destroy', $dep->id) }}" onsubmit="return confirm('¿Seguro que deseas eliminar este comprobante?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Eliminar registro" class="p-1.5 text-slate-500 hover:text-rose-400 hover:bg-rose-500/15 rounded-lg transition cursor-pointer">
                                                    🗑️
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="p-4 border-t border-slate-800">
                {{ $deposits->links() }}
            </div>
        @endif
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL PARA RECHAZAR RECARGA CON MOTIVO -->
<!-- ========================================== -->
<div id="rejectModal" class="fixed inset-0 bg-black/90 backdrop-blur-md z-50 hidden flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 max-w-lg w-full shadow-2xl relative max-h-[90vh] overflow-y-auto">
        <!-- Botón Cerrar -->
        <button onclick="closeRejectModal()" class="absolute right-5 top-5 text-slate-400 hover:text-white text-2xl font-bold transition">
            ✕
        </button>

        <div class="flex items-center gap-2 mb-3">
            <span class="text-2xl">⚠️</span>
            <div>
                <h3 class="text-lg font-black text-white">Rechazar Comprobante / Recarga</h3>
                <p id="rejectModalSubtitle" class="text-xs text-slate-400"></p>
            </div>
        </div>

        <form id="rejectForm" method="POST" action="" class="space-y-4 text-xs">
            @csrf

            <!-- Resumen del Pago -->
            <div class="flex items-center gap-3 bg-slate-950 p-3 rounded-2xl border border-slate-800">
                <div id="rejectProofThumb" class="w-14 h-14 bg-slate-900 border border-slate-800 rounded-xl overflow-hidden flex items-center justify-center flex-shrink-0">
                    <!-- Thumbnail preview -->
                </div>
                <div class="flex-1 space-y-0.5">
                    <p id="rejectModalAmount" class="font-black text-rose-400 text-sm font-mono"></p>
                    <p id="rejectModalMethod" class="text-slate-300 text-[11px]"></p>
                    <p id="rejectModalRef" class="text-slate-400 text-[10px] font-mono"></p>
                </div>
            </div>

            <!-- Selección de Motivo Rápido -->
            <div>
                <label class="block font-semibold text-slate-300 mb-1.5">Selecciona el Motivo del Rechazo:</label>
                <div class="space-y-2">
                    <label class="flex items-center gap-2.5 p-2.5 bg-slate-950 hover:bg-slate-800/80 border border-slate-800 rounded-xl cursor-pointer transition">
                        <input type="radio" name="reason_preset" value="Comprobante falso o manipulado." checked onchange="handleReasonChange(this.value)" class="text-rose-500 focus:ring-rose-500">
                        <span class="text-slate-200">🚫 Comprobante falso o manipulado</span>
                    </label>

                    <label class="flex items-center gap-2.5 p-2.5 bg-slate-950 hover:bg-slate-800/80 border border-slate-800 rounded-xl cursor-pointer transition">
                        <input type="radio" name="reason_preset" value="No se recibió la transferencia en la cuenta de Nequi / Bancolombia." onchange="handleReasonChange(this.value)" class="text-rose-500 focus:ring-rose-500">
                        <span class="text-slate-200">💳 Pago no recibido en la cuenta bancaria</span>
                    </label>

                    <label class="flex items-center gap-2.5 p-2.5 bg-slate-950 hover:bg-slate-800/80 border border-slate-800 rounded-xl cursor-pointer transition">
                        <input type="radio" name="reason_preset" value="El monto de la transferencia no coincide con lo reportado." onchange="handleReasonChange(this.value)" class="text-rose-500 focus:ring-rose-500">
                        <span class="text-slate-200">💰 Monto transferido no coincide</span>
                    </label>

                    <label class="flex items-center gap-2.5 p-2.5 bg-slate-950 hover:bg-slate-800/80 border border-slate-800 rounded-xl cursor-pointer transition">
                        <input type="radio" name="reason_preset" value="Comprobante ya utilizado en otra recarga (Duplicado)." onchange="handleReasonChange(this.value)" class="text-rose-500 focus:ring-rose-500">
                        <span class="text-slate-200">🔁 Comprobante duplicado / ya usado</span>
                    </label>

                    <label class="flex items-center gap-2.5 p-2.5 bg-slate-950 hover:bg-slate-800/80 border border-slate-800 rounded-xl cursor-pointer transition">
                        <input type="radio" name="reason_preset" value="custom" onchange="handleReasonChange(this.value)" class="text-rose-500 focus:ring-rose-500">
                        <span class="text-slate-200">✍️ Escribir otro motivo personalizado</span>
                    </label>
                </div>
            </div>

            <!-- Campo de Texto para el Motivo / Mensaje que verá el cliente -->
            <div>
                <label class="block font-semibold text-slate-300 mb-1">Mensaje de Rechazo (El cliente lo verá en su panel):</label>
                <textarea id="admin_notes_input" name="admin_notes" rows="2" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none focus:border-rose-500 text-xs">Comprobante falso o manipulado.</textarea>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <button type="button" onclick="closeRejectModal()" class="flex-1 py-3 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-xl text-xs transition cursor-pointer">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 py-3 bg-rose-500 hover:bg-rose-600 text-white font-black rounded-xl text-xs transition cursor-pointer shadow-lg shadow-rose-500/25">
                    🚫 Confirmar Rechazo
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL LIGHTBOX PARA VER EL COMPROBANTE EN GRANDE -->
<!-- ========================================== -->
<div id="proofModal" class="fixed inset-0 bg-black/90 backdrop-blur-md z-50 hidden flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 max-w-xl w-full shadow-2xl relative max-h-[90vh] overflow-y-auto">
        <!-- Botón Cerrar -->
        <button onclick="closeProofModal()" class="absolute right-5 top-5 text-slate-400 hover:text-white text-2xl font-bold transition">
            ✕
        </button>

        <div class="flex items-center gap-2 mb-4">
            <span class="text-xl">🧾</span>
            <div>
                <h3 class="text-base font-extrabold text-white">Comprobante de Pago del Cliente</h3>
                <p id="proofSubtitle" class="text-xs text-slate-400"></p>
            </div>
        </div>

        <!-- Imagen del Comprobante -->
        <div class="bg-slate-950 p-2 rounded-2xl border border-slate-800 flex items-center justify-center overflow-hidden mb-4 min-h-[250px] max-h-[450px]">
            <img id="proofImage" src="" alt="Comprobante de Pago" class="max-h-[430px] w-auto object-contain rounded-xl shadow-lg">
        </div>

        <!-- Detalles del Pago -->
        <div class="grid grid-cols-2 gap-2 bg-slate-950 p-3 rounded-xl border border-slate-800 text-xs mb-4">
            <div>
                <span class="text-slate-400 block text-[10px]">Monto Declarado:</span>
                <span id="proofAmount" class="font-bold text-emerald-400 font-mono text-sm"></span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px]">Método:</span>
                <span id="proofMethod" class="font-bold text-white"></span>
            </div>
            <div class="col-span-2 pt-1 border-t border-slate-800">
                <span class="text-slate-400 block text-[10px]">Referencia / Hash / Celular:</span>
                <span id="proofRef" class="font-mono font-bold text-amber-400"></span>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a id="proofDownloadBtn" href="" target="_blank" class="flex-1 py-2.5 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-xl text-xs text-center transition flex items-center justify-center gap-1.5">
                <span>⬇️</span> Abrir / Descargar Imagen
            </a>
            <button type="button" onclick="closeProofModal()" class="px-5 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-black rounded-xl text-xs transition cursor-pointer">
                Listo
            </button>
        </div>
    </div>
</div>

<script>
    function openProofModal(imageUrl, clientName, amount, method, ref) {
        document.getElementById('proofImage').src = imageUrl;
        document.getElementById('proofSubtitle').innerText = `Enviado por: ${clientName}`;
        document.getElementById('proofAmount').innerText = amount;
        document.getElementById('proofMethod').innerText = method;
        document.getElementById('proofRef').innerText = ref || 'Sin referencia';
        document.getElementById('proofDownloadBtn').href = imageUrl;

        document.getElementById('proofModal').classList.remove('hidden');
    }

    function closeProofModal() {
        document.getElementById('proofModal').classList.add('hidden');
    }

    // Modal de Rechazo
    function openRejectModal(id, clientName, amount, method, ref, proofUrl) {
        document.getElementById('rejectForm').action = `/admin/deposits/${id}/reject`;
        document.getElementById('rejectModalSubtitle').innerText = `Cliente: ${clientName}`;
        document.getElementById('rejectModalAmount').innerText = amount;
        document.getElementById('rejectModalMethod').innerText = `Método: ${method}`;
        document.getElementById('rejectModalRef').innerText = `Ref: ${ref || 'Sin ref'}`;

        const thumbContainer = document.getElementById('rejectProofThumb');
        if (proofUrl && proofUrl.trim() !== '') {
            thumbContainer.innerHTML = `<img src="${proofUrl}" alt="Comprobante" class="w-full h-full object-cover cursor-pointer" onclick="openProofModal('${proofUrl}', '${clientName}', '${amount}', '${method}', '${ref}')">`;
        } else {
            thumbContainer.innerHTML = `<span class="text-xs text-slate-500">Sin foto</span>`;
        }

        document.getElementById('admin_notes_input').value = 'Comprobante falso o manipulado.';
        document.getElementById('rejectModal').classList.remove('hidden');
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
    }

    function handleReasonChange(value) {
        const input = document.getElementById('admin_notes_input');
        if (value === 'custom') {
            input.value = '';
            input.placeholder = 'Escribe aquí la razón exacta del rechazo...';
            input.focus();
        } else {
            input.value = value;
        }
    }
</script>
@endsection
