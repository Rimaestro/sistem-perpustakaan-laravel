
<div align="center">

# 📚 Sistem Manajemen Perpustakaan
### SMA Negeri 1 Sampang - Solusi Digital untuk Perpustakaan Modern

![Laravel](https://img.shields.io/badge/Laravel-10-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge)

[🚀 Demo Live](#) • [📖 Dokumentasi](#) • [🐛 Laporkan Bug](#) • [💡 Request Fitur](#)

</div>

---

## 📋 Daftar Isi

- [📖 Tentang Proyek](#-tentang-proyek)
- [✨ Fitur Utama](#-fitur-utama)
- [💻 Teknologi yang Digunakan](#-teknologi-yang-digunakan)
- [🚀 Memulai](#-memulai)
  - [📋 Prasyarat](#-prasyarat)
  - [⚙️ Instalasi](#️-instalasi)
  - [🔧 Konfigurasi](#-konfigurasi)
- [📱 Penggunaan](#-penggunaan)
- [🤝 Kontribusi](#-kontribusi)
- [📄 Lisensi](#-lisensi)

---

## 📖 Tentang Proyek

**Sistem Manajemen Perpustakaan SMA Negeri 1 Sampang** adalah solusi digital modern yang dirancang khusus untuk mengotomatisasi dan mempermudah pengelolaan perpustakaan sekolah. Sistem ini menggabungkan teknologi web terkini dengan antarmuka yang intuitif untuk memberikan pengalaman terbaik bagi pustakawan, guru, dan siswa.

### 🎯 Tujuan Proyek
- Digitalisasi sistem perpustakaan tradisional
- Meningkatkan efisiensi pengelolaan buku dan transaksi
- Menyediakan laporan dan analitik yang komprehensif
- Memberikan akses mudah bagi seluruh civitas akademika

### 🏫 Mengapa SMA Negeri 1 Sampang?
Proyek ini dikembangkan sebagai bagian dari upaya modernisasi pendidikan di SMA Negeri 1 Sampang, Cilacap, dengan fokus pada peningkatan layanan perpustakaan yang lebih efektif dan efisien.

---

## ✨ Fitur Utama

<details>
<summary>📚 <strong>Manajemen Buku</strong></summary>

- ✅ CRUD (Create, Read, Update, Delete) buku
- ✅ Pencarian dan filter buku
- ✅ Kategorisasi buku
- ✅ Manajemen stok dan status buku
- ✅ Support barcode untuk identifikasi cepat
- ✅ Upload cover buku

</details>

<details>
<summary>👥 <strong>Manajemen Anggota</strong></summary>

- ✅ Registrasi dan manajemen data anggota
- ✅ Sistem role (Admin, Staff, Member)
- ✅ Pencarian dan filter anggota
- ✅ Kartu anggota digital
- ✅ Riwayat aktivitas anggota

</details>

<details>
<summary>🔄 <strong>Transaksi Peminjaman</strong></summary>

- ✅ Peminjaman dan pengembalian buku
- ✅ Scan barcode untuk proses cepat
- ✅ Sistem denda otomatis
- ✅ Notifikasi jatuh tempo
- ✅ Perpanjangan peminjaman
- ✅ Riwayat transaksi lengkap

</details>

<details>
<summary>📊 <strong>Laporan & Analitik</strong></summary>

- ✅ Laporan peminjaman harian/bulanan
- ✅ Statistik buku populer
- ✅ Laporan keterlambatan
- ✅ Export ke PDF dan Excel
- ✅ Dashboard analitik real-time

</details>

<details>
<summary>🔐 <strong>Keamanan & Akses</strong></summary>

- ✅ Autentikasi multi-level
- ✅ Role-based access control
- ✅ Audit trail aktivitas
- ✅ Backup data otomatis

</details>

---

## 💻 Teknologi yang Digunakan

### Backend
- **Laravel 10** - Framework PHP modern dan powerful
- **PHP 8.1+** - Bahasa pemrograman server-side
- **MySQL 8.0+** - Database relasional yang robust

### Frontend
- **Blade Templates** - Template engine Laravel
- **TailwindCSS** - Framework CSS utility-first
- **Alpine.js** - Framework JavaScript ringan
- **Vite** - Build tool modern untuk asset

### Tools & Libraries
- **Composer** - Dependency manager PHP
- **NPM** - Package manager JavaScript
- **Laravel Sanctum** - API authentication
- **Laravel Excel** - Export/import Excel
- **DomPDF** - Generate PDF reports

---

## 🚀 Memulai

### 📋 Prasyarat

Pastikan sistem Anda memiliki:

- **PHP** >= 8.1
- **Composer** >= 2.0
- **Node.js** >= 16.0
- **NPM** >= 8.0
- **MySQL** >= 8.0
- **Git**

### ⚙️ Instalasi

1. **Clone repository**
   ```bash
   git clone https://github.com/username/fp-library.git
   cd fp-library
   ```

2. **Install dependencies PHP**
   ```bash
   composer install
   ```

3. **Install dependencies JavaScript**
   ```bash
   npm install
   ```

4. **Setup environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

### 🔧 Konfigurasi

1. **Konfigurasi Database**

   Edit file `.env` dan sesuaikan dengan konfigurasi database Anda:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=library_db
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

2. **Migrasi Database**
   ```bash
   php artisan migrate --seed
   ```

3. **Generate Storage Link**
   ```bash
   php artisan storage:link
   ```

4. **Jalankan Development Server**
   ```bash
   # Terminal 1 - Laravel Server
   php artisan serve

   # Terminal 2 - Vite Dev Server
   npm run dev
   ```

5. **Akses Aplikasi**

   Buka browser dan akses: `http://localhost:8000`

---

## 📱 Penggunaan

### 🔑 Login Default

Setelah menjalankan seeder, Anda dapat login dengan akun berikut:

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@library.com | password |
| Staff | staff@library.com | password |
| Member | member@library.com | password |

### 📖 Panduan Penggunaan

1. **Dashboard** - Lihat ringkasan aktivitas perpustakaan
2. **Manajemen Buku** - Tambah, edit, atau hapus data buku
3. **Manajemen Anggota** - Kelola data anggota perpustakaan
4. **Transaksi** - Proses peminjaman dan pengembalian
5. **Laporan** - Generate dan export berbagai laporan

---

## 🤝 Kontribusi

Kami sangat menghargai kontribusi dari komunitas! Berikut cara untuk berkontribusi:

### 🔧 Development Setup

1. Fork repository ini
2. Buat branch fitur baru (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

### 📝 Guidelines

- Ikuti [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards
- Tulis tests untuk fitur baru
- Update dokumentasi jika diperlukan
- Gunakan commit message yang deskriptif

### 🐛 Melaporkan Bug

Gunakan [GitHub Issues](https://github.com/username/fp-library/issues) untuk melaporkan bug dengan template:

- **Deskripsi bug**
- **Langkah reproduksi**
- **Hasil yang diharapkan**
- **Screenshots** (jika ada)
- **Environment** (OS, PHP version, dll)

---

## 📄 Lisensi

Proyek ini dilisensikan di bawah [MIT License](LICENSE) - lihat file LICENSE untuk detail lengkap.

```
MIT License

Copyright (c) 2024 SMA Negeri 1 Sampang

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.
```


---

[⬆ Kembali ke atas](#-sistem-manajemen-perpustakaan)

</div>

---

> **Palet Warna Proyek:** ![#4960A8](https://via.placeholder.com/15/4960A8/000000?text=+) `#4960A8` ![#F5F5F5](https://via.placeholder.com/15/F5F5F5/000000?text=+) `#F5F5F5` ![#E07A5F](https://via.placeholder.com/15/E07A5F/000000?text=+) `#E07A5F` ![#355C4A](https://via.placeholder.com/15/355C4A/000000?text=+) `#355C4A` ![#FFBE98](https://via.placeholder.com/15/FFBE98/000000?text=+) `#FFBE98` ![#FF8C42](https://via.placeholder.com/15/FF8C42/000000?text=+) `#FF8C42`