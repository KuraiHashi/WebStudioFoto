<?php

namespace Tests\Feature;

use App\Models\Galery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GaleryDisplaySectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders_gallery_photos_only_in_the_assigned_sections(): void
    {
        Galery::create([
            'filename' => 'galeri/kisah.jpg',
            'display_section' => 'story',
        ]);
        Galery::create([
            'filename' => 'galeri/terpilih.jpg',
            'display_section' => 'selected',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder([
                'id="kisah-terbingkai"',
                'href="'.asset('storage/galeri/kisah.jpg').'"',
                'id="profil"',
                'id="galeri"',
                'href="'.asset('storage/galeri/terpilih.jpg').'"',
                'id="paket"',
            ], false);
    }

    public function test_admin_can_assign_gallery_photos_to_different_sections(): void
    {
        $storyPhoto = Galery::create([
            'filename' => 'galeri/kisah.jpg',
            'display_section' => 'story',
        ]);
        $selectedPhoto = Galery::create([
            'filename' => 'galeri/terpilih.jpg',
            'display_section' => 'story',
        ]);

        $this->put(route('admin.galery.display-sections'), [
            'display_sections' => [
                $storyPhoto->id => 'story',
                $selectedPhoto->id => 'selected',
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('galeries', [
            'id' => $storyPhoto->id,
            'display_section' => 'story',
        ]);
        $this->assertDatabaseHas('galeries', [
            'id' => $selectedPhoto->id,
            'display_section' => 'selected',
        ]);
    }

    public function test_admin_gallery_shows_the_display_section_for_each_photo(): void
    {
        $photo = Galery::create([
            'filename' => 'galeri/terpilih.jpg',
            'display_section' => 'selected',
        ]);

        $this->get(route('admin.galery'))
            ->assertOk()
            ->assertSee('Atur penempatan foto')
            ->assertSee('name="display_sections['.$photo->id.']"', false)
            ->assertSee('value="selected" selected', false);
    }

    public function test_admin_uploads_photos_to_the_selected_section(): void
    {
        Storage::fake('public');

        $this->post(route('admin.galery.store'), [
            'display_section' => 'selected',
            'photos' => [UploadedFile::fake()->create('potret.jpg', 10, 'image/jpeg')],
        ])->assertRedirect()
            ->assertSessionHas('success');

        $photo = Galery::firstOrFail();

        $this->assertDatabaseHas('galeries', [
            'id' => $photo->id,
            'display_section' => 'selected',
        ]);
        Storage::disk('public')->assertExists($photo->filename);
    }

    public function test_admin_cannot_assign_a_photo_to_an_unknown_section(): void
    {
        $photo = Galery::create([
            'filename' => 'galeri/kisah.jpg',
            'display_section' => 'story',
        ]);

        $this->from(route('admin.galery'))
            ->put(route('admin.galery.display-sections'), [
                'display_sections' => [$photo->id => 'unknown'],
            ])
            ->assertRedirect(route('admin.galery'))
            ->assertSessionHasErrors("display_sections.{$photo->id}");

        $this->assertDatabaseHas('galeries', [
            'id' => $photo->id,
            'display_section' => 'story',
        ]);
    }
}
