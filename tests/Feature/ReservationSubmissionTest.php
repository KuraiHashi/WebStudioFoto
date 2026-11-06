<?php

namespace Tests\Feature;

use App\Models\PhotographyPackage;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_reservation_page_renders_the_studio_form_and_schedule_panel(): void
    {
        $package = $this->createPackage([
            'name' => 'Paket Wedding',
            'is_wedding' => false,
        ]);

        $this->get(route('reservasi.index'))
            ->assertOk()
            ->assertSee('class="pg reservation-page"', false)
            ->assertSee('MARI RENCANAKAN MOMEN ANDA')
            ->assertSee('name="photography_package_id"', false)
            ->assertSee('id="toggle-konsultasi"', false)
            ->assertSee('id="konsultasi-section" class="consultation-details" style="display: none;"', false)
            ->assertSee('type="hidden" id="rt" name="date"', false)
            ->assertDontSee('<label>TANGGAL</label>', false)
            ->assertSee('id="date-selection-error"', false)
            ->assertSee('class="reservation-booking-grid"', false)
            ->assertSee('id="available-time-slots"', false)
            ->assertSee('data-duration-seconds="7200"', false)
            ->assertSee('type="hidden" id="rj" name="time"', false)
            ->assertSee('Detail Layanan')
            ->assertSee('Jam tersedia setiap 30 menit')
            ->assertSee('06.00–18.00')
            ->assertSee('data-wedding="1"', false)
            ->assertSee('id="rsum"', false)
            ->assertSee('Ketersediaan')
            ->assertSee('Salin link')
            ->assertSee('Tempel link Google Maps di sini')
            ->assertSee($package->name);
    }

    public function test_customer_can_submit_a_reservation_and_consultation_request(): void
    {
        $package = $this->createPackage();

        $this->from(route('reservasi.index'))
            ->post(route('reservasi.store'), $this->reservationPayload($package, [
                'request_consultation' => '1',
                'consultation_topic' => 'Konsep & Wardrobe',
                'consultation_media' => 'WhatsApp Chat',
                'consultation_notes' => 'Diskusi warna busana.',
            ]))
            ->assertRedirect(route('reservasi.index'))
            ->assertSessionHas('swal_success', true)
            ->assertSessionHas('is_consultation', true);

        $reservation = Reservation::sole();

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'photography_package_id' => $package->id,
            'name' => 'Pelanggan Baru',
            'duration' => '2 jam',
            'request_consultation' => true,
            'consultation_topic' => 'Konsep & Wardrobe',
            'consultation_notes' => 'Diskusi warna busana.',
            'location' => 'https://www.google.com/maps/search/?api=1&query=3.595200%2C98.672200',
        ]);
        $this->assertDatabaseHas('payments', [
            'reservation_id' => $reservation->id,
            'token' => $reservation->token,
            'status' => 'Masa konsultasi',
            'tagihan' => 1500000,
        ]);
    }

    public function test_customer_can_open_the_deposit_payment_page(): void
    {
        $package = $this->createPackage();
        $reservation = Reservation::create($this->reservationPayload($package, [
            'token' => 'RSV-PAY001',
        ]));
        $reservation->payment()->create([
            'token' => $reservation->token,
            'status' => 'Menunggu pembayaran DP',
            'tagihan' => $package->price,
        ]);

        $this->get(route('reservasi.pembayaran', ['token' => $reservation->token]))
            ->assertOk()
            ->assertSee('Pembayaran')
            ->assertSee('RSV-PAY001')
            ->assertSee('METODE PEMBAYARAN');
    }

    public function test_reservation_rejects_a_location_that_is_not_a_google_maps_link(): void
    {
        $package = $this->createPackage();

        $this->from(route('reservasi.index'))
            ->post(route('reservasi.store'), $this->reservationPayload($package, [
                'location' => 'https://example.com/location',
            ]))
            ->assertRedirect(route('reservasi.index'))
            ->assertSessionHasErrors('location');

        $this->assertDatabaseCount('reservations', 0);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_regular_reservation_is_rejected_when_its_schedule_overlaps(): void
    {
        $package = $this->createPackage();
        $existingReservation = Reservation::create($this->reservationPayload($package, [
            'token' => 'RSV-EXIST1',
            'time' => '10:00',
        ]));
        $existingReservation->payment()->create([
            'token' => $existingReservation->token,
            'status' => 'Menunggu pembayaran DP',
            'tagihan' => $package->price,
        ]);

        $this->from(route('reservasi.index'))
            ->post(route('reservasi.store'), $this->reservationPayload($package, [
                'time' => '11:00',
            ]))
            ->assertRedirect(route('reservasi.index'))
            ->assertSessionHasErrors('time');

        $this->assertDatabaseCount('reservations', 1);
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_minute_duration_is_used_when_validating_a_new_reservation_schedule(): void
    {
        $shortPackage = $this->createPackage(['duration' => '10 menit']);
        $existingReservation = Reservation::create($this->reservationPayload($shortPackage, [
            'token' => 'RSV-SHORT01',
            'time' => '10:00',
        ]));
        $existingReservation->payment()->create([
            'token' => $existingReservation->token,
            'status' => 'Menunggu pembayaran DP',
            'tagihan' => $shortPackage->price,
        ]);
        $nextPackage = $this->createPackage();

        $this->get(route('reservasi.booked-slots'))
            ->assertOk()
            ->assertJsonFragment([
                'time' => '10:00',
                'duration' => '10 menit',
                'duration_seconds' => 600,
            ]);

        $this->from(route('reservasi.index'))
            ->post(route('reservasi.store'), $this->reservationPayload($nextPackage, [
                'time' => '10:30',
            ]))
            ->assertRedirect(route('reservasi.index'))
            ->assertSessionHas('swal_success', true);

        $this->assertDatabaseCount('reservations', 2);
        $this->assertDatabaseCount('payments', 2);
    }

    public function test_regular_reservation_is_rejected_when_it_starts_at_an_existing_session_end(): void
    {
        $package = $this->createPackage();
        $existingReservation = Reservation::create($this->reservationPayload($package, [
            'token' => 'RSV-EXIST4',
            'time' => '06:00',
        ]));
        $existingReservation->payment()->create([
            'token' => $existingReservation->token,
            'status' => 'Menunggu pembayaran DP',
            'tagihan' => $package->price,
        ]);

        $this->from(route('reservasi.index'))
            ->post(route('reservasi.store'), $this->reservationPayload($package, [
                'time' => '08:00',
            ]))
            ->assertRedirect(route('reservasi.index'))
            ->assertSessionHasErrors('time');

        $this->assertDatabaseCount('reservations', 1);
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_regular_reservation_can_end_when_an_existing_session_starts(): void
    {
        $package = $this->createPackage();
        $existingReservation = Reservation::create($this->reservationPayload($package, [
            'token' => 'RSV-EXIST5',
            'time' => '10:00',
        ]));
        $existingReservation->payment()->create([
            'token' => $existingReservation->token,
            'status' => 'Menunggu pembayaran DP',
            'tagihan' => $package->price,
        ]);

        $this->from(route('reservasi.index'))
            ->post(route('reservasi.store'), $this->reservationPayload($package, [
                'time' => '08:00',
            ]))
            ->assertRedirect(route('reservasi.index'))
            ->assertSessionHas('swal_success', true);

        $this->assertDatabaseCount('reservations', 2);
        $this->assertDatabaseCount('payments', 2);
    }

    public function test_four_hour_wedding_session_can_start_at_six_pm(): void
    {
        $package = $this->createPackage([
            'name' => 'Paket Wedding',
            'duration' => '4 jam',
            'is_wedding' => true,
        ]);

        $this->from(route('reservasi.index'))
            ->post(route('reservasi.store'), $this->reservationPayload($package, [
                'time' => '18:00',
            ]))
            ->assertRedirect(route('reservasi.index'))
            ->assertSessionHas('swal_success', true);

        $this->assertDatabaseHas('reservations', [
            'photography_package_id' => $package->id,
            'date' => '2027-01-20',
            'time' => '18:00',
            'duration' => '4 jam',
        ]);
    }

    public function test_wedding_package_uses_a_fixed_start_time_without_requiring_a_future_hour(): void
    {
        $this->travelTo(now()->setTime(12, 0));
        $package = $this->createPackage([
            'name' => 'Paket Wedding',
            'is_wedding' => true,
        ]);

        $this->from(route('reservasi.index'))
            ->post(route('reservasi.store'), $this->reservationPayload($package, [
                'date' => now()->toDateString(),
                'time' => '06:00',
            ]))
            ->assertRedirect(route('reservasi.index'))
            ->assertSessionHas('swal_success', true);

        $this->assertDatabaseHas('reservations', [
            'photography_package_id' => $package->id,
            'date' => now()->toDateString(),
            'time' => '06:00',
        ]);
    }

    public function test_reservation_is_rejected_when_start_time_is_before_opening_time(): void
    {
        $package = $this->createPackage();

        $this->from(route('reservasi.index'))
            ->post(route('reservasi.store'), $this->reservationPayload($package, [
                'time' => '05:30',
            ]))
            ->assertRedirect(route('reservasi.index'))
            ->assertSessionHasErrors('time');

        $this->assertDatabaseCount('reservations', 0);
    }

    public function test_reservation_is_rejected_when_start_time_is_after_closing_time(): void
    {
        $package = $this->createPackage();

        $this->from(route('reservasi.index'))
            ->followingRedirects()
            ->post(route('reservasi.store'), $this->reservationPayload($package, [
                'time' => '18:30',
            ]))
            ->assertOk()
            ->assertSee('Pilih waktu mulai setiap 30 menit antara pukul 06.00–18.00.');

        $this->assertDatabaseCount('reservations', 0);
    }

    public function test_reservation_is_rejected_when_start_time_is_not_on_a_half_hour_slot(): void
    {
        $package = $this->createPackage();

        $this->from(route('reservasi.index'))
            ->post(route('reservasi.store'), $this->reservationPayload($package, [
                'time' => '10:15',
            ]))
            ->assertRedirect(route('reservasi.index'))
            ->assertSessionHasErrors('time');

        $this->assertDatabaseCount('reservations', 0);
    }

    public function test_reservation_is_rejected_when_date_is_in_the_past(): void
    {
        $package = $this->createPackage();

        $this->from(route('reservasi.index'))
            ->post(route('reservasi.store'), $this->reservationPayload($package, [
                'date' => '2020-01-01',
            ]))
            ->assertRedirect(route('reservasi.index'))
            ->assertSessionHasErrors('date');

        $this->assertDatabaseCount('reservations', 0);
    }

    public function test_reservation_is_rejected_when_selected_time_has_already_passed_today(): void
    {
        $this->travelTo('2026-11-05 12:00:00');
        $package = $this->createPackage();

        $this->from(route('reservasi.index'))
            ->post(route('reservasi.store'), $this->reservationPayload($package, [
                'date' => '2026-11-05',
                'time' => '10:00',
            ]))
            ->assertRedirect(route('reservasi.index'))
            ->assertSessionHasErrors('time');

        $this->assertDatabaseCount('reservations', 0);
    }

    public function test_wedding_reservation_is_rejected_when_any_active_reservation_exists_that_day(): void
    {
        $regularPackage = $this->createPackage();
        $weddingPackage = $this->createPackage([
            'name' => 'Paket Wedding',
            'duration' => '4 jam',
            'is_wedding' => true,
        ]);
        $existingReservation = Reservation::create($this->reservationPayload($regularPackage, [
            'token' => 'RSV-EXIST2',
            'time' => '10:00',
        ]));
        $existingReservation->payment()->create([
            'token' => $existingReservation->token,
            'status' => 'Menunggu pembayaran DP',
            'tagihan' => $regularPackage->price,
        ]);

        $this->from(route('reservasi.index'))
            ->post(route('reservasi.store'), $this->reservationPayload($weddingPackage, [
                'time' => '14:00',
            ]))
            ->assertRedirect(route('reservasi.index'))
            ->assertSessionHasErrors('date');

        $this->assertDatabaseCount('reservations', 1);
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_regular_reservation_is_rejected_on_a_day_with_an_active_wedding(): void
    {
        $regularPackage = $this->createPackage();
        $weddingPackage = $this->createPackage([
            'name' => 'Paket Wedding',
            'duration' => '4 jam',
            'is_wedding' => false,
        ]);
        $existingReservation = Reservation::create($this->reservationPayload($weddingPackage, [
            'token' => 'RSV-EXIST3',
            'time' => '10:00',
        ]));
        $existingReservation->payment()->create([
            'token' => $existingReservation->token,
            'status' => 'Menunggu pembayaran DP',
            'tagihan' => $weddingPackage->price,
        ]);

        $this->from(route('reservasi.index'))
            ->post(route('reservasi.store'), $this->reservationPayload($regularPackage, [
                'time' => '16:00',
            ]))
            ->assertRedirect(route('reservasi.index'))
            ->assertSessionHasErrors('date');

        $this->assertDatabaseCount('reservations', 1);
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_booked_schedule_marks_a_package_named_wedding_as_full_day(): void
    {
        $weddingPackage = $this->createPackage([
            'name' => 'Paket Wedding',
            'duration' => '4 jam',
            'is_wedding' => false,
        ]);
        $reservation = Reservation::create($this->reservationPayload($weddingPackage, [
            'token' => 'RSV-WEDD01',
            'date' => '2027-01-20',
            'time' => '06:00',
        ]));
        $reservation->payment()->create([
            'token' => $reservation->token,
            'status' => 'Menunggu pembayaran DP',
            'tagihan' => $weddingPackage->price,
        ]);

        $this->get(route('reservasi.booked-slots'))
            ->assertOk()
            ->assertJsonFragment([
                'date' => '2027-01-20',
                'time' => '06:00',
                'duration' => '4 jam',
                'is_wedding' => true,
            ]);
    }

    public function test_pre_wedding_booking_leaves_the_rest_of_the_date_available_by_duration(): void
    {
        $preWeddingPackage = $this->createPackage([
            'name' => 'Paket Pre Wedding',
            'duration' => '4 jam',
            'is_wedding' => true,
        ]);
        $graduationPackage = $this->createPackage([
            'name' => 'Paket Wisuda',
            'duration' => '2 jam',
        ]);
        $existingReservation = Reservation::create($this->reservationPayload($preWeddingPackage, [
            'token' => 'RSV-PREWD01',
            'date' => '2027-01-20',
            'time' => '09:00',
        ]));
        $existingReservation->payment()->create([
            'token' => $existingReservation->token,
            'status' => 'Menunggu pembayaran DP',
            'tagihan' => $preWeddingPackage->price,
        ]);

        $this->get(route('reservasi.booked-slots'))
            ->assertOk()
            ->assertJsonFragment([
                'date' => '2027-01-20',
                'time' => '09:00',
                'duration' => '4 jam',
                'is_wedding' => false,
            ]);

        $this->from(route('reservasi.index'))
            ->post(route('reservasi.store'), $this->reservationPayload($graduationPackage, [
                'date' => '2027-01-20',
                'time' => '13:30',
            ]))
            ->assertRedirect(route('reservasi.index'))
            ->assertSessionHas('swal_success', true);

        $this->assertDatabaseCount('reservations', 2);
        $this->assertDatabaseCount('payments', 2);
    }

    public function test_pre_wedding_package_is_not_marked_as_full_day_in_the_calendar(): void
    {
        $package = $this->createPackage([
            'name' => 'Paket Pre Wedding',
            'duration' => '4 jam',
            'is_wedding' => true,
        ]);

        $this->get(route('reservasi.index', ['package_id' => $package->id]))
            ->assertOk()
            ->assertSee('data-wedding="0"', false);
    }

    private function createPackage(array $attributes = []): PhotographyPackage
    {
        return PhotographyPackage::create(array_merge([
            'name' => 'Paket Foto',
            'price' => 1500000,
            'duration' => '2 jam',
            'facilities' => 'Studio',
        ], $attributes));
    }

    private function reservationPayload(PhotographyPackage $package, array $overrides = []): array
    {
        return array_merge([
            'photography_package_id' => $package->id,
            'name' => 'Pelanggan Baru',
            'whatsapp' => '081234567890',
            'date' => '2027-01-20',
            'time' => '10:00',
            'duration' => $package->duration,
            'location' => 'https://www.google.com/maps/search/?api=1&query=3.595200%2C98.672200',
        ], $overrides);
    }
}
