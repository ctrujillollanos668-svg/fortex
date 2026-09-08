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
        if (Schema::hasTable('plans') && !Schema::hasColumn('plans', 'show_on_home')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->boolean('show_on_home')->default(false)->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('plans') && Schema::hasColumn('plans', 'show_on_home')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->dropColumn('show_on_home');
            });
        }
    }
};
