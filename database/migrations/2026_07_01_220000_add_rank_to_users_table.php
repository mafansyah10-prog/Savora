<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('rank')->default('reguler')->after('email');
            $table->unsignedBigInteger('total_spent')->default(0)->after('rank');
            $table->timestamp('rank_upgraded_at')->nullable()->after('total_spent');
            $table->timestamp('rank_notified_at')->nullable()->after('rank_upgraded_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['rank', 'total_spent', 'rank_upgraded_at', 'rank_notified_at']);
        });
    }
};
