<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\PaymentMethod;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Asegurar que los métodos de pago Nequi y Bancolombia tengan sus QR si están vacíos
        $nequi = PaymentMethod::where('type', 'nequi')->first();
        if ($nequi && empty($nequi->qr_image)) {
            $nequi->update(['qr_image' => 'qrs/1p8cmE5IEHJTHR1U4UGuSVQSgFLloOG63Vtdptas.jpg']);
        }

        $bancolombia = PaymentMethod::where('type', 'bancolombia')->first();
        if ($bancolombia && empty($bancolombia->qr_image)) {
            $bancolombia->update(['qr_image' => 'qrs/xJbKknd3TNGdrYlIJiDRI6CmHjYd3z01AELSOSMY.jpg']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
