<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use App\Models\PromoCodeRedemption;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;

class RewardController extends Controller
{
    /**
     * Girar la Ruleta de la Suerte VIP (1 vez cada 24 horas o giros acumulados)
     */
    public function spin(Request $request)
    {
        // Si el usuario entra por GET directamente desde la barra del navegador, redirigir al panel
        if ($request->isMethod('get') && !$request->expectsJson() && !$request->ajax()) {
            return redirect()->route('cliente.dashboard');
        }

        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesión expirada. Por favor inicia sesión nuevamente.',
                ], 401);
            }

            // Asegurar columna roulette_spins y last_spin_at de forma segura
            if (!Schema::hasColumn('users', 'roulette_spins')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->unsignedInteger('roulette_spins')->default(1)->after('status');
                });
            }
            if (!Schema::hasColumn('users', 'last_spin_at')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->timestamp('last_spin_at')->nullable()->after('status');
                });
            }

            // Comprobar si tiene giros disponibles
            $availableSpins = $user->roulette_spins ?? 0;
            $canSpinByTime = true;

            if ($user->last_spin_at) {
                $hoursSinceLastSpin = now()->diffInHours($user->last_spin_at);
                if ($hoursSinceLastSpin < 24 && $availableSpins <= 0) {
                    $canSpinByTime = false;
                    $remainingHours = 24 - $hoursSinceLastSpin;
                    return response()->json([
                        'success' => false,
                        'message' => "⏳ Ya utilizaste tu giro diario. Podrás girar de nuevo en {$remainingHours} hora(s) o acumulando giros por recargas/compras.",
                    ], 422);
                }
            }

            // Premios en pesos colombianos ($ COP) configurados con pesos probabilísticos
            // 0: $1.000 (35%)
            // 1: $2.000 (25%)
            // 2: $5.000 (18%)
            // 3: $10.000 (10%)
            // 4: $20.000 (7%)
            // 5: $50.000 (4%)
            // 6: $100.000 (0.9%)
            // 7: $200.000 (0.1%)
            $prizes = [
                0 => ['amount' => 1000, 'weight' => 350],
                1 => ['amount' => 2000, 'weight' => 250],
                2 => ['amount' => 5000, 'weight' => 180],
                3 => ['amount' => 10000, 'weight' => 100],
                4 => ['amount' => 20000, 'weight' => 70],
                5 => ['amount' => 50000, 'weight' => 40],
                6 => ['amount' => 100000, 'weight' => 9],
                7 => ['amount' => 200000, 'weight' => 1],
            ];

            // Algoritmo de selección ponderada
            $rand = mt_rand(1, 1000);
            $currentWeight = 0;
            $selectedSegment = 0;

            foreach ($prizes as $index => $prizeData) {
                $currentWeight += $prizeData['weight'];
                if ($rand <= $currentWeight) {
                    $selectedSegment = $index;
                    break;
                }
            }

            $wonAmount = $prizes[$selectedSegment]['amount'];

            // Ejecutar la acreditación en transacción segura
            DB::transaction(function () use ($user, $wonAmount) {
                // Descontar giro si tenía acumulados
                if ($user->roulette_spins > 0) {
                    $user->roulette_spins -= 1;
                }
                $user->last_spin_at = now();
                $user->balance += $wonAmount;
                $user->save();

                // Registrar en transacciones
                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'roulette_reward',
                    'amount' => $wonAmount,
                    'balance_after' => $user->balance,
                    'description' => 'Premio Ruleta de la Suerte VIP (+$' . number_format($wonAmount, 0, ',', '.') . ' COP)',
                ]);
            });

            return response()->json([
                'success' => true,
                'segment' => $selectedSegment,
                'prize' => $wonAmount,
                'message' => '¡Felicidades! Ganaste $' . number_format($wonAmount, 0, ',', '.') . ' COP acreditados a tu saldo.',
                'new_balance' => $user->balance,
                'new_balance_formatted' => '$' . number_format($user->balance, 0, ',', '.') . ' COP',
                'remaining_spins' => $user->roulette_spins,
            ]);

        } catch (\Throwable $e) {
            Log::error('Error en Ruleta: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al procesar tu giro. Intenta de nuevo.',
            ], 500);
        }
    }

    /**
     * Abrir Sobre Rojo VIP o Canjear Código de Sorteo / Regalo
     */
    public function claimRedPacket(Request $request)
    {
        // Si entra por GET en el navegador, redirigir al panel
        if ($request->isMethod('get') && !$request->expectsJson() && !$request->ajax()) {
            return redirect()->route('cliente.dashboard');
        }

        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesión expirada. Por favor inicia sesión nuevamente.',
                ], 401);
            }

            if (!Schema::hasColumn('users', 'claimed_red_packet')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->boolean('claimed_red_packet')->default(false);
                });
            }

            $code = strtoupper(trim($request->input('code', '')));

            // 1. Canje con código exclusivo creado por el Administrador
            if (!empty($code)) {
                $promoCode = PromoCode::where('code', $code)->first();

                if (!$promoCode) {
                    return response()->json([
                        'success' => false,
                        'message' => '❌ El código de regalo ingresado no existe o no es válido.',
                    ], 422);
                }

                if (!$promoCode->status) {
                    return response()->json([
                        'success' => false,
                        'message' => '⏸️ Este código de regalo ha sido pausado por el administrador.',
                    ], 422);
                }

                if ($promoCode->expires_at && $promoCode->expires_at->isPast()) {
                    return response()->json([
                        'success' => false,
                        'message' => '⏰ Este código de regalo ha expirado.',
                    ], 422);
                }

                if ($promoCode->max_uses > 0 && $promoCode->used_count >= $promoCode->max_uses) {
                    return response()->json([
                        'success' => false,
                        'message' => '❌ Este código ya alcanzó su límite máximo de ganadores y se encuentra agotado.',
                    ], 422);
                }

                // Validar si el usuario ya canjeó este código
                $alreadyRedeemed = PromoCodeRedemption::where('promo_code_id', $promoCode->id)
                    ->where('user_id', $user->id)
                    ->exists();

                if ($alreadyRedeemed) {
                    return response()->json([
                        'success' => false,
                        'message' => "⚠️ Ya canjeaste el código [{$code}] anteriormente en tu cuenta.",
                    ], 422);
                }

                $prize = (float) $promoCode->reward_amount;

                DB::transaction(function () use ($user, $promoCode, $prize, $code) {
                    // Incrementar contador de usos
                    $promoCode->increment('used_count');

                    // Registrar canje
                    PromoCodeRedemption::create([
                        'promo_code_id' => $promoCode->id,
                        'user_id' => $user->id,
                        'reward_amount' => $prize,
                    ]);

                    // Acreditar saldo
                    $user->balance += $prize;
                    $user->save();

                    // Registrar transacción
                    Transaction::create([
                        'user_id' => $user->id,
                        'type' => 'promo_code',
                        'amount' => $prize,
                        'balance_after' => $user->balance,
                        'description' => "Premio de Código de Sorteo [{$code}] (+" . number_format($prize, 0, ',', '.') . " COP)",
                    ]);
                });

                return response()->json([
                    'success' => true,
                    'prize' => $prize,
                    'message' => "🎉 ¡Código [{$code}] canjeado con éxito! Recibes +$" . number_format($prize, 0, ',', '.') . " COP en tu saldo disponible.",
                    'new_balance' => $user->balance,
                    'new_balance_formatted' => '$' . number_format($user->balance, 0, ',', '.') . ' COP',
                ]);
            }

            // 2. Sobre Rojo de Bienvenida para Nuevos Usuarios (1 vez por cuenta)
            if ($user->claimed_red_packet) {
                return response()->json([
                    'success' => false,
                    'message' => '🧧 Ya abriste tu Sobre Rojo de Bienvenida. ¡Ingresa un código promocional de Telegram para más bonos!',
                ], 422);
            }

            // Bono de bienvenida sorpresa
            $welcomePrizes = [2000, 2500, 3000, 5000];
            $prize = $welcomePrizes[array_rand($welcomePrizes)];

            DB::transaction(function () use ($user, $prize) {
                $user->balance += $prize;
                $user->claimed_red_packet = true;
                $user->save();

                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'welcome_bonus',
                    'amount' => $prize,
                    'balance_after' => $user->balance,
                    'description' => 'Bono de Bienvenida Sobre Rojo (+' . number_format($prize, 0, ',', '.') . ' COP)',
                ]);
            });

            return response()->json([
                'success' => true,
                'prize' => $prize,
                'message' => "🧧 ¡Sobre Rojo abierto! Has ganado un bono de bienvenida de +$" . number_format($prize, 0, ',', '.') . " COP acreditado a tu balance.",
                'new_balance' => $user->balance,
                'new_balance_formatted' => '$' . number_format($user->balance, 0, ',', '.') . ' COP',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al abrir sobre rojo: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al reclamar la recompensa: ' . $e->getMessage(),
            ], 500);
        }
    }
}
