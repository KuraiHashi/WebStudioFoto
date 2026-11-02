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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained('reservations')->onDelete('cascade');
            $table->string('token')->unique(); 
            
            // Status: KONSULTASI, DP, atau LUNAS
            $table->enum('status', ['KONSULTASI', 'DP', 'LUNAS'])->default('KONSULTASI'); 
            
            $table->decimal('tagihan', 12, 2)->default(0);    // Total tagihan akhir
            $table->decimal('total_paid', 12, 2)->default(0); // Akumulasi uang yang sudah dibayar
            
            $table->text('description')->nullable();       // Breakdown / rincian penyesuaian harga
            $table->text('notes')->nullable();             // Catatan umum
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
