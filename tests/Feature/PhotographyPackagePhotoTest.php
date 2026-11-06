<?php

namespace Tests\Feature;

use App\Models\PhotographyPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotographyPackagePhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_package_detail_shows_a_placeholder_when_no_photos_have_been_uploaded(): void
    {
        $package = PhotographyPackage::create($this->packagePayload());

        $this->get(route('paket.detail', $package))
            ->assertOk()
            ->assertSee('Foto paket belum tersedia')
            ->assertDontSee('id="mainPhotoImage"', false);
    }

    public function test_admin_uploaded_package_photos_are_saved_and_displayed_on_the_detail_page(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->admin()->create());

        $this->from(route('admin.fotografi'))
            ->post(route('admin.fotografi.store'), $this->packagePayload([
                'photos' => [
                    UploadedFile::fake()->image('wedding.jpg'),
                    UploadedFile::fake()->image('portrait.png'),
                ],
            ]))
            ->assertRedirect(route('admin.fotografi'));

        $package = PhotographyPackage::sole();
        $this->assertCount(2, $package->photos);

        foreach ($package->photos as $photo) {
            Storage::disk('public')->assertExists($photo);
        }

        $this->get(route('paket.detail', $package))
            ->assertOk()
            ->assertSee('id="mainPhotoImage"', false)
            ->assertSee(asset('storage/'.$package->photos[0]), false)
            ->assertSee('data-image="'.asset('storage/'.$package->photos[1]).'"', false);
    }

    public function test_admin_can_append_photos_to_a_package_without_removing_existing_photos(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->admin()->create());
        $existingPhoto = 'packages/existing.jpg';
        Storage::disk('public')->put($existingPhoto, 'existing photo');
        $package = PhotographyPackage::create($this->packagePayload([
            'photos' => [$existingPhoto],
        ]));

        $this->from(route('admin.fotografi'))
            ->put(route('admin.fotografi.update', $package), $this->packagePayload([
                'photos' => [UploadedFile::fake()->image('new-photo.jpg')],
            ]))
            ->assertRedirect(route('admin.fotografi'));

        $package->refresh();
        $this->assertCount(2, $package->photos);
        $this->assertSame($existingPhoto, $package->photos[0]);
        Storage::disk('public')->assertExists($package->photos[1]);
    }

    public function test_admin_cannot_upload_a_non_image_as_a_package_photo(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->admin()->create());

        $this->from(route('admin.fotografi'))
            ->post(route('admin.fotografi.store'), $this->packagePayload([
                'photos' => [UploadedFile::fake()->create('not-an-image.txt', 10, 'text/plain')],
            ]))
            ->assertRedirect(route('admin.fotografi'))
            ->assertSessionHasErrors('photos.0');

        $this->assertDatabaseCount('photography_packages', 0);
    }

    private function packagePayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Paket Tes Foto',
            'price' => 1000000,
            'duration' => '2 jam',
            'facilities' => 'Studio',
            'description' => 'Paket fotografi studio',
            'bonus' => '',
        ], $overrides);
    }
}
