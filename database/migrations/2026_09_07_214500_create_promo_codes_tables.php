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
        if (!Schema::hasTable('promo_codes')) {
            Schema::create('promo_codes', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->decimal('reward_amount', 12, 2)->default(0);
                $table->unsignedInteger('max_uses')->default(1);
                $table->unsignedInteger('used_count')->default(0);
                $table->boolean('status')->default(true);
                $table->string('description')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('promo_code_redemptions')) {
            Schema::create('promo_code_redemptions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('promo_code_id');
                $table->unsignedBigInteger('user_id');
                $table->decimal('reward_amount', 12, 2)->default(0);
                $table->timestamps();

                $table->index(['promo_code_id', 'user_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promo_code_redemptions');
        Schema::dropIfExists('promo_codes');
    }
};
