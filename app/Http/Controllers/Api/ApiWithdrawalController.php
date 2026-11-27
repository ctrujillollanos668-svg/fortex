<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ApiWithdrawalController extends Controller
{
    /**
     * Información de retiro: saldos, comisión y retiros recientes.
     */
    public function info()
    {
        $user = Auth::user();
        $withdrawals = $user->withdrawals()->orderBy('created_at', 'desc')->take(20)->get()->map(function ($w) {
            return [
                'id' => $w->id,
                'amount' => (float) $w->amount,
                'fee' => (float) $w->fee,
                'net_amount' => (float) $w->net_amount,
                'wallet_or_account' => $w->wallet_or_account,
                'account_holder_name' => $w->account_holder_name,
                'status' => $w->status,
                'status_label' => match($w->status) {
                    'approved' => 'Pagado',
                    'rejected' => 'Rechazado',
                    default => 'En Proceso',
                },
                'created_at' => $w->created_at->format('d/m/Y h:i A'),
            ];
        });

        return response()->json([
            'success' => true,
            'withdrawable_balance' => (float) $user->withdrawableBalance(),
            'recharge_balance' => (float) $user->rechargeBalance(),
            'total_balance' => (float) $user->balance,
            'min_withdrawal' => 15000,
            'fee_percentage' => 8,
            'recent_withdrawals' => $withdrawals,
        ]);
    }

    /**
     * Solicitar retiro de saldo retirable.
     */
    public function submit(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:15000',
            'bank' => 'required|string|in:Nequi,Bancolombia',
            'account_number' => 'required|string|min:6|max:30',
            'holder_name' => 'required|string|min:3|max:100',
        ], [
            'amount.min' => 'El monto mínimo de retiro es de $15.000 COP.',
            'amount.required' => 'Debes ingresar el valor a retirar.',
            'account_number.required' => 'Ingresa tu número de cuenta o celular.',
            'holder_name.required' => 'Ingresa el nombre del titular.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $amount = (float) $request->input('amount');
        $withdrawable = $user->withdrawableBalance();

        if ($amount > $withdrawable) {
            return response()->json([
                'success' => false,
                'message' => 'Saldo retirable insuficiente. Tienes $' . number_format($withdrawable, 0, ',', '.') . ' COP retirables (ganancias de planes y comisiones).',
            ], 422);
        }

        // Revisar si ya tiene un retiro pendiente
        $hasPending = $user->withdrawals()->where('status', 'pending')->exists();
        if ($hasPending) {
            return response()->json([
                'success' => false,
                'message' => 'Ya tienes una solicitud de retiro en proceso. Por favor espera a que sea procesada.',
            ], 422);
        }

        $fee = round($amount * 0.08, 2); // 8% comisión
        $net = round($amount - $fee, 2);

        try {
            DB::transaction(function () use ($user, $amount, $fee, $net, $request) {
                // Descontar del balance
                $user->balance -= $amount;
                $user->save();

                Withdrawal::create([
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'fee' => $fee,
                    'net_amount' => $net,
                    'wallet_or_account' => $request->input('bank') . ': ' . $request->input('account_number'),
                    'account_holder_name' => $request->input('holder_name'),
                    'status' => 'pending',
                ]);

                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'withdrawal',
                    'amount' => -$amount,
                    'balance_after' => $user->balance,
                    'description' => 'Solicitud de retiro a ' . $request->input('bank') . ' (' . $request->input('account_number') . ')',
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => '💸 Solicitud de retiro por $' . number_format($amount, 0, ',', '.') . ' COP enviada con éxito. Recibirás $' . number_format($net, 0, ',', '.') . ' COP en tu cuenta.',
                'new_withdrawable_balance' => (float) $user->fresh()->withdrawableBalance(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar retiro: ' . $e->getMessage(),
            ], 500);
        }
    }
}
