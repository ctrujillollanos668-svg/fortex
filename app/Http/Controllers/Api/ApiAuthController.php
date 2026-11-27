<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ApiAuthController extends Controller
{
    /**
     * Iniciar sesión desde la app móvil.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Por favor completa todos los campos.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $login = trim($request->input('login'));
        $password = $request->input('password');

        $user = User::where('phone', $login)
            ->orWhere('email', $login)
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Número de teléfono o contraseña incorrectos.',
            ], 401);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Tu cuenta se encuentra bloqueada o suspendida. Contacta a soporte.',
            ], 403);
        }

        $tokenPayload = json_encode([
            'id' => $user->id,
            'time' => time(),
            'rand' => Str::random(16),
        ]);
        $token = Crypt::encryptString($tokenPayload);

        return response()->json([
            'success' => true,
            'message' => 'Bienvenido a Fortex',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email,
                'balance' => (float) $user->balance,
                'withdrawable_balance' => (float) $user->withdrawableBalance(),
                'recharge_balance' => (float) $user->rechargeBalance(),
                'referral_code' => $user->referral_code,
                'roulette_spins' => (int) ($user->roulette_spins ?? 0),
            ],
        ]);
    }

    /**
     * Registro de nuevo cliente.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'phone' => 'required|string|min:8|max:20|unique:users,phone',
            'password' => 'required|string|min:6',
            'referral_code' => 'nullable|string|exists:users,referral_code',
        ], [
            'phone.unique' => 'Este número de teléfono ya está registrado en Fortex.',
            'referral_code.exists' => 'El código de invitación ingresado no es válido.',
            'password.min' => 'La contraseña debe tener mínimo 6 caracteres.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $referredBy = null;
        if ($request->filled('referral_code')) {
            $sponsor = User::where('referral_code', trim($request->input('referral_code')))->first();
            if ($sponsor) {
                $referredBy = $sponsor->id;
            }
        }

        // Generar código de referido único
        $myRefCode = strtoupper(Str::random(8));
        while (User::where('referral_code', $myRefCode)->exists()) {
            $myRefCode = strtoupper(Str::random(8));
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', $request->input('phone'));
        $email = $cleanPhone . '@fortex.app';

        $user = User::create([
            'name' => trim($request->input('name')),
            'phone' => $cleanPhone,
            'email' => $email,
            'password' => Hash::make($request->input('password')),
            'role' => 'cliente',
            'balance' => 0.00,
            'referral_code' => $myRefCode,
            'referred_by' => $referredBy,
            'status' => 'active',
            'roulette_spins' => 1, // 1 giro de bienvenida
        ]);

        $tokenPayload = json_encode([
            'id' => $user->id,
            'time' => time(),
            'rand' => Str::random(16),
        ]);
        $token = Crypt::encryptString($tokenPayload);

        return response()->json([
            'success' => true,
            'message' => '¡Registro exitoso! Tienes 1 giro gratis de bienvenida.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email,
                'balance' => 0.0,
                'withdrawable_balance' => 0.0,
                'recharge_balance' => 0.0,
                'referral_code' => $user->referral_code,
                'roulette_spins' => 1,
            ],
        ], 201);
    }

    /**
     * Perfil del usuario autenticado con saldos en vivo.
     */
    public function profile(Request $request)
    {
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email,
                'balance' => (float) $user->balance,
                'withdrawable_balance' => (float) $user->withdrawableBalance(),
                'recharge_balance' => (float) $user->rechargeBalance(),
                'referral_code' => $user->referral_code,
                'roulette_spins' => (int) ($user->roulette_spins ?? 0),
                'active_plans_count' => $user->userPlans()->where('status', 'active')->count(),
                'referrals_count' => $user->referrals()->count(),
            ],
        ]);
    }
}
