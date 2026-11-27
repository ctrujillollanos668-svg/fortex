<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ApiDepositController extends Controller
{
    /**
     * Información de cuentas bancarias y depósitos recientes.
     */
    public function info()
    {
        $user = Auth::user();
        $methods = PaymentMethod::where('status', true)->get()->map(function ($m) {
            $qrUrl = null;
            if ($m->qr_image) {
                if (str_starts_with($m->qr_image, 'http')) {
                    $qrUrl = $m->qr_image;
                } else {
                    $qrUrl = asset($m->qr_image);
                }
            }
            return [
                'id' => $m->id,
                'name' => $m->name,
                'type' => $m->type,
                'account_number' => $m->account_number,
                'account_holder' => $m->account_holder,
                'account_type' => $m->account_type,
                'instructions' => $m->instructions,
                'qr_url' => $qrUrl,
                'color_theme' => $m->color_theme ?? 'emerald',
            ];
        });

        $deposits = $user->deposits()->orderBy('created_at', 'desc')->take(20)->get()->map(function ($d) {
            return [
                'id' => $d->id,
                'amount' => (float) $d->amount,
                'payment_method' => $d->payment_method,
                'status' => $d->status,
                'status_label' => match($d->status) {
                    'approved' => 'Aprobado',
                    'rejected' => 'Rechazado',
                    default => 'En Verificación',
                },
                'created_at' => $d->created_at->format('d/m/Y h:i A'),
            ];
        });

        return response()->json([
            'success' => true,
            'min_deposit' => 10000,
            'methods' => $methods,
            'recent_deposits' => $deposits,
        ]);
    }

    /**
     * Enviar comprobante de recarga.
     */
    public function submit(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:10000',
            'payment_method' => 'required|string',
            'proof' => 'required|image|mimes:jpeg,png,jpg,webp|max:8192',
        ], [
            'amount.min' => 'El monto mínimo de recarga es de $10.000 COP.',
            'amount.required' => 'Debes ingresar el valor transferido.',
            'proof.required' => 'Debes adjuntar la foto o captura del comprobante.',
            'proof.image' => 'El archivo debe ser una imagen válida.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $proofPath = null;
            if ($request->hasFile('proof')) {
                $file = $request->file('proof');
                $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/proofs'), $filename);
                $proofPath = 'uploads/proofs/' . $filename;
            }

            $deposit = Deposit::create([
                'user_id' => $user->id,
                'amount' => $request->input('amount'),
                'payment_method' => $request->input('payment_method'),
                'transaction_hash' => $request->input('reference', 'APP-' . strtoupper(Str::random(8))),
                'proof_image' => $proofPath,
                'status' => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'message' => '✅ Comprobante enviado con éxito. Tu saldo se acreditará en breves momentos tras la verificación.',
                'deposit_id' => $deposit->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al subir comprobante: ' . $e->getMessage(),
            ], 500);
        }
    }
}
