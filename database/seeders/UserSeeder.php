<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat user admin
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@library.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Buat user staff
        User::create([
            'name' => 'Staff Perpustakaan',
            'email' => 'staff@library.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
        ]);

        // Buat user member
        User::create([
            'name' => 'Member Perpustakaan',
            'email' => 'member@library.com',
            'password' => Hash::make('password'),
            'role' => 'member',
        ]);

        // Buat beberapa user member tambahan untuk testing
        User::create([
            'name' => 'Ahmad Siswa',
            'email' => 'ahmad@student.com',
            'password' => Hash::make('password'),
            'role' => 'member',
        ]);

        User::create([
            'name' => 'Siti Siswi',
            'email' => 'siti@student.com',
            'password' => Hash::make('password'),
            'role' => 'member',
        ]);

        echo "✅ User seeder berhasil dijalankan!\n";
        echo "📧 Admin: admin@library.com | Password: password\n";
        echo "📧 Staff: staff@library.com | Password: password\n";
        echo "📧 Member: member@library.com | Password: password\n";
    }
}
