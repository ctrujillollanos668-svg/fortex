<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('withdrawals')) {
            Schema::table('withdrawals', function (Blueprint $table) {
                if (!Schema::hasColumn('withdrawals', 'account_holder_name')) {
                    $table->string('account_holder_name')->nullable()->after('wallet_or_account');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('withdrawals')) {
            Schema::table('withdrawals', function (Blueprint $table) {
                if (Schema::hasColumn('withdrawals', 'account_holder_name')) {
                    $table->dropColumn('account_holder_name');
                }
            });
        }
    }
};
