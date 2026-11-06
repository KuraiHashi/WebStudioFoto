<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAccountSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_forced_password_rotation_admin_accounts_and_does_not_reset_them_on_reseed(): void
    {
        config([
            'admin.accounts.super_admin.password' => 'InitialSuperAdminPassword!',
            'admin.accounts.admin.password' => 'InitialStudioAdminPassword!',
        ]);

        $this->seed();

        $superAdmin = User::query()->where('email', 'superadmin@gmail.com')->firstOrFail();
        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();

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

        $this->seed();

        $admin->refresh();
        $this->assertTrue(Hash::check('UpdatedStudioAdminPassword!', $admin->password));
        $this->assertFalse($admin->must_change_password);
    }
}
