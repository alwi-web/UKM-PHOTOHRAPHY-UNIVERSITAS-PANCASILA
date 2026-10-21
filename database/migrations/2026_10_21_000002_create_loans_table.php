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
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->text('purpose')->nullable();
            $table->string('status', 20)->default('diajukan')->index();
            $table->dateTime('borrowed_at')->nullable()->comment('Mulai dipinjam');
            $table->dateTime('due_at')->comment('Batas pengembalian');
            $table->dateTime('returned_at')->nullable();
            $table->string('condition_before', 20)->nullable();
            $table->string('condition_after', 20)->nullable();
            $table->text('condition_notes')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
