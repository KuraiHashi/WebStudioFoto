<?php

namespace Tests\Feature;

use App\Models\Galery;
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
}
