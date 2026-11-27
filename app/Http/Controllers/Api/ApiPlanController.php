<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\ReferralCommission;
use App\Models\Transaction;
use App\Models\UserPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApiPlanController extends Controller
{
    /**
     * Catálogo oficial de Planes VIP para comprar.
     */
    public function catalog()
    {
        $user = Auth::user();
        $plans = Plan::where('status', true)->orderBy('price', 'asc')->get();

        $formatted = $plans->map(function ($plan) {
            $dailyEarning = ($plan->price * $plan->daily_percentage) / 100;
            $maxReturn = ($plan->max_return && (float) $plan->max_return > 0)
                ? (float) $plan->max_return
                : ($dailyEarning * (int) ($plan->duration_days ?: 30));

            return [
                'id' => $plan->id,
                'name' => $plan->name,
                'description' => $plan->description,
                'price' => (float) $plan->price,
                'daily_percentage' => (float) $plan->daily_percentage,
                'daily_earning' => round($dailyEarning, 2),
                'duration_days' => (int) $plan->duration_days,
                'max_return' => round($maxReturn, 2),
                'badge' => $plan->badge,
                'is_sold_out' => $plan->isSoldOut(),
                'stock' => $plan->stock,
            ];
        });

        return response()->json([
            'success' => true,
            'balance' => (float) $user->balance,
            'recharge_balance' => (float) $user->rechargeBalance(),
            'earnings_balance' => (float) $user->earningsBalance(),
            'plans' => $formatted,
        ]);
    }

    /**
     * Mis Planes (Activos y Completados).
     */
    public function myPlans()
    {
        $user = Auth::user();
        $userPlans = $user->userPlans()->with('plan')->orderBy('created_at', 'desc')->get();

        $plansData = $userPlans->map(function ($up) {
            $secondsUntilClaim = $up->canClaim() ? 0 : $up->secondsUntilNextClaim();
            $hours = floor($secondsUntilClaim / 3600);
            $minutes = floor(($secondsUntilClaim % 3600) / 60);

            $progress = $up->max_earning > 0
                ? min(1.0, round($up->earned_so_far / $up->max_earning, 2))
                : 0.0;

            return [
                'id' => $up->id,
                'plan_id' => $up->plan_id,
                'plan_name' => $up->plan ? $up->plan->name : 'Plan VIP',
                'invested_amount' => (float) $up->invested_amount,
                'daily_earning' => (float) $up->daily_earning,
                'earned_so_far' => (float) $up->earned_so_far,
                'max_earning' => (float) $up->max_earning,
                'status' => $up->status,
                'can_claim' => $up->canClaim(),
                'seconds_until_claim' => $secondsUntilClaim,
                'time_remaining_label' => $up->canClaim() ? '¡Listo para reclamar!' : "{$hours}h {$minutes}m",
                'progress' => $progress,
                'last_claimed_at' => $up->last_claimed_at ? $up->last_claimed_at->toDateTimeString() : null,
                'created_at' => $up->created_at->toDateTimeString(),
            ];
        });

        return response()->json([
            'success' => true,
            'balance' => (float) $user->balance,
            'withdrawable_balance' => (float) $user->withdrawableBalance(),
            'plans' => $plansData,
        ]);
    }

    /**
     * Comprar / Activar un Plan VIP.
     */
    public function buy(Request $request)
    {
        $user = Auth::user();
        $planId = $request->input('plan_id');

        $plan = Plan::where('status', true)->find($planId);
        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'El plan seleccionado no existe o no está activo.',
            ], 404);
        }

        if ($plan->isSoldOut()) {
            return response()->json([
                'success' => false,
                'message' => '⚠️ Este paquete VIP ya se encuentra agotado.',
            ], 422);
        }

        if ($user->balance < $plan->price) {
            return response()->json([
                'success' => false,
                'message' => 'Saldo insuficiente ($' . number_format($user->balance, 0, ',', '.') . ' COP disponibles). Necesitas $' . number_format($plan->price, 0, ',', '.') . ' COP.',
            ], 422);
        }

        try {
            DB::transaction(function () use ($user, $plan) {
                if ($plan->hasStockLimit() && $plan->stock > 0) {
                    try {
                        $plan->decrement('stock');
                    } catch (\Throwable $e) {}
                }

                $user->balance -= $plan->price;
                $user->save();

                $dailyEarning = ($plan->price * $plan->daily_percentage) / 100;
                $maxReturn = ($plan->max_return && (float) $plan->max_return > 0)
                    ? (float) $plan->max_return
                    : ($dailyEarning * (int) ($plan->duration_days ?: 30));

                UserPlan::create([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'invested_amount' => $plan->price,
                    'daily_earning' => $dailyEarning,
                    'earned_so_far' => 0,
                    'max_earning' => $maxReturn,
                    'last_claimed_at' => now(), // Cuenta regresiva 24h
                    'status' => 'active',
                ]);

                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'plan_purchase',
                    'amount' => -$plan->price,
                    'balance_after' => $user->balance,
                    'description' => 'Activación de ' . $plan->name,
                ]);

                // Comisiones al sponsor (10% N1, 2% N2)
                if ($user->referred_by) {
                    $sponsorL1 = $user->sponsor;
                    if ($sponsorL1 && $sponsorL1->id !== $user->id) {
                        $commL1 = $plan->price * 0.10;
                        $sponsorL1->balance += $commL1;
                        $sponsorL1->save();

                        Transaction::create([
                            'user_id' => $sponsorL1->id,
                            'type' => 'referral_commission',
                            'amount' => $commL1,
                            'balance_after' => $sponsorL1->balance,
                            'description' => 'Comisión de Referido Nivel 1 (10%) por ' . $user->name,
                        ]);

                        if ($sponsorL1->referred_by) {
                            $sponsorL2 = $sponsorL1->sponsor;
                            if ($sponsorL2 && $sponsorL2->id !== $user->id && $sponsorL2->id !== $sponsorL1->id) {
                                $commL2 = $plan->price * 0.02;
                                $sponsorL2->balance += $commL2;
                                $sponsorL2->save();

                                Transaction::create([
                                    'user_id' => $sponsorL2->id,
                                    'type' => 'referral_commission',
                                    'amount' => $commL2,
                                    'balance_after' => $sponsorL2->balance,
                                    'description' => 'Comisión de Referido Nivel 2 (2%) por ' . $user->name,
                                ]);
                            }
                        }
                    }
                }
            });

            return response()->json([
                'success' => true,
                'message' => '🎉 ¡Felicidades! Has activado el ' . $plan->name . '. Tu rendimiento cada 24h ya comenzó a correr.',
                'balance' => (float) $user->fresh()->balance,
            ]);
        } catch (\Exception $e) {
            Log::error('Error compra plan API: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la compra: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reclamar ganancia diaria de 24 horas.
     */
    public function claimDaily(Request $request, $id)
    {
        $user = Auth::user();
        $userPlan = UserPlan::where('user_id', $user->id)
            ->where('status', 'active')
            ->find($id);

        if (!$userPlan) {
            return response()->json([
                'success' => false,
                'message' => 'Plan no encontrado o no activo.',
            ], 404);
        }

        if (!$userPlan->canClaim()) {
            $seconds = $userPlan->secondsUntilNextClaim();
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            return response()->json([
                'success' => false,
                'message' => "⏳ Debes esperar exactamente 24 horas entre reclamos. Podrás reclamar en {$hours}h {$minutes}m.",
            ], 422);
        }

        $earning = 0;
        DB::transaction(function () use ($user, $userPlan, &$earning) {
            $earning = $userPlan->daily_earning;
            $remaining = $userPlan->max_earning - $userPlan->earned_so_far;
            if ($earning > $remaining) {
                $earning = $remaining;
            }

            $user->balance += $earning;
            $user->save();

            $userPlan->earned_so_far += $earning;
            $userPlan->last_claimed_at = now();

            if ($userPlan->earned_so_far >= $userPlan->max_earning) {
                $userPlan->status = 'completed';
            }
            $userPlan->save();

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'daily_claim',
                'amount' => $earning,
                'balance_after' => $user->balance,
                'description' => 'Ganancia diaria reclamada: ' . ($userPlan->plan ? $userPlan->plan->name : 'Plan VIP'),
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => '💰 ¡Ganancia de $' . number_format($earning, 0, ',', '.') . ' COP reclamada con éxito!',
            'claimed_amount' => (float) $earning,
            'new_balance' => (float) $user->fresh()->balance,
            'withdrawable_balance' => (float) $user->fresh()->withdrawableBalance(),
        ]);
    }
}
