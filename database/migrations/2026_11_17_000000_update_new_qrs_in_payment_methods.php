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
        // Actualizar los códigos QR oficiales con las nuevas imágenes qrnequi.png y qrbancolombia.png
        $nequi = PaymentMethod::where('type', 'nequi')->orWhere('name', 'like', '%nequi%')->first();
        if ($nequi) {
            $nequi->update(['qr_image' => 'qrs/qrnequi.png']);
        }

        $bancolombia = PaymentMethod::where('type', 'bancolombia')->orWhere('name', 'like', '%banco%')->first();
        if ($bancolombia) {
            $bancolombia->update(['qr_image' => 'qrs/qrbancolombia.png']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
