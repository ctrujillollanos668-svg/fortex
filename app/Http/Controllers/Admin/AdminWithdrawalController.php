<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminWithdrawalController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');

        $query = Withdrawal::with('user')->latest();

        if ($status && in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        $withdrawals = $query->paginate(15);
        $pendingCount = Withdrawal::where('status', 'pending')->count();

        return view('admin.withdrawals.index', compact('withdrawals', 'status', 'pendingCount'));
    }

    public function approve($id)
    {
        $withdrawal = Withdrawal::findOrFail($id);

        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Esta solicitud de retiro ya fue procesada.');
        }

        $withdrawal->update([
            'status' => 'approved',
            'admin_notes' => 'Transferencia completada el ' . now()->format('d/m/Y H:i'),
        ]);

        return back()->with('success', '¡Retiro de $' . number_format($withdrawal->net_amount, 2) . ' marcado como pagado exitosamente!');
    }

    public function reject(Request $request, $id)
    {
        $withdrawal = Withdrawal::with('user')->findOrFail($id);

        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Esta solicitud de retiro ya fue procesada.');
        }

        $reason = $request->input('admin_notes') ?: 'Dirección de billetera o cuenta incorrecta / no válida.';

        DB::transaction(function () use ($withdrawal, $reason) {
            $user = $withdrawal->user;

            // 1. Devolver saldo al cliente
            $user->balance += $withdrawal->amount;
            $user->save();

            // 2. Marcar retiro como rechazado con el motivo/explicación
            $withdrawal->update([
                'status' => 'rejected',
                'admin_notes' => $reason,
            ]);

            // 3. Registrar reembolso en historial
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'withdrawal_refund',
                'amount' => $withdrawal->amount,
                'balance_after' => $user->balance,
                'description' => 'Reembolso por retiro rechazado: ' . $reason,
            ]);
        });

        return back()->with('success', 'El retiro fue cancelado y el saldo ($' . number_format($withdrawal->amount, 0, ',', '.') . ' COP) fue devuelto a la cuenta del usuario. Motivo: "' . $reason . '"');
    }
}
