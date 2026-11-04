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
        Schema::table('reservations', function (Blueprint $table): void {
            $table->timestamp('session_completed_at')->nullable();
            $table->string('photo_status')->default('belum_diproses');
            $table->text('photo_delivery_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table): void {
            $table->dropColumn(['session_completed_at', 'photo_status', 'photo_delivery_url']);
        });
    }
};
