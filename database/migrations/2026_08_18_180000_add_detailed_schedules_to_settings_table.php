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
            if (! Schema::hasColumn('settings', 'weekly_schedule')) {
                $table->json('weekly_schedule')->nullable();
            }
            if (! Schema::hasColumn('settings', 'special_schedules')) {
                $table->json('special_schedules')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['weekly_schedule', 'special_schedules']);
        });
    }
};
