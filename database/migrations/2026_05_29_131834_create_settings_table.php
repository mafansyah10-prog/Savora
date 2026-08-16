<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('store_name')->default('Savora');
            $table->string('whatsapp_number')->default('6289601905406');
            $table->string('instagram_url')->default('https://instagram.com/m.afansyah_');
            $table->text('store_address')->nullable();
            $table->string('hero_title')->default('Tingkatkan Pengalaman Bersantap Anda');
            $table->string('hero_subtitle')->default('Temukan Kelezatan Kuliner Artisan Rumahan');
            $table->text('about_text')->nullable();
            $table->timestamps();
        });

        // Insert default setting
        DB::table('settings')->insert([
            'store_name' => 'Savora',
            'whatsapp_number' => '6289601905406',
            'instagram_url' => 'https://instagram.com/m.afansyah_',
            'store_address' => 'Jl. Madrasah RT 05/02, Kecamatan Cilandak, Kelurahan Gandaria Selatan, Jakarta Selatan',
            'hero_title' => 'Tingkatkan Pengalaman Bersantap Anda',
            'hero_subtitle' => 'Temukan Kelezatan Kuliner Artisan Rumahan',
            'about_text' => 'Di Savora, kami menyajikan masakan rumah berkualitas tinggi yang dibuat dengan bahan-bahan segar dan cinta. Setiap hidangan dirancang untuk membawa kenyamanan dan kelezatan masakan artisan langsung ke meja Anda.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
