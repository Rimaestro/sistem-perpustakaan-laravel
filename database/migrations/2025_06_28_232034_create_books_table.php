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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('Judul buku');
            $table->string('author')->comment('Penulis buku');
            $table->string('isbn', 20)->nullable()->unique()->comment('International Standard Book Number');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade')->comment('Kategori buku');
            $table->enum('status', ['available', 'borrowed', 'maintenance', 'lost'])->default('available')->comment('Status ketersediaan buku');
            $table->string('barcode')->nullable()->unique()->comment('Barcode untuk scanning');
            $table->year('publication_year')->nullable()->comment('Tahun terbit');
            $table->text('description')->nullable()->comment('Deskripsi buku');
            $table->integer('quantity')->default(1)->comment('Jumlah total buku');
            $table->integer('available_quantity')->default(1)->comment('Jumlah buku yang tersedia');
            $table->timestamps();
            $table->softDeletes()->comment('Soft delete untuk audit trail');

            // Indexes untuk performance
            $table->index('title');
            $table->index('author');
            $table->index('status');
            $table->index('category_id');
            $table->index(['status', 'available_quantity']); // Composite index untuk pencarian buku tersedia
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
