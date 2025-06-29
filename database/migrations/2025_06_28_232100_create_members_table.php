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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('member_id', 20)->unique()->comment('ID anggota yang di-generate (M2025001)');
            $table->string('name')->comment('Nama lengkap anggota');
            $table->string('email')->unique()->comment('Email anggota');
            $table->string('phone', 20)->nullable()->comment('Nomor telepon');
            $table->text('address')->nullable()->comment('Alamat lengkap');
            $table->date('join_date')->comment('Tanggal bergabung');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->comment('Status keanggotaan');
            $table->string('card_number', 50)->nullable()->unique()->comment('Nomor kartu anggota');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->comment('Link ke user account (optional)');
            $table->timestamps();
            $table->softDeletes()->comment('Soft delete untuk audit trail');
            
            // Indexes untuk performance
            $table->index('member_id');
            $table->index('name');
            $table->index('email');
            $table->index('status');
            $table->index('join_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
