<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artworks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('caption')->nullable();
            // segitiga exposure
            $table->string('aperture', 20)->nullable()->comment('contoh: f/1.8');
            $table->string('shutter_speed', 20)->nullable()->comment('contoh: 1/250');
            $table->unsignedInteger('iso')->nullable();
            $table->text('exposure_notes')->nullable()->comment('Penjelasan penggunaan segitiga exposure');
            $table->string('image');
            $table->string('instagram_url')->nullable();
            $table->string('tiktok_url')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->boolean('is_featured')->default(false)->comment('Ditampilkan di website');
            $table->text('curator_note')->nullable();
            $table->foreignId('curated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('curated_at')->nullable();
            $table->unsignedInteger('views')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artworks');
    }
};
