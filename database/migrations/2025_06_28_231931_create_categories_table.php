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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nama kategori buku');
            $table->text('description')->nullable()->comment('Deskripsi kategori');
            $table->string('slug')->unique()->comment('URL-friendly name');
            $table->timestamps();
            $table->softDeletes()->comment('Soft delete untuk audit trail');

            // Indexes untuk performance
            $table->index('name');
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
