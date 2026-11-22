<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta - Plataforma VIP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        img { max-width: 100%; height: auto; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #020617; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    <!-- Luces decorativas -->
    <div class="absolute -top-32 -right-32 w-96 h-96 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-purple-500/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md bg-slate-900/80 border border-slate-800 backdrop-blur-xl rounded-2xl p-6 sm:p-8 shadow-2xl relative z-10 my-8">
        <!-- Logo / Título -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-500 to-cyan-500 text-white font-extrabold text-2xl shadow-lg shadow-emerald-500/25 mb-3">
                🚀
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-white">Crear Nueva Cuenta</h1>
            <p class="text-slate-400 text-sm mt-1">Únete a la plataforma y activa tu plan</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-3.5 bg-rose-500/10 border border-rose-500/30 rounded-xl text-rose-400 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf

            <!-- Nombre Completo -->
            <div>
                <label for="name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Nombre Completo</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                    placeholder="Ej. Carlos Trujillo"
                    class="w-full px-4 py-2.5 bg-slate-950/60 border border-slate-800 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all text-sm">
            </div>

            <!-- Correo Electrónico -->
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Correo Electrónico</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required
                    placeholder="tu@correo.com"
                    class="w-full px-4 py-2.5 bg-slate-950/60 border border-slate-800 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all text-sm">
            </div>

            <!-- Teléfono / WhatsApp -->
            <div>
                <label for="phone" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Teléfono / WhatsApp (Opcional)</label>
                <input id="phone" type="text" name="phone" value="{{ old('phone') }}"
                    placeholder="+57 300 1234567"
                    class="w-full px-4 py-2.5 bg-slate-950/60 border border-slate-800 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all text-sm">
            </div>

            <!-- Código de Referido (Opcional o detectado por URL) -->
            <div>
                <label for="referral_code" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                    <span>Código de Invitación / Referido</span>
                    <span class="text-[10px] text-emerald-400 font-normal">Opcional</span>
                </label>
                <div class="relative">
                    <input id="referral_code" type="text" name="referral_code" value="{{ old('referral_code', $referralCode ?? '') }}"
                        placeholder="Ej. JUANVIP"
                        class="w-full px-4 py-2.5 bg-slate-950/60 border border-slate-800 rounded-xl text-emerald-400 font-mono tracking-wider placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all text-sm uppercase">
                    @if(!empty($referralCode))
                        <span class="absolute right-3 top-2.5 text-xs text-emerald-400 font-medium">✓ Vinculado</span>
                    @endif
                </div>
            </div>

            <!-- Contraseña -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Contraseña</label>
                    <input id="password" type="password" name="password" required
                        placeholder="••••••••"
                        class="w-full px-4 py-2.5 bg-slate-950/60 border border-slate-800 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all text-sm">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Confirmar</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required
                        placeholder="••••••••"
                        class="w-full px-4 py-2.5 bg-slate-950/60 border border-slate-800 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all text-sm">
                </div>
            </div>

            <!-- Botón de Registro -->
            <button type="submit" class="w-full mt-2 py-3.5 px-4 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-semibold rounded-xl shadow-lg shadow-emerald-500/25 transition-all duration-200 active:scale-[0.99] cursor-pointer">
                Completar Registro
            </button>
        </form>

        <!-- Enlace a Login -->
        <div class="mt-6 text-center text-sm text-slate-400">
            ¿Ya tienes una cuenta? 
            <a href="{{ route('login') }}" class="font-semibold text-emerald-400 hover:text-emerald-300 transition-colors">Inicia sesión</a>
        </div>
    </div>
</body>
</html>
