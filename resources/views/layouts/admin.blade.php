<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - FORTEX</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('img/fortex.jpg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .swal2-popup.swal-custom-dark {
            background: #090d16 !important;
            border: 1px solid #1e293b !important;
            border-radius: 1.5rem !important;
            color: #f1f5f9 !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.75) !important;
        }
        .swal2-title {
            color: #ffffff !important;
            font-weight: 800 !important;
        }
        .swal2-html-container {
            color: #94a3b8 !important;
            font-size: 0.875rem !important;
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col md:flex-row">

    <!-- BARRA SUPERIOR MÓVIL CON MENÚ HAMBURGUESA (SOLO EN TELÉFONOS) -->
    <div class="md:hidden bg-slate-900/95 border-b border-slate-800 p-4 flex items-center justify-between sticky top-0 z-50 backdrop-blur-xl">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-amber-500 to-yellow-400 flex items-center justify-center text-slate-950 text-base font-black shadow-md">
                👑
            </div>
            <div>
                <h2 class="text-sm font-extrabold text-white leading-none">ADMIN PANEL</h2>
                <span class="text-[9px] text-amber-400 font-bold tracking-wider uppercase">Super Admin</span>
            </div>
        </div>

        <button onclick="toggleAdminMenu()" class="p-2 bg-slate-950 border border-slate-800 rounded-xl text-slate-300 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
            </svg>
        </button>
    </div>

    <!-- SIDEBAR LATERAL (DESKTOP FIJO & MÓVIL COLAPSABLE) -->
    <aside id="adminSidebar" class="hidden md:flex w-full md:w-64 bg-slate-900/90 border-r border-slate-800 flex-shrink-0 flex-col justify-between p-4 z-40 sticky top-0 md:h-screen overflow-y-auto">
        <div>
            <!-- Logo Admin en Desktop -->
            <div class="hidden md:flex items-center gap-3 px-2 py-4 mb-4 border-b border-slate-800">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-500 to-yellow-400 flex items-center justify-center text-slate-950 text-xl font-black shadow-lg shadow-amber-500/20">
                    👑
                </div>
                <div>
                    <h2 class="text-base font-extrabold text-white leading-tight">ADMIN PANEL</h2>
                    <span class="text-[10px] text-amber-400 font-bold tracking-wider uppercase">Super Administrador</span>
                </div>
            </div>

            <!-- Navegación -->
            <nav class="space-y-1.5 text-xs font-semibold">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3.5 py-3 rounded-xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-amber-500/15 text-amber-400 border border-amber-500/30' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <span class="text-base">📊</span>
                    <span>Dashboard General</span>
                </a>

                <a href="{{ route('admin.deposits.index') }}" class="flex items-center justify-between px-3.5 py-3 rounded-xl transition {{ request()->routeIs('admin.deposits.*') ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <span class="text-base">📥</span>
                        <span>Depósitos / Recargas</span>
                    </div>
                    @php $pendingDeps = \App\Models\Deposit::where('status', 'pending')->count(); @endphp
                    <span id="badge-pending-deposits" class="px-2 py-0.5 rounded-full bg-emerald-500 text-slate-950 text-[10px] font-extrabold {{ $pendingDeps > 0 ? '' : 'hidden' }}">{{ $pendingDeps }}</span>
                </a>

                <a href="{{ route('admin.withdrawals.index') }}" class="flex items-center justify-between px-3.5 py-3 rounded-xl transition {{ request()->routeIs('admin.withdrawals.*') ? 'bg-cyan-500/15 text-cyan-400 border border-cyan-500/30' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <span class="text-base">📤</span>
                        <span>Solicitudes de Retiro</span>
                    </div>
                    @php $pendingWiths = \App\Models\Withdrawal::where('status', 'pending')->count(); @endphp
                    <span id="badge-pending-withdrawals" class="px-2 py-0.5 rounded-full bg-cyan-500 text-slate-950 text-[10px] font-extrabold {{ $pendingWiths > 0 ? '' : 'hidden' }}">{{ $pendingWiths }}</span>
                </a>

                <a href="{{ route('admin.plans.index') }}" class="flex items-center gap-3 px-3.5 py-3 rounded-xl transition {{ request()->routeIs('admin.plans.*') ? 'bg-purple-500/15 text-purple-400 border border-purple-500/30' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <span class="text-base">📦</span>
                    <span>Planes y Membresías</span>
                </a>

                <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3.5 py-3 rounded-xl transition {{ request()->routeIs('admin.users.*') ? 'bg-amber-500/15 text-amber-400 border border-amber-500/30' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <span class="text-base">👥</span>
                    <span>Gestión de Clientes</span>
                </a>

                <a href="{{ route('admin.payment-methods.index') }}" class="flex items-center gap-3 px-3.5 py-3 rounded-xl transition {{ request()->routeIs('admin.payment-methods.*') ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <span class="text-base">💳</span>
                    <span>Cuentas de Pago & QR</span>
                </a>

                <a href="{{ route('admin.promo-codes.index') }}" class="flex items-center gap-3 px-3.5 py-3 rounded-xl transition {{ request()->routeIs('admin.promo-codes.*') ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <span class="text-base">🎁</span>
                    <span>Códigos de Sorteo / Regalo</span>
                </a>

                <a href="{{ route('dashboard') }}" target="_blank" class="flex items-center gap-3 px-3.5 py-3 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition">
                    <span class="text-base">🌐</span>
                    <span>Ver Vista de Cliente</span>
                </a>
            </nav>
        </div>

        <!-- Usuario Logueado & Salir -->
        <div class="pt-4 border-t border-slate-800/80 mt-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-white">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] text-slate-400 truncate max-w-[120px]">{{ Auth::user()->email }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Cerrar Sesión" class="group flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-950 hover:bg-rose-500/15 border border-slate-800 hover:border-rose-500/30 text-slate-400 hover:text-rose-400 transition cursor-pointer text-xs font-semibold shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400 group-hover:text-rose-400 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Salir</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- CONTENIDO PRINCIPAL ADAPTABLE -->
    <main class="flex-1 p-3 sm:p-6 lg:p-8 overflow-y-auto max-h-screen">
        @if ($errors->any())
            <div class="mb-5 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs space-y-1">
                @foreach ($errors->all() as $error)
                    <p>• {{ $error }}</p>
                @endforeach
            </div>
        @endif

        @yield('content')
    </main>

    <!-- SweetAlert2 Scripts para Admin -->
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true,
            customClass: { popup: 'swal-custom-dark' }
        });

        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: '¡Operación Exitosa!',
                text: "{{ session('success') }}",
                customClass: { popup: 'swal-custom-dark' },
                confirmButtonColor: '#10b981',
                confirmButtonText: 'Continuar'
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Atención',
                text: "{{ session('error') }}",
                customClass: { popup: 'swal-custom-dark' },
                confirmButtonColor: '#f43f5e',
                confirmButtonText: 'Entendido'
            });
        @endif

        function toggleAdminMenu() {
            const sidebar = document.getElementById('adminSidebar');
            sidebar.classList.toggle('hidden');
        }

        // Función Global de Confirmación SweetAlert2 para Formularios
        function confirmCustomAction(options) {
            Swal.fire({
                title: options.title || '¿Estás seguro?',
                html: options.html || 'Esta acción no se puede deshacer.',
                icon: options.icon || 'warning',
                showCancelButton: true,
                confirmButtonColor: options.confirmColor || '#f43f5e',
                cancelButtonColor: '#334155',
                confirmButtonText: options.confirmText || 'Sí, Continuar',
                cancelButtonText: 'Cancelar',
                customClass: { popup: 'swal-custom-dark' }
            }).then((result) => {
                if (result.isConfirmed) {
                    if (options.formId) {
                        const form = document.getElementById(options.formId);
                        if (form) {
                            form.submit();
                        }
                    } else if (typeof options.onConfirm === 'function') {
                        options.onConfirm();
                    }
                }
            });
        }

        // =========================================================================
        // SISTEMA DE NOTIFICACIONES EN VIVO Y SONIDO (COMPATIBLE CON CUALQUIER HOSTING)
        // =========================================================================

        // Sintetizador de Sonido de Campanita (No requiere archivos externos ni mp3)
        function playNotificationChime() {
            try {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (!AudioContext) return;
                const ctx = new AudioContext();

                // Nota 1
                const osc1 = ctx.createOscillator();
                const gain1 = ctx.createGain();
                osc1.connect(gain1);
                gain1.connect(ctx.destination);
                osc1.type = 'sine';
                osc1.frequency.setValueAtTime(587.33, ctx.currentTime); // D5
                gain1.gain.setValueAtTime(0.3, ctx.currentTime);
                gain1.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.35);
                osc1.start(ctx.currentTime);
                osc1.stop(ctx.currentTime + 0.35);

                // Nota 2
                const osc2 = ctx.createOscillator();
                const gain2 = ctx.createGain();
                osc2.connect(gain2);
                gain2.connect(ctx.destination);
                osc2.type = 'sine';
                osc2.frequency.setValueAtTime(880, ctx.currentTime + 0.15); // A5
                gain2.gain.setValueAtTime(0.35, ctx.currentTime + 0.15);
                gain2.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.7);
                osc2.start(ctx.currentTime + 0.15);
                osc2.stop(ctx.currentTime + 0.7);
            } catch (e) {
                console.warn('AudioContext bloqueado hasta que el usuario interactúe con la página');
            }
        }

        // Ventana Flotante Toast de Aviso
        function showLiveToastNotification(opts) {
            const container = document.getElementById('adminNotificationToastContainer');
            if (!container) return;

            const toast = document.createElement('div');
            const isEmerald = opts.color === 'emerald';
            toast.className = `pointer-events-auto bg-slate-900/95 border ${isEmerald ? 'border-emerald-500/80 shadow-emerald-500/20' : 'border-cyan-500/80 shadow-cyan-500/20'} rounded-2xl p-4 shadow-2xl transition-all duration-500 transform translate-y-[-10px] opacity-0 backdrop-blur-xl flex flex-col gap-2`;

            toast.innerHTML = `
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full ${isEmerald ? 'bg-emerald-400' : 'bg-cyan-400'} animate-ping"></span>
                        <h4 class="text-xs font-black text-white">${opts.title}</h4>
                    </div>
                    <button onclick="this.closest('.pointer-events-auto').remove()" class="text-slate-400 hover:text-white text-xs font-bold transition">✕</button>
                </div>
                <p class="text-[11px] text-slate-300">${opts.message}</p>
                <div class="flex justify-end pt-1">
                    <a href="${opts.url}" class="text-[11px] font-black px-3 py-1.5 rounded-xl ${isEmerald ? 'bg-emerald-500 text-slate-950 hover:bg-emerald-400' : 'bg-cyan-500 text-slate-950 hover:bg-cyan-400'} transition shadow-md">
                        ${opts.actionText} →
                    </a>
                </div>
            `;

            container.appendChild(toast);
            setTimeout(() => {
                toast.classList.remove('translate-y-[-10px]', 'opacity-0');
            }, 50);

            // Auto-cerrar después de 14 segundos
            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-x-full');
                setTimeout(() => toast.remove(), 500);
            }, 14000);
        }

        // Monitoreo en segundo plano cada 10 segundos
        let lastPendingDeposits = {{ $pendingDeps }};
        let lastPendingWithdrawals = {{ $pendingWiths }};
        let isFirstAlertCheck = true;

        async function checkAdminLiveAlerts() {
            try {
                const res = await fetch("{{ route('admin.notifications.check') }}", {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!res.ok) return;
                const data = await res.json();

                // 1. Actualizar badges en sidebar
                const depBadge = document.getElementById('badge-pending-deposits');
                if (depBadge) {
                    depBadge.innerText = data.pending_deposits;
                    if (data.pending_deposits > 0) {
                        depBadge.classList.remove('hidden');
                    } else {
                        depBadge.classList.add('hidden');
                    }
                }

                const withBadge = document.getElementById('badge-pending-withdrawals');
                if (withBadge) {
                    withBadge.innerText = data.pending_withdrawals;
                    if (data.pending_withdrawals > 0) {
                        withBadge.classList.remove('hidden');
                    } else {
                        withBadge.classList.add('hidden');
                    }
                }

                // 2. Si aumentó la cantidad de depósitos pendientes, sonar y mostrar aviso
                if (!isFirstAlertCheck && data.pending_deposits > lastPendingDeposits && data.latest_deposit) {
                    playNotificationChime();
                    showLiveToastNotification({
                        title: '📥 ¡Nueva Recarga Reportada!',
                        message: `<strong>${data.latest_deposit.user_name}</strong> subió comprobante por <strong>$${data.latest_deposit.amount_formatted} COP</strong> (${data.latest_deposit.payment_method}).`,
                        url: "{{ route('admin.deposits.index') }}",
                        actionText: 'Revisar y Aprobar',
                        color: 'emerald'
                    });
                }

                // 3. Si aumentó la cantidad de retiros pendientes
                if (!isFirstAlertCheck && data.pending_withdrawals > lastPendingWithdrawals && data.latest_withdrawal) {
                    playNotificationChime();
                    showLiveToastNotification({
                        title: '📤 ¡Nueva Solicitud de Retiro!',
                        message: `<strong>${data.latest_withdrawal.user_name}</strong> solicita <strong>$${data.latest_withdrawal.amount_formatted} COP</strong> vía ${data.latest_withdrawal.payment_method}.`,
                        url: "{{ route('admin.withdrawals.index') }}",
                        actionText: 'Revisar Retiro',
                        color: 'cyan'
                    });
                }

                lastPendingDeposits = data.pending_deposits;
                lastPendingWithdrawals = data.pending_withdrawals;
                isFirstAlertCheck = false;

            } catch (err) {
                // Silencioso en caso de pérdida momentánea de red
            }
        }

        // Iniciar chequeo automático cada 10 segundos
        setInterval(checkAdminLiveAlerts, 10000);
    </script>

    <!-- Contenedor Flotante de Notificaciones Toast -->
    <div id="adminNotificationToastContainer" class="fixed top-5 right-5 z-50 flex flex-col gap-3 max-w-sm w-full pointer-events-none"></div>
</body>
</html>
