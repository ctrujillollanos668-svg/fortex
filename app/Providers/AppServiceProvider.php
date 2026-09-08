<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Auto-sincronización segura y ultrarrápida para Wasmer / Serverless
        try {
            if (!cache()->has('system_db_sync_v6')) {
                // 1. Columnas en tabla users
                if (Schema::hasTable('users')) {
                    if (!Schema::hasColumn('users', 'last_spin_at')) {
                        Schema::table('users', function (Blueprint $table) {
                            $table->timestamp('last_spin_at')->nullable()->after('status');
                        });
                    }
                    if (!Schema::hasColumn('users', 'claimed_red_packet')) {
                        Schema::table('users', function (Blueprint $table) {
                            $table->boolean('claimed_red_packet')->default(false)->after('status');
                        });
                    }
                    if (!Schema::hasColumn('users', 'roulette_spins')) {
                        Schema::table('users', function (Blueprint $table) {
                            $table->unsignedInteger('roulette_spins')->default(1)->after('status');
                        });
                    }
                }

                // 2. Columnas en tabla plans
                if (Schema::hasTable('plans')) {
                    if (!Schema::hasColumn('plans', 'stock')) {
                        Schema::table('plans', function (Blueprint $table) {
                            $table->integer('stock')->nullable()->after('max_return');
                        });
                    }
                    if (!Schema::hasColumn('plans', 'show_on_home')) {
                        Schema::table('plans', function (Blueprint $table) {
                            $table->boolean('show_on_home')->default(true)->after('status');
                        });
                    }
                }

                // 3. Columnas en tabla withdrawals
                if (Schema::hasTable('withdrawals')) {
                    if (!Schema::hasColumn('withdrawals', 'admin_notes')) {
                        Schema::table('withdrawals', function (Blueprint $table) {
                            $table->text('admin_notes')->nullable()->after('status');
                        });
                    }
                }

                cache()->forever('system_db_sync_v6', true);
            }
        } catch (\Throwable $e) {
            // Continuar sin bloquear el request
        }
    }
}
