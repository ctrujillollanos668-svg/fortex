@extends('layouts.admin')

@section('title', 'Gestión de Clientes y Red')

@section('content')
<div class="space-y-6">
    <!-- Encabezado y Barra de Búsqueda -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-white flex items-center gap-2">
                <span>👥</span> Gestión de Clientes y Red
            </h1>
            <p class="text-slate-400 text-xs sm:text-sm mt-0.5">Controla saldos, planes comprados, ganancias generadas, historial y auditoría.</p>
        </div>

        <div class="flex items-center gap-3">
            <div class="bg-slate-900 px-4 py-2 rounded-2xl border border-slate-800 text-xs">
                <span class="text-slate-400">Saldo Total en Manos de Clientes:</span>
                <strong class="text-emerald-400 font-mono ml-1 text-sm">${{ number_format($totalBalance, 0, ',', '.') }} COP</strong>
            </div>
        </div>
    </div>

    <!-- Buscador -->
    <div class="bg-slate-900/60 p-4 rounded-2xl border border-slate-800">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex gap-3">
            <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por nombre, correo o código de referido..." class="flex-1 px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs focus:outline-none focus:border-amber-500">
            <button type="submit" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold rounded-xl text-xs transition cursor-pointer">
                🔍 Buscar
            </button>
            @if($search)
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2.5 bg-slate-800 text-slate-300 rounded-xl text-xs flex items-center">Limpiar</a>
            @endif
        </form>
    </div>

    <!-- Tabla de Clientes -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
        @if($users->isEmpty())
            <div class="p-12 text-center text-slate-500 text-xs">
                <span class="text-3xl block mb-2">👤</span>
                No se encontraron clientes registrados con ese criterio.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 bg-slate-950/60 border-b border-slate-800">
                            <th class="py-3.5 px-4 font-semibold">Cliente</th>
                            <th class="py-3.5 px-4 font-semibold">Planes Comprados</th>
                            <th class="py-3.5 px-4 font-semibold">Ingresos / Ganancias</th>
                            <th class="py-3.5 px-4 font-semibold">Saldo Actual</th>
                            <th class="py-3.5 px-4 font-semibold">Red / Patrocinador</th>
                            <th class="py-3.5 px-4 font-semibold">Estado</th>
                            <th class="py-3.5 px-4 font-semibold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/80">
                        @foreach($users as $u)
                            <tr class="hover:bg-slate-800/40 transition">
                                <!-- Cliente -->
                                <td class="py-4 px-4">
                                    <div class="font-bold text-white text-sm">{{ $u->name }}</div>
                                    <div class="text-slate-400 text-[11px]">{{ $u->email }}</div>
                                    @if($u->phone)
                                        <div class="text-cyan-400 text-[11px] font-mono mt-0.5">📞 {{ $u->phone }}</div>
                                    @endif
                                    <div class="mt-1">
                                        <span class="font-mono text-emerald-400 text-[10px] font-bold bg-slate-950 px-2 py-0.5 rounded border border-slate-800">Cód: {{ $u->referral_code }}</span>
                                    </div>
                                </td>

                                <!-- Planes Comprados -->
                                <td class="py-4 px-4">
                                    @if($u->userPlans && $u->userPlans->count() > 0)
                                        <div class="space-y-1">
                                            @foreach($u->userPlans as $up)
                                                <div class="inline-flex items-center gap-1.5 bg-slate-950 px-2.5 py-1 rounded-lg border border-slate-800 text-[11px] whitespace-nowrap">
                                                    <span class="text-amber-400 font-bold">⚡ {{ $up->plan->name ?? 'Plan' }}</span>
                                                    <span class="text-slate-400 font-mono">(${{ number_format($up->invested_amount, 0, ',', '.') }})</span>
                                                    <span class="text-[9px] px-1.5 py-0.2 rounded font-bold uppercase {{ $up->status === 'active' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-700 text-slate-300' }}">
                                                        {{ $up->status === 'active' ? 'Activo' : 'Finalizado' }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-slate-500 italic text-[11px] bg-slate-950/60 px-2.5 py-1 rounded-lg border border-slate-800/60">Sin planes comprados</span>
                                    @endif
                                </td>

                                <!-- Ingresos Recibidos -->
                                <td class="py-4 px-4 whitespace-nowrap">
                                    @php
                                        $plansEarned = (float) $u->userPlans->sum('earned_so_far');
                                        $withdrawable = (float) $u->withdrawableBalance();
                                    @endphp
                                    <div>
                                        <div class="text-emerald-400 font-mono font-extrabold text-xs">
                                            +${{ number_format($plansEarned, 0, ',', '.') }} COP
                                        </div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">
                                            Retirable: <span class="text-cyan-400 font-mono font-bold">${{ number_format($withdrawable, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Saldo Actual -->
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <span class="text-white font-mono font-extrabold text-sm">${{ number_format($u->balance, 0, ',', '.') }}</span>
                                    <span class="text-[10px] text-slate-500 block font-medium">COP</span>
                                </td>

                                <!-- Red / Patrocinador -->
                                <td class="py-4 px-4">
                                    <div class="text-[11px]">
                                        <span class="text-slate-300 font-medium font-mono">{{ $u->referrals_count }} directos</span>
                                        <div class="text-[10px] text-slate-500 mt-0.5">
                                            Patrocinador:
                                            @if($u->sponsor)
                                                <span class="text-cyan-400 font-semibold">{{ $u->sponsor->name }}</span>
                                            @else
                                                <span class="italic text-slate-600">Directo</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Estado -->
                                <td class="py-4 px-4">
                                    @if($u->status === 'active')
                                        <span class="px-2.5 py-1 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-[10px] font-bold uppercase">Activo</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full bg-rose-500/15 border border-rose-500/30 text-rose-400 text-[10px] font-bold uppercase">Bloqueado</span>
                                    @endif
                                </td>

                                <!-- Acciones -->
                                <td class="py-4 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                        <!-- Botón Ver Auditoría / Detalle Completo -->
                                        <button onclick="openAuditModal({{ $u->id }})" class="px-2.5 py-1.5 bg-indigo-500/15 hover:bg-indigo-500/25 border border-indigo-500/30 text-indigo-300 font-bold rounded-xl text-xs transition cursor-pointer flex items-center gap-1" title="Ver auditoría financiera, planes y transacciones">
                                            🔍 Detalle
                                        </button>

                                        <!-- Botón Ajustar Saldo -->
                                        <button onclick="openBalanceModal({{ json_encode($u) }})" class="px-2.5 py-1.5 bg-amber-500/15 hover:bg-amber-500/25 border border-amber-500/30 text-amber-400 font-bold rounded-xl text-xs transition cursor-pointer">
                                            💵 Saldo
                                        </button>

                                        <!-- Botón Cambiar Contraseña -->
                                        <button onclick="openPasswordModal({{ json_encode($u) }})" class="px-2.5 py-1.5 bg-cyan-500/15 hover:bg-cyan-500/25 border border-cyan-500/30 text-cyan-400 font-bold rounded-xl text-xs transition cursor-pointer" title="Asignar nueva clave">
                                            🔑 Clave
                                        </button>

                                        <!-- Botón Bloquear/Desbloquear -->
                                        <form method="POST" action="{{ route('admin.users.toggleStatus', $u->id) }}">
                                            @csrf
                                            <button type="submit" onclick="return confirm('¿Cambiar estado de la cuenta de {{ $u->name }}?')" class="px-2.5 py-1.5 {{ $u->status === 'active' ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30 hover:bg-rose-500/25' : 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 hover:bg-emerald-500/25' }} font-bold rounded-xl text-xs transition cursor-pointer">
                                                {{ $u->status === 'active' ? '🚫' : '✓' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="p-4 border-t border-slate-800">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL DE AUDITORÍA Y DETALLE COMPLETO DEL CLIENTE              -->
<!-- ============================================================== -->
<div id="auditModal" class="fixed inset-0 bg-black/85 backdrop-blur-md z-50 hidden flex items-center justify-center p-3 sm:p-6 overflow-y-auto">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-4xl w-full p-6 sm:p-8 shadow-2xl relative my-auto max-h-[90vh] flex flex-col">
        <!-- Encabezado Modal -->
        <div class="flex items-start justify-between pb-4 border-b border-slate-800 shrink-0">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-2xl">🔍</span>
                    <h2 class="text-xl font-black text-white" id="auditModalTitle">Detalle y Auditoría del Cliente</h2>
                </div>
                <p class="text-xs text-slate-400 mt-1" id="auditModalSubtitle">Cargando información contable...</p>
            </div>
            <button onclick="closeAuditModal()" class="text-slate-400 hover:text-white text-2xl p-1 leading-none cursor-pointer">✕</button>
        </div>

        <!-- Contenido Scrollable -->
        <div class="overflow-y-auto space-y-6 pt-6 pr-1 flex-1 text-xs" id="auditModalBody">
            <div class="text-center py-12 text-slate-400">
                <span class="inline-block animate-spin text-3xl mb-2">⏳</span>
                <p>Consultando bases de datos de Fortex...</p>
            </div>
        </div>
    </div>
</div>

<!-- MODAL AJUSTAR SALDO -->
<div id="balanceModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl relative">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-white">Ajustar Saldo Manual</h3>
            <button onclick="document.getElementById('balanceModal').classList.add('hidden')" class="text-slate-400 hover:text-white text-xl">✕</button>
        </div>

        <p class="text-xs text-slate-400 mb-4">Cliente: <strong id="modalUserName" class="text-white"></strong> (Saldo actual: <span id="modalUserBalance" class="text-emerald-400 font-mono"></span>)</p>

        <form id="balanceForm" method="POST" action="" class="space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-semibold text-slate-300 mb-1">Tipo de Ajuste</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="p-3 bg-slate-950 border border-slate-800 rounded-xl flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="action" value="add" checked class="text-emerald-500">
                        <span class="text-emerald-400 font-bold">➕ Agregar Saldo</span>
                    </label>
                    <label class="p-3 bg-slate-950 border border-slate-800 rounded-xl flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="action" value="subtract" class="text-rose-500">
                        <span class="text-rose-400 font-bold">➖ Restar Saldo</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-300 mb-1">Monto en Pesos ($ COP)</label>
                <input type="number" step="any" min="1" name="amount" required placeholder="Ej: 50000" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono text-sm focus:outline-none focus:border-amber-500">
                <span class="text-[10px] text-slate-500 mt-0.5 block">Ej: 50000 = $50.000 COP</span>
            </div>

            <div>
                <label class="block font-semibold text-slate-300 mb-1">Motivo / Razón del Ajuste</label>
                <input type="text" name="reason" required placeholder="Ej: Bono de bienvenida, corrección, premio especial..." class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none focus:border-amber-500">
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-800">
                <button type="button" onclick="document.getElementById('balanceModal').classList.add('hidden')" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl font-semibold cursor-pointer">Cancelar</button>
                <button type="submit" class="px-5 py-2 bg-amber-500 text-slate-950 font-bold rounded-xl shadow-lg cursor-pointer">Aplicar Ajuste</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Asignar / Cambiar Contraseña -->
<div id="passwordModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl relative">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <span>🔑</span> Cambiar Contraseña
            </h3>
            <button type="button" onclick="document.getElementById('passwordModal').classList.add('hidden')" class="text-slate-400 hover:text-white text-sm">✕</button>
        </div>
        <p class="text-xs text-slate-400 mb-4">
            Cliente: <strong id="pwdModalUserName" class="text-cyan-400"></strong><br>
            Contacto: <span id="pwdModalUserPhone" class="text-slate-300 font-mono"></span>
        </p>

        <form id="passwordForm" method="POST" class="space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-semibold text-slate-300 mb-1.5">Nueva Contraseña para el Usuario</label>
                <div class="flex gap-2">
                    <input type="text" id="newPasswordInput" name="password" required minlength="6" placeholder="Ej: Fortex2026*" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono text-sm focus:outline-none focus:border-cyan-500">
                    <button type="button" onclick="generateQuickPassword()" class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-cyan-400 border border-slate-700 rounded-xl font-bold whitespace-nowrap" title="Generar clave aleatoria">
                        🎲 Generar
                    </button>
                </div>
                <span class="text-[10px] text-slate-500 mt-1 block">Mínimo 6 caracteres. Puedes escribir una nueva o presionar «Generar».</span>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-800">
                <button type="button" onclick="document.getElementById('passwordModal').classList.add('hidden')" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl font-semibold cursor-pointer">Cancelar</button>
                <button type="submit" class="px-5 py-2 bg-cyan-500 text-slate-950 font-bold rounded-xl shadow-lg cursor-pointer">Guardar Clave</button>
            </div>
        </form>
    </div>
</div>

<script>
    function fmtCop(num) {
        return '$' + Math.round(Number(num) || 0).toLocaleString('es-CO') + ' COP';
    }

    function openAuditModal(userId) {
        const modal = document.getElementById('auditModal');
        const body = document.getElementById('auditModalBody');
        const title = document.getElementById('auditModalTitle');
        const subtitle = document.getElementById('auditModalSubtitle');

        modal.classList.remove('hidden');
        title.innerText = 'Consultando cliente #' + userId + '...';
        subtitle.innerText = 'Cargando auditoría de saldos, planes y movimientos contables...';
        body.innerHTML = `
            <div class="text-center py-12 text-slate-400">
                <span class="inline-block animate-spin text-3xl mb-2">⏳</span>
                <p>Consultando base de datos oficial...</p>
            </div>
        `;

        fetch('/admin/users/' + userId + '/detail')
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    body.innerHTML = '<div class="p-6 text-center text-rose-400 bg-rose-500/10 rounded-2xl">No se pudo cargar el detalle del usuario.</div>';
                    return;
                }

                const u = data.user;
                title.innerText = u.name;
                subtitle.innerHTML = `<span>✉️ ${u.email}</span> • <span>📞 ${u.phone}</span> • <span class="text-emerald-400 font-mono">Cód: ${u.referral_code}</span> • <span class="text-slate-400">Patrocinador: ${u.sponsor_name}</span>`;

                // Renderizar Tarjetas de Resumen Financiero
                let html = `
                    <!-- TARJETAS DE AUDITORÍA FINANCIERA -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="bg-slate-950 p-3.5 rounded-2xl border border-slate-800">
                            <span class="text-slate-400 text-[10px] uppercase font-bold block">1. Total Recargado</span>
                            <span class="text-base font-extrabold text-emerald-400 font-mono">${fmtCop(u.total_deposited)}</span>
                            <span class="text-[9px] text-slate-500 block">Depósitos aprobados</span>
                        </div>
                        <div class="bg-slate-950 p-3.5 rounded-2xl border border-slate-800">
                            <span class="text-slate-400 text-[10px] uppercase font-bold block">2. Invertido en Planes</span>
                            <span class="text-base font-extrabold text-amber-400 font-mono">${fmtCop(u.total_spent_on_plans)}</span>
                            <span class="text-[9px] text-slate-500 block">Saldo consumido</span>
                        </div>
                        <div class="bg-slate-950 p-3.5 rounded-2xl border border-slate-800">
                            <span class="text-slate-400 text-[10px] uppercase font-bold block">3. Recarga No Invertida</span>
                            <span class="text-base font-extrabold text-rose-400 font-mono">${fmtCop(u.recharge_balance)}</span>
                            <span class="text-[9px] text-slate-500 block">No retirable</span>
                        </div>
                        <div class="bg-slate-950 p-3.5 rounded-2xl border border-slate-800">
                            <span class="text-slate-400 text-[10px] uppercase font-bold block">4. Saldo Retirable</span>
                            <span class="text-base font-extrabold text-cyan-400 font-mono">${fmtCop(u.withdrawable_balance)}</span>
                            <span class="text-[9px] text-slate-500 block">Ganancias netas</span>
                        </div>
                    </div>

                    <div class="bg-slate-950/70 p-3 rounded-xl border border-slate-800/80 flex items-center justify-between text-xs">
                        <span class="text-slate-300">Saldo Total Disponible en Cuenta: <strong class="text-white font-mono text-sm">${fmtCop(u.balance)}</strong></span>
                        <span class="text-slate-400">Giros Ruleta Disponibles: <strong class="text-amber-400 font-mono">${u.roulette_spins}</strong></span>
                    </div>

                    <!-- SECCIÓN: PLANES COMPRADOS -->
                    <div class="bg-slate-950/50 p-4 rounded-2xl border border-slate-800">
                        <h4 class="text-sm font-bold text-white mb-3 flex items-center gap-1.5">
                            <span>⚡</span> Planes Comprados (${u.plans.length})
                        </h4>
                `;

                if (u.plans.length === 0) {
                    html += `<div class="p-4 text-center text-slate-500 bg-slate-900/60 rounded-xl">Este cliente aún no ha comprado ningún plan de inversión.</div>`;
                } else {
                    html += `
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="text-slate-400 border-b border-slate-800">
                                        <th class="pb-2">Plan</th>
                                        <th class="pb-2">Inversión</th>
                                        <th class="pb-2">Rendimiento/Día</th>
                                        <th class="pb-2">Ganado Hasta Hoy</th>
                                        <th class="pb-2">Tope Máximo</th>
                                        <th class="pb-2">Fecha Compra</th>
                                        <th class="pb-2">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800/50">
                    `;
                    u.plans.forEach(p => {
                        const statusBadge = p.status === 'active' 
                            ? '<span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-bold uppercase text-[9px]">Activo</span>'
                            : '<span class="px-2 py-0.5 rounded bg-slate-700 text-slate-300 font-bold uppercase text-[9px]">Finalizado</span>';

                        html += `
                            <tr>
                                <td class="py-2.5 font-bold text-white">${p.name}</td>
                                <td class="py-2.5 font-mono text-slate-300">${fmtCop(p.invested_amount)}</td>
                                <td class="py-2.5 font-mono text-emerald-400">+${fmtCop(p.daily_earning)}</td>
                                <td class="py-2.5 font-mono text-cyan-400 font-extrabold">${fmtCop(p.earned_so_far)}</td>
                                <td class="py-2.5 font-mono text-slate-400">${fmtCop(p.max_earning)}</td>
                                <td class="py-2.5 text-slate-400">${p.created_at}</td>
                                <td class="py-2.5">${statusBadge}</td>
                            </tr>
                        `;
                    });
                    html += `</tbody></table></div>`;
                }

                html += `</div>`;

                // SECCIÓN: SOLICITUDES DE RETIRO
                html += `
                    <div class="bg-slate-950/50 p-4 rounded-2xl border border-slate-800">
                        <h4 class="text-sm font-bold text-white mb-3 flex items-center gap-1.5">
                            <span>💸</span> Solicitudes de Retiro (${u.withdrawals.length})
                        </h4>
                `;

                if (u.withdrawals.length === 0) {
                    html += `<div class="p-4 text-center text-slate-500 bg-slate-900/60 rounded-xl">No registra retiros solicitados.</div>`;
                } else {
                    html += `
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="text-slate-400 border-b border-slate-800">
                                        <th class="pb-2">Fecha</th>
                                        <th class="pb-2">Monto Solicitado</th>
                                        <th class="pb-2">Comisión (8%)</th>
                                        <th class="pb-2">Neto a Transferir</th>
                                        <th class="pb-2">Titular y Cuenta</th>
                                        <th class="pb-2">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800/50">
                    `;
                    u.withdrawals.forEach(w => {
                        let wBadge = '';
                        if (w.status === 'approved') wBadge = '<span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-bold uppercase text-[9px]">Pagado</span>';
                        else if (w.status === 'pending') wBadge = '<span class="px-2 py-0.5 rounded bg-amber-500/20 text-amber-400 font-bold uppercase text-[9px] animate-pulse">Pendiente</span>';
                        else wBadge = '<span class="px-2 py-0.5 rounded bg-rose-500/20 text-rose-400 font-bold uppercase text-[9px]">Rechazado</span>';

                        html += `
                            <tr>
                                <td class="py-2.5 text-slate-400">${w.created_at}</td>
                                <td class="py-2.5 font-mono text-white font-bold">${fmtCop(w.amount)}</td>
                                <td class="py-2.5 font-mono text-slate-400">${fmtCop(w.fee)}</td>
                                <td class="py-2.5 font-mono text-emerald-400 font-black">${fmtCop(w.net_amount)}</td>
                                <td class="py-2.5 text-slate-300 font-mono"><strong>${w.account_holder_name}</strong><br><span class="text-[10px] text-slate-500">${w.wallet_or_account}</span></td>
                                <td class="py-2.5">${wBadge}</td>
                            </tr>
                        `;
                    });
                    html += `</tbody></table></div>`;
                }
                html += `</div>`;

                // SECCIÓN: HISTORIAL DE DEPÓSITOS
                html += `
                    <div class="bg-slate-950/50 p-4 rounded-2xl border border-slate-800">
                        <h4 class="text-sm font-bold text-white mb-3 flex items-center gap-1.5">
                            <span>💳</span> Depósitos / Recargas Realizadas (${u.deposits.length})
                        </h4>
                `;

                if (u.deposits.length === 0) {
                    html += `<div class="p-4 text-center text-slate-500 bg-slate-900/60 rounded-xl">No registra depósitos reportados.</div>`;
                } else {
                    html += `
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="text-slate-400 border-b border-slate-800">
                                        <th class="pb-2">Fecha</th>
                                        <th class="pb-2">Monto Recargado</th>
                                        <th class="pb-2">Método</th>
                                        <th class="pb-2">Referencia / Hash</th>
                                        <th class="pb-2">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800/50">
                    `;
                    u.deposits.forEach(d => {
                        let dBadge = '';
                        if (d.status === 'approved') dBadge = '<span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-bold uppercase text-[9px]">Aprobado</span>';
                        else if (d.status === 'pending') dBadge = '<span class="px-2 py-0.5 rounded bg-amber-500/20 text-amber-400 font-bold uppercase text-[9px]">Pendiente</span>';
                        else dBadge = '<span class="px-2 py-0.5 rounded bg-rose-500/20 text-rose-400 font-bold uppercase text-[9px]">Rechazado</span>';

                        html += `
                            <tr>
                                <td class="py-2.5 text-slate-400">${d.created_at}</td>
                                <td class="py-2.5 font-mono text-emerald-400 font-bold">${fmtCop(d.amount)}</td>
                                <td class="py-2.5 text-slate-300">${d.payment_method}</td>
                                <td class="py-2.5 font-mono text-slate-400 text-[10px]">${d.transaction_hash}</td>
                                <td class="py-2.5">${dBadge}</td>
                            </tr>
                        `;
                    });
                    html += `</tbody></table></div>`;
                }
                html += `</div>`;

                // SECCIÓN: HISTORIAL DE TRANSACCIONES CONTABLES
                html += `
                    <div class="bg-slate-950/50 p-4 rounded-2xl border border-slate-800">
                        <h4 class="text-sm font-bold text-white mb-3 flex items-center gap-1.5">
                            <span>🧾</span> Movimientos Contables Recientes (${u.transactions.length})
                        </h4>
                `;

                if (u.transactions.length === 0) {
                    html += `<div class="p-4 text-center text-slate-500 bg-slate-900/60 rounded-xl">Sin movimientos contables registrados.</div>`;
                } else {
                    html += `
                        <div class="overflow-x-auto max-h-64 overflow-y-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="sticky top-0 bg-slate-950">
                                    <tr class="text-slate-400 border-b border-slate-800">
                                        <th class="pb-2">Fecha</th>
                                        <th class="pb-2">Monto</th>
                                        <th class="pb-2">Saldo Resultante</th>
                                        <th class="pb-2">Descripción</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800/50">
                    `;
                    u.transactions.forEach(t => {
                        const isPositive = Number(t.amount) >= 0;
                        const amtColor = isPositive ? 'text-emerald-400' : 'text-rose-400';
                        const sign = isPositive ? '+' : '';

                        html += `
                            <tr>
                                <td class="py-2 text-slate-400 whitespace-nowrap text-[11px]">${t.created_at}</td>
                                <td class="py-2 font-mono font-bold ${amtColor} whitespace-nowrap">${sign}${fmtCop(t.amount)}</td>
                                <td class="py-2 font-mono text-slate-300 whitespace-nowrap">${fmtCop(t.balance_after)}</td>
                                <td class="py-2 text-slate-300 text-[11px]">${t.description}</td>
                            </tr>
                        `;
                    });
                    html += `</tbody></table></div>`;
                }
                html += `</div>`;

                body.innerHTML = html;
            })
            .catch(err => {
                body.innerHTML = '<div class="p-6 text-center text-rose-400 bg-rose-500/10 rounded-2xl">Error de conexión al cargar la auditoría del usuario.</div>';
            });
    }

    function closeAuditModal() {
        document.getElementById('auditModal').classList.add('hidden');
    }

    function openBalanceModal(user) {
        document.getElementById('balanceForm').action = '/admin/users/' + user.id + '/adjust-balance';
        document.getElementById('modalUserName').innerText = user.name;
        document.getElementById('modalUserBalance').innerText = fmtCop(user.balance);
        document.getElementById('balanceModal').classList.remove('hidden');
    }

    function openPasswordModal(user) {
        document.getElementById('passwordForm').action = '/admin/users/' + user.id + '/password';
        document.getElementById('pwdModalUserName').innerText = user.name;
        document.getElementById('pwdModalUserPhone').innerText = (user.phone ? '📞 ' + user.phone + ' | ' : '') + '✉️ ' + user.email;
        document.getElementById('newPasswordInput').value = '';
        document.getElementById('passwordModal').classList.remove('hidden');
    }

    function generateQuickPassword() {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        let pass = 'Fortex';
        for (let i = 0; i < 4; i++) {
            pass += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        pass += '*';
        document.getElementById('newPasswordInput').value = pass;
    }
</script>
@endsection
