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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();

            $table->string('token')->unique();

            $table->foreignId('photography_package_id')->constrained('photography_packages')->onDelete('cascade');
            $table->string('name');
            $table->string('whatsapp');
            $table->date('date');
            $table->time('time');
            $table->string('duration');
            $table->string('location');
            
            // Kolom Tambahan untuk Konsultasi
            $table->boolean('request_consultation')->default(false);
            $table->string('consultation_topic')->nullable();
            $table->string('consultation_custom_topic')->nullable();
            $table->string('consultation_media')->nullable();
            $table->string('consultation_offline_location')->nullable();
            $table->text('consultation_notes')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
