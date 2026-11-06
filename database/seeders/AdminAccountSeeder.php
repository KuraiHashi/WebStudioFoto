<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminAccountSeeder extends Seeder
{
    /**
     * Create the configured studio administrator accounts.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate(); // Hati-hati: ini akan menghapus semua data user jika tabel di-truncate. Jika hanya ingin update/create, bisa menggunakan updateOrCreate.
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. SUPER ADMIN
        User::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@webstudiofoto.test',
            'role' => User::ROLE_SUPER_ADMIN,
            'password' => Hash::make('PasswordAman123!'), // Password langsung ter-encrypt
            'must_change_password' => false,
        ]);

        // 2. ADMIN STUDIO / OPERATOR (Contoh tambahan jika ada)
        User::create([
            'name' => 'Studio Admin',
            'email' => 'admin@webstudiofoto.test',
            'role' => 'admin',
            'password' => Hash::make('AdminStudio2026!'), // Password langsung ter-encrypt
            'must_change_password' => false,
        ]);
    }
}
