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
            $extension = $file->getClientOriginalExtension() ?: 'jpg';
            $fileName = time() . '_' . uniqid() . '.' . $extension;
            
            $destinationPath = public_path('uploads/proofs');
            if (!file_exists($destinationPath)) {
                @mkdir($destinationPath, 0777, true);
            }

            try {
                $file->move($destinationPath, $fileName);
            } catch (\Throwable $e) {
                if ($file->getRealPath() && file_exists($file->getRealPath())) {
                    @copy($file->getRealPath(), $destinationPath . DIRECTORY_SEPARATOR . $fileName);
                }
            }
            $proofPath = 'uploads/proofs/' . $fileName;
        }

        $deposit = Deposit::create([
            'user_id' => $user->id,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'transaction_hash' => $request->transaction_hash,
            'proof_image' => $proofPath,
            'status' => 'pending',
            'admin_notes' => 'En espera de verificación por el Administrador.',
        ]);

        // Notificación instantánea al Administrador por Telegram protegida
        try {
            $absoluteProof = ($proofPath && file_exists(public_path($proofPath))) ? public_path($proofPath) : null;
            \App\Services\TelegramService::notifyNewDeposit($deposit, $user, $absoluteProof);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Telegram notification error: ' . $e->getMessage());
        }

        return redirect()->route('cliente.deposits.index')
            ->with('success', '¡Recarga de $' . number_format($request->amount, 0, ',', '.') . ' COP reportada con éxito! Por favor espera aproximadamente 10 minutos mientras verificamos tu comprobante para acreditar el saldo en tu cuenta.');
    }

    /**
     * Servir la imagen del código QR de un método de pago con soporte de respaldos
     */
    public function showQr($id)
    {
        $pm = \App\Models\PaymentMethod::find($id);

        if ($pm && $pm->qr_image) {
            // 1. Probar en public/
            if (file_exists(public_path($pm->qr_image))) {
                return $this->deliverImage(public_path($pm->qr_image));
            }
            // 2. Probar en public/qrs/
            $basename = basename($pm->qr_image);
            if (file_exists(public_path('qrs/' . $basename))) {
                return $this->deliverImage(public_path('qrs/' . $basename));
            }
            // 3. Probar en storage/app/public/
            if (file_exists(storage_path('app/public/' . $pm->qr_image))) {
                return $this->deliverImage(storage_path('app/public/' . $pm->qr_image));
            }
        }

        // Si no se encontró por ruta específica, respaldar según tipo o nombre
        $type = $pm ? strtolower($pm->type . ' ' . $pm->name) : '';

        if (str_contains($type, 'nequi')) {
            if (file_exists(public_path('qrs/qrnequi.png'))) {
                return $this->deliverImage(public_path('qrs/qrnequi.png'));
            }
            if (file_exists(storage_path('app/public/qrs/qrnequi.png'))) {
                return $this->deliverImage(storage_path('app/public/qrs/qrnequi.png'));
            }
            if (file_exists(public_path('qrs/nequi.jpg'))) {
                return $this->deliverImage(public_path('qrs/nequi.jpg'));
            }
        }

        if (str_contains($type, 'banco')) {
            if (file_exists(public_path('qrs/qrbancolombia.png'))) {
                return $this->deliverImage(public_path('qrs/qrbancolombia.png'));
            }
            if (file_exists(storage_path('app/public/qrs/qrbancolombia.png'))) {
                return $this->deliverImage(storage_path('app/public/qrs/qrbancolombia.png'));
            }
            if (file_exists(public_path('qrs/bancolombia.jpg'))) {
                return $this->deliverImage(public_path('qrs/bancolombia.jpg'));
            }
        }

        // Respaldo global
        if (file_exists(public_path('qrs/qrnequi.png'))) {
            return $this->deliverImage(public_path('qrs/qrnequi.png'));
        }
        if (file_exists(public_path('qrs/nequi.jpg'))) {
            return $this->deliverImage(public_path('qrs/nequi.jpg'));
        }

        abort(404, 'Código QR no disponible.');
    }

    /**
     * Enviar imagen directamente sin depender de restricciones del sistema de archivos
     */
    protected function deliverImage(string $path)
    {
        $mime = str_ends_with(strtolower($path), '.png') ? 'image/png' : 'image/jpeg';
        return response(file_get_contents($path), 200, [
            'Content-Type' => $mime,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
