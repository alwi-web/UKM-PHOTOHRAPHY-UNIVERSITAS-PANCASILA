<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // data peminjam disalin saat pengajuan agar riwayat tetap utuh
            $table->string('borrower_name');
            $table->string('borrower_npm', 20);
            $table->string('borrower_unit')->comment('Fakultas / asal UKM');
            $table->text('purpose')->nullable();
            $table->string('status', 20)->default('diajukan')->index();
            $table->dateTime('borrowed_at')->comment('Mulai dipinjam');
            $table->dateTime('due_at')->comment('Batas pengembalian');
            $table->dateTime('returned_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('loan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->string('condition_before', 20)->nullable();
            $table->string('condition_after', 20)->nullable();
            $table->timestamps();
            $table->unique(['loan_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_items');
        Schema::dropIfExists('loans');
    }
};
