<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso - FORTEX</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('img/fortex.jpg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        img { max-width: 100%; height: auto; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #030712; }
    </style>
</head>
<body class="text-slate-100 min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    <!-- Luces de fondo -->
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-cyan-500/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md bg-slate-900/90 border border-slate-800 backdrop-blur-2xl rounded-3xl p-6 sm:p-8 shadow-2xl relative z-10 my-6">
        
        <!-- Logo / Volver -->
        <div class="flex items-center justify-between mb-5">
            <a href="/" class="text-xs text-slate-400 hover:text-emerald-400 flex items-center gap-1 transition">
                ← Volver al Inicio
            </a>
            <div class="w-8 h-8 rounded-xl bg-black border border-emerald-500/30 flex items-center justify-center overflow-hidden shadow-md">
                <img src="{{ asset('img/fortex.jpg') }}" alt="FORTEX" class="w-full h-full object-cover">
            </div>
        </div>

        <!-- Pestañas de Cambio Rápido entre Login y Registro sin salir de la página -->
        <div class="grid grid-cols-2 p-1 bg-slate-950 rounded-2xl border border-slate-800 mb-6 text-xs font-bold">
            <button type="button" id="tabLoginBtn" onclick="switchAuthTab('login')" class="py-2.5 rounded-xl transition cursor-pointer bg-emerald-500 text-slate-950 font-bold shadow-md">
                🔑 Iniciar Sesión
            </button>
            <button type="button" id="tabRegisterBtn" onclick="switchAuthTab('register')" class="py-2.5 rounded-xl transition cursor-pointer text-slate-400 hover:text-white">
                🚀 Registrarse
            </button>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-3.5 bg-rose-500/10 border border-rose-500/30 rounded-xl text-rose-400 text-xs">
                <div class="flex items-center gap-2 font-bold mb-1.5 text-rose-300">
                    <span>⚠️</span> Atención:
                </div>
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- FORMULARIO 1: INICIAR SESIÓN -->
        <div id="loginFormSection">
            <form method="POST" action="{{ route('login') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Correo Electrónico</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="usuario@ejemplo.com" class="w-full px-4 py-3 bg-slate-950/70 border border-slate-800 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Contraseña</label>
                    <input id="password" type="password" name="password" required placeholder="••••••••" class="w-full px-4 py-3 bg-slate-950/70 border border-slate-800 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500">
                </div>

                <div class="flex items-center justify-between text-[11px] text-slate-400">
                    <div class="flex items-center gap-1.5">
                        <input type="checkbox" name="remember" id="remember" class="rounded bg-slate-950 border-slate-800 text-emerald-500 focus:ring-emerald-500">
                        <label for="remember" class="cursor-pointer">Recordarme</label>
                    </div>
                    <button type="button" onclick="openForgotModal()" class="text-emerald-400 hover:text-emerald-300 font-bold transition hover:underline cursor-pointer">
                        ¿Olvidaste tu contraseña?
                    </button>
                </div>

                <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-bold rounded-xl shadow-lg shadow-emerald-500/25 transition active:scale-95 text-xs sm:text-sm cursor-pointer">
                    Iniciar Sesión
                </button>
            </form>
        </div>

        <!-- FORMULARIO 2: REGISTRO EN LA MISMA VENTANA -->
        <div id="registerFormSection" class="hidden">
            <form method="POST" action="{{ route('register') }}" class="space-y-3.5 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1">Nombre Completo</label>
                    <input type="text" name="name" required placeholder="Ej: Carlos Trujillo" class="w-full px-3.5 py-2.5 bg-slate-950/70 border border-slate-800 rounded-xl text-white focus:outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1">Correo Electrónico</label>
                    <input type="email" name="email" required placeholder="tu@correo.com" class="w-full px-3.5 py-2.5 bg-slate-950/70 border border-slate-800 rounded-xl text-white focus:outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1">Celular / WhatsApp</label>
                    <input type="tel" name="phone" placeholder="Ej: 3001234567" class="w-full px-3.5 py-2.5 bg-slate-950/70 border border-slate-800 rounded-xl text-white focus:outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1">Contraseña</label>
                    <input type="password" name="password" required placeholder="••••••••" class="w-full px-3.5 py-2.5 bg-slate-950/70 border border-slate-800 rounded-xl text-white focus:outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1">Confirmar Contraseña</label>
                    <input type="password" name="password_confirmation" required placeholder="••••••••" class="w-full px-3.5 py-2.5 bg-slate-950/70 border border-slate-800 rounded-xl text-white focus:outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1">Código de Invitación (Opcional)</label>
                    <input type="text" name="referral_code" value="{{ request('ref') }}" placeholder="Ej: VIPA1B2C3D" class="w-full px-3.5 py-2.5 bg-slate-950/70 border border-slate-800 rounded-xl text-emerald-400 font-mono focus:outline-none focus:border-emerald-500 uppercase">
                </div>

                <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-slate-950 font-black rounded-xl shadow-lg shadow-emerald-500/25 transition active:scale-95 text-xs sm:text-sm cursor-pointer mt-2">
                    Crear Cuenta y Ganar Bono
                </button>
            </form>
        </div>

    </div>

    <!-- Modal Recuperar Contraseña por WhatsApp -->
    <div id="forgotModal" class="fixed inset-0 bg-black/85 backdrop-blur-md z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-sm w-full p-6 shadow-2xl relative text-xs">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center font-bold text-sm">
                        💬
                    </div>
                    <h3 class="text-sm font-black text-white">Recuperar Contraseña</h3>
                </div>
                <button type="button" onclick="closeForgotModal()" class="text-slate-400 hover:text-white text-base font-bold transition cursor-pointer">✕</button>
            </div>

            <p class="text-slate-400 text-[11px] mb-4 leading-relaxed">
                Ingresa tu <strong>número de celular / WhatsApp</strong> registrado. Te conectaremos con el <strong>Soporte Oficial de FORTEX</strong> para validar tu cuenta y entregarte una nueva contraseña al instante.
            </p>

            <div class="space-y-3">
                <div>
                    <label class="block font-semibold text-slate-300 uppercase tracking-wider text-[10px] mb-1">Tu Número de Celular / WhatsApp</label>
                    <div class="flex items-center bg-slate-950/80 border border-slate-800 rounded-xl focus-within:border-emerald-500 overflow-hidden">
                        <select id="forgotCountryCode" class="bg-slate-900 text-white text-xs font-semibold px-2 sm:px-3 py-2.5 border-r border-slate-800 focus:outline-none cursor-pointer">
                            <option value="+57" selected>🇨🇴 +57 (CO)</option>
                            <option value="+52">🇲🇽 +52 (MX)</option>
                            <option value="+51">🇵🇪 +51 (PE)</option>
                            <option value="+593">🇪🇨 +593 (EC)</option>
                            <option value="+58">🇻🇪 +58 (VE)</option>
                            <option value="+54">🇦🇷 +54 (AR)</option>
                            <option value="+56">🇨🇱 +56 (CL)</option>
                            <option value="+591">🇧🇴 +591 (BO)</option>
                            <option value="+1">🇺🇸 +1 (US)</option>
                            <option value="+34">🇪🇸 +34 (ES)</option>
                            <option value="+507">🇵🇦 +507 (PA)</option>
                            <option value="+506">🇨🇷 +506 (CR)</option>
                            <option value="+1">🇩🇴 +1 (DO)</option>
                            <option value="+502">🇬🇹 +502 (GT)</option>
                            <option value="+504">🇭🇳 +504 (HN)</option>
                            <option value="+503">🇸🇻 +503 (SV)</option>
                            <option value="+505">🇳🇮 +505 (NI)</option>
                            <option value="+595">🇵🇾 +595 (PY)</option>
                            <option value="+598">🇺🇾 +598 (UY)</option>
                            <option value="+55">🇧🇷 +55 (BR)</option>
                        </select>
                        <input type="tel" id="forgotPhoneInput" placeholder="Ej: 3222216725" class="w-full bg-transparent text-white placeholder-slate-600 focus:outline-none font-mono text-xs px-3 py-2.5">
                    </div>
                    <span class="text-[10px] text-slate-500 mt-1 block">Toca la bandera para cambiar el código de país.</span>
                </div>

                <button type="button" onclick="sendWhatsAppRecovery()" class="w-full py-3 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-black rounded-xl shadow-lg shadow-emerald-500/25 transition flex items-center justify-center gap-2 text-xs cursor-pointer active:scale-95">
                    <span>💬</span> Solicitar Clave por WhatsApp
                </button>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-800/80 text-[10px] text-slate-500 text-center">
                🔒 Atención humana y segura 24/7 sin intermediarios
            </div>
        </div>
    </div>

    <script>
        function switchAuthTab(tab) {
            const loginSection = document.getElementById('loginFormSection');
            const registerSection = document.getElementById('registerFormSection');
            const tabLoginBtn = document.getElementById('tabLoginBtn');
            const tabRegisterBtn = document.getElementById('tabRegisterBtn');

            if (tab === 'login') {
                loginSection.classList.remove('hidden');
                registerSection.classList.add('hidden');
                tabLoginBtn.className = "py-2.5 rounded-xl transition cursor-pointer bg-emerald-500 text-slate-950 font-bold shadow-md";
                tabRegisterBtn.className = "py-2.5 rounded-xl transition cursor-pointer text-slate-400 hover:text-white";
            } else {
                loginSection.classList.add('hidden');
                registerSection.classList.remove('hidden');
                tabRegisterBtn.className = "py-2.5 rounded-xl transition cursor-pointer bg-emerald-500 text-slate-950 font-bold shadow-md";
                tabLoginBtn.className = "py-2.5 rounded-xl transition cursor-pointer text-slate-400 hover:text-white";
            }
        }

        function openForgotModal() {
            document.getElementById('forgotModal').classList.remove('hidden');
        }

        function closeForgotModal() {
            document.getElementById('forgotModal').classList.add('hidden');
        }

        function sendWhatsAppRecovery() {
            const countryCode = document.getElementById('forgotCountryCode').value;
            const phone = document.getElementById('forgotPhoneInput').value.trim();
            if (!phone) {
                alert('Por favor ingresa tu número de celular o WhatsApp');
                return;
            }
            const fullPhone = `${countryCode} ${phone}`;
            const supportNumber = "{{ env('SUPPORT_WHATSAPP', '573115138588') }}";
            const msg = encodeURIComponent(`Hola Soporte FORTEX 🟢, solicito recuperar la contraseña de mi cuenta registrada con el celular: ${fullPhone}`);
            window.open(`https://api.whatsapp.com/send?phone=${supportNumber}&text=${msg}`, '_blank');
        }

        // Si la URL tiene ?register=1 o ?ref=..., activar pestaña de registro automáticamente
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('register') || urlParams.get('ref')) {
            switchAuthTab('register');
        }
    </script>
</body>
</html>
