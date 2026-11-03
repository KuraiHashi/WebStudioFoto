<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PhotographyPackage;
use Illuminate\Support\Facades\DB;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        PhotographyPackage::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. PRE-WEDDING
        PhotographyPackage::create([
            'name' => 'Pre-Wedding (Basic)',
            'price' => 900000,
            'duration' => '3 Jam Sesi',
            'bonus' => 'Foto File Only',
            'description' => 'Spot foto Indoor Caffe atau Outdoor',
            'facilities' => 'Edit tone 70 foto, File via Google Drive',
            'photos' => null,
        ]);

        PhotographyPackage::create([
            'name' => 'Pre-Wedding (Classic)',
            'price' => 1400000,
            'duration' => '4 Jam Sesi',
            'bonus' => '2 Kostum Baju',
            'description' => 'Spot Foto Indoor Caffe, Home Studio, atau Outdoor',
            'facilities' => '2 Cetak Ukuran 18R + Bingkai, File Unlimited via Google Drive, Edit Tone 100 Foto',
            'photos' => null,
        ]);

        PhotographyPackage::create([
            'name' => 'Pre-Wedding (Deluxe)',
            'price' => 1850000,
            'duration' => '4 Jam Sesi',
            'bonus' => 'Flash Disk Simpan File',
            'description' => 'Spot Foto Indoor Caffe, Home Studio, atau Outdoor',
            'facilities' => '2 Cetak Ukuran 20R + Bingkai, Edit Tone 150 Foto, File Unlimited in Flash Disk, 2 Kostum Baju',
            'photos' => null,
        ]);

        PhotographyPackage::create([
            'name' => 'Pre-Wedding (Plus A) Foto & Video Cinematic',
            'price' => 3400000,
            'duration' => '4-5 Jam Sesi',
            'bonus' => 'Free Drone & Flashdisk',
            'description' => 'Paket Komplit Foto & Video Cinematic',
            'facilities' => '2 Lembar Cetak Ukuran 20Rs (Canvas) + Bingkai, Video Cinematic Prewedd Durasi 1-3 Menit, 3 Costume Baju',
            'photos' => null,
        ]);

        PhotographyPackage::create([
            'name' => 'Pre-Wedding (Plus B) Foto & Video Cinematic',
            'price' => 2700000,
            'duration' => '4 Jam Sesi',
            'bonus' => 'Video Cinematic 1-2 Menit',
            'description' => 'Paket Komplit Foto & Video Cinematic',
            'facilities' => '2 Lembar Cetak Ukuran 16R (Canvas) + Bingkai, File via Google Drive, 2 Costume Baju',
            'photos' => null,
        ]);

        // 2. WEDDING FOTO EKONOMIS & ELEGAN
        PhotographyPackage::create([
            'name' => 'Wedding (Promo A) Foto Ekonomis',
            'price' => 1700000,
            'duration' => 'Full Acara',
            'bonus' => 'Album Foto 4R',
            'description' => 'Paket Liputan Acara Pernikahan Ekonomis',
            'facilities' => '100 lembar Cetak Ukuran 4R + Album, 1 lembar Cetak 18R + Bingkai, File via Google Drive',
            'photos' => null,
        ]);

        PhotographyPackage::create([
            'name' => 'Wedding (Promo B) Foto Ekonomis',
            'price' => 1500000,
            'duration' => 'Full Acara',
            'bonus' => 'Album Foto 4R',
            'description' => 'Paket Liputan Acara Pernikahan Ekonomis',
            'facilities' => '100 Cetak Ukuran 4R + Album, 1 Lembar Cetak 16R + Bingkai, File via Google Drive',
            'photos' => null,
        ]);

        PhotographyPackage::create([
            'name' => 'Wedding (Elegan A)',
            'price' => 3000000,
            'duration' => 'Full Acara',
            'bonus' => 'Flashdisk Simpan File',
            'description' => 'Paket Liputan Acara Pernikahan Elegan',
            'facilities' => '2 Lembar Cetak Ukuran 20Rs (Canvas) + Bingkai, 120 Lembar Cetak Ukuran 4R + Album, 4 lembar Cetak Ukuran 10R',
            'photos' => null,
        ]);

        PhotographyPackage::create([
            'name' => 'Wedding (Elegan B)',
            'price' => 2500000,
            'duration' => 'Full Acara',
            'bonus' => 'Flashdisk Simpan File',
            'description' => 'Paket Liputan Acara Pernikahan Elegan',
            'facilities' => '2 Lembar Cetak Ukuran 18R + Bingkai, 100 Lembar Cetak Ukuran 4R + Album',
            'photos' => null,
        ]);

        // 3. WEDDING PHOTO + VIDEO CINEMATIK
        PhotographyPackage::create([
            'name' => 'Wedding Photo & Video Cinematik (Promo A)',
            'price' => 4500000,
            'duration' => 'Full Acara',
            'bonus' => 'DVD-Room & Cover Album Video',
            'description' => 'Dokumentasi Foto & Video Cinematic Wedding',
            'facilities' => '120 lembar Cetak 4R + Album, 2 lembar Cetak 20R + Bingkai (Canvas), 2 Lembar Cetak 10R + Bingkai, Video Cinematic Max 10 Menit',
            'photos' => null,
        ]);

        PhotographyPackage::create([
            'name' => 'Wedding Photo & Video Cinematik (Promo B)',
            'price' => 3700000,
            'duration' => 'Full Acara',
            'bonus' => 'DVD-Room & Cover Album Video',
            'description' => 'Dokumentasi Foto & Video Cinematic Wedding',
            'facilities' => '120 lembar Cetak 4R + Album, 2 lembar Cetak 18R + Bingkai (Canvas), 4 Lembar Cetak 10R, Video Cinematic Max 10 Menit',
            'photos' => null,
        ]);

        // 4. WISUDA OUTDOOR
        PhotographyPackage::create([
            'name' => 'Wisuda Outdoor (Basic)',
            'price' => 250000,
            'duration' => '30 Menit Sesi',
            'bonus' => 'File Unlimited',
            'description' => 'Sesi Foto Wisuda Outdoor',
            'facilities' => '25 edited photos Full HD, File via Google Drive',
            'photos' => null,
        ]);

        PhotographyPackage::create([
            'name' => 'Wisuda Outdoor (Classic)',
            'price' => 350000,
            'duration' => '1 Jam Sesi',
            'bonus' => 'File Unlimited',
            'description' => 'Sesi Foto Wisuda Outdoor',
            'facilities' => '50 edited photos Full HD, File via Google Drive',
            'photos' => null,
        ]);

        PhotographyPackage::create([
            'name' => 'Wisuda Outdoor (Deluxe)',
            'price' => 600000,
            'duration' => '1 Jam Sesi',
            'bonus' => '1 Cetak 16R Canvas + Bingkai',
            'description' => 'Sesi Foto Wisuda Outdoor Cetak Canvas',
            'facilities' => '70 edited photos Full HD, File Unlimited via Google Drive',
            'photos' => null,
        ]);

        PhotographyPackage::create([
            'name' => 'Wisuda Outdoor (Gold Foto & Video)',
            'price' => 1100000,
            'duration' => '1 Jam 30 Menit Sesi',
            'bonus' => 'Video Cinematic Reels IG 1 Min',
            'description' => 'Foto & Video Wisuda Outdoor',
            'facilities' => '60 edited photos Full HD, File Unlimited via Google Drive',
            'photos' => null,
        ]);

        // 5. STUDIO FOTO (Wisuda, Keluarga, Baby, Group, Dll)
        PhotographyPackage::create([
            'name' => 'Studio Foto Paket I (Maks 10-12 Orang)',
            'price' => 650000,
            'duration' => '20 Menit Sesi',
            'bonus' => 'Unlimited File Foto',
            'description' => 'Foto Indoor Studio untuk Wisuda/Keluarga/Group',
            'facilities' => '1 Lembar Cetak Ukuran 16R (Canvas) + Bingkai',
            'photos' => null,
        ]);

        PhotographyPackage::create([
            'name' => 'Studio Foto Paket II (Maks 15-16 Orang)',
            'price' => 950000,
            'duration' => '30 Menit Sesi',
            'bonus' => 'Unlimited File Foto',
            'description' => 'Foto Indoor Studio untuk Wisuda/Keluarga/Group',
            'facilities' => '1 Lembar Cetak Ukuran 18R (Canvas) + Bingkai, 1 lembar Cetak Ukuran 10R (Canvas) + Bingkai',
            'photos' => null,
        ]);

        PhotographyPackage::create([
            'name' => 'Studio Foto Paket III (Maks 20-25 Orang)',
            'price' => 1400000,
            'duration' => '60 Menit Sesi',
            'bonus' => 'Unlimited File Foto',
            'description' => 'Foto Indoor Studio untuk Wisuda/Keluarga/Group',
            'facilities' => '1 Lembar Cetak Ukuran 20R (Canvas) + Bingkai, 2 lembar Cetak Ukuran 10R (Canvas) + Bingkai',
            'photos' => null,
        ]);

        PhotographyPackage::create([
            'name' => 'Studio Foto Paket IV (Maks 25-30 Orang)',
            'price' => 1850000,
            'duration' => '60 Menit Sesi',
            'bonus' => 'Unlimited File Foto',
            'description' => 'Foto Indoor Studio untuk Wisuda/Keluarga/Group',
            'facilities' => '1 Lembar Cetak Ukuran 24R (Canvas) + Bingkai, 1 lembar Cetak Ukuran 16R (Canvas) + Bingkai',
            'photos' => null,
        ]);
    }
}