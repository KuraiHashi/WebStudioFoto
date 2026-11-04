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
        Schema::create('payment_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->onDelete('cascade');
            
            // Cukup gunakan nullable(), karena posisinya sudah pasti berada di bawah payment_id
            $table->unsignedBigInteger('rekening_id')->nullable();
            $table->foreign('rekening_id')
                  ->references('id')
                  ->on('rekening')
                  ->nullOnDelete();
            
            $table->decimal('amount', 12, 2);              // Nominal yang dibayar pada sesi ini
            $table->string('payment_method');              // Metode: 'QRIS', 'Transfer BCA', dll.
            $table->string('receipt_photo');               // Path foto bukti transfer / kwitansi
            $table->text('notes')->nullable();             // Catatan pengirim
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_histories');
    }
};
