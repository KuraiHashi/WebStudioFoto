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
        Schema::create('photography_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 12, 2);
            $table->string('duration');
            $table->string('bonus')->nullable();
            $table->text('description')->nullable();
            $table->text('facilities'); // Disimpan sebagai string (misal dipisah koma atau JSON)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photography_packages');
    }
};
