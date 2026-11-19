<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDepositController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');

        $query = Deposit::with('user')->latest();

        if ($status && in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        $deposits = $query->paginate(15);

        // Convertir cada comprobante a Base64 para que cargue directamente sin depender de symlinks ni rutas
        $deposits->getCollection()->transform(function ($deposit) {
            if ($deposit->proof_image) {
                $path = storage_path('app/public/' . $deposit->proof_image);
                if (!file_exists($path)) {
                    $path = storage_path('app/' . $deposit->proof_image);
                }
                if (!file_exists($path)) {
                    $path = public_path($deposit->proof_image);
                }

                if (file_exists($path)) {
                    $type = pathinfo($path, PATHINFO_EXTENSION) ?: 'jpeg';
                    $data = file_get_contents($path);
                    $deposit->encoded_image = 'data:image/' . $type . ';base64,' . base64_encode($data);
                } else {
                    $deposit->encoded_image = null;
                }
            } else {
                $deposit->encoded_image = null;
            }
            return $deposit;
        });

        $pendingCount = Deposit::where('status', 'pending')->count();

        return view('admin.deposits.index', compact('deposits', 'status', 'pendingCount'));
    }

    public function approve($id)
    {
        $deposit = Deposit::with('user')->findOrFail($id);

        if ($deposit->status !== 'pending') {
            return back()->with('error', 'Esta recarga ya fue procesada anteriormente.');
        }

        DB::transaction(function () use ($deposit) {
            $user = $deposit->user;

            // 1. Marcar depósito como aprobado
            $deposit->update([
                'status' => 'approved',
                'admin_notes' => 'Aprobado por el Administrador el ' . now()->format('d/m/Y H:i'),
            ]);

            // 2. Sumar saldo al usuario
            $user->balance += $deposit->amount;
            $user->save();

            // 3. Registrar en libro contable de transacciones
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'deposit',
                'amount' => $deposit->amount,
                'balance_after' => $user->balance,
                'description' => 'Recarga de saldo aprobada (' . $deposit->payment_method . ')',
            ]);

            // 4. Premiar al usuario que recarga con +3 Giros de Ruleta VIP
            $user->increment('roulette_spins', 3);
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'deposit_spins',
                'amount' => 0,
                'balance_after' => $user->balance,
                'description' => '🎁 Recompensa por Recarga: +3 Giros de Ruleta VIP',
            ]);

            // 4. Premiar al Patrocinador con +2 Giros de Ruleta por recarga de su invitado
            if ($user->sponsor) {
                $sponsor = $user->sponsor;
                $sponsor->increment('roulette_spins', 2);

                Transaction::create([
                    'user_id' => $sponsor->id,
                    'type' => 'referral_spins',
                    'amount' => 0,
                    'balance_after' => $sponsor->balance,
                    'description' => '🎁 Recompensa: +2 Giros de Ruleta por recarga de tu invitado (' . $user->name . ')',
                ]);
            }
        });

        return back()->with('success', '¡Depósito de $' . number_format($deposit->amount, 0, ',', '.') . ' COP aprobado con éxito!');
    }

    public function reject(Request $request, $id)
    {
        $deposit = Deposit::findOrFail($id);

        if ($deposit->status !== 'pending') {
            return back()->with('error', 'Esta recarga ya fue procesada anteriormente.');
        }

        $reason = $request->input('admin_notes') ?: 'Comprobante falso o pago no recibido en la cuenta.';

        $deposit->update([
            'status' => 'rejected',
            'admin_notes' => $reason,
        ]);

        return back()->with('success', 'La solicitud de depósito ha sido rechazada con el motivo: "' . $reason . '"');
    }

    public function showImage($id)
    {
        $deposit = Deposit::findOrFail($id);

        $path = storage_path('app/public/' . $deposit->proof_image);
        if (!file_exists($path)) {
            $path = storage_path('app/' . $deposit->proof_image);
        }

        if (!file_exists($path)) {
            $path = public_path($deposit->proof_image);
        }

        if (!file_exists($path)) {
            abort(404, 'Imagen no encontrada.');
        }

        return response()->file($path);
    }

    public function destroy($id)
    {
        $deposit = Deposit::findOrFail($id);

        if ($deposit->proof_image) {
            $path = public_path($deposit->proof_image);
            if (file_exists($path)) {
                @unlink($path);
            }
        }

        $deposit->delete();

        return back()->with('success', 'Registro de comprobante eliminado exitosamente.');
    }
}
