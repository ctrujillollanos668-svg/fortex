<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Deposit;
use App\Models\Transaction;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Eliminar todos los depósitos de prueba registrados
        Deposit::query()->delete();

        // 2. Eliminar transacciones de depósitos de prueba
        Transaction::whereIn('type', ['deposit', 'deposit_spins', 'referral_spins', 'referral_commission'])->delete();

        // 3. Resetear saldo y giros de prueba del usuario Cristian / Admin
        User::where('phone', '3117944193')
            ->orWhere('email', 'like', '%cristian%')
            ->update([
                'balance' => 0.00,
                'roulette_spins' => 0,
            ]);

        // 4. Limpiar archivos de comprobantes de prueba en disco
        $files = glob(public_path('uploads/proofs/wa_proof_*'));
        if ($files) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
