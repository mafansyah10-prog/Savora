<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('shipping_zones')) {
            Schema::create('shipping_zones', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->decimal('cost', 15, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Seed default shipping zones if table is empty
        if (DB::table('shipping_zones')->count() === 0) {
            DB::table('shipping_zones')->insert([
                ['name' => 'Jakarta', 'cost' => 15000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Depok', 'cost' => 20000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Bekasi', 'cost' => 20000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Bogor', 'cost' => 25000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Tangerang', 'cost' => 20000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Tangerang Selatan', 'cost' => 22000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_zones');
    }
};
