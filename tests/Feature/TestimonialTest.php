<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\PhotographyPackage;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestimonialTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_shows_customer_testimonials_safely_and_links_to_reservation_lookup(): void
    {
        $payment = $this->createEligiblePayment();
        $payment->reservation->testimonial()->create([
            'rating' => 5,
            'message' => '<script>alert(1)</script>',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('href="'.route('reservasi.cek').'">Tulis testimoni</a>', false);
    }

    public function test_customer_can_submit_a_testimonial_after_payment_and_photo_delivery_are_complete(): void
    {
        $payment = $this->createEligiblePayment();
        $resultUrl = route('reservasi.result', ['token' => $payment->token]);

        $this->get($resultUrl)
            ->assertOk()
            ->assertSee('name="message"', false)
            ->assertSee('type="radio"', false)
            ->assertSee('aria-label="5 bintang"', false)
            ->assertDontSee('<select id="rating"', false)
            ->assertSee(route('reservasi.testimoni.store'), false);

        $this->from($resultUrl)
            ->post(route('reservasi.testimoni.store'), [
                'token' => $payment->token,
                'rating' => 5,
                'message' => 'Hasil foto sangat bagus dan pelayanannya ramah.',
            ])
            ->assertRedirect($resultUrl)
            ->assertSessionHas('testimonial_success', 'Terima kasih, testimoni Anda berhasil dikirim.');

        $this->assertDatabaseHas('testimonials', [
            'reservation_id' => $payment->reservation_id,
            'rating' => 5,
            'message' => 'Hasil foto sangat bagus dan pelayanannya ramah.',
        ]);

        $this->get($resultUrl)
            ->assertOk()
            ->assertSee('Testimoni Anda sudah diterima')
            ->assertDontSee('name="message"', false);
    }

    public function test_customer_can_submit_a_testimonial_as_soon_as_reservation_status_is_complete(): void
    {
        $payment = $this->createEligiblePayment();
        $payment->reservation->update([
            'date' => now()->addMonth()->toDateString(),
            'session_completed_at' => null,
            'photo_status' => 'diproses',
            'photo_delivery_url' => null,
        ]);
        $resultUrl = route('reservasi.result', ['token' => $payment->token]);

        $this->get($resultUrl)
            ->assertOk()
            ->assertSee('name="message"', false);

        $this->from($resultUrl)
            ->post(route('reservasi.testimoni.store'), [
                'token' => $payment->token,
                'rating' => 5,
                'message' => 'Hasil foto sangat bagus dan pelayanannya ramah.',
            ])
            ->assertRedirect($resultUrl)
            ->assertSessionHas('testimonial_success');

        $this->assertDatabaseHas('testimonials', [
            'reservation_id' => $payment->reservation_id,
            'rating' => 5,
            'message' => 'Hasil foto sangat bagus dan pelayanannya ramah.',
        ]);
    }

    public function test_customer_cannot_submit_a_testimonial_before_payment_is_complete(): void
    {
        $payment = $this->createEligiblePayment();
        $payment->update(['status' => 'Menunggu pelunasan']);
        $resultUrl = route('reservasi.result', ['token' => $payment->token]);

        $this->from($resultUrl)
            ->post(route('reservasi.testimoni.store'), [
                'token' => $payment->token,
                'rating' => 5,
                'message' => 'Hasil foto sangat bagus dan pelayanannya ramah.',
            ])
            ->assertRedirectBackWithErrors(['token']);

        $this->assertDatabaseMissing('testimonials', [
            'reservation_id' => $payment->reservation_id,
        ]);
    }

    public function test_customer_cannot_submit_an_invalid_rating_or_short_testimonial(): void
    {
        $payment = $this->createEligiblePayment();
        $resultUrl = route('reservasi.result', ['token' => $payment->token]);

        $response = $this->from($resultUrl)
            ->post(route('reservasi.testimoni.store'), [
                'token' => $payment->token,
                'rating' => 6,
                'message' => 'Pendek',
            ])
            ->assertRedirectBackWithErrors(['rating', 'message']);
        $response->assertSessionHasErrors([
            'rating' => 'Rating harus antara 1 sampai 5 bintang.',
            'message' => 'Testimoni minimal 10 karakter.',
        ]);

        $this->assertDatabaseMissing('testimonials', [
            'reservation_id' => $payment->reservation_id,
        ]);
    }

    public function test_submission_with_an_unknown_reservation_token_returns_not_found(): void
    {
        $this->post(route('reservasi.testimoni.store'), [
            'token' => 'UNKNOWN-001',
            'rating' => 5,
            'message' => 'Hasil foto sangat bagus dan pelayanannya ramah.',
        ])->assertNotFound();

        $this->assertDatabaseCount('testimonials', 0);
    }

    public function test_customer_cannot_submit_more_than_one_testimonial_per_reservation(): void
    {
        $payment = $this->createEligiblePayment();
        $payment->reservation->testimonial()->create([
            'rating' => 4,
            'message' => 'Pengalaman awal sangat memuaskan.',
        ]);
        $resultUrl = route('reservasi.result', ['token' => $payment->token]);

        $this->from($resultUrl)
            ->post(route('reservasi.testimoni.store'), [
                'token' => $payment->token,
                'rating' => 5,
                'message' => 'Hasil foto sangat bagus dan pelayanannya ramah.',
            ])
            ->assertRedirectBackWithErrors(['testimonial']);

        $this->assertDatabaseHas('testimonials', [
            'reservation_id' => $payment->reservation_id,
            'message' => 'Pengalaman awal sangat memuaskan.',
        ]);
        $this->assertDatabaseMissing('testimonials', [
            'reservation_id' => $payment->reservation_id,
            'message' => 'Hasil foto sangat bagus dan pelayanannya ramah.',
        ]);
    }

    private function createEligiblePayment(): Payment
    {
        $package = PhotographyPackage::create([
            'name' => 'Paket Tes',
            'price' => 1000000,
            'duration' => '2 jam',
            'facilities' => 'Studio',
        ]);

        $reservation = Reservation::create([
            'photography_package_id' => $package->id,
            'token' => 'TESTI-001',
            'name' => 'Pelanggan Tes',
            'whatsapp' => '081234567890',
            'date' => '2026-10-30',
            'time' => '10:00:00',
            'duration' => '2 jam',
            'location' => 'Studio',
            'session_completed_at' => '2026-10-30 12:00:00',
            'photo_status' => 'siap',
            'photo_delivery_url' => 'https://example.test/galeri',
        ]);

        return Payment::create([
            'reservation_id' => $reservation->id,
            'token' => $reservation->token,
            'status' => 'Selesai',
            'tagihan' => 1000000,
            'total_paid' => 1000000,
        ]);
    }
}
