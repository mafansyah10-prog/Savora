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
            if (! Schema::hasColumn('settings', 'midtrans_is_active')) {
                $table->boolean('midtrans_is_active')->default(false);
            }
            if (! Schema::hasColumn('settings', 'midtrans_server_key')) {
                $table->string('midtrans_server_key')->nullable();
            }
            if (! Schema::hasColumn('settings', 'midtrans_client_key')) {
                $table->string('midtrans_client_key')->nullable();
            }
            if (! Schema::hasColumn('settings', 'midtrans_is_production')) {
                $table->boolean('midtrans_is_production')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['midtrans_is_active', 'midtrans_server_key', 'midtrans_client_key', 'midtrans_is_production']);
        });
    }
};
