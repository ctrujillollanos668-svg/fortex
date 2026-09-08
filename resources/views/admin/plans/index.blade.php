@extends('layouts.admin')

@section('title', 'Gestión de Planes y Membresías')

@section('content')
<div class="space-y-8">
    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-white flex items-center gap-2">
                <span>📦</span> Planes y Membresías VIP (Pesos Colombianos - COP)
            </h1>
            <p class="text-slate-400 text-xs sm:text-sm mt-0.5">Crea, edita o pausa los paquetes de rendimiento en Pesos Colombianos ($ COP).</p>
        </div>

        <button onclick="document.getElementById('createPlanModal').classList.remove('hidden')" class="px-5 py-2.5 bg-gradient-to-r from-purple-500 to-indigo-500 hover:from-purple-600 hover:to-indigo-600 text-white font-bold rounded-2xl text-xs sm:text-sm shadow-lg shadow-purple-500/25 transition cursor-pointer flex items-center gap-2">
            <span>➕</span> Crear Nuevo Plan
        </button>
    </div>

    <!-- Guía Rápida Informativa -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-slate-900/40 p-4 rounded-2xl border border-slate-800/80 text-xs">
        <div class="flex items-start gap-2.5">
            <span class="text-lg">🏠</span>
            <div>
                <strong class="text-emerald-400">¿Mostrar en Inicio?</strong>
                <p class="text-slate-400 mt-0.5">Elige con el botón <span class="text-white font-semibold">🏠</span> cuáles planes salen en la portada de Inicio del cliente. Los demás se verán en el catálogo <span class="text-white font-semibold">⚡ Planes</span>.</p>
            </div>
        </div>
        <div class="flex items-start gap-2.5">
            <span class="text-lg">🏷️</span>
            <div>
                <strong class="text-cyan-400">Insignia / Badge</strong>
                <p class="text-slate-400 mt-0.5">Etiqueta visual que resalta el plan (ej: <span class="text-white font-semibold">"🔥 Más Popular", "⭐ Recomendado"</span>).</p>
            </div>
        </div>
        <div class="flex items-start gap-2.5">
            <span class="text-lg">🛑</span>
            <div>
                <strong class="text-amber-400">Tope Máximo ($ COP)</strong>
                <p class="text-slate-400 mt-0.5">Límite total de dinero que ganará el cliente antes de que el plan venza.</p>
            </div>
        </div>
    </div>

    <!-- Lista de Planes en Tarjetas -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($plans as $plan)
            <div class="bg-slate-900/80 border {{ $plan->status ? 'border-slate-800' : 'border-rose-500/30 opacity-75' }} rounded-3xl p-6 flex flex-col justify-between relative shadow-xl">
                <div>
                    <div class="flex flex-wrap items-center justify-between gap-1.5">
                        <div class="flex items-center gap-1.5">
                            <span class="px-2.5 py-0.5 rounded-full {{ $plan->status ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400' }} text-[10px] font-extrabold uppercase">
                                {{ $plan->status ? 'Activo' : 'Pausado' }}
                            </span>
                            @if($plan->show_on_home)
                                <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[10px] font-bold" title="Visible en la pantalla de Inicio del cliente">
                                    🏠 En Inicio
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full bg-slate-800 text-slate-400 text-[10px] font-semibold" title="Solo visible en el módulo Planes">
                                    🌐 Solo Catálogo
                                </span>
                            @endif
                        </div>
                        @if($plan->badge)
                            <span class="px-2.5 py-0.5 rounded-full bg-cyan-500/20 text-cyan-400 text-[10px] font-bold uppercase">
                                {{ $plan->badge }}
                            </span>
                        @endif
                    </div>

                    <h3 class="text-2xl font-extrabold text-white mt-3">{{ $plan->name }}</h3>
                    <p class="text-xs text-slate-400 mt-1 min-h-[32px]">{{ $plan->description ?? 'Sin descripción.' }}</p>

                    <div class="my-5 py-4 border-y border-slate-800">
                        <div class="text-2xl sm:text-3xl font-extrabold text-white font-mono">
                            ${{ number_format($plan->price, 0, ',', '.') }} <span class="text-xs font-normal text-emerald-400 font-sans">COP</span>
                        </div>
                        <div class="text-xs text-emerald-400 font-bold mt-1">
                            Paga {{ $plan->daily_percentage }}% diario (${{ number_format(($plan->price * $plan->daily_percentage) / 100, 0, ',', '.') }} COP / día)
                        </div>
                        <div class="text-xs text-slate-400 mt-0.5">
                            Tope máximo de retorno: <strong class="text-amber-400">${{ number_format($plan->max_return, 0, ',', '.') }} COP</strong> ({{ $plan->duration_days }} días)
                        </div>
                    </div>

                    <div class="space-y-1.5 mb-4 text-xs">
                        <div class="text-slate-400 flex items-center justify-between">
                            <span>Unidades a la venta (Stock):</span>
                            @if($plan->hasStockLimit())
                                <span class="font-mono font-bold px-2 py-0.5 rounded-lg {{ $plan->isSoldOut() ? 'bg-rose-500/20 text-rose-400' : 'bg-cyan-500/20 text-cyan-400' }}">
                                    {{ $plan->stock }} {{ $plan->stock == 1 ? 'unidad' : 'unidades' }} {{ $plan->isSoldOut() ? '(Agotado)' : '' }}
                                </span>
                            @else
                                <span class="font-mono text-slate-400 bg-slate-800 px-2 py-0.5 rounded-lg">Ilimitado</span>
                            @endif
                        </div>
                        <div class="text-slate-400 flex items-center justify-between">
                            <span>Usuarios con este plan:</span>
                            <strong class="text-white font-mono">{{ $plan->user_plans_count }} clientes</strong>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="pt-4 border-t border-slate-800/80 flex items-center gap-2">
                    <!-- Toggle Activar / Pausar -->
                    <form method="POST" action="{{ route('admin.plans.toggle', $plan->id) }}" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full py-2 rounded-xl {{ $plan->status ? 'bg-amber-500/15 hover:bg-amber-500/25 text-amber-400 border border-amber-500/30' : 'bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-400 border border-emerald-500/30' }} text-xs font-bold transition cursor-pointer">
                            {{ $plan->status ? '⏸ Pausar' : '▶ Activar' }}
                        </button>
                    </form>

                    <!-- Toggle Mostrar en Inicio -->
                    <form method="POST" action="{{ route('admin.plans.toggle-home', $plan->id) }}">
                        @csrf
                        <button type="submit" class="p-2 rounded-xl {{ $plan->show_on_home ? 'bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-400 border border-emerald-500/40' : 'bg-slate-800 hover:bg-slate-700 text-slate-400' }} transition cursor-pointer text-xs font-bold" title="{{ $plan->show_on_home ? 'Quitar de Inicio' : 'Poner en Inicio' }}">
                            🏠
                        </button>
                    </form>

                    <!-- Editar -->
                    <button onclick="openEditModal({{ json_encode($plan) }})" class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 transition cursor-pointer" title="Editar">
                        ✏️
                    </button>

                    <!-- Eliminar -->
                    <form id="del-plan-{{ $plan->id }}" method="POST" action="{{ route('admin.plans.destroy', $plan->id) }}">
                        @csrf
                        @method('DELETE')
                        <button type="button" onclick="confirmCustomAction({
                            title: '¿Eliminar {{ $plan->name }}?',
                            html: 'Este plan será eliminado permanentemente.',
                            icon: 'warning',
                            confirmText: '🗑️ Sí, Eliminar',
                            confirmColor: '#f43f5e',
                            formId: 'del-plan-{{ $plan->id }}'
                        })" class="p-2 rounded-xl bg-rose-500/15 hover:bg-rose-500/25 text-rose-400 transition cursor-pointer" title="Eliminar">
                            🗑️
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- MODAL CREAR PLAN -->
<div id="createPlanModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl relative max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-xl font-bold text-white">Crear Nuevo Plan VIP</h3>
                <p class="text-xs text-slate-400">Valores en Pesos Colombianos ($ COP)</p>
            </div>
            <button onclick="document.getElementById('createPlanModal').classList.add('hidden')" class="text-slate-400 hover:text-white text-xl">✕</button>
        </div>

        <form method="POST" action="{{ route('admin.plans.store') }}" class="space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-semibold text-slate-300 mb-1">Nombre del Plan</label>
                <input type="text" name="name" required placeholder="Ej: Plan Bronce, Vacaciones, VIP 1..." class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none focus:border-purple-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Precio ($ COP)</label>
                    <input type="number" id="create_price" step="any" name="price" required placeholder="30000" oninput="autoCalcCreate()" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono focus:outline-none focus:border-purple-500">
                    <span class="text-[10px] text-slate-500">Ej: 30000 = $30.000 COP</span>
                </div>
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">% Diario de Retorno</label>
                    <input type="number" id="create_percentage" step="any" name="daily_percentage" required placeholder="6.0" oninput="autoCalcCreate()" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono focus:outline-none focus:border-purple-500">
                    <div class="flex items-center justify-between mt-0.5">
                        <span class="text-[10px] text-slate-500">Ej: 5 (5%), 7 (7%)</span>
                        <span id="create_daily_preview" class="text-[10px] text-emerald-400 font-bold">$0 COP / día</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Duración (Días)</label>
                    <input type="number" id="create_duration" name="duration_days" value="30" required oninput="autoCalcCreate()" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono focus:outline-none focus:border-purple-500">
                </div>
                <div>
                    <label class="block font-semibold text-slate-300 mb-1 flex items-center justify-between">
                        <span>Tope Máximo ($ COP)</span>
                    </label>
                    <input type="number" id="create_max_return" step="any" name="max_return" required placeholder="45000" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono focus:outline-none focus:border-purple-500">
                    <span class="text-[10px] text-amber-400 block font-semibold">Límite total a cobrar</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-300 mb-1 flex items-center justify-between">
                        <span>Unidades a la Venta (Stock)</span>
                        <span class="text-[10px] text-purple-400 font-normal">Opcional</span>
                    </label>
                    <input type="number" min="0" step="1" name="stock" placeholder="Ej: 20 (Vacío = Ilimitado)" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono focus:outline-none focus:border-purple-500">
                    <span class="text-[10px] text-slate-500">¿Cuántos vas a vender? (Ej: 20)</span>
                </div>
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Insignia / Badge (Opcional)</label>
                    <input type="text" name="badge" placeholder="Ej: 🔥 Más Vendido, ⭐ VIP" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none focus:border-purple-500">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-300 mb-1">Descripción</label>
                <textarea name="description" rows="2" placeholder="Detalles o beneficios del plan..." class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none focus:border-purple-500"></textarea>
            </div>

            <div class="space-y-2 pt-1">
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="status" id="status" value="1" checked class="rounded bg-slate-950 text-purple-600 focus:ring-purple-500">
                    <label for="status" class="text-slate-300 cursor-pointer">Activar inmediatamente para la venta</label>
                </div>
                <div class="flex items-center gap-2 p-2.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20">
                    <input type="checkbox" name="show_on_home" id="show_on_home" value="1" checked class="rounded bg-slate-950 text-emerald-500 focus:ring-emerald-500">
                    <label for="show_on_home" class="text-emerald-300 cursor-pointer font-semibold">🏠 Mostrar en la portada de Inicio del cliente</label>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-800">
                <button type="button" onclick="document.getElementById('createPlanModal').classList.add('hidden')" class="px-4 py-2.5 bg-slate-800 text-slate-300 rounded-xl font-semibold cursor-pointer">Cancelar</button>
                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-purple-500 to-indigo-500 text-white font-bold rounded-xl shadow-lg cursor-pointer">Guardar Plan</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDITAR PLAN -->
