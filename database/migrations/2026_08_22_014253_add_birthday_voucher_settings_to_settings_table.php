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
            if (! Schema::hasColumn('settings', 'birthday_voucher_is_active')) {
                $table->boolean('birthday_voucher_is_active')->default(true);
            }
            if (! Schema::hasColumn('settings', 'birthday_voucher_type')) {
                $table->string('birthday_voucher_type')->default('fixed');
            }
            if (! Schema::hasColumn('settings', 'birthday_voucher_value')) {
                $table->decimal('birthday_voucher_value', 15, 2)->default(25000.00);
            }
            if (! Schema::hasColumn('settings', 'birthday_voucher_min_order_amount')) {
                $table->decimal('birthday_voucher_min_order_amount', 15, 2)->default(50000.00);
            }
            if (! Schema::hasColumn('settings', 'birthday_voucher_expires_in_days')) {
                $table->integer('birthday_voucher_expires_in_days')->default(7);
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
                'birthday_voucher_is_active',
                'birthday_voucher_type',
                'birthday_voucher_value',
                'birthday_voucher_min_order_amount',
                'birthday_voucher_expires_in_days',
            ]);
        });
    }
};
