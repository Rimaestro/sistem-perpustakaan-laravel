<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Book;
use App\Models\Category;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil kategori yang sudah ada
        $categories = Category::all();

        if ($categories->isEmpty()) {
            echo "❌ Tidak ada kategori. Jalankan CategorySeeder terlebih dahulu.\n";
            return;
        }

        $books = [
            [
                'title' => 'Laskar Pelangi',
                'author' => 'Andrea Hirata',
                'isbn' => '9789792202298',
                'category_id' => $categories->where('slug', 'fiksi')->first()?->id ?? $categories->first()->id,
                'publication_year' => 2005,
                'description' => 'Novel tentang perjuangan anak-anak Belitung untuk mendapatkan pendidikan.',
                'quantity' => 5,
                'available_quantity' => 5,
            ],
            [
                'title' => 'Bumi Manusia',
                'author' => 'Pramoedya Ananta Toer',
                'isbn' => '9789799731234',
                'category_id' => $categories->where('slug', 'fiksi')->first()?->id ?? $categories->first()->id,
                'publication_year' => 1980,
                'description' => 'Novel sejarah tentang kehidupan di masa kolonial Belanda.',
                'quantity' => 3,
                'available_quantity' => 3,
            ],
            [
                'title' => 'Sejarah Indonesia Modern',
                'author' => 'M.C. Ricklefs',
                'isbn' => '9789799234567',
                'category_id' => $categories->where('slug', 'sejarah')->first()?->id ?? $categories->first()->id,
                'publication_year' => 2008,
                'description' => 'Buku sejarah komprehensif tentang Indonesia modern.',
                'quantity' => 4,
                'available_quantity' => 4,
            ],
            [
                'title' => 'Fisika Dasar',
                'author' => 'Halliday & Resnick',
                'isbn' => '9789799876543',
                'category_id' => $categories->where('slug', 'sains-teknologi')->first()?->id ?? $categories->first()->id,
                'publication_year' => 2015,
                'description' => 'Buku teks fisika untuk tingkat menengah atas.',
                'quantity' => 10,
                'available_quantity' => 10,
            ],
            [
                'title' => 'Matematika SMA Kelas XII',
                'author' => 'Tim Penulis',
                'isbn' => '9789799345678',
                'category_id' => $categories->where('slug', 'pendidikan')->first()?->id ?? $categories->first()->id,
                'publication_year' => 2020,
                'description' => 'Buku pelajaran matematika untuk kelas XII SMA.',
                'quantity' => 15,
                'available_quantity' => 15,
            ],
            [
                'title' => 'Ekonomi Mikro',
                'author' => 'N. Gregory Mankiw',
                'isbn' => '9789799456789',
                'category_id' => $categories->where('slug', 'ekonomi-bisnis')->first()?->id ?? $categories->first()->id,
                'publication_year' => 2018,
                'description' => 'Pengantar ekonomi mikro untuk pemula.',
                'quantity' => 6,
                'available_quantity' => 6,
            ],
            [
                'title' => 'Seni Rupa Indonesia',
                'author' => 'Claire Holt',
                'isbn' => '9789799567890',
                'category_id' => $categories->where('slug', 'seni-budaya')->first()?->id ?? $categories->first()->id,
                'publication_year' => 2000,
                'description' => 'Kajian komprehensif tentang seni rupa Indonesia.',
                'quantity' => 2,
                'available_quantity' => 2,
            ],
            [
                'title' => 'Tafsir Al-Quran',
                'author' => 'M. Quraish Shihab',
                'isbn' => '9789799678901',
                'category_id' => $categories->where('slug', 'agama-spiritual')->first()?->id ?? $categories->first()->id,
                'publication_year' => 2012,
                'description' => 'Tafsir Al-Quran dalam bahasa Indonesia.',
                'quantity' => 8,
                'available_quantity' => 8,
            ],
            [
                'title' => 'Ayat-Ayat Cinta',
                'author' => 'Habiburrahman El Shirazy',
                'isbn' => '9789799789012',
                'category_id' => $categories->where('slug', 'fiksi')->first()?->id ?? $categories->first()->id,
                'publication_year' => 2004,
                'description' => 'Novel islami tentang cinta dan kehidupan.',
                'quantity' => 7,
                'available_quantity' => 7,
            ],
            [
                'title' => 'Biologi SMA',
                'author' => 'Campbell & Reece',
                'isbn' => '9789799890123',
                'category_id' => $categories->where('slug', 'sains-teknologi')->first()?->id ?? $categories->first()->id,
                'publication_year' => 2019,
                'description' => 'Buku teks biologi untuk SMA.',
                'quantity' => 12,
                'available_quantity' => 12,
            ],
        ];

        foreach ($books as $bookData) {
            Book::create($bookData);
        }

        echo "✅ Book seeder berhasil dijalankan!\n";
        echo "📚 " . count($books) . " buku telah ditambahkan ke perpustakaan.\n";
    }
}
