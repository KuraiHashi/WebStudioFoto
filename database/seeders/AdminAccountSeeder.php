<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class AdminAccountSeeder extends Seeder
{
    /**
     * Create the configured studio administrator accounts.
     */
    public function run(): void
    {
        $accounts = config('admin.accounts');

        foreach ($accounts as $account) {
            if (blank($account['password'])) {
                throw new RuntimeException(
                    'Atur '.$account['password_env'].' pada environment sebelum menjalankan seeder akun admin.'
                );
            }
        }

        foreach ($accounts as $account) {
            $user = User::query()->where('email', $account['email'])->first();
            $credentialsChanged = ! $user || $user->role !== $account['role'];
            $user ??= new User;
            $user->name = $account['name'];
            $user->email = $account['email'];
            $user->role = $account['role'];

            if ($credentialsChanged) {
                $user->password = $account['password'];
                $user->must_change_password = true;
            }

            $user->save();
        }
    }
}