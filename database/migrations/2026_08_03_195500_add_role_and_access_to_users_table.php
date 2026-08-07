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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('customer')->after('email');
            $table->boolean('can_access_admin_panel')->default(false)->after('role');
        });

        // Set the existing admin user
        \DB::table('users')
            ->where('email', 'admin@gmail.com')
            ->update([
                'role' => 'admin',
                'can_access_admin_panel' => true,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'can_access_admin_panel']);
        });
    }
};
