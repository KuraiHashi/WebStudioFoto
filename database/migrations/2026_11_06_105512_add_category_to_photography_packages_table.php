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
        Schema::table('photography_packages', function (Blueprint $table) {
            $table->string('category')->default('Lainnya');
        });

        DB::table('photography_packages')
            ->select(['id', 'name', 'is_wedding'])
            ->orderBy('id')
            ->chunk(100, function ($packages): void {
                foreach ($packages as $package) {
                    $name = mb_strtolower($package->name);

                    $category = match (true) {
                        str_contains($name, 'prewedding'),
                        str_contains($name, 'pre-wedding'),
                        str_contains($name, 'pre wedding'),
                        str_contains($name, 'engagement'),
                        str_contains($name, 'tunangan') => 'Prewedding',
                        str_contains($name, 'wedding'),
                        str_contains($name, 'pernikahan'),
                        str_contains($name, 'nikah'),
                        (bool) $package->is_wedding => 'Wedding',
                        str_contains($name, 'wisuda'),
                        str_contains($name, 'graduation'),
                        str_contains($name, 'kelulusan') => 'Wisuda',
                        str_contains($name, 'keluarga'),
                        str_contains($name, 'family') => 'Keluarga',
                        str_contains($name, 'couple'),
                        str_contains($name, 'pasangan') => 'Pasangan',
                        str_contains($name, 'maternity'),
                        str_contains($name, 'pregnancy'),
                        str_contains($name, 'bumil'),
                        str_contains($name, 'kehamilan') => 'Maternity',
                        str_contains($name, 'newborn'),
                        str_contains($name, 'bayi'),
                        str_contains($name, 'baby') => 'Newborn & Bayi',
                        str_contains($name, 'produk'),
                        str_contains($name, 'product'),
                        str_contains($name, 'katalog'),
                        str_contains($name, 'komersial') => 'Produk & Komersial',
                        str_contains($name, 'portrait'),
                        str_contains($name, 'potret'),
                        str_contains($name, 'personal'),
                        str_contains($name, 'individu') => 'Potret Personal',
                        default => 'Lainnya',
                    };

                    DB::table('photography_packages')
                        ->where('id', $package->id)
                        ->update(['category' => $category]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('photography_packages', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
