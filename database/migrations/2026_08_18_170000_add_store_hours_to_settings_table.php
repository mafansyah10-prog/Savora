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
            if (! Schema::hasColumn('settings', 'is_store_open')) {
                $table->boolean('is_store_open')->default(true);
            }
            if (! Schema::hasColumn('settings', 'store_open_time')) {
                $table->time('store_open_time')->nullable();
            }
            if (! Schema::hasColumn('settings', 'store_close_time')) {
                $table->time('store_close_time')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['is_store_open', 'store_open_time', 'store_close_time']);
        });
    }
};
