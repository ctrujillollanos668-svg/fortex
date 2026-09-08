<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
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
                    'message' => 'Sesión expirada. Por favor recarga e inicia sesión nuevamente.',
                ], 401);
            }

            // Asegurar que las columnas existan en caso de base de datos remota desfasada
            if (!Schema::hasColumn('users', 'roulette_spins')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->unsignedInteger('roulette_spins')->default(1);
                });
            }
            if (!Schema::hasColumn('users', 'last_spin_at')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->timestamp('last_spin_at')->nullable();
                });
            }

            // Recargar datos frescos del usuario
            $user->refresh();

            // Si el usuario nunca ha girado (usuario nuevo) y sus giros están en 0 o null, garantizar su giro gratis de bienvenida
            if ($user->last_spin_at === null && ($user->roulette_spins === null || (int)$user->roulette_spins <= 0)) {
                $user->roulette_spins = 1;
                $user->save();
            }

            $spinsAvailable = (int) ($user->roulette_spins ?? 0);

            if ($spinsAvailable <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => '⚠️ No tienes giros disponibles. ¡Invita amigos con tu link de referido o realiza una recarga para ganar más giros!',
                    'spins_left' => 0,
                ], 422);
            }

            // Segmentos de la ruleta con premios en COP
            $segments = [
                ['index' => 0, 'prize' => 1000,  'label' => '$1.000 COP',  'color' => '#10b981'],
                ['index' => 1, 'prize' => 2000,  'label' => '$2.000 COP',  'color' => '#06b6d4'],
                ['index' => 2, 'prize' => 5000,  'label' => '$5.000 COP',  'color' => '#f59e0b'],
                ['index' => 3, 'prize' => 9000,  'label' => '$9.000 COP',  'color' => '#ec4899'],
                ['index' => 4, 'prize' => 500,   'label' => '$500 COP',    'color' => '#8b5cf6'],
                ['index' => 5, 'prize' => 13000, 'label' => '👑 $13.000',  'color' => '#ef4444'],
                ['index' => 6, 'prize' => 3000,  'label' => '$3.000 COP',  'color' => '#14b8a6'],
                ['index' => 7, 'prize' => 1000,  'label' => '$1.000 COP',  'color' => '#3b82f6'],
            ];

            // Ponderación de probabilidades balanceada y emocionante
            $weights = [
                0 => 25, // $1.000 COP
                1 => 20, // $2.000 COP
                2 => 10, // $5.000 COP
                3 => 6,  // $9.000 COP
                4 => 14, // $500 COP
                5 => 3,  // 👑 $13.000 COP (Premio Mayor VIP)
                6 => 12, // $3.000 COP
                7 => 10, // $1.000 COP
            ];

            $totalWeight = array_sum($weights);
            $rand = mt_rand(1, $totalWeight);
            $current = 0;
            $chosenIndex = 0;
            foreach ($weights as $index => $weight) {
                $current += $weight;
                if ($rand <= $current) {
                    $chosenIndex = $index;
                    break;
                }
            }

            $selected = $segments[$chosenIndex] ?? $segments[0];

            $prize = $selected['prize'];

            DB::transaction(function () use ($user, $prize, $spinsAvailable) {
                $user->roulette_spins = max(0, $spinsAvailable - 1);
                $user->balance += $prize;
                $user->last_spin_at = now();
                $user->save();

                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'roulette_reward',
                    'amount' => $prize,
                    'balance_after' => $user->balance,
                    'description' => 'Premio de Ruleta de la Suerte (+' . number_format($prize, 0, ',', '.') . ' COP)',
                ]);
            });

            return response()->json([
                'success' => true,
                'segment_index' => $selected['index'],
                'prize' => $prize,
                'prize_label' => $selected['label'],
                'spins_left' => $user->roulette_spins,
                'new_balance' => $user->balance,
                'new_balance_formatted' => '$' . number_format($user->balance, 0, ',', '.') . ' COP',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al girar ruleta: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al procesar el giro: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Abrir Sobre Rojo VIP o Canjear Código Promocional
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

            // 1. Canje con código promocional exclusivo
            if (!empty($code)) {
                $validPromoCodes = [
                    'VIP2026' => 5000,
                    'BONO777' => 3000,
                    'FORTEX' => 2500,
                    'PLATA' => 2000,
                    'NEQUI' => 1500,
                ];

                if (!isset($validPromoCodes[$code])) {
                    return response()->json([
                        'success' => false,
                        'message' => '❌ El código promocional ingresado no es válido o ya caducó.',
                    ], 422);
                }

                // Validar si ya canjeó este código
                $alreadyUsed = Transaction::where('user_id', $user->id)
                    ->where('description', 'LIKE', "%Código Promocional [{$code}]%")
                    ->exists();

                if ($alreadyUsed) {
                    return response()->json([
                        'success' => false,
                        'message' => "⚠️ Ya canjeaste el código {$code} anteriormente.",
                    ], 422);
                }

                $prize = $validPromoCodes[$code];

                DB::transaction(function () use ($user, $prize, $code) {
                    $user->balance += $prize;
                    $user->save();

                    Transaction::create([
                        'user_id' => $user->id,
                        'type' => 'promo_code',
                        'amount' => $prize,
                        'balance_after' => $user->balance,
                        'description' => "Bono de Código Promocional [{$code}] (+" . number_format($prize, 0, ',', '.') . " COP)",
                    ]);
                });

                return response()->json([
                    'success' => true,
                    'prize' => $prize,
                    'message' => "🎉 ¡Código {$code} canjeado con éxito! Recibes +$" . number_format($prize, 0, ',', '.') . " COP en tu saldo disponible.",
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
