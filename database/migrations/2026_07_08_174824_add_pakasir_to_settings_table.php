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
            if (!Schema::hasColumn('settings', 'pakasir_project')) {
                $table->string('pakasir_project')->nullable()->after('qris_image');
            }
            if (!Schema::hasColumn('settings', 'pakasir_api_key')) {
                $table->string('pakasir_api_key')->nullable()->after('pakasir_project');
            }
            if (!Schema::hasColumn('settings', 'pakasir_is_active')) {
                $table->boolean('pakasir_is_active')->default(false)->after('pakasir_api_key');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['pakasir_project', 'pakasir_api_key', 'pakasir_is_active']);
        });
    }
};
