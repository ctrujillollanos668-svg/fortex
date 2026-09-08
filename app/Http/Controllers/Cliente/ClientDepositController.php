<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientDepositController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $deposits = $user->deposits()->latest()->paginate(10);
        $paymentMethods = \App\Models\PaymentMethod::where('status', true)->get();

        return view('cliente.deposits.index', compact('user', 'deposits', 'paymentMethods'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000',
            'payment_method' => 'required|string',
            'transaction_hash' => 'required|string|max:255',
            'proof_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:8192',
        ], [
            'amount.min' => 'El monto mínimo de recarga es de $10.000 COP.',
            'transaction_hash.required' => 'Por favor ingresa el número de comprobante o referencia de pago.',
            'proof_image.required' => '⚠️ Es obligatorio adjuntar la foto o captura del comprobante de transferencia.',
            'proof_image.image' => 'El comprobante debe ser un archivo de imagen válido (JPG, PNG, WEBP).',
            'proof_image.max' => 'La imagen no debe pesar más de 8MB.',
        ]);

        $user = Auth::user();
        $proofPath = null;

        if ($request->hasFile('proof_image')) {
            $file = $request->file('proof_image');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            $destinationPath = public_path('uploads/proofs');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            $file->move($destinationPath, $fileName);
            $proofPath = 'uploads/proofs/' . $fileName;
        }

        Deposit::create([
            'user_id' => $user->id,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'transaction_hash' => $request->transaction_hash,
            'proof_image' => $proofPath,
            'status' => 'pending',
            'admin_notes' => 'En espera de verificación por el Administrador.',
        ]);

        return redirect()->route('cliente.deposits.index')
            ->with('success', '¡Recarga de $' . number_format($request->amount, 0, ',', '.') . ' COP reportada con éxito! Por favor espera aproximadamente 10 minutos mientras verificamos tu comprobante para acreditar el saldo en tu cuenta.');
    }
}