<div id="editPlanModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl relative max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-xl font-bold text-white">Editar Plan VIP</h3>
                <p class="text-xs text-slate-400">Valores en Pesos Colombianos ($ COP)</p>
            </div>
            <button onclick="document.getElementById('editPlanModal').classList.add('hidden')" class="text-slate-400 hover:text-white text-xl">✕</button>
        </div>

        <form id="editPlanForm" method="POST" action="" class="space-y-4 text-xs">
            @csrf
            @method('PUT')
            <div>
                <label class="block font-semibold text-slate-300 mb-1">Nombre del Plan</label>
                <input type="text" id="edit_name" name="name" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Precio ($ COP)</label>
                    <input type="number" step="any" id="edit_price" name="price" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                </div>
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">% Diario de Retorno</label>
                    <input type="number" step="any" id="edit_daily_percentage" name="daily_percentage" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Duración (Días)</label>
                    <input type="number" id="edit_duration_days" name="duration_days" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                </div>
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Tope Máximo ($ COP)</label>
                    <input type="number" step="any" id="edit_max_return" name="max_return" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-300 mb-1 flex items-center justify-between">
                        <span>Unidades a la Venta (Stock)</span>
                        <span class="text-[10px] text-purple-400 font-normal">Opcional</span>
                    </label>
                    <input type="number" min="0" step="1" id="edit_stock" name="stock" placeholder="Vacío = Ilimitado" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono focus:outline-none focus:border-purple-500">
                    <span class="text-[10px] text-slate-500">Modifica unidades restantes.</span>
                </div>
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Insignia / Badge</label>
                    <input type="text" id="edit_badge" name="badge" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-300 mb-1">Descripción</label>
                <textarea id="edit_description" name="description" rows="2" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none"></textarea>
            </div>

            <div class="space-y-2 pt-1">
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="status" id="edit_status" value="1" class="rounded bg-slate-950 text-purple-600">
                    <label for="edit_status" class="text-slate-300 cursor-pointer">Plan activo para la venta</label>
                </div>
                <div class="flex items-center gap-2 p-2.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20">
                    <input type="checkbox" name="show_on_home" id="edit_show_on_home" value="1" class="rounded bg-slate-950 text-emerald-500">
                    <label for="edit_show_on_home" class="text-emerald-300 cursor-pointer font-semibold">🏠 Mostrar en la portada de Inicio del cliente</label>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-800">
                <button type="button" onclick="document.getElementById('editPlanModal').classList.add('hidden')" class="px-4 py-2.5 bg-slate-800 text-slate-300 rounded-xl font-semibold">Cancelar</button>
                <button type="submit" class="px-6 py-2.5 bg-purple-600 text-white font-bold rounded-xl">Actualizar Cambios</button>
            </div>
        </form>
    </div>
