<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('studio_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('address');
            $table->string('phone');
            $table->string('operational_hours');
            $table->string('social_media')->nullable();
            $table->text('maps_link')->nullable();
            $table->text('description');
            $table->timestamps();
        });

        Schema::table('studio_profiles', function (Blueprint $table) {
            $table->string('nama_bank')->nullable();
            $table->string('no_rekening')->nullable();
            $table->string('nama_pemilik')->nullable();
            $table->string('foto_qris')->nullable();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('studio_profiles');
    }
};
