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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->onDelete('cascade')->comment('Buku yang dipinjam');
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade')->comment('Anggota yang meminjam');
            $table->date('loan_date')->comment('Tanggal peminjaman');
            $table->date('due_date')->comment('Tanggal jatuh tempo');
            $table->date('return_date')->nullable()->comment('Tanggal pengembalian (null jika belum dikembalikan)');
            $table->decimal('fine_amount', 8, 2)->default(0.00)->comment('Jumlah denda dalam rupiah');
            $table->enum('status', ['active', 'returned', 'overdue'])->default('active')->comment('Status peminjaman');
            $table->text('notes')->nullable()->comment('Catatan tambahan');
            $table->foreignId('processed_by')->constrained('users')->onDelete('cascade')->comment('Staff/Admin yang memproses transaksi');
            $table->timestamps();
            $table->softDeletes()->comment('Soft delete untuk audit trail');
            
            // Indexes untuk performance
            $table->index('book_id');
            $table->index('member_id');
            $table->index('loan_date');
            $table->index('due_date');
            $table->index('status');
            $table->index('processed_by');
            $table->index(['status', 'due_date']); // Composite index untuk mencari overdue loans
            $table->index(['member_id', 'status']); // Composite index untuk loan history per member
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
