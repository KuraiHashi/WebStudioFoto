<?php

namespace Tests\Feature;

use App\Models\Galery;
use App\Models\PhotographyPackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_shows_auto_moving_gallery_and_working_calls_to_action(): void
    {
        Galery::create([
            'filename' => 'galeri/sesi-pilihan.jpg',
            'display_section' => 'story',
        ]);
        Galery::create([
            'filename' => 'galeri/kisah-kedua.jpg',
            'display_section' => 'story',
        ]);
        Galery::create([
            'filename' => 'galeri/potret-kedua.jpg',
            'display_section' => 'selected',
        ]);

        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Mengenal Liquif Picture')
            ->assertSee('class="hero-background"', false)
            ->assertSee('Cerita Anda,')
            ->assertSee('STORIES, HELD GENTLY')
            ->assertSee('story-photo-secondary', false)
            ->assertSee('Lihat cerita yang pernah kami bingkai')
            ->assertSee('storage/galeri/sesi-pilihan.jpg', false)
            ->assertSee('storage/galeri/potret-kedua.jpg', false)
            ->assertSee('data-gallery-carousel', false)
            ->assertSee('data-direction="reverse"', false)
            ->assertSee('gallery-play-control', false)
            ->assertSee('Karya', false)
            ->assertSee('data-gallery="gallery-selected"', false)
            ->assertSee('href="/reservasi"', false)
            ->assertSee('href="#galeri"', false)
            ->assertSeeInOrder([
                'class="hero hero-editorial"',
                'id="kisah-terbingkai"',
                'id="profil"',
                'id="galeri"',
                'id="paket"',
            ], false);
    }

    public function test_homepage_shows_a_styled_empty_state_when_the_gallery_has_no_photos(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Galeri sedang menyiapkan cerita pertamanya.')
            ->assertSee('Karya pilihan sedang disiapkan.')
            ->assertDontSee('gallery-play-control', false);
    }

    public function test_homepage_shows_controls_for_the_package_carousel(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('data-package-scroll="left"', false)
            ->assertSee('data-package-scroll="right"', false)
            ->assertSee('aria-label="Geser daftar paket ke kiri"', false)
            ->assertSee('aria-label="Geser daftar paket ke kanan"', false)
            ->assertSee('aria-controls="packageContainer"', false);
    }

    public function test_homepage_offers_only_available_package_categories_and_assigns_pre_wedding_correctly(): void
    {
        PhotographyPackage::create([
            'name' => 'Paket Pre Wedding',
            'category' => 'Prewedding',
            'price' => 2500000,
            'duration' => '4 jam',
            'facilities' => 'Sesi foto',
            'is_wedding' => true,
        ]);
        PhotographyPackage::create([
            'name' => 'Paket Wedding',
            'category' => 'Wedding',
            'price' => 5000000,
            'duration' => 'Sehari',
            'facilities' => 'Dokumentasi acara',
            'is_wedding' => true,
        ]);
        PhotographyPackage::create([
            'name' => 'Paket Wisuda',
            'category' => 'Wisuda',
            'price' => 1000000,
            'duration' => '2 jam',
            'facilities' => 'Sesi foto',
        ]);
        PhotographyPackage::create([
            'name' => 'Paket Foto Studio',
            'category' => 'Lainnya',
            'price' => 750000,
            'duration' => '1 jam',
            'facilities' => 'Sesi foto',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('data-category-filter=""', false)
            ->assertSee('data-category-filter="Prewedding"', false)
            ->assertSee('data-category="Prewedding"', false)
            ->assertSee('data-category-filter="Wedding"', false)
            ->assertSee('data-category="Wedding"', false)
            ->assertDontSee('data-category="Prewedding Wedding"', false)
            ->assertDontSee('data-category="Wedding Prewedding"', false)
            ->assertSee('data-category-filter="Wisuda"', false)
            ->assertSee('data-category="Wisuda"', false)
            ->assertSee('data-category-filter="Lainnya"', false)
            ->assertDontSee('data-category-filter="maternity"', false)
            ->assertDontSee('packageSearch', false)
            ->assertDontSee('type="search"', false);
    }

    public function test_homepage_adds_custom_package_categories_as_exact_filters(): void
    {
        PhotographyPackage::create([
            'name' => 'Paket Foto Dokumen',
            'category' => 'Pas Foto',
            'price' => 200000,
            'duration' => '15 menit',
            'facilities' => 'Foto dokumen',
        ]);
        PhotographyPackage::create([
            'name' => 'Paket Prewedding Mini',
            'category' => 'Prewedding',
            'price' => 900000,
            'duration' => '10 menit',
            'facilities' => 'Sesi foto',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('data-category-filter="Pas Foto"', false)
            ->assertSee('data-category="Pas Foto"', false)
            ->assertSee('data-category-filter="Prewedding"', false)
            ->assertSee('data-category="Prewedding"', false)
            ->assertSee('card.dataset.category === selectedCategory', false);
    }
}
