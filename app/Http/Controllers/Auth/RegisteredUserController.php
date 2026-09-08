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
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['nullable', 'string', 'max:30'],
            'referral_code' => ['nullable', 'string'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'name.required' => 'Por favor ingresa tu nombre completo.',
            'email.required' => 'Por favor ingresa tu correo electrónico.',
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
