<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\ReferralCommission;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientTeamController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Referidos directos (Nivel 1)
        $directReferrals = User::where('referred_by', $user->id)
            ->with(['userPlans.plan'])
            ->latest()
            ->get();

        // 2. Referidos de segundo nivel (Nivel 2)
        $directIds = $directReferrals->pluck('id')->toArray();
        $level2Referrals = User::whereIn('referred_by', $directIds)
            ->with(['userPlans.plan', 'sponsor'])
            ->latest()
            ->get();

        // 3. Comisiones totales ganadas registradas para este usuario
        $commissions = ReferralCommission::where('sponsor_id', $user->id)->get();
        
        $commL1 = (float) $commissions->where('level', 1)->sum('amount');
        $commL2 = (float) $commissions->where('level', 2)->sum('amount');
        $totalCommissions = $commL1 + $commL2;

        // Comprobar también en transacciones contables
        $txCommissions = (float) $user->transactions()
            ->where('type', 'referral_commission')
            ->sum('amount');

        if ($txCommissions > $totalCommissions) {
            $totalCommissions = $txCommissions;
            if ($commL1 == 0 && $commL2 == 0) {
                $commL1 = $txCommissions;
            }
        }

        // Mapeo de comisiones agrupadas por downline_id
        $commByDownline = $commissions->groupBy('downline_id')->map(function ($items) {
            return (float) $items->sum('amount');
        })->toArray();

        // Asignar a cada referido directo la comisión exacta generada
        foreach ($directReferrals as $ref) {
            $earnedFromRef = (float) ($commByDownline[$ref->id] ?? 0);
            
            // Si no está registrado en referral_commissions pero compró planes, calculamos el 10%:
            if ($earnedFromRef <= 0 && $ref->userPlans && $ref->userPlans->count() > 0) {
                $totalInvested = (float) $ref->userPlans->sum('invested_amount');
                $earnedFromRef = round($totalInvested * 0.10, 2);
            }

            $ref->commission_generated = $earnedFromRef;
            $ref->plans_count = $ref->userPlans ? $ref->userPlans->where('status', 'active')->count() : 0;
            $ref->total_invested = $ref->userPlans ? (float) $ref->userPlans->sum('invested_amount') : 0;
        }

        // Asignar a cada referido de Nivel 2 la comisión generada (2%)
        foreach ($level2Referrals as $ref2) {
            $earnedFromRef2 = (float) ($commByDownline[$ref2->id] ?? 0);
            if ($earnedFromRef2 <= 0 && $ref2->userPlans && $ref2->userPlans->count() > 0) {
                $totalInvested2 = (float) $ref2->userPlans->sum('invested_amount');
                $earnedFromRef2 = round($totalInvested2 * 0.02, 2);
            }
            $ref2->commission_generated = $earnedFromRef2;
            $ref2->plans_count = $ref2->userPlans ? $ref2->userPlans->where('status', 'active')->count() : 0;
            $ref2->total_invested = $ref2->userPlans ? (float) $ref2->userPlans->sum('invested_amount') : 0;
        }

        // Ajustar totales si el cálculo por planes de referidos es mayor
        $calculatedL1 = (float) $directReferrals->sum('commission_generated');
        $calculatedL2 = (float) $level2Referrals->sum('commission_generated');
        if (($calculatedL1 + $calculatedL2) > $totalCommissions) {
            $commL1 = $calculatedL1;
            $commL2 = $calculatedL2;
            $totalCommissions = $commL1 + $commL2;
        }

        return view('cliente.team.index', compact(
            'user',
            'directReferrals',
            'level2Referrals',
            'totalCommissions',
            'commL1',
            'commL2'
        ));
    }
}
