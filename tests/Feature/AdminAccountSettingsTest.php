<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAccountSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_change_own_email_and_password_with_current_password(): void
    {
        $admin = User::factory()->admin()->create([
            'password' => 'CurrentAdminPassword!',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.account.update'), [
                'email' => 'updated-admin@example.test',
                'current_password' => 'CurrentAdminPassword!',
                'password' => 'NewAdminPassword123!',
                'password_confirmation' => 'NewAdminPassword123!',
            ])
            ->assertRedirect(route('admin.account.edit'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'email' => 'updated-admin@example.test',
            'must_change_password' => false,
        ]);
        $this->assertTrue(Hash::check('NewAdminPassword123!', $admin->fresh()->password));
    }

    public function test_admin_account_update_rejects_an_incorrect_current_password(): void
    {
        $admin = User::factory()->admin()->create([
            'password' => 'CurrentAdminPassword!',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.account.edit'))
            ->put(route('admin.account.update'), [
                'email' => 'updated-admin@example.test',
                'current_password' => 'IncorrectPassword!',
                'password' => 'NewAdminPassword123!',
                'password_confirmation' => 'NewAdminPassword123!',
            ])
            ->assertRedirect(route('admin.account.edit'))
            ->assertSessionHasErrors('current_password');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'email' => $admin->email,
        ]);
    }

    public function test_admin_with_an_initial_password_can_update_email_without_changing_password(): void
    {
        $admin = User::factory()->admin()->create([
            'password' => 'CurrentAdminPassword!',
            'must_change_password' => true,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.account.update'), [
                'email' => 'updated-admin@example.test',
                'current_password' => 'CurrentAdminPassword!',
            ])
            ->assertRedirect(route('admin.account.edit'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'email' => 'updated-admin@example.test',
            'must_change_password' => false,
        ]);
        $this->assertTrue(Hash::check('CurrentAdminPassword!', $admin->fresh()->password));
    }

    public function test_admin_cannot_reset_another_admin_account(): void
    {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put(route('admin.accounts.update', $otherAdmin), [
                'email' => 'changed@example.test',
                'password' => 'ReplacementPassword123!',
                'password_confirmation' => 'ReplacementPassword123!',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('users', [
            'id' => $otherAdmin->id,
            'email' => $otherAdmin->email,
        ]);
    }

    public function test_super_admin_can_reset_an_admin_account_without_forcing_password_rotation(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($superAdmin)
            ->put(route('admin.accounts.update', $admin), [
                'email' => 'reset-admin@example.test',
                'password' => 'ReplacementPassword123!',
                'password_confirmation' => 'ReplacementPassword123!',
            ])
            ->assertRedirect(route('admin.account.edit'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'email' => 'reset-admin@example.test',
            'role' => User::ROLE_ADMIN,
            'must_change_password' => false,
        ]);
        $this->assertTrue(Hash::check('ReplacementPassword123!', $admin->fresh()->password));
    }

    public function test_admin_account_settings_only_lists_admin_accounts_for_super_admin(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();

        $this->actingAs($superAdmin)
            ->get(route('admin.account.edit'))
            ->assertOk()
            ->assertSee($admin->email)
            ->assertDontSee($customer->email);

        $this->actingAs($admin)
            ->get(route('admin.account.edit'))
            ->assertOk()
            ->assertDontSee('Kelola akun admin');
    }

    public function test_admin_sidebar_shows_account_and_logout_in_the_profile_menu(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Studio Admin',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('data-admin-profile-toggle', false)
            ->assertSee('Studio Admin')
            ->assertSee('Akun & kata sandi')
            ->assertSee(route('admin.account.edit'), false)
            ->assertSee('action="'.route('logout').'"', false)
            ->assertSee('Keluar')
            ->assertDontSee('<span>Akun Admin</span>', false)
            ->assertDontSee('<span>Logout</span>', false);
    }

    public function test_admin_flash_messages_are_displayed_with_sweetalert2(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->withSession(['success' => 'Pengaturan akun berhasil disimpan.'])
            ->get(route('admin.account.edit'))
            ->assertOk()
            ->assertSee('Swal.fire', false)
            ->assertSee('Pengaturan akun berhasil disimpan.')
            ->assertDontSee('dashboard-feedback', false);
    }
}
