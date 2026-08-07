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
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('enable_spiciness')->default(false)->after('is_active');
            $table->json('spiciness_levels')->nullable()->after('enable_spiciness');
            
            $table->boolean('enable_toppings')->default(false)->after('spiciness_levels');
            $table->json('toppings')->nullable()->after('enable_toppings');
            
            $table->boolean('enable_sauces')->default(false)->after('toppings');
            $table->json('sauces')->nullable()->after('enable_sauces');
            
            $table->boolean('enable_additionals')->default(false)->after('sauces');
            $table->json('additionals')->nullable()->after('enable_additionals');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'enable_spiciness',
                'spiciness_levels',
                'enable_toppings',
                'toppings',
                'enable_sauces',
                'sauces',
                'enable_additionals',
                'additionals',
            ]);
        });
    }
};
