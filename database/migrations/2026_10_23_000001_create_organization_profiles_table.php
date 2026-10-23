<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Profil UKM yang tampil di website: hanya satu baris, diubah oleh Admin UKM. */
    public function up(): void
    {
        Schema::create('organization_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('tagline')->nullable();
            $table->text('about')->nullable();
            $table->longText('history')->nullable();
            $table->text('vision')->nullable();
            $table->json('missions')->nullable();
            $table->json('core_members')->nullable()->comment('[{position, name, photo}]');
            $table->json('divisions')->nullable()->comment('[{name, coordinator, description}]');
            $table->string('address')->nullable();
            $table->string('email')->nullable();
            $table->string('instagram')->nullable();
            $table->string('tiktok')->nullable();
            $table->string('phone')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_profiles');
    }
};
