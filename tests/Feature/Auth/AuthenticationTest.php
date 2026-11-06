<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertOk()
            ->assertSee('Login Admin')
            ->assertSee('name="email"', false)
            ->assertDontSee('Daftar')
            ->assertDontSee('name="register"', false);
    }

    public function test_admins_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.dashboard', absolute: false));
    }

    public function test_admins_cannot_authenticate_with_an_invalid_password(): void
    {
        $user = User::factory()->admin()->create();

        $this->from('/login')
            ->post('/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ])
            ->assertRedirect('/login');

        $this->assertGuest();

        $this->get('/login')
            ->assertOk()
            ->assertSee('Swal.fire', false)
            ->assertSee('Email atau kata sandi yang Anda masukkan tidak sesuai.');
    }

    public function test_login_validation_alerts_are_displayed_in_indonesian(): void
    {
        $this->from('/login')
            ->post('/login', [
                'email' => '',
                'password' => '',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors([
                'email' => 'alamat email wajib diisi.',
                'password' => 'kata sandi wajib diisi.',
            ]);
    }

    public function test_customers_cannot_authenticate_to_the_admin_panel(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_registration_is_not_available(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register')->assertNotFound();
    }

    public function test_admin_routes_redirect_guests_to_login(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_admins_with_an_initial_password_can_open_the_dashboard_without_changing_it(): void
    {
        $user = User::factory()->admin()->create(['must_change_password' => true]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->get(route('admin.dashboard'))->assertOk();
    }

    public function test_super_admin_with_an_initial_password_can_open_the_dashboard_without_changing_it(): void
    {
        $superAdmin = User::factory()->superAdmin()->create(['must_change_password' => true]);

        $this->post('/login', [
            'email' => $superAdmin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->get(route('admin.dashboard'))->assertOk();
    }

    public function test_admin_is_not_asked_to_change_password_again_after_changing_it_and_logging_in_again(): void
    {
        $user = User::factory()->admin()->create([
            'password' => 'InitialAdminPassword!',
            'must_change_password' => true,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'InitialAdminPassword!',
        ])->assertRedirect(route('admin.dashboard'));

        $this->put(route('admin.account.update'), [
            'email' => $user->email,
            'current_password' => 'InitialAdminPassword!',
            'password' => 'UpdatedAdminPassword123!',
            'password_confirmation' => 'UpdatedAdminPassword123!',
        ])->assertRedirect(route('admin.account.edit'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'must_change_password' => false,
        ]);

        $this->post('/logout')->assertRedirect('/');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'UpdatedAdminPassword123!',
        ])->assertRedirect(route('admin.dashboard'));

        $this->get(route('admin.dashboard'))->assertOk();
    }

    public function test_admins_can_logout(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
