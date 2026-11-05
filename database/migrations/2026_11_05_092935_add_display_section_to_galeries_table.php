<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('galeries', function (Blueprint $table) {
            $table->string('display_section')->default('story');
        });

        $photoIds = DB::table('galeries')->orderByDesc('id')->pluck('id');
        $storyPhotoCount = (int) ceil($photoIds->count() / 2);
        $selectedPhotoIds = $photoIds->slice($storyPhotoCount);

        if ($selectedPhotoIds->isNotEmpty()) {
            DB::table('galeries')
                ->whereIn('id', $selectedPhotoIds)
                ->update(['display_section' => 'selected']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('galeries', function (Blueprint $table) {
            $table->dropColumn('display_section');
        });
    }
};
