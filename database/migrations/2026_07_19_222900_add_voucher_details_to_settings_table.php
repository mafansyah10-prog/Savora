<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('new_user_voucher_type')->default('fixed');
            $table->decimal('new_user_voucher_value', 15, 2)->default(10000.00);
            $table->decimal('new_user_voucher_min_order_amount', 15, 2)->default(0.00);
            $table->integer('new_user_voucher_expires_in_days')->default(30);
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'new_user_voucher_type',
                'new_user_voucher_value',
                'new_user_voucher_min_order_amount',
                'new_user_voucher_expires_in_days',
            ]);
        });
    }
};
