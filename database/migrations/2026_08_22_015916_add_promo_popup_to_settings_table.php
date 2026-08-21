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
            if (! Schema::hasColumn('settings', 'promo_popup_is_active')) {
                $table->boolean('promo_popup_is_active')->default(false);
            }
            if (! Schema::hasColumn('settings', 'promo_popup_product_id')) {
                $table->unsignedBigInteger('promo_popup_product_id')->nullable();
            }
            if (! Schema::hasColumn('settings', 'promo_popup_duration_seconds')) {
                $table->integer('promo_popup_duration_seconds')->default(7);
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
                'promo_popup_is_active',
                'promo_popup_product_id',
                'promo_popup_duration_seconds',
            ]);
        });
    }
};
