<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Si es invitado, mostrar la landing page pública (welcome.blade.php intacta)
        if (!$user) {
            $plans = Plan::where('status', true)->get();
            return view('welcome', compact('plans'));
        }

        // Si es administrador, redirigir a su panel de administración
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        // Si es cliente autenticado, cargar la app móvil VIP (cliente/dashboard.blade.php)
        // En Inicio solo se muestran los planes que el admin eligió mostrar en el Inicio
        $availablePlans = Plan::where('status', true)->where('show_on_home', true)->latest()->get();

        // Si el admin aún no ha marcado ninguno específico para el inicio, mostrar los primeros 2 o 3 activos
        if ($availablePlans->isEmpty()) {
            $availablePlans = Plan::where('status', true)->latest()->take(3)->get();
        }

        $userPlans = $user->userPlans()
            ->with('plan')
            ->where('status', 'active')
            ->get();

        $referralsCount = $user->referrals()->count();
        $totalCommissions = $user->commissionsReceived()->sum('amount');
        $recentTransactions = $user->transactions()->latest()->take(5)->get();
        $rechargeBalance = $user->rechargeBalance();
        $earningsBalance = $user->earningsBalance();

        return view('cliente.dashboard', compact(
            'user',
            'availablePlans',
            'userPlans',
            'referralsCount',
            'totalCommissions',
            'recentTransactions',
            'rechargeBalance',
            'earningsBalance'
        ));
    }
}
