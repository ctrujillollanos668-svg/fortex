<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): View
    {
        $referralCode = $request->query('ref') ?: $request->query('referral_code') ?: $request->query('referred_by');
        return view('auth.register', compact('referralCode'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Normalizar y capturar código de referido desde cualquier parámetro
        $rawRef = $request->input('referral_code') ?: $request->input('referred_by') ?: $request->input('ref');
        if ($rawRef) {
            $request->merge(['referral_code' => trim($rawRef)]);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email:rfc,filter',
                'max:255',
                'unique:' . User::class,
                function ($attribute, $value, $fail) {
                    $domain = strtolower(substr(strrchr($value, "@"), 1));

                    // Lista de servicios de correos temporales / desechables bloqueados
                    $disposableDomains = [
                        'yopmail.com', 'yopmail.net', 'yopmail.fr', 'cool.fr.nf', 'jetable.fr.nf',
                        'tempmail.com', 'temp-mail.org', 'tempmail.net', 'temp-mail.io',
                        'guerrillamail.com', 'guerrillamail.net', 'guerrillamail.org', 'guerrillamailblock.com',
                        'mailinator.com', 'mailinater.com', 'suremail.info', 'reconmail.com',
                        '10minutemail.com', '10minutemail.net', '10minutemail.org', '10minmail.com',
                        'sharklasers.com', 'dispostable.com', 'trashmail.com', 'trashmail.net',
                        'getairmail.com', 'mohmal.com', 'crazymailing.com', 'fakemailgenerator.com',
                        'generator.email', 'emailondeck.com', 'throwawaymail.com', 'tempail.com',
                        'inboxbear.com', 'dropmail.me', 'mytemp.email', 'getnada.com', 'nada.ltd',
                        'tempmailo.com', 'burnermail.io', 'inboxkitten.com', 'disposablemail.com',
                        'mailpoof.com', 'fakeinbox.com', 'disposable.com', 'luxusmail.org',
                        'zillamail.com', 'armyspy.com', 'cuvox.de', 'dayrep.com', 'einrot.com',
                        'fleckens.hu', 'gustr.com', 'jourrapide.com', 'rhyta.com', 'superrito.com',
                        'teleworm.us', 'disbox.org', 'trashmail.me'
                    ];

                    if (in_array($domain, $disposableDomains)) {
                        $fail('⚠️ No se permiten correos electrónicos temporales o desechables. Por favor usa un correo personal real (Gmail, Outlook, Hotmail, etc.).');
                        return;
                    }

                    // Verificar que el dominio tenga formato válido con TLD existente
                    if (!str_contains($domain, '.') || strlen(substr(strrchr($domain, "."), 1)) < 2) {
                        $fail('⚠️ El dominio del correo no es válido.');
                        return;
                    }
                },
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'referral_code' => ['nullable', 'string'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'name.required' => 'Por favor ingresa tu nombre completo.',
            'email.required' => 'Por favor ingresa tu correo electrónico.',
            'email.email' => '⚠️ El correo electrónico ingresado no es válido o su dominio no existe en internet.',
            'email.unique' => 'Este correo electrónico ya se encuentra registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ]);

        $sponsorId = null;
        if ($request->filled('referral_code')) {
            $code = strtoupper(trim($request->referral_code));
            // Buscar patrocinador insensible a mayúsculas/minúsculas
            $sponsor = User::whereRaw('UPPER(referral_code) = ?', [$code])->first();
            if ($sponsor) {
                $sponsorId = $sponsor->id;
            }
        }

        // Generar código de referido único para el nuevo usuario
        do {
            $newReferralCode = 'VIP' . strtoupper(Str::random(6));
        } while (User::where('referral_code', $newReferralCode)->exists());

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'cliente',
            'balance' => 0.00,
            'referral_code' => $newReferralCode,
            'referred_by' => $sponsorId,
            'status' => 'active',
            'roulette_spins' => 1, // 1 giro de bienvenida gratis garantizado
            'claimed_red_packet' => false,
        ]);

        // Premiar al patrocinador con 1 giro adicional de ruleta por invitar
        if ($sponsorId) {
            $sponsor = User::find($sponsorId);
            if ($sponsor) {
                $sponsor->increment('roulette_spins', 1);
            }
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
