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

    public function test_admin_can_create_a_package_with_a_new_filter_category(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->from(route('admin.fotografi'))
            ->post(route('admin.fotografi.store'), $this->packagePayload([
                'category' => 'Pas Foto',
            ]))
            ->assertRedirect(route('admin.fotografi'));

        $this->assertDatabaseHas('photography_packages', [
            'name' => 'Paket Tes Foto',
            'category' => 'Pas Foto',
        ]);

        $this->get(route('admin.fotografi'))
            ->assertOk()
            ->assertSee('Pas Foto')
            ->assertSee('list="package-category-options"', false);
    }

    public function test_admin_can_change_a_package_filter_category(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $package = PhotographyPackage::create($this->packagePayload([
            'category' => 'Prewedding',
        ]));

        $this->from(route('admin.fotografi'))
            ->put(route('admin.fotografi.update', $package), $this->packagePayload([
                'category' => 'Wedding',
            ]))
            ->assertRedirect(route('admin.fotografi'));

        $this->assertDatabaseHas('photography_packages', [
            'id' => $package->id,
            'category' => 'Wedding',
        ]);
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

    public function test_admin_can_update_a_package_duration_in_seconds(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $package = PhotographyPackage::create($this->packagePayload());

        $this->from(route('admin.fotografi'))
            ->put(route('admin.fotografi.update', $package), $this->packagePayload([
                'duration' => '30 detik',
            ]))
            ->assertRedirect(route('admin.fotografi'));

        $this->assertDatabaseHas('photography_packages', [
            'id' => $package->id,
            'duration' => '30 detik',
        ]);
    }

    public function test_admin_can_update_a_package_duration_in_minutes(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $package = PhotographyPackage::create($this->packagePayload());

        $this->from(route('admin.fotografi'))
            ->put(route('admin.fotografi.update', $package), $this->packagePayload([
                'duration' => '10 menit',
            ]))
            ->assertRedirect(route('admin.fotografi'));

        $this->assertDatabaseHas('photography_packages', [
            'id' => $package->id,
            'duration' => '10 menit',
        ]);
    }

    public function test_admin_cannot_save_a_package_with_an_unrecognized_duration(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $package = PhotographyPackage::create($this->packagePayload());

        $this->from(route('admin.fotografi'))
            ->put(route('admin.fotografi.update', $package), $this->packagePayload([
                'duration' => 'seven minutes',
            ]))
            ->assertRedirect(route('admin.fotografi'))
            ->assertSessionHasErrors('duration');

        $this->assertDatabaseHas('photography_packages', [
            'id' => $package->id,
            'duration' => '2 jam',
        ]);
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
            'category' => 'Lainnya',
            'price' => 1000000,
            'duration' => '2 jam',
            'facilities' => 'Studio',
            'description' => 'Paket fotografi studio',
            'bonus' => '',
        ], $overrides);
    }
}
