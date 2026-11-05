<?php

namespace Tests\Feature;

use App\Models\StudioProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialMediaLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_links_an_instagram_handle_to_its_profile(): void
    {
        $this->createStudioProfile('@liquifpicture');

        $this->get('/')
            ->assertOk()
            ->assertSee('href="https://www.instagram.com/liquifpicture/"', false)
            ->assertSee('target="_blank" rel="noopener noreferrer"', false)
            ->assertSee('class="social-link-icon"', false)
            ->assertSee('aria-label="Kunjungi Instagram studio (terbuka di tab baru)"', false)
            ->assertSee('@liquifpicture');
    }

    public function test_homepage_links_a_complete_social_media_url_directly(): void
    {
        $this->createStudioProfile('https://instagram.com/liquifpicture/?igsh=abc123');

        $this->get('/')
            ->assertOk()
            ->assertSee('href="https://instagram.com/liquifpicture/?igsh=abc123"', false)
            ->assertSee('@liquifpicture')
            ->assertSee('class="social-link-icon"', false);
    }

    public function test_homepage_does_not_turn_an_unsafe_social_media_value_into_a_link(): void
    {
        $this->createStudioProfile('javascript:alert(1)');

        $this->get('/')
            ->assertOk()
            ->assertDontSee('href="javascript:alert(1)"', false)
            ->assertSee('javascript:alert(1)');
    }

    private function createStudioProfile(string $socialMedia): void
    {
        StudioProfile::create([
            'name' => 'Liquif Picture',
            'address' => 'Studio',
            'phone' => '081234567890',
            'operational_hours' => '09.00–18.00',
            'social_media' => $socialMedia,
            'description' => 'Studio fotografi',
        ]);
    }
}
