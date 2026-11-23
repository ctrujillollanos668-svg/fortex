<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\ReferralCommission;
use App\Models\Transaction;
use App\Models\UserPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClientPlanController extends Controller
{
    /**
     * Módulo: Planes (Catálogo de Planes VIP creados por el Admin listos para Comprar).
     */
    public function index()
    {
        $user = Auth::user();
        $availablePlans = Plan::where('status', true)->get();
        $rechargeBalance = $user->rechargeBalance();
        $earningsBalance = $user->earningsBalance();

        return view('cliente.plans.index', compact('user', 'availablePlans', 'rechargeBalance', 'earningsBalance'));
    }

    /**
     * Módulo: Mis Planes (Solo los planes que el cliente ha comprado y tiene activos/completados).
     */
    public function myPlans()
    {
        $user = Auth::user();
        $activePlans = $user->userPlans()->with('plan')->where('status', 'active')->get();
        $completedPlans = $user->userPlans()->with('plan')->where('status', 'completed')->get();
        $rechargeBalance = $user->rechargeBalance();
        $earningsBalance = $user->earningsBalance();

        return view('cliente.plans.my_plans', compact('user', 'activePlans', 'completedPlans', 'rechargeBalance', 'earningsBalance'));
    }

    public function buy(Request $request, $id)
    {
        $plan = Plan::where('status', true)->findOrFail($id);
        $user = Auth::user();

        if ($plan->isSoldOut()) {
            return back()->with('error', '⚠️ Este paquete VIP ya se encuentra agotado (se vendieron todas las unidades disponibles).');
        }

        $request->validate([
            'payment_source' => 'required|in:deposit,earnings',
        ], [
            'payment_source.required' => 'Debes seleccionar el saldo con el que deseas activar el plan.',
            'payment_source.in' => 'El tipo de saldo seleccionado no es válido.',
        ]);

        $source = $request->input('payment_source');
        $rechargeBalance = $user->rechargeBalance();
        $earningsBalance = $user->earningsBalance();

        if ($source === 'deposit') {
            if ($user->balance < $plan->price) {
                return back()->with('error', 'Saldo insuficiente para activar el plan ($' . number_format($user->balance, 0, ',', '.') . ' COP disponibles). Necesitas $' . number_format($plan->price, 0, ',', '.') . ' COP.');
            }
        } else {
            // Re-inversión de ganancias
            if ($earningsBalance < $plan->price) {
                return back()->with('error', 'Saldo de Ganancias insuficiente ($' . number_format($earningsBalance, 0, ',', '.') . ' COP disponibles de ganancias generadas por planes). Necesitas $' . number_format($plan->price, 0, ',', '.') . ' COP de ganancias acumuladas para re-invertir, o puedes activar seleccionando saldo de recarga/bonos.');
            }
        }

        try {
            DB::transaction(function () use ($user, $plan, $source) {
                // 0. Descontar una unidad del inventario/stock si tiene límite
                if ($plan->hasStockLimit() && $plan->stock > 0) {
                    try {
                        $plan->decrement('stock');
                    } catch (\Throwable $eStock) {
                        \Log::warning('No se pudo decrementar el stock del plan: ' . $eStock->getMessage());
                    }
                }

                // 1. Descontar precio del plan del saldo del usuario
                $user->balance -= $plan->price;
                $user->save();

                // 2. Calcular rendimiento diario y tope de ganancia seguro
                $dailyEarning = ($plan->price * $plan->daily_percentage) / 100;
                $maxReturn = ($plan->max_return && (float) $plan->max_return > 0)
                    ? (float) $plan->max_return
                    : ($dailyEarning * (int) ($plan->duration_days ?: 30));

                // 3. Crear el plan activo para el usuario (Inicia conteo de 24 horas)
                $userPlan = UserPlan::create([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'invested_amount' => $plan->price,
                    'daily_earning' => $dailyEarning,
                    'earned_so_far' => 0.00,
                    'max_earning' => $maxReturn,
                    'last_claimed_at' => now(), // Cuenta regresiva de 24 horas inicia en el momento de la compra
                    'status' => 'active',
                ]);

                // 4. Registrar transacción según el origen seleccionado
                $txType = $source === 'deposit' ? 'plan_purchase_deposit' : 'plan_purchase_earnings';
                $txDesc = $source === 'deposit' 
                    ? 'Activación de Membresía con Saldo Recargado: ' . $plan->name 
                    : 'Re-inversión de Membresía con Saldo de Ganancias: ' . $plan->name;

                Transaction::create([
                    'user_id' => $user->id,
                    'type' => $txType,
                    'amount' => -$plan->price,
                    'balance_after' => $user->balance,
                    'description' => $txDesc,
                ]);

                // 5. Pagar comisión del 10% al patrocinador directo (Nivel 1)
                if ($user->referred_by) {
                    $sponsorL1 = $user->sponsor;
                    if ($sponsorL1 && $sponsorL1->id !== $user->id) {
                        $commissionL1 = $plan->price * 0.10; // 10% directo

                        $sponsorL1->balance += $commissionL1;
                        $sponsorL1->save();

                        // Guardar comisión en historial contable
                        Transaction::create([
                            'user_id' => $sponsorL1->id,
                            'type' => 'referral_commission',
                            'amount' => $commissionL1,
                            'balance_after' => $sponsorL1->balance,
                            'description' => 'Comisión de Referido Nivel 1 (10%) por ' . $user->name,
                        ]);

                        // Guardar en tabla específica de comisiones (con salvaguarda por si falta alguna columna en bd)
                        try {
                            $commData = [
                                'sponsor_id' => $sponsorL1->id,
                                'level' => 1,
                                'amount' => $commissionL1,
                                'percentage' => 10.00,
                            ];
                            if (\Illuminate\Support\Facades\Schema::hasColumn('referral_commissions', 'downline_id')) {
                                $commData['downline_id'] = $user->id;
                            }
                            if (\Illuminate\Support\Facades\Schema::hasColumn('referral_commissions', 'description')) {
                                $commData['description'] = 'Comisión Nivel 1 (10%) por compra de ' . $plan->name . ' de ' . $user->name;
                            }
                            ReferralCommission::create($commData);
                        } catch (\Throwable $eCommL1) {
                            \Log::warning('Advertencia al registrar referral_commissions L1: ' . $eCommL1->getMessage());
                        }

                        // 6. Pagar comisión del 2% al patrocinador de segundo nivel (Nivel 2)
                        if ($sponsorL1->referred_by) {
                            $sponsorL2 = $sponsorL1->sponsor;
                            if ($sponsorL2 && $sponsorL2->id !== $user->id && $sponsorL2->id !== $sponsorL1->id) {
                                $commissionL2 = $plan->price * 0.02; // 2% indirecto

                                $sponsorL2->balance += $commissionL2;
                                $sponsorL2->save();

                                Transaction::create([
                                    'user_id' => $sponsorL2->id,
                                    'type' => 'referral_commission',
                                    'amount' => $commissionL2,
                                    'balance_after' => $sponsorL2->balance,
                                    'description' => 'Comisión de Referido Nivel 2 (2%) por ' . $user->name,
                                ]);

                                try {
                                    $commDataL2 = [
                                        'sponsor_id' => $sponsorL2->id,
                                        'level' => 2,
                                        'amount' => $commissionL2,
                                        'percentage' => 2.00,
                                    ];
                                    if (\Illuminate\Support\Facades\Schema::hasColumn('referral_commissions', 'downline_id')) {
                                        $commDataL2['downline_id'] = $user->id;
                                    }
                                    if (\Illuminate\Support\Facades\Schema::hasColumn('referral_commissions', 'description')) {
                                        $commDataL2['description'] = 'Comisión Nivel 2 (2%) por compra de ' . $plan->name . ' de ' . $user->name . ' (Equipo de ' . $sponsorL1->name . ')';
                                    }
                                    ReferralCommission::create($commDataL2);
                                } catch (\Throwable $eCommL2) {
                                    \Log::warning('Advertencia al registrar referral_commissions L2: ' . $eCommL2->getMessage());
                                }
                            }
                        }
                    }
                }
            });
        } catch (\Throwable $e) {
            \Log::error('Error en compra de plan: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', '⚠️ No se pudo procesar la compra: ' . $e->getMessage());
        }

        $walletLabel = $source === 'deposit' ? 'Saldo de Recargas' : 'Saldo de Ganancias';
        return redirect()->route('cliente.plans.my-plans')
            ->with('success', '🎉 ¡Felicidades! Has activado el ' . $plan->name . ' usando tu ' . $walletLabel . '. Tu primer rendimiento de 24 horas ya comenzó a correr.');
    }

    public function claimDaily(Request $request, $id)
    {
        $user = Auth::user();
        $userPlan = UserPlan::where('user_id', $user->id)
            ->where('status', 'active')
            ->findOrFail($id);

        // Validar si ya transcurrieron exactamente las 24 horas
        if (!$userPlan->canClaim()) {
            $seconds = $userPlan->secondsUntilNextClaim();
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $errorMsg = "⏳ Debes esperar exactamente 24 horas entre reclamos. Podrás reclamar nuevamente en {$hours}h {$minutes}m.";

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMsg,
                ], 422);
            }

            return back()->with('error', $errorMsg);
        }

        $earning = 0;
        DB::transaction(function () use ($user, $userPlan, &$earning) {
            $earning = $userPlan->daily_earning;

            // Ajustar si el reclamo excede el tope máximo
            $remainingToMax = $userPlan->max_earning - $userPlan->earned_so_far;
            if ($earning > $remainingToMax) {
                $earning = $remainingToMax;
            }

            // 1. Sumar ganancia al balance del usuario
            $user->balance += $earning;
            $user->save();

            // 2. Actualizar el acumulado del plan y reiniciar contador de 24 horas
            $userPlan->earned_so_far += $earning;
            $userPlan->last_claimed_at = now();

            // 3. Si alcanzó el tope máximo, marcar como completado
            if ($userPlan->earned_so_far >= $userPlan->max_earning) {
                $userPlan->status = 'completed';
            }

            $userPlan->save();

            // 4. Registrar en libro contable
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'daily_claim',
                'amount' => $earning,
                'balance_after' => $user->balance,
                'description' => 'Rendimiento diario de ' . ($userPlan->plan->name ?? 'Plan VIP'),
            ]);
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => '¡Ganancia acreditada correctamente!',
                'earning' => $earning,
                'earning_formatted' => number_format($earning, 0, ',', '.'),
                'new_balance' => $user->balance,
                'new_balance_formatted' => number_format($user->balance, 0, ',', '.'),
                'earned_so_far' => $userPlan->earned_so_far,
                'earned_so_far_formatted' => number_format($userPlan->earned_so_far, 0, ',', '.'),
                'max_earning' => $userPlan->max_earning,
                'max_earning_formatted' => number_format($userPlan->max_earning, 0, ',', '.'),
                'percent' => $userPlan->max_earning > 0 ? min(100, round(($userPlan->earned_so_far / $userPlan->max_earning) * 100)) : 0,
                'next_seconds' => $userPlan->secondsUntilNextClaim(),
                'status' => $userPlan->status,
            ]);
        }

        // Si fue una petición estándar por formulario, no mostramos alerta invasiva
        return back();
    }
}
