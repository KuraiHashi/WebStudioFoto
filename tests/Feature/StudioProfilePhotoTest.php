<?php

namespace Tests\Feature;

use App\Models\StudioProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudioProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_admin_can_upload_a_studio_photo_that_appears_in_the_navbar_and_footer(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('studio/previous.jpg', 'old photo');
        StudioProfile::create([
            ...$this->validProfileData(),
            'foto_studio' => 'studio/previous.jpg',
        ]);

        $this->post(route('admin.profile.update'), [
            ...$this->validProfileData(),
            'foto_studio' => UploadedFile::fake()->image('studio.jpg'),
        ])->assertRedirect()
            ->assertSessionHas('success');

        $profile = StudioProfile::firstOrFail();
        Storage::disk('public')->assertExists($profile->foto_studio);
        Storage::disk('public')->assertMissing('studio/previous.jpg');
        $this->assertDatabaseHas('studio_profiles', [
            'id' => $profile->id,
            'foto_studio' => $profile->foto_studio,
        ]);

        $photoUrl = asset('storage/'.$profile->foto_studio);
        $adminResponse = $this->get(route('admin.profile'));
        $adminResponse
            ->assertOk()
            ->assertSee('name="foto_studio"', false)
            ->assertSee($photoUrl, false);
        $this->assertSame(3, substr_count($adminResponse->getContent(), $photoUrl));

        $response = $this->get('/');
        $response->assertOk()
            ->assertSee($photoUrl, false);
        $this->assertSame(2, substr_count($response->getContent(), $photoUrl));
    }

    public function test_admin_cannot_upload_a_non_image_as_the_studio_photo(): void
    {
        Storage::fake('public');

        $this->from(route('admin.profile'))
            ->post(route('admin.profile.update'), [
                ...$this->validProfileData(),
                'foto_studio' => UploadedFile::fake()->create('studio.pdf', 10, 'application/pdf'),
            ])
            ->assertRedirect(route('admin.profile'))
            ->assertSessionHasErrors('foto_studio');

        $this->assertDatabaseMissing('studio_profiles', ['id' => 1]);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_admin_sidebar_shows_a_placeholder_when_no_studio_photo_has_been_uploaded(): void
    {
        $this->get(route('admin.profile'))
            ->assertOk()
            ->assertSee('Logo belum tersedia')
            ->assertSee('class="admin-logo-placeholder"', false);
    }

    public function test_updating_profile_without_a_new_photo_keeps_the_existing_photo(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('studio/previous.jpg', 'existing photo');

        StudioProfile::create([
            ...$this->validProfileData(),
            'foto_studio' => 'studio/previous.jpg',
        ]);

        $this->post(route('admin.profile.update'), [
            ...$this->validProfileData(),
            'name' => 'Studio Terbaru',
        ])->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('studio_profiles', [
            'id' => 1,
            'name' => 'Studio Terbaru',
            'foto_studio' => 'studio/previous.jpg',
        ]);
        Storage::disk('public')->assertExists('studio/previous.jpg');
    }

    private function validProfileData(): array
    {
        return [
            'name' => 'Liquif Picture',
            'address' => 'Jalan Studio, Jakarta',
            'phone' => '081234567890',
            'operational_hours' => '09.00–18.00',
            'description' => 'Studio fotografi untuk momen istimewa.',
        ];
    }
}
