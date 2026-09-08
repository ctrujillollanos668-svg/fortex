<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Crear Administrador
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@plata.test',
            'phone' => '+57 300 1234567',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'balance' => 0.00,
            'referral_code' => 'ADMIN01',
            'status' => 'active',
        ]);

        // 2. Crear Cliente de Prueba
        $cliente = User::create([
            'name' => 'Juan Pérez',
            'email' => 'cliente@plata.test',
            'phone' => '+57 310 9876543',
            'password' => Hash::make('cliente123'),
            'role' => 'cliente',
            'balance' => 50000.00, // $50.000 COP
            'referral_code' => 'JUANVIP',
            'status' => 'active',
        ]);

        // 3. Crear Planes de Inversión en Pesos Colombianos (COP)
        Plan::create([
            'name' => 'Plan Nivel 1',
            'description' => 'Membresía inicial con 5% de ganancia diaria en pesos colombianos ($ COP).',
            'price' => 30000.00, // $30.000 COP
            'daily_percentage' => 5.00, // 5% = $1.500 COP diarios
            'duration_days' => 30,
            'max_return' => 45000.00, // $45.000 COP total
            'badge' => '⭐ VIP Nivel 1',
            'status' => true,
            'show_on_home' => true,
        ]);

        Plan::create([
            'name' => 'Plan Nivel 2',
            'description' => 'Membresía intermedia más popular con 6% de retorno diario garantizado.',
            'price' => 60000.00, // $60.000 COP
            'daily_percentage' => 6.00, // 6% = $3.600 COP diarios
            'duration_days' => 30,
            'max_return' => 108000.00, // $108.000 COP total
            'badge' => '🔥 VIP Nivel 2 - Más Popular',
            'status' => true,
            'show_on_home' => true,
        ]);

        Plan::create([
            'name' => 'Plan Nivel 3',
            'description' => 'Membresía avanzada de alta rentabilidad con 7% diario y soporte prioritario.',
            'price' => 120000.00, // $120.000 COP
            'daily_percentage' => 7.00, // 7% = $8.400 COP diarios
            'duration_days' => 30,
            'max_return' => 252000.00, // $252.000 COP total
            'badge' => '💎 VIP Nivel 3 - Élite',
            'status' => true,
            'show_on_home' => true,
        ]);

        // 4. Métodos de Pago Dinámicos
        \App\Models\PaymentMethod::create([
            'name' => 'Nequi',
            'type' => 'nequi',
            'account_number' => '3115138588',
            'account_holder' => 'Carlos Trujillo',
            'account_type' => 'Celular',
            'color_theme' => 'purple',
            'status' => true,
        ]);

        \App\Models\PaymentMethod::create([
            'name' => 'Daviplata',
            'type' => 'daviplata',
            'account_number' => '3109876543',
            'account_holder' => 'Administrador Daviplata',
            'account_type' => 'Celular',
            'color_theme' => 'rose',
            'status' => true,
        ]);

        \App\Models\PaymentMethod::create([
            'name' => 'Bancolombia',
            'type' => 'bancolombia',
            'account_number' => '123-456789-00',
            'account_holder' => 'Administrador Bancolombia',
            'account_type' => 'Ahorros',
            'color_theme' => 'amber',
            'status' => true,
        ]);

        \App\Models\PaymentMethod::create([
            'name' => 'USDT (Binance)',
            'type' => 'crypto',
            'account_number' => 'TX9d82u3J1k9Lp8z2AqX9012a8',
            'account_holder' => 'Billetera Crypto',
            'account_type' => 'TRC20',
            'color_theme' => 'emerald',
            'status' => true,
        ]);
    }
}
