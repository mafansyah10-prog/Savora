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
        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'rank_bronze_min')) {
                $table->integer('rank_bronze_min')->default(200000)->after('manual_payment_is_active');
            }
            if (! Schema::hasColumn('settings', 'rank_silver_min')) {
                $table->integer('rank_silver_min')->default(1000000)->after('rank_bronze_min');
            }
            if (! Schema::hasColumn('settings', 'rank_gold_min')) {
                $table->integer('rank_gold_min')->default(3000000)->after('rank_silver_min');
            }
            if (! Schema::hasColumn('settings', 'rank_platinum_min')) {
                $table->integer('rank_platinum_min')->default(10000000)->after('rank_gold_min');
            }
            if (! Schema::hasColumn('settings', 'rank_diamond_min')) {
                $table->integer('rank_diamond_min')->default(25000000)->after('rank_platinum_min');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'rank_bronze_min',
                'rank_silver_min',
                'rank_gold_min',
                'rank_platinum_min',
                'rank_diamond_min',
            ]);
        });
    }
};
