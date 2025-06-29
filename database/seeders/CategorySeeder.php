<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Fiksi',
                'description' => 'Buku-buku cerita fiksi, novel, dan karya sastra',
                'slug' => 'fiksi',
            ],
            [
                'name' => 'Non-Fiksi',
                'description' => 'Buku-buku faktual, biografi, dan referensi',
                'slug' => 'non-fiksi',
            ],
            [
                'name' => 'Sains & Teknologi',
                'description' => 'Buku-buku tentang sains, teknologi, dan penelitian',
                'slug' => 'sains-teknologi',
            ],
            [
                'name' => 'Sejarah',
                'description' => 'Buku-buku sejarah Indonesia dan dunia',
                'slug' => 'sejarah',
            ],
            [
                'name' => 'Pendidikan',
                'description' => 'Buku pelajaran dan materi pendidikan',
                'slug' => 'pendidikan',
            ],
            [
                'name' => 'Agama & Spiritual',
                'description' => 'Buku-buku keagamaan dan spiritual',
                'slug' => 'agama-spiritual',
            ],
            [
                'name' => 'Ekonomi & Bisnis',
                'description' => 'Buku tentang ekonomi, bisnis, dan keuangan',
                'slug' => 'ekonomi-bisnis',
            ],
            [
                'name' => 'Seni & Budaya',
                'description' => 'Buku tentang seni, budaya, dan kreativitas',
                'slug' => 'seni-budaya',
            ],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->insert([
                'name' => $category['name'],
                'description' => $category['description'],
                'slug' => $category['slug'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        echo "✅ Category seeder berhasil dijalankan!\n";
        echo "📚 " . count($categories) . " kategori buku telah ditambahkan.\n";
    }
}
