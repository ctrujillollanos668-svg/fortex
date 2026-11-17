<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (PaymentMethod::count() == 0) {
            PaymentMethod::create([
                'name' => 'Nequi',
                'type' => 'nequi',
                'account_number' => '3115138588',
                'account_holder' => 'Carlos Trujillo',
                'account_type' => 'Celular',
                'qr_image' => 'qrs/1p8cmE5IEHJTHR1U4UGuSVQSgFLloOG63Vtdptas.jpg',
                'color_theme' => 'purple',
                'status' => true,
            ]);

            PaymentMethod::create([
                'name' => 'Daviplata',
                'type' => 'daviplata',
                'account_number' => '3109876543',
                'account_holder' => 'Administrador Daviplata',
                'account_type' => 'Celular',
                'color_theme' => 'rose',
                'status' => true,
            ]);

            PaymentMethod::create([
                'name' => 'Bancolombia',
                'type' => 'bancolombia',
                'account_number' => '123-456789-00',
                'account_holder' => 'Administrador Bancolombia',
                'account_type' => 'Ahorros',
                'qr_image' => 'qrs/xJbKknd3TNGdrYlIJiDRI6CmHjYd3z01AELSOSMY.jpg',
                'color_theme' => 'amber',
                'status' => true,
            ]);

            PaymentMethod::create([
                'name' => 'Binance USDT',
                'type' => 'crypto',
                'account_number' => 'TX9d82u3J1k9Lp8z2AqX9012a8',
                'account_holder' => 'Billetera Crypto',
                'account_type' => 'TRC20',
                'color_theme' => 'emerald',
                'status' => true,
            ]);
        }

        // Si ya existen pero no tienen QR asignado, asegurar las imágenes
        $nequi = PaymentMethod::where('type', 'nequi')->first();
        if ($nequi && empty($nequi->qr_image)) {
            $nequi->update(['qr_image' => 'qrs/1p8cmE5IEHJTHR1U4UGuSVQSgFLloOG63Vtdptas.jpg']);
        }

        $bancolombia = PaymentMethod::where('type', 'bancolombia')->first();
        if ($bancolombia && empty($bancolombia->qr_image)) {
            $bancolombia->update(['qr_image' => 'qrs/xJbKknd3TNGdrYlIJiDRI6CmHjYd3z01AELSOSMY.jpg']);
        }
    }
}
