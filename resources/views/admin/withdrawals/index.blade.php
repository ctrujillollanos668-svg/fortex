@extends('layouts.admin')

@section('title', 'Gestión de Retiros')

@section('content')
<div class="space-y-6">
    <!-- Encabezado y Filtros -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-white flex items-center gap-2">
                <span>📤</span> Solicitudes de Retiro
            </h1>
            <p class="text-slate-400 text-xs sm:text-sm mt-0.5">Revisa las cuentas/billeteras de los clientes y marca los pagos enviados.</p>
        </div>

        <!-- Filtros por Estado -->
        <div class="flex items-center gap-2 bg-slate-900 p-1.5 rounded-2xl border border-slate-800 text-xs">
            <a href="{{ route('admin.withdrawals.index') }}" class="px-3 py-1.5 rounded-xl font-semibold transition {{ empty($status) ? 'bg-cyan-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white' }}">
                Todos
            </a>
            <a href="{{ route('admin.withdrawals.index', ['status' => 'pending']) }}" class="px-3 py-1.5 rounded-xl font-semibold transition flex items-center gap-1.5 {{ $status === 'pending' ? 'bg-amber-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white' }}">
                <span>Pendientes</span>
                @if($pendingCount > 0)
                    <span class="px-1.5 py-0.2 rounded-full bg-slate-950 text-white text-[10px]">{{ $pendingCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.withdrawals.index', ['status' => 'approved']) }}" class="px-3 py-1.5 rounded-xl font-semibold transition {{ $status === 'approved' ? 'bg-emerald-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white' }}">
                Pagados
            </a>
            <a href="{{ route('admin.withdrawals.index', ['status' => 'rejected']) }}" class="px-3 py-1.5 rounded-xl font-semibold transition {{ $status === 'rejected' ? 'bg-rose-500 text-white font-bold' : 'text-slate-400 hover:text-white' }}">
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

    <!-- Tabla de Retiros -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
        @if($withdrawals->isEmpty())
            <div class="p-12 text-center text-slate-500 text-xs">
                <span class="text-3xl block mb-2">📭</span>
                No hay solicitudes de retiro en esta categoría.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 bg-slate-950/60 border-b border-slate-800">
                            <th class="py-3.5 px-4 font-semibold">ID</th>
                            <th class="py-3.5 px-4 font-semibold">Cliente</th>
                            <th class="py-3.5 px-4 font-semibold">Monto Solicitado</th>
                            <th class="py-3.5 px-4 font-semibold">Comisión / Neto a Enviar</th>
                            <th class="py-3.5 px-4 font-semibold">Billetera / Cuenta Destino</th>
                            <th class="py-3.5 px-4 font-semibold">Estado / Explicación</th>
                            <th class="py-3.5 px-4 font-semibold">Fecha</th>
                            <th class="py-3.5 px-4 font-semibold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/80">
                        @foreach($withdrawals as $with)
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="py-4 px-4 font-mono text-slate-400">#{{ $with->id }}</td>
                                <td class="py-4 px-4">
                                    <div class="font-bold text-white">{{ $with->user->name ?? 'Usuario Eliminado' }}</div>
                                    <div class="text-slate-400 text-[11px]">{{ $with->user->email ?? '' }}</div>
                                    @if($with->user->phone ?? false)
                                        <div class="text-emerald-400 text-[10px]">📱 {{ $with->user->phone }}</div>
                                    @endif
                                </td>
                                <td class="py-4 px-4 font-mono text-slate-300 font-bold">
                                    ${{ number_format($with->amount, 0, ',', '.') }} <span class="text-[10px] text-slate-500 font-normal">COP</span>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="text-cyan-400 font-black font-mono text-sm">${{ number_format($with->net_amount, 0, ',', '.') }} COP</div>
                                    <div class="text-[10px] text-rose-400 font-mono font-semibold">Comisión (8%): -${{ number_format($with->fee, 0, ',', '.') }} COP</div>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-1.5 font-mono text-slate-200 bg-slate-950 px-2.5 py-1.5 rounded-lg border border-slate-800 text-[11px]">
                                        <span class="truncate max-w-[200px]" id="wallet-{{ $with->id }}">{{ $with->wallet_or_account }}</span>
                                        <button onclick="navigator.clipboard.writeText('{{ $with->wallet_or_account }}'); alert('¡Billetera copiada!')" title="Copiar billetera" class="text-cyan-400 hover:text-cyan-300 cursor-pointer">📋</button>
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    @if($with->status === 'pending')
                                        <span class="px-2.5 py-1 rounded-full bg-amber-500/15 border border-amber-500/30 text-amber-400 text-[10px] font-bold uppercase">Pendiente</span>
                                    @elseif($with->status === 'approved')
                                        <span class="px-2.5 py-1 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-[10px] font-bold uppercase">Pagado</span>
                                        @if($with->admin_notes)
                                            <p class="text-[10px] text-emerald-400/80 mt-1 max-w-[180px] leading-tight">{{ $with->admin_notes }}</p>
                                        @endif
                                    @else
                                        <span class="px-2.5 py-1 rounded-full bg-rose-500/15 border border-rose-500/30 text-rose-400 text-[10px] font-bold uppercase">Rechazado & Reembolsado</span>
                                        @if($with->admin_notes)
                                            <p class="text-[10px] text-rose-300 mt-1 max-w-[190px] leading-tight">⚠️ {{ $with->admin_notes }}</p>
                                        @endif
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-slate-400 text-[11px]">
                                    {{ $with->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="py-4 px-4 text-right">
                                    @if($with->status === 'pending')
                                        <div class="flex items-center justify-end gap-2">
                                            <!-- Formulario Aprobar -->
                                            <form id="approve-with-{{ $with->id }}" method="POST" action="{{ route('admin.withdrawals.approve', $with->id) }}">
                                                @csrf
                                                <button type="button" onclick="confirmCustomAction({
                                                    title: '¿Confirmar Pago de Retiro?',
                                                    html: '¿Confirmas que ya realizaste la transferencia de <b class=\'text-cyan-400\'>${{ number_format($with->net_amount, 0, ',', '.') }} COP</b> a la cuenta <b class=\'text-white\'>{{ $with->wallet_or_account }}</b>?',
                                                    icon: 'question',
                                                    confirmText: '✓ Sí, Marcar como Pagado',
                                                    confirmColor: '#06b6d4',
                                                    formId: 'approve-with-{{ $with->id }}'
                                                })" class="px-3 py-1.5 bg-cyan-500 hover:bg-cyan-600 text-slate-950 font-black rounded-xl text-xs transition cursor-pointer shadow-md">
                                                    ✓ Marcar Pagado
                                                </button>
                                            </form>

                                            <!-- Botón Rechazar que Abre Modal de Motivo -->
                                            <button type="button" onclick="openRejectWithdrawalModal({{ $with->id }}, '{{ addslashes($with->user->name ?? 'Cliente') }}', '${{ number_format($with->amount, 0, ',', '.') }} COP', '{{ addslashes($with->wallet_or_account) }}')" class="px-3 py-1.5 bg-rose-500/15 hover:bg-rose-500/25 border border-rose-500/30 text-rose-400 font-bold rounded-xl text-xs transition cursor-pointer">
                                                ✕ Rechazar & Reembolsar
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-slate-500 text-[11px] italic">Procesado</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="p-4 border-t border-slate-800">
                {{ $withdrawals->links() }}
            </div>
        @endif
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL PARA RECHAZAR Y REEMBOLSAR RETIRO CON EXPLICACIÓN -->
<!-- ======================================================== -->
<div id="rejectWithdrawalModal" class="fixed inset-0 bg-black/90 backdrop-blur-md z-50 hidden flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 max-w-lg w-full shadow-2xl relative max-h-[90vh] overflow-y-auto">
        <!-- Botón Cerrar -->
        <button onclick="closeRejectWithdrawalModal()" class="absolute right-5 top-5 text-slate-400 hover:text-white text-2xl font-bold transition">
            ✕
        </button>

        <div class="flex items-center gap-2 mb-3">
            <span class="text-2xl">⚠️</span>
            <div>
                <h3 class="text-lg font-black text-white">Rechazar Solicitud de Retiro</h3>
                <p id="rejectWithSubtitle" class="text-xs text-slate-400"></p>
            </div>
        </div>

        <div class="p-3 bg-rose-500/10 border border-rose-500/20 rounded-2xl text-[11px] text-rose-300 mb-4 flex items-center gap-2.5">
            <span class="text-base">💰</span>
            <span>El monto total se <b>reembolsará automáticamente</b> al balance del cliente para que pueda corregir sus datos.</span>
        </div>

        <form id="rejectWithdrawalForm" method="POST" action="" class="space-y-4 text-xs">
            @csrf

            <!-- Resumen del Retiro -->
            <div class="bg-slate-950 p-3 rounded-2xl border border-slate-800 space-y-1">
                <div class="flex justify-between">
                    <span class="text-slate-400">Monto a reembolsar:</span>
                    <span id="rejectWithAmount" class="font-black text-rose-400 font-mono"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Cuenta de destino:</span>
                    <span id="rejectWithAccount" class="font-mono text-slate-200"></span>
                </div>
            </div>

            <!-- Selección de Motivo Rápido -->
            <div>
                <label class="block font-semibold text-slate-300 mb-1.5">Selecciona el Motivo / Explicación del Rechazo:</label>
                <div class="space-y-2">
                    <label class="flex items-center gap-2.5 p-2.5 bg-slate-950 hover:bg-slate-800/80 border border-slate-800 rounded-xl cursor-pointer transition">
                        <input type="radio" name="with_reason_preset" value="Número de cuenta o billetera incorrecto / no existe en el banco." checked onchange="handleWithdrawReasonChange(this.value)" class="text-rose-500 focus:ring-rose-500">
                        <span class="text-slate-200">🚫 Cuenta o Nequi incorrecto / no existe</span>
                    </label>

                    <label class="flex items-center gap-2.5 p-2.5 bg-slate-950 hover:bg-slate-800/80 border border-slate-800 rounded-xl cursor-pointer transition">
                        <input type="radio" name="with_reason_preset" value="El nombre del titular de la cuenta no coincide con el registrado en la plataforma." onchange="handleWithdrawReasonChange(this.value)" class="text-rose-500 focus:ring-rose-500">
                        <span class="text-slate-200">👤 Titular de la cuenta no coincide</span>
                    </label>

                    <label class="flex items-center gap-2.5 p-2.5 bg-slate-950 hover:bg-slate-800/80 border border-slate-800 rounded-xl cursor-pointer transition">
                        <input type="radio" name="with_reason_preset" value="Billetera temporalmente con límites de recepción alcanzados (Topes Nequi/Daviplata)." onchange="handleWithdrawReasonChange(this.value)" class="text-rose-500 focus:ring-rose-500">
                        <span class="text-slate-200">💳 Billetera con topes mensuales superados</span>
                    </label>

                    <label class="flex items-center gap-2.5 p-2.5 bg-slate-950 hover:bg-slate-800/80 border border-slate-800 rounded-xl cursor-pointer transition">
                        <input type="radio" name="with_reason_preset" value="Información incompleta: especifica tipo de cuenta (Ahorros/Corriente) o banco." onchange="handleWithdrawReasonChange(this.value)" class="text-rose-500 focus:ring-rose-500">
                        <span class="text-slate-200">📝 Datos incompletos o ambiguos</span>
                    </label>

                    <label class="flex items-center gap-2.5 p-2.5 bg-slate-950 hover:bg-slate-800/80 border border-slate-800 rounded-xl cursor-pointer transition">
                        <input type="radio" name="with_reason_preset" value="custom" onchange="handleWithdrawReasonChange(this.value)" class="text-rose-500 focus:ring-rose-500">
                        <span class="text-slate-200">✍️ Escribir otro motivo personalizado</span>
                    </label>
                </div>
            </div>

            <!-- Campo de Texto para el Mensaje / Explicación al Cliente -->
            <div>
                <label class="block font-semibold text-slate-300 mb-1">Mensaje de Explicación (El cliente lo verá en su panel de retiros):</label>
                <textarea id="with_admin_notes_input" name="admin_notes" rows="2" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none focus:border-rose-500 text-xs">Número de cuenta o billetera incorrecto / no existe en el banco.</textarea>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <button type="button" onclick="closeRejectWithdrawalModal()" class="flex-1 py-3 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-xl text-xs transition cursor-pointer">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 py-3 bg-rose-500 hover:bg-rose-600 text-white font-black rounded-xl text-xs transition cursor-pointer shadow-lg shadow-rose-500/25">
                    🚫 Confirmar Rechazo & Reembolso
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openRejectWithdrawalModal(id, clientName, amount, account) {
        document.getElementById('rejectWithdrawalForm').action = `/admin/withdrawals/${id}/reject`;
        document.getElementById('rejectWithSubtitle').innerText = `Cliente: ${clientName}`;
        document.getElementById('rejectWithAmount').innerText = amount;
        document.getElementById('rejectWithAccount').innerText = account;

        document.getElementById('with_admin_notes_input').value = 'Número de cuenta o billetera incorrecto / no existe en el banco.';
        document.getElementById('rejectWithdrawalModal').classList.remove('hidden');
    }

    function closeRejectWithdrawalModal() {
        document.getElementById('rejectWithdrawalModal').classList.add('hidden');
    }

    function handleWithdrawReasonChange(value) {
        const input = document.getElementById('with_admin_notes_input');
        if (value === 'custom') {
            input.value = '';
            input.placeholder = 'Escribe aquí la razón o explicación para el cliente...';
            input.focus();
        } else {
            input.value = value;
        }
    }
</script>
@endsection
