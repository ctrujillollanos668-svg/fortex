<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiTeamController extends Controller
{
    /**
     * Información del equipo de referidos y comisiones de red.
     */
    public function info()
    {
        $user = Auth::user();

        // Nivel 1 (Directos)
        $level1Users = User::where('referred_by', $user->id)
            ->with(['userPlans' => function ($q) {
                $q->where('status', 'active')->orWhere('status', 'completed');
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        $level1Ids = $level1Users->pluck('id');

        // Nivel 2 (Invitados de mis invitados)
        $level2Count = User::whereIn('referred_by', $level1Ids)->count();

        // Calcular comisiones generadas por cada directo
        $level1Data = $level1Users->map(function ($u) {
            $totalInvested = $u->userPlans->sum('invested_amount');
            $commGenerated = $totalInvested * 0.10; // 10%
            return [
                'id' => $u->id,
                'name' => $u->name,
                'phone' => substr($u->phone ?? '3000000000', 0, 3) . '****' . substr($u->phone ?? '3000000000', -3),
                'active_plans_count' => $u->userPlans->count(),
                'commission_generated' => round($commGenerated, 2),
                'joined_at' => $u->created_at->format('d/m/Y'),
            ];
        });

        $totalCommissionEarned = (float) $user->totalReferralEarnings();

        return response()->json([
            'success' => true,
            'referral_code' => $user->referral_code,
            'referral_link' => 'https://fortex.wasmer.app/register?ref=' . $user->referral_code,
            'total_commissions_earned' => $totalCommissionEarned,
            'level1_count' => $level1Users->count(),
            'level2_count' => $level2Count,
            'level1_team' => $level1Data,
        ]);
    }
}
