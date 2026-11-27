<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApiRewardController extends Controller
{
    /**
     * Estado de la Ruleta VIP y Sobre Rojo.
     */
    public function status()
    {
        $user = Auth::user();
        $spins = (int) ($user->roulette_spins ?? 0);
        $seconds = $user->secondsUntilNextSpin();

        return response()->json([
            'success' => true,
            'spins' => $spins,
            'can_spin' => $spins > 0,
            'seconds_until_next' => $seconds,
            'prizes' => [
                ['label' => '$1.000 COP', 'amount' => 1000],
                ['label' => '$2.000 COP', 'amount' => 2000],
                ['label' => '$5.000 COP', 'amount' => 5000],
                ['label' => '$10.000 COP', 'amount' => 10000],
                ['label' => 'Sigue intentando', 'amount' => 0],
                ['label' => '$500 COP', 'amount' => 500],
            ],
        ]);
    }

    /**
     * Girar la ruleta de la suerte.
     */
    public function spin(Request $request)
    {
        $user = Auth::user();
        $spins = (int) ($user->roulette_spins ?? 0);

        if ($spins <= 0) {
            return response()->json([
                'success' => false,
                'message' => '⚠️ No tienes giros disponibles. Recarga saldo o invita amigos que compren planes para ganar más giros.',
            ], 422);
        }

        // Premios reales con pesos probabilísticos
        $prizes = [
            ['amount' => 500, 'label' => '$500 COP', 'index' => 5, 'weight' => 45],
            ['amount' => 1000, 'label' => '$1.000 COP', 'index' => 0, 'weight' => 30],
            ['amount' => 2000, 'label' => '$2.000 COP', 'index' => 1, 'weight' => 15],
            ['amount' => 5000, 'label' => '$5.000 COP', 'index' => 2, 'weight' => 8],
            ['amount' => 10000, 'label' => '$10.000 COP', 'index' => 3, 'weight' => 2],
        ];

        $rand = rand(1, 100);
        $selected = $prizes[0];
        $cumulative = 0;

        foreach ($prizes as $p) {
            $cumulative += $p['weight'];
            if ($rand <= $cumulative) {
                $selected = $p;
                break;
            }
        }

        DB::transaction(function () use ($user, $selected) {
            $user->roulette_spins = max(0, ((int) ($user->roulette_spins ?? 0)) - 1);
            $user->last_spin_at = now();
            $user->balance += $selected['amount'];
            $user->save();

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'roulette_reward',
                'amount' => $selected['amount'],
                'balance_after' => $user->balance,
                'description' => 'Premio de Ruleta VIP: ' . $selected['label'],
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => '🎉 ¡Felicidades! Has ganado ' . $selected['label'] . ' en saldo para planes.',
            'won_amount' => $selected['amount'],
            'won_label' => $selected['label'],
            'prize_index' => $selected['index'],
            'remaining_spins' => (int) $user->fresh()->roulette_spins,
            'new_balance' => (float) $user->fresh()->balance,
        ]);
    }
}
