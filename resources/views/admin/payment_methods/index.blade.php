@extends('layouts.admin')

@section('title', 'Cuentas de Pago y Códigos QR')

@section('content')
<div class="space-y-6">

    <!-- Encabezado con Botón para Crear Nueva Cuenta -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-white flex items-center gap-2">
                <span>💳</span> Gestión de Cuentas y Códigos QR
            </h1>
            <p class="text-xs text-slate-400 mt-1">Crea, edita, activa o elimina las cuentas de Nequi, Daviplata, Bancolombia y USDT que verán tus clientes para pagar.</p>
        </div>

        <button onclick="openCreateModal()" class="px-5 py-3 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-slate-950 font-black rounded-2xl shadow-lg shadow-emerald-500/20 text-xs sm:text-sm transition flex items-center justify-center gap-2 cursor-pointer">
            <span>➕</span> Agregar Nueva Cuenta / QR
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

    <!-- Cuadrícula de Métodos de Pago Activos y Configurables -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($paymentMethods as $pm)
            @php
                $colorClasses = [
                    'purple' => 'border-purple-500/40 bg-purple-500/10 text-purple-400',
                    'rose' => 'border-rose-500/40 bg-rose-500/10 text-rose-400',
                    'amber' => 'border-amber-500/40 bg-amber-500/10 text-amber-400',
                    'emerald' => 'border-emerald-500/40 bg-emerald-500/10 text-emerald-400',
                    'blue' => 'border-cyan-500/40 bg-cyan-500/10 text-cyan-400',
                ][$pm->color_theme] ?? 'border-slate-800 bg-slate-900 text-slate-300';
            @endphp

            <div class="bg-slate-900/90 border border-slate-800 hover:border-slate-700 rounded-3xl p-6 relative overflow-hidden shadow-xl flex flex-col justify-between transition-all">
                
                <div>
                    <!-- Cabecera de la Tarjeta -->
                    <div class="flex items-center justify-between mb-4 border-b border-slate-800 pb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="px-3 py-1 rounded-full text-xs font-black uppercase {{ $colorClasses }}">
                                {{ $pm->name }}
                            </span>
                            @if($pm->status)
                                <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 text-[10px] font-bold">🟢 Visible al Cliente</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full bg-slate-800 text-slate-400 text-[10px] font-bold">🔴 Oculto</span>
                            @endif
                        </div>

                        <!-- Botón Eliminar Directo -->
                        <form id="del-pm-{{ $pm->id }}" method="POST" action="{{ route('admin.payment-methods.destroy', $pm->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="button" onclick="confirmCustomAction({
                                title: '¿Eliminar Método de Pago?',
                                html: 'Se eliminará el método <b class=\'text-rose-400\'>{{ $pm->name }}</b> de forma permanente.',
                                icon: 'warning',
                                confirmText: '🗑️ Sí, Eliminar',
                                confirmColor: '#f43f5e',
                                formId: 'del-pm-{{ $pm->id }}'
                            })" title="Eliminar Método" class="p-2 rounded-xl bg-slate-950 hover:bg-rose-500/20 text-slate-500 hover:text-rose-400 transition cursor-pointer text-xs">
                                🗑️ Eliminar
                            </button>
                        </form>
                    </div>

                    <!-- Datos y QR -->
                    <div class="flex flex-col sm:flex-row items-center gap-4 bg-slate-950 p-4 rounded-2xl border border-slate-800/80 mb-4">
                        <!-- Preview del QR -->
                        <div class="w-24 h-24 bg-slate-900 border border-slate-800 rounded-xl flex items-center justify-center overflow-hidden flex-shrink-0">
                            @if($pm->qr_image)
                                <img src="{{ route('payment-methods.qr', $pm->id) }}" alt="QR {{ $pm->name }}" class="w-full h-full object-contain">
                            @else
                                <div class="text-center p-2 text-slate-500 text-[10px]">
                                    <span class="text-xl block mb-1">📷</span>
                                    Sin QR subido
                                </div>
                            @endif
                        </div>

                        <!-- Datos del titular y estado -->
                        <div class="flex-1 text-xs space-y-1.5 w-full">
                            <div class="flex justify-between items-center text-slate-400">
                                <span>Titular (Visible al Cliente):</span>
                                <span class="font-bold text-white text-sm">{{ $pm->account_holder ?? 'Sin titular' }}</span>
                            </div>

                            <div class="flex justify-between items-center text-slate-400">
                                <span>Código QR:</span>
                                @if($pm->qr_image)
                                    <span class="text-emerald-400 font-bold text-[11px] flex items-center gap-1">✅ QR Activo</span>
                                @else
                                    <span class="text-amber-400 font-bold text-[11px] flex items-center gap-1">⚠️ Sin QR (Sube uno)</span>
                                @endif
                            </div>

                            @if($pm->account_number)
                                <div class="flex justify-between items-center text-slate-500 text-[11px]">
                                    <span>Ref. Interna:</span>
                                    <span class="font-mono text-slate-400">{{ $pm->account_number }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción: Editar y Activar/Desactivar -->
                <div class="grid grid-cols-2 gap-2 pt-3 border-t border-slate-800">
                    <form method="POST" action="{{ route('admin.payment-methods.toggle', $pm->id) }}">
                        @csrf
                        <button type="submit" class="w-full py-2.5 rounded-xl text-xs font-bold transition cursor-pointer {{ $pm->status ? 'bg-slate-800 hover:bg-slate-700 text-slate-300' : 'bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-400 border border-emerald-500/30' }}">
                            {{ $pm->status ? '⏸️ Pausar' : '▶️ Activar' }}
                        </button>
                    </form>

                    <button type="button" onclick="openEditModal({{ json_encode($pm) }})" class="w-full py-2.5 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-xl text-xs transition cursor-pointer">
                        ✏️ Editar Datos / QR
                    </button>
                </div>

            </div>
        @empty
            <div class="col-span-2 bg-slate-900/40 border border-slate-800 rounded-3xl p-12 text-center text-slate-400">
                <span class="text-4xl block mb-2">💳</span>
                <h3 class="text-base font-bold text-white">No tienes ninguna cuenta de pago creada</h3>
                <p class="text-xs text-slate-400 mt-1">Haz clic en el botón de arriba para crear tu primera cuenta de Nequi o Daviplata.</p>
            </div>
        @endforelse
    </div>

</div>

<!-- ========================================== -->
<!-- MODAL PARA CREAR NUEVO MÉTODO DE PAGO -->
<!-- ========================================== -->
<div id="createModal" class="fixed inset-0 bg-black/85 backdrop-blur-md z-50 hidden flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl relative max-h-[90vh] overflow-y-auto">
        <button onclick="closeCreateModal()" class="absolute right-5 top-5 text-slate-400 hover:text-white text-2xl font-bold transition">
            ✕
        </button>

        <h3 class="text-xl font-black text-white mb-1 flex items-center gap-2">
            <span>➕</span> Agregar Nueva Cuenta de Pago
        </h3>
        <p class="text-xs text-slate-400 mb-5">Ingresa los datos para que tus clientes puedan transferirte o escanear tu QR.</p>

        <form method="POST" action="{{ route('admin.payment-methods.store') }}" enctype="multipart/form-data" class="space-y-4 text-xs">
            @csrf

            <div>
                <label class="block font-semibold text-slate-300 mb-1">Nombre del Método / Banco</label>
                <input type="text" name="name" required placeholder="Ej: Nequi Personal, Daviplata, Bancolombia" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none focus:border-emerald-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Tipo de Plataforma</label>
                    <select name="type" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none focus:border-emerald-500">
                        <option value="nequi">🟣 Nequi</option>
                        <option value="daviplata">🔴 Daviplata</option>
                        <option value="bancolombia">🟡 Bancolombia</option>
                        <option value="crypto">🟢 Cripto / USDT</option>
                        <option value="other">⚪ Otro Banco / Billetera</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Color de la Tarjeta</label>
                    <select name="color_theme" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none focus:border-emerald-500">
                        <option value="purple">🟣 Morado (Nequi)</option>
                        <option value="rose">🔴 Rojo / Rosado (Daviplata)</option>
                        <option value="amber">🟡 Amarillo (Bancolombia)</option>
                        <option value="emerald">🟢 Verde (USDT / General)</option>
                        <option value="blue">🔵 Azul (Otros)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-300 mb-1">Nombre del Titular de la Cuenta <span class="text-emerald-400 font-bold">* (Visible al cliente)</span></label>
                <input type="text" name="account_holder" placeholder="Ej: Carlos Trujillo" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none focus:border-emerald-500">
            </div>

            <div>
                <label class="block font-semibold text-slate-300 mb-1 flex items-center justify-between">
                    <span>Imagen del Código QR <span class="text-emerald-400 font-bold">* (Recomendado)</span></span>
                    <span class="text-[10px] text-slate-500">PNG, JPG, WEBP</span>
                </label>
                <input type="file" name="qr_image" accept="image/*" class="w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-500/20 file:text-emerald-300 hover:file:bg-emerald-500/30 cursor-pointer">
                <span class="text-[10px] text-slate-500 mt-1 block">Sube el código QR que los clientes escanearán para transferir.</span>
            </div>

            <div class="grid grid-cols-2 gap-3 pt-1 border-t border-slate-800/60">
                <div>
                    <label class="block font-semibold text-slate-400 mb-1 text-[11px]">N° de Cuenta / Celular (Opcional)</label>
                    <input type="text" name="account_number" placeholder="Opcional (Uso interno)" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono text-xs focus:outline-none focus:border-slate-700">
                </div>
                <div>
                    <label class="block font-semibold text-slate-400 mb-1 text-[11px]">Tipo de Cuenta (Opcional)</label>
                    <input type="text" name="account_type" placeholder="Ej: Ahorros, Celular" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs focus:outline-none focus:border-slate-700">
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-slate-950 font-black rounded-xl shadow-lg shadow-emerald-500/25 transition active:scale-95 text-xs sm:text-sm cursor-pointer mt-4">
                🚀 Crear Método de Pago
            </button>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL PARA EDITAR MÉTODO DE PAGO -->
<!-- ========================================== -->
<div id="editModal" class="fixed inset-0 bg-black/85 backdrop-blur-md z-50 hidden flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl relative max-h-[90vh] overflow-y-auto">
        <button onclick="closeEditModal()" class="absolute right-5 top-5 text-slate-400 hover:text-white text-2xl font-bold transition">
            ✕
        </button>

        <h3 class="text-xl font-black text-white mb-1 flex items-center gap-2">
            <span>✏️</span> Editar Cuenta de Pago
        </h3>
        <p class="text-xs text-slate-400 mb-5">Actualiza el titular o cambia la imagen del código QR que verán los clientes.</p>

        <form id="editForm" method="POST" action="" enctype="multipart/form-data" class="space-y-4 text-xs">
            @csrf
            @method('PUT')

            <div>
                <label class="block font-semibold text-slate-300 mb-1">Nombre del Método / Banco</label>
                <input type="text" id="edit_name" name="name" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none focus:border-emerald-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Tipo de Plataforma</label>
                    <select id="edit_type" name="type" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none focus:border-emerald-500">
                        <option value="nequi">🟣 Nequi</option>
                        <option value="daviplata">🔴 Daviplata</option>
                        <option value="bancolombia">🟡 Bancolombia</option>
                        <option value="crypto">🟢 Cripto / USDT</option>
                        <option value="other">⚪ Otro Banco / Billetera</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Color de la Tarjeta</label>
                    <select id="edit_color_theme" name="color_theme" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none focus:border-emerald-500">
                        <option value="purple">🟣 Morado (Nequi)</option>
                        <option value="rose">🔴 Rojo / Rosado (Daviplata)</option>
                        <option value="amber">🟡 Amarillo (Bancolombia)</option>
                        <option value="emerald">🟢 Verde (USDT / General)</option>
                        <option value="blue">🔵 Azul (Otros)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-300 mb-1">Nombre del Titular de la Cuenta <span class="text-emerald-400 font-bold">* (Visible al cliente)</span></label>
                <input type="text" id="edit_account_holder" name="account_holder" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none focus:border-emerald-500">
            </div>

            <div>
                <label class="block font-semibold text-slate-300 mb-1 flex items-center justify-between">
                    <span>Subir / Reemplazar Imagen del QR</span>
                    <span class="text-[10px] text-slate-500">PNG, JPG, WEBP</span>
                </label>
                <div id="edit_qr_preview" class="mb-2 hidden">
                    <span class="text-[10px] text-slate-400 block mb-1">QR actual cargado:</span>
                    <img id="edit_qr_img" src="" class="w-20 h-20 object-contain rounded-xl border border-slate-800 p-1 bg-slate-950">
                </div>
                <input type="file" name="qr_image" accept="image/*" class="w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-500/20 file:text-emerald-300 hover:file:bg-emerald-500/30 cursor-pointer">
            </div>

            <div class="grid grid-cols-2 gap-3 pt-1 border-t border-slate-800/60">
                <div>
                    <label class="block font-semibold text-slate-400 mb-1 text-[11px]">N° de Cuenta / Celular (Opcional)</label>
                    <input type="text" id="edit_account_number" name="account_number" placeholder="Opcional (Uso interno)" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono text-xs focus:outline-none focus:border-slate-700">
                </div>
                <div>
                    <label class="block font-semibold text-slate-400 mb-1 text-[11px]">Tipo de Cuenta (Opcional)</label>
                    <input type="text" id="edit_account_type" name="account_type" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs focus:outline-none focus:border-slate-700">
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-slate-950 font-black rounded-xl shadow-lg shadow-emerald-500/25 transition active:scale-95 text-xs sm:text-sm cursor-pointer mt-4">
                💾 Guardar Cambios
            </button>
        </form>
    </div>
</div>

<script>
    function openCreateModal() {
        document.getElementById('createModal').classList.remove('hidden');
    }
    function closeCreateModal() {
        document.getElementById('createModal').classList.add('hidden');
    }

    function openEditModal(pm) {
        document.getElementById('editForm').action = `/admin/payment-methods/${pm.id}`;
        document.getElementById('edit_name').value = pm.name || '';
        document.getElementById('edit_type').value = pm.type || 'nequi';
        document.getElementById('edit_color_theme').value = pm.color_theme || 'purple';
        document.getElementById('edit_account_number').value = pm.account_number || '';
        document.getElementById('edit_account_type').value = pm.account_type || '';
        document.getElementById('edit_account_holder').value = pm.account_holder || '';

        const previewContainer = document.getElementById('edit_qr_preview');
        const previewImg = document.getElementById('edit_qr_img');
        if (pm.qr_image && pm.qr_image.trim() !== '') {
            previewContainer.classList.remove('hidden');
            previewImg.src = `/payment-methods/${pm.id}/qr`;
        } else {
            previewContainer.classList.add('hidden');
            previewImg.src = '';
        }

        document.getElementById('editModal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }
</script>
@endsection
