<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin User
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin Savora',
                'password' => bcrypt('admin123'),
                'role' => 'admin',
                'can_access_admin_panel' => true,
            ]
        );

        // Categories
        $categories = [
            [
                'name' => 'Gourmet Meals',
                'slug' => 'gourmet-meals',
                'description' => 'Makanan berat dengan cita rasa pilihan.',
                'image' => null,
            ],
            [
                'name' => 'Fresh Produce',
                'slug' => 'fresh-produce',
                'description' => 'Hasil bumi segar untuk kesehatan Anda.',
                'image' => null,
            ],
            [
                'name' => 'Artisanal Drinks',
                'slug' => 'artisanal-drinks',
                'description' => 'Minuman artisan dengan racikan istimewa.',
                'image' => null,
            ],
            [
                'name' => 'Bakery',
                'slug' => 'bakery',
                'description' => 'Roti dan kue panggang segar setiap hari.',
                'image' => null,
            ],
            [
                'name' => 'Gifting',
                'slug' => 'gifting',
                'description' => 'Paket hadiah eksklusif untuk orang tersayang.',
                'image' => null,
            ],
            [
                'name' => 'Pantry',
                'slug' => 'pantry',
                'description' => 'Kebutuhan dapur berkualitas tinggi.',
                'image' => null,
            ],
        ];

        foreach ($categories as $catData) {
            $category = Category::updateOrCreate(['slug' => $catData['slug']], $catData);

            // Products for each category
            if ($catData['slug'] === 'gourmet-meals') {
                Product::updateOrCreate(
                    ['slug' => 'mediterranean-salmon-bowl'],
                    [
                        'category_id' => $category->id,
                        'name' => 'Mediterranean Salmon Bowl',
                        'price' => 180000,
                        'description' => 'Salmon panggang dengan hiasan sayuran sehat.',
                        'is_active' => true,
                        'stock' => 15,
                    ]
                );
            }

            if ($catData['slug'] === 'artisanal-drinks') {
                Product::updateOrCreate(
                    ['slug' => 'sparkling-citrus-spritz'],
                    [
                        'category_id' => $category->id,
                        'name' => 'Sparkling Citrus Spritz',
                        'price' => 65000,
                        'description' => 'Minuman jeruk segar berkarbonasi.',
                        'is_active' => true,
                        'stock' => 25,
                    ]
                );
            }

            if ($catData['slug'] === 'bakery') {
                Product::updateOrCreate(
                    ['slug' => 'artisan-sourdough-loaf'],
                    [
                        'category_id' => $category->id,
                        'name' => 'Artisan Sourdough Loaf',
                        'price' => 95000,
                        'description' => 'Roti gandum utuh dengan fermentasi alami.',
                        'is_active' => true,
                        'stock' => 10,
                    ]
                );
            }
            
            if ($catData['slug'] === 'fresh-produce') {
                Product::updateOrCreate(
                    ['slug' => 'organic-berry-smoothie-kit'],
                    [
                        'category_id' => $category->id,
                        'name' => 'Organic Berry Smoothie Kit',
                        'price' => 145000,
                        'description' => 'Paket buah berry organik siap blender.',
                        'is_active' => true,
                        'stock' => 8,
                    ]
                );
            }
        }

        // Sample Posts (Journal)
        Post::updateOrCreate(
            ['slug' => 'rahasia-salmon-panggang-sempurna'],
            [
                'title' => 'Rahasia Salmon Panggang Sempurna',
                'content' => "Di Savora, kami percaya bahwa kualitas bahan adalah segalanya. Salmon yang kami gunakan berasal dari perairan dingin yang terjaga kualitasnya.\n\nTips memasak salmon di rumah:\n1. Pastikan suhu ruangan sebelum dimasak.\n2. Gunakan api sedang agar kulit tetap renyah.\n3. Jangan terlalu lama memasak (Medium is best!).",
                'excerpt' => 'Pelajari teknik rahasia koki kami dalam menyajikan Mediterranean Salmon yang selalu juicy.',
                'published_at' => now(),
            ]
        );

        Post::updateOrCreate(
            ['slug' => 'manfaat-smoothie-organik-pagi-hari'],
            [
                'title' => 'Manfaat Smoothie Organik di Pagi Hari',
                'content' => "Memulai hari dengan nutrisi yang tepat dapat meningkatkan produktivitas Anda sepanjang hari. Smoothie kit kami dirancang untuk memberikan keseimbangan vitamin dan mineral.\n\nKombinasi berry kami kaya akan antioksidan yang membantu melawan radikal bebas.",
                'excerpt' => 'Kenapa Anda harus mulai beralih ke sarapan organik? Temukan jawabannya di sini.',
                'published_at' => now(),
            ]
        );
    }
}
