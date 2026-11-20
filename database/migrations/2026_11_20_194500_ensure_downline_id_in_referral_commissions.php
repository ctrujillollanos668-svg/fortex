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
        if (Schema::hasTable('referral_commissions')) {
            Schema::table('referral_commissions', function (Blueprint $table) {
                if (!Schema::hasColumn('referral_commissions', 'downline_id')) {
                    $table->unsignedBigInteger('downline_id')->nullable()->after('sponsor_id');
                }
                if (!Schema::hasColumn('referral_commissions', 'description')) {
                    $table->string('description')->nullable()->after('percentage');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('referral_commissions')) {
            Schema::table('referral_commissions', function (Blueprint $table) {
                if (Schema::hasColumn('referral_commissions', 'downline_id')) {
                    $table->dropColumn('downline_id');
                }
                if (Schema::hasColumn('referral_commissions', 'description')) {
                    $table->dropColumn('description');
                }
            });
        }
    }
};
