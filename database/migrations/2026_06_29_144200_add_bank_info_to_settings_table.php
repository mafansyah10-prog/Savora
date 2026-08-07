<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'bank_name')) {
                $table->string('bank_name')->default('BCA')->after('store_address');
            }
            if (!Schema::hasColumn('settings', 'bank_account_number')) {
                $table->string('bank_account_number')->default('1234567890')->after('bank_name');
            }
            if (!Schema::hasColumn('settings', 'bank_account_name')) {
                $table->string('bank_account_name')->default('Savora Store')->after('bank_account_number');
            }
            if (!Schema::hasColumn('settings', 'qris_image')) {
                $table->string('qris_image')->nullable()->after('bank_account_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'bank_account_number', 'bank_account_name', 'qris_image']);
        });
    }
};
