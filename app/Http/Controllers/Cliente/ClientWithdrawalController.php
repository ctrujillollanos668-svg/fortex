<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClientWithdrawalController extends Controller
{
    public static function isWithdrawalWindowOpen(): bool
    {
        $now = now()->setTimezone('America/Bogota');
        $hour = (int) $now->format('H');
        // Horarios: 8:00 AM a 12:00 PM (08:00 - 11:59) y 2:00 PM a 6:00 PM (14:00 - 17:59)
        return ($hour >= 8 && $hour < 12) || ($hour >= 14 && $hour < 18);
    }

    public function index()
    {
        $user = Auth::user();
        $withdrawals = $user->withdrawals()->latest()->paginate(10);
        $paymentMethods = PaymentMethod::where('status', true)->get();
        $withdrawableBalance = $user->withdrawableBalance();
        $uninvestedDeposit = $user->uninvestedDeposit();
        $isWithdrawalOpen = self::isWithdrawalWindowOpen();
        $totalDeposited = $user->totalDeposited();
        $isAccountActivated = ($totalDeposited >= 15000) || ($user->totalSpentOnPlans() >= 15000);
        $currentBogotaTime = now()->setTimezone('America/Bogota')->format('h:i A');

        return view('cliente.withdrawals.index', compact('user', 'withdrawals', 'paymentMethods', 'withdrawableBalance', 'uninvestedDeposit', 'totalDeposited', 'isAccountActivated', 'isWithdrawalOpen', 'currentBogotaTime'));
    }

    public function store(Request $request)
    {
        if (!self::isWithdrawalWindowOpen()) {
            return back()->with('error', '⚠️ Los retiros solo se procesan en los horarios oficiales: de 8:00 AM a 12:00 PM y de 2:00 PM a 6:00 PM (Hora Colombia). Por favor envía tu solicitud dentro de esas franjas.');
        }

        $user = Auth::user();

        // Validación de seguridad estricta: Requiere haber invertido o recargado OBLIGATORIAMENTE mínimo $15.000 COP
        $totalDeposited = $user->totalDeposited();
        $isAccountActivated = ($totalDeposited >= 15000) || ($user->totalSpentOnPlans() >= 15000);

        if (!$isAccountActivated) {
            return back()->with('error', '⚠️ Para habilitar tu cuenta para retiros, es obligatorio haber realizado una inversión o recarga mínima de $15.000 COP.');
        }

        $withdrawable = $user->withdrawableBalance();

        if ($withdrawable < 15000) {
            $uninvested = $user->uninvestedDeposit();
            $msg = $uninvested > 0
                ? 'El saldo de recargas y los premios de la Ruleta VIP ($' . number_format($uninvested, 0, ',', '.') . ' COP) son exclusivos para activar Planes VIP. Para retirar, debes generar ganancias a través de tus planes activos o comisiones de referidos (mínimo de retiro: $15.000 COP). Tu saldo retirable actual de ganancias de planes es de $' . number_format($withdrawable, 0, ',', '.') . ' COP.'
                : 'Tu saldo retirable de ganancias generadas por planes es de $' . number_format($withdrawable, 0, ',', '.') . ' COP. El monto mínimo para retirar es de $15.000 COP.';

            return back()->with('error', $msg);
        }

        $request->validate([
            'amount' => 'required|numeric|min:15000|max:' . $withdrawable,
            'payment_method' => 'required|string',
            'account_holder_name' => 'required|string|min:3|max:150',
            'wallet_or_account' => 'required|string|max:255',
        ], [
            'amount.min' => 'El monto mínimo de retiro es de $15.000 COP.',
            'amount.max' => 'El monto solicitado supera tu saldo retirable de ganancias ($' . number_format($withdrawable, 0, ',', '.') . ' COP). El saldo de recargas y los premios de la Ruleta VIP deben ser invertidos en Planes VIP para generar rendimientos.',
            'account_holder_name.required' => 'El nombre del titular de la cuenta es obligatorio.',
            'account_holder_name.min' => 'El nombre del titular debe tener al menos 3 caracteres.',
            'wallet_or_account.required' => 'Ingresa tu número de cuenta, celular o billetera de destino.',
        ]);

        $amount = (float) $request->amount;
        $feePercentage = 8.00;
        $fee = round(($amount * $feePercentage) / 100, 2);
        $netAmount = $amount - $fee;
        $holderName = trim($request->account_holder_name);
        $destination = $request->payment_method . ' - ' . $request->wallet_or_account;

        $newWithdrawal = null;
        DB::transaction(function () use ($user, $amount, $fee, $netAmount, $destination, $holderName, &$newWithdrawal) {
            // 1. Descontar saldo del usuario
            $user->balance -= $amount;
            $user->save();

            // 2. Crear solicitud de retiro
            $newWithdrawal = Withdrawal::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'fee' => $fee,
                'net_amount' => $netAmount,
                'wallet_or_account' => $destination,
                'account_holder_name' => $holderName,
                'status' => 'pending',
                'admin_notes' => 'En proceso de pago.',
            ]);

            // 3. Registrar en transacciones
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'withdrawal',
                'amount' => -$amount,
                'balance_after' => $user->balance,
                'description' => 'Retiro a ' . $destination . ' — Titular: ' . $holderName . ' (Comisión 8%: $' . number_format($fee, 0, ',', '.') . ' COP)',
            ]);
        });

        // Notificación instantánea al Administrador por Telegram protegida
        if ($newWithdrawal) {
            try {
                \App\Services\TelegramService::notifyNewWithdrawal($newWithdrawal, $user);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Telegram withdrawal notification error: ' . $e->getMessage());
            }
        }

        return redirect()->route('cliente.withdrawals.index')
            ->with('success', '¡Solicitud de retiro de $' . number_format($amount, 0, ',', '.') . ' COP enviada con éxito! Será transferida a tu cuenta en breve.');
    }
}
