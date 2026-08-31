<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('pickup_is_active')->default(true)->after('store_hours_mode');
            $table->time('pickup_max_time')->nullable()->after('pickup_is_active');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['pickup_is_active', 'pickup_max_time']);
        });
    }
};
