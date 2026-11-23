<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $query = User::where('role', 'cliente')
            ->with(['sponsor', 'userPlans.plan'])
            ->withCount('referrals')
            ->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('referral_code', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15);
        $totalBalance = User::where('role', 'cliente')->sum('balance');

        return view('admin.users.index', compact('users', 'search', 'totalBalance'));
    }

    public function detail($id)
    {
        $user = User::with([
            'sponsor',
            'userPlans.plan',
            'deposits' => fn($q) => $q->latest()->limit(20),
            'withdrawals' => fn($q) => $q->latest()->limit(20),
            'transactions' => fn($q) => $q->latest()->limit(35),
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?: 'Sin teléfono',
                'referral_code' => $user->referral_code,
                'sponsor_name' => $user->sponsor ? $user->sponsor->name : 'Sin patrocinador (Directo)',
                'balance' => (float) $user->balance,
                'total_deposited' => (float) $user->totalDeposited(),
                'total_spent_on_plans' => (float) $user->totalDepositSpentOnPlans(),
                'recharge_balance' => (float) $user->rechargeBalance(),
                'earnings_balance' => (float) $user->earningsBalance(),
                'withdrawable_balance' => (float) $user->withdrawableBalance(),
                'roulette_spins' => (int) ($user->roulette_spins ?? 0),
                'plans' => $user->userPlans->map(function ($up) {
                    return [
                        'name' => $up->plan ? $up->plan->name : 'Plan',
                        'invested_amount' => (float) $up->invested_amount,
                        'daily_earning' => (float) $up->daily_earning,
                        'earned_so_far' => (float) $up->earned_so_far,
                        'max_earning' => (float) $up->max_earning,
                        'status' => $up->status,
                        'created_at' => $up->created_at ? $up->created_at->format('d/m/Y - h:i A') : '-',
                        'last_claimed_at' => $up->last_claimed_at ? $up->last_claimed_at->format('d/m/Y - h:i A') : 'Nunca',
                    ];
                }),
                'deposits' => $user->deposits->map(function ($d) {
                    return [
                        'id' => $d->id,
                        'amount' => (float) $d->amount,
                        'payment_method' => $d->payment_method,
                        'transaction_hash' => $d->transaction_hash,
                        'status' => $d->status,
                        'created_at' => $d->created_at ? $d->created_at->format('d/m/Y - h:i A') : '-',
                    ];
                }),
                'withdrawals' => $user->withdrawals->map(function ($w) {
                    return [
                        'id' => $w->id,
                        'amount' => (float) $w->amount,
                        'fee' => (float) $w->fee,
                        'net_amount' => (float) $w->net_amount,
                        'account_holder_name' => $w->account_holder_name ?: 'No especificado',
                        'wallet_or_account' => $w->wallet_or_account,
                        'status' => $w->status,
                        'created_at' => $w->created_at ? $w->created_at->format('d/m/Y - h:i A') : '-',
                    ];
                }),
                'transactions' => $user->transactions->map(function ($t) {
                    return [
                        'id' => $t->id,
                        'type' => $t->type,
                        'amount' => (float) $t->amount,
                        'balance_after' => (float) $t->balance_after,
                        'description' => $t->description,
                        'created_at' => $t->created_at ? $t->created_at->format('d/m/Y - h:i A') : '-',
                    ];
                }),
            ]
        ]);
    }

    public function adjustBalance(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'amount' => 'required|numeric',
            'action' => 'required|in:add,subtract',
            'reason' => 'required|string|max:255',
        ]);

        $amount = abs($request->amount);

        if ($request->action === 'subtract' && $user->balance < $amount) {
            return back()->with('error', 'El usuario no tiene suficiente saldo para descontar ese monto.');
        }

        if ($request->action === 'add') {
            $user->balance += $amount;
            $type = 'admin_credit';
            $desc = 'Ajuste manual (+): ' . $request->reason;
        } else {
            $user->balance -= $amount;
            $type = 'admin_debit';
            $desc = 'Ajuste manual (-): ' . $request->reason;
        }

        $user->save();

        Transaction::create([
            'user_id' => $user->id,
            'type' => $type,
            'amount' => $request->action === 'add' ? $amount : -$amount,
            'balance_after' => $user->balance,
            'description' => $desc,
        ]);

        return back()->with('success', 'Saldo del usuario actualizado correctamente.');
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);

        if ($user->isAdmin()) {
            return back()->with('error', 'No puedes bloquear a un administrador.');
        }

        $user->status = $user->status === 'active' ? 'blocked' : 'active';
        $user->save();

        $state = $user->status === 'active' ? 'activada' : 'bloqueada';
        return back()->with('success', "La cuenta de {$user->name} ha sido {$state}.");
    }

    public function updatePassword(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'password' => 'required|string|min:6',
        ]);

        $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        $user->save();

        return back()->with('success', "Nueva contraseña asignada a {$user->name} correctamente.");
    }
}
