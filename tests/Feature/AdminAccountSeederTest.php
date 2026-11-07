<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class AdminAccountSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_configured_admin_accounts_and_preserves_existing_passwords(): void
    {
        config([
            'admin.accounts.super_admin.email' => 'superadmin@example.test',
            'admin.accounts.super_admin.password' => 'InitialSuperAdminPassword!',
            'admin.accounts.admin.email' => 'admin@example.test',
            'admin.accounts.admin.password' => 'InitialStudioAdminPassword!',
        ]);

        $customer = User::factory()->create();

        $this->seed(AdminAccountSeeder::class);

        $superAdmin = User::query()->where('email', 'superadmin@example.test')->firstOrFail();
        $admin = User::query()->where('email', 'admin@example.test')->firstOrFail();

        $this->assertSame('super admin', $superAdmin->name);
        $this->assertSame(User::ROLE_SUPER_ADMIN, $superAdmin->role);
        $this->assertTrue($superAdmin->must_change_password);
        $this->assertTrue(Hash::check('InitialSuperAdminPassword!', $superAdmin->password));
        $this->assertSame(User::ROLE_ADMIN, $admin->role);
        $this->assertTrue($admin->must_change_password);
        $this->assertTrue(Hash::check('InitialStudioAdminPassword!', $admin->password));

        $admin->password = 'UpdatedStudioAdminPassword!';
        $admin->must_change_password = false;
        $admin->save();

        $this->seed(AdminAccountSeeder::class);

        $admin->refresh();
        $this->assertTrue(Hash::check('UpdatedStudioAdminPassword!', $admin->password));
        $this->assertFalse($admin->must_change_password);
        $this->assertDatabaseHas('users', ['id' => $customer->id]);
    }

    public function test_seeder_requires_environment_passwords_without_deleting_existing_users(): void
    {
        $customer = User::factory()->create();
        config([
            'admin.accounts.super_admin.password' => null,
            'admin.accounts.admin.password' => null,
        ]);

        try {
            $this->seed(AdminAccountSeeder::class);
            $this->fail('Expected missing admin credentials to prevent seeding.');
        } catch (RuntimeException $exception) {
            $this->assertSame(
                'Atur SUPER_ADMIN_PASSWORD pada environment sebelum menjalankan seeder akun admin.',
                $exception->getMessage()
            );
        }

        $this->assertDatabaseHas('users', ['id' => $customer->id]);
    }
}