</div>

<script>
    function autoCalcCreate() {
        const price = parseFloat(document.getElementById('create_price').value) || 0;
        const percentage = parseFloat(document.getElementById('create_percentage').value) || 0;
        const duration = parseInt(document.getElementById('create_duration').value) || 30;

        if (price > 0 && percentage > 0) {
            const daily = (price * percentage) / 100;
            const total = daily * duration;

            document.getElementById('create_daily_preview').innerText = '$' + daily.toLocaleString('es-CO') + ' COP / día';
            
            // Si el tope está vacío o desactualizado, sugerir el cálculo total
            const maxField = document.getElementById('create_max_return');
            if (!maxField.dataset.customized) {
                maxField.value = total;
            }
        }
    }

    document.getElementById('create_max_return').addEventListener('input', function() {
        this.dataset.customized = "true";
    });

    function openEditModal(plan) {
        document.getElementById('editPlanForm').action = '/admin/plans/' + plan.id;
        document.getElementById('edit_name').value = plan.name;
        document.getElementById('edit_price').value = plan.price;
        document.getElementById('edit_daily_percentage').value = plan.daily_percentage;
        document.getElementById('edit_duration_days').value = plan.duration_days;
        document.getElementById('edit_max_return').value = plan.max_return;
        document.getElementById('edit_badge').value = plan.badge || '';
        document.getElementById('edit_stock').value = (plan.stock !== null && plan.stock !== undefined) ? plan.stock : '';
        document.getElementById('edit_description').value = plan.description || '';
        document.getElementById('edit_status').checked = plan.status ? true : false;
        document.getElementById('edit_show_on_home').checked = plan.show_on_home ? true : false;
        document.getElementById('editPlanModal').classList.remove('hidden');
    }
</script>
@endsection
