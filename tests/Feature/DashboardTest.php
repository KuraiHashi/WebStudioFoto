<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\PaymentHistory;
use App\Models\PhotographyPackage;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_dashboard_cards_show_the_correct_reservation_counts(): void
    {
        $this->seedDashboardReservations();

        $response = $this->get(route('admin.dashboard'));

        $response
            ->assertOk()
            ->assertViewHas('reservasiBaru', 2)
            ->assertViewHas('menungguVerifikasi', 1)
            ->assertViewHas('reservasiSelesai', 1)
            ->assertSee('Reservasi baru')
            ->assertSee('Cek bukti pembayaran')
            ->assertSee('Lunas / selesai')
            ->assertDontSee('Dari booking sampai hasil foto');

        $this->assertSame(3, substr_count($response->getContent(), 'class="kp-card'));
    }

    public function test_dashboard_stat_cards_filter_the_reservation_table(): void
    {
        $this->seedDashboardReservations();

        $this->get(route('admin.dashboard', ['filter' => 'new']))
            ->assertOk()
            ->assertSee('NEW-0001')
            ->assertSee('NEW-0002')
            ->assertDontSee('VERIFY-0001')
            ->assertDontSee('PHOTO-0001');

        $this->get(route('admin.dashboard', ['filter' => 'verification']))
            ->assertOk()
            ->assertSee('VERIFY-0001')
            ->assertDontSee('NEW-0001');

        $this->get(route('admin.dashboard', ['filter' => 'completed']))
            ->assertOk()
            ->assertSee('PHOTO-0001')
            ->assertDontSee('NEW-0001')
            ->assertDontSee('BALANCE-0001')
            ->assertDontSee('UPCOMING-0001');
    }

    public function test_dashboard_shows_pending_proof_amount_to_help_admin_reconcile_a_bill(): void
    {
        $package = $this->createPackage();
        $reservation = $this->createReservation($package, 'MISMATCH-001', 'Pelanggan', now()->toDateString(), 'Verifikasi pelunasan');
        $this->createPaymentHistory($reservation->payment, 4500000);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Bukti menunggu verifikasi')
            ->assertSee('id="inputPendingProof"', false)
            ->assertSee('data-proof-amount="4500000"', false)
            ->assertSee('paymentMismatchWarning');
    }

    public function test_admin_can_activate_dp_for_reservation_without_payment_record(): void
    {
        $package = $this->createPackage();
        $reservation = $this->createReservation($package, 'VERIFY-NEW-001', 'Reservasi Baru', now()->toDateString());

        $this->put(route('admin.reservasi.verify', $reservation->id), [
            'status' => 'Menunggu pembayaran DP',
            'notes' => 'Konsultasi selesai',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseHas('payments', [
            'reservation_id' => $reservation->id,
            'token' => $reservation->token,
            'status' => 'Menunggu pembayaran DP',
            'tagihan' => 1000000,
            'total_paid' => 0,
            'notes' => 'Konsultasi selesai',
        ]);
    }

    public function test_customer_payment_proof_waits_for_admin_before_counting_as_paid(): void
    {
        Storage::fake('public');
        $package = $this->createPackage();
        $reservation = $this->createReservation($package, 'PAY-0001', 'Pelanggan', now()->toDateString(), 'Menunggu pembayaran DP');
        $payment = $reservation->payment;

        $this->post(route('reservasi.payment.store'), [
            'token' => $payment->token,
            'amount' => 500000,
            'payment_method' => 'Transfer Bank',
            'receipt_photo' => $this->fakeReceipt(),
        ])->assertRedirect(route('reservasi.pembayaran', ['token' => $payment->token]));

        $history = PaymentHistory::query()->where('payment_id', $payment->id)->firstOrFail();
        Storage::disk('public')->assertExists($history->receipt_photo);
        $this->assertDatabaseHas('payment_histories', [
            'id' => $history->id,
            'status' => 'menunggu_verifikasi',
            'amount' => 500000,
        ]);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'Konfirmasi pembayaran',
            'total_paid' => 0,
        ]);
    }

    public function test_customer_cannot_submit_a_deposit_below_half_of_the_bill(): void
    {
        Storage::fake('public');
        $package = $this->createPackage();
        $reservation = $this->createReservation($package, 'SMALL-DP-001', 'Pelanggan', now()->toDateString(), 'Menunggu pembayaran DP');
        $payment = $reservation->payment;

        $this->from(route('reservasi.pembayaran', ['token' => $payment->token]))
            ->post(route('reservasi.payment.store'), [
                'token' => $payment->token,
                'amount' => 400000,
                'payment_method' => 'Transfer Bank',
                'receipt_photo' => $this->fakeReceipt(),
            ])
            ->assertRedirect(route('reservasi.pembayaran', ['token' => $payment->token]))
            ->assertSessionHasErrors('amount');

        $this->assertDatabaseCount('payment_histories', 0);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'Menunggu pembayaran DP',
            'total_paid' => 0,
        ]);
    }

    public function test_admin_approval_counts_dp_only_after_review(): void
    {
        $package = $this->createPackage();
        $reservation = $this->createReservation($package, 'DP-APPROVAL-001', 'Pelanggan', now()->toDateString(), 'Konfirmasi pembayaran');
        $payment = $reservation->payment;
        $history = $this->createPaymentHistory($payment, 500000);

        $this->put(route('admin.reservasi.verify', $reservation->id), [
            'status' => 'Menunggu pelunasan',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseHas('payment_histories', [
            'id' => $history->id,
            'status' => 'diterima',
        ]);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'Menunggu pelunasan',
            'total_paid' => 500000,
        ]);
    }

    public function test_admin_cannot_reject_payment_proof_without_an_explanation(): void
    {
        $package = $this->createPackage();
        $reservation = $this->createReservation($package, 'DP-REJECT-001', 'Pelanggan', now()->toDateString(), 'Konfirmasi pembayaran');
        $payment = $reservation->payment;
        $history = $this->createPaymentHistory($payment, 500000);

        $this->from(route('admin.dashboard'))
            ->put(route('admin.reservasi.verify', $reservation->id), [
                'status' => 'Menunggu pembayaran DP',
            ])
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHasErrors('notes');

        $this->assertDatabaseHas('payment_histories', [
            'id' => $history->id,
            'status' => 'menunggu_verifikasi',
        ]);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'Konfirmasi pembayaran',
            'total_paid' => 0,
        ]);
    }

    public function test_admin_cannot_approve_a_final_payment_that_does_not_cover_the_bill(): void
    {
        $package = $this->createPackage();
        $reservation = $this->createReservation(
            $package,
            'UNDERPAID-001',
            'Pelanggan',
            now()->subDay()->toDateString(),
            'Verifikasi pelunasan',
            now()->subDay()
        );
        $payment = $reservation->payment;
        $payment->update(['total_paid' => 400000]);
        $history = $this->createPaymentHistory($payment, 500000);

        $this->from(route('admin.dashboard'))
            ->put(route('admin.reservasi.verify', $reservation->id), [
                'status' => 'Selesai',
            ])
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('payment_histories', [
            'id' => $history->id,
            'status' => 'menunggu_verifikasi',
        ]);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'Verifikasi pelunasan',
            'total_paid' => 400000,
        ]);
    }

    public function test_admin_can_complete_session_then_confirm_full_payment_and_update_completed_count(): void
    {
        $package = $this->createPackage();
        $reservation = $this->createReservation(
            $package,
            'FINAL-APPROVAL-001',
            'Pelanggan',
            now()->subDay()->toDateString(),
            'Verifikasi pelunasan'
        );
        $payment = $reservation->payment;
        $payment->update(['total_paid' => 500000]);
        $history = $this->createPaymentHistory($payment, 500000);

        $this->put(route('admin.reservasi.session-complete', $reservation))
            ->assertRedirect(route('admin.dashboard'));
        $this->assertNotNull($reservation->fresh()->session_completed_at);

        $this->put(route('admin.reservasi.verify', $reservation->id), [
            'status' => 'Selesai',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseHas('payment_histories', [
            'id' => $history->id,
            'status' => 'diterima',
        ]);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'Selesai',
            'total_paid' => 1000000,
        ]);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('reservasiSelesai', 1)
            ->assertSee('Status verifikasi pembayaran berhasil diperbarui.');
    }

    public function test_admin_can_update_tagihan_without_overwriting_verified_payments(): void
    {
        $package = $this->createPackage();
        $reservation = $this->createReservation($package, 'BILLING-001', 'Pelanggan', now()->toDateString(), 'Menunggu pelunasan');
        $reservation->payment->update(['total_paid' => 500000]);

        $this->put(route('admin.reservasi.tagihan', $reservation->id), [
            'tagihan' => 1200000,
            'total_paid' => 0,
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseHas('payments', [
            'id' => $reservation->payment->id,
            'tagihan' => 1200000,
            'total_paid' => 500000,
        ]);
    }

    public function test_admin_cannot_set_tagihan_below_a_pending_payment_proof(): void
    {
        $package = $this->createPackage();
        $reservation = $this->createReservation($package, 'BILLING-PENDING-001', 'Pelanggan', now()->toDateString(), 'Verifikasi pelunasan');
        $payment = $reservation->payment;
        $payment->update(['total_paid' => 500000]);
        $this->createPaymentHistory($payment, 500000);

        $this->from(route('admin.dashboard'))
            ->followingRedirects()
            ->put(route('admin.reservasi.tagihan', $reservation->id), [
                'tagihan' => 900000,
                'total_paid' => 500000,
            ])
            ->assertOk()
            ->assertSee('Tagihan tidak boleh lebih kecil dari pembayaran yang sudah diterima dan bukti yang menunggu verifikasi.');

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'tagihan' => 1000000,
            'total_paid' => 500000,
        ]);
    }

    public function test_admin_can_update_reservation_schedule_from_dashboard(): void
    {
        $package = $this->createPackage();
        $reservation = $this->createReservation($package, 'SCHEDULE-001', 'Pelanggan', now()->toDateString());

        $this->put(route('admin.reservasi.detail', $reservation->id), [
            'name' => 'Nama Baru',
            'whatsapp' => '081111111111',
            'date' => now()->addWeek()->toDateString(),
            'time' => '14:30',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'name' => 'Nama Baru',
            'whatsapp' => '081111111111',
            'date' => now()->addWeek()->toDateString(),
            'time' => '14:30',
        ]);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Detail data reservasi berhasil diperbarui.');
    }

    public function test_admin_can_send_photo_link_after_completing_a_past_session(): void
    {
        $package = $this->createPackage();
        $reservation = $this->createReservation(
            $package,
            'PHOTO-FLOW-001',
            'Pelanggan',
            now()->subDay()->toDateString(),
            'Selesai'
        );
        $reservation->payment->update(['total_paid' => 1000000]);

        $this->put(route('admin.reservasi.session-complete', $reservation))
            ->assertRedirect(route('admin.dashboard'));

        $reservation->refresh();
        $this->assertNotNull($reservation->session_completed_at);

        $this->from(route('admin.dashboard'))
            ->put(route('admin.reservasi.photos', $reservation), [
                'photo_delivery_url' => '',
            ])
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHasErrors('photo_delivery_url');

        $this->put(route('admin.reservasi.photos', $reservation), [
            'photo_delivery_url' => 'https://drive.google.com/folder',
        ])->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas('success', 'Tautan hasil foto berhasil dikirim kepada pelanggan.');

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'photo_status' => 'siap',
            'photo_delivery_url' => 'https://drive.google.com/folder',
        ]);

        $this->get(route('reservasi.result', ['token' => $reservation->token]))
            ->assertOk()
            ->assertSee('Buka hasil foto')
            ->assertSee('https://drive.google.com/folder');
    }

    public function test_customer_can_request_photo_edit_and_admin_can_send_the_edited_link(): void
    {
        $package = $this->createPackage();
        $reservation = $this->createReservation(
            $package,
            'PHOTO-EDIT-001',
            'Pelanggan',
            now()->subDay()->toDateString(),
            'Selesai'
        );
        $reservation->payment->update(['total_paid' => 1000000]);
        $reservation->forceFill([
            'photo_status' => 'siap',
            'photo_delivery_url' => 'https://drive.google.com/original',
        ])->save();

        $this->post(route('reservasi.request-edit'), [
            'token' => $reservation->token,
            'edit_request_message' => 'Sertakan nama file foto mana yang mau di edit',
        ])->assertRedirect(route('reservasi.result', ['token' => $reservation->token]))
            ->assertSessionHas('success', 'Permintaan edit foto berhasil dikirim ke admin.');

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'edit_request_message' => 'Sertakan nama file foto mana yang mau di edit',
        ]);

        $this->put(route('admin.reservasi.photos', $reservation), [
            'photo_kind' => 'edited',
            'photo_delivery_url' => 'https://drive.google.com/edited',
        ])->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas('success', 'Tautan hasil edit foto berhasil dikirim kepada pelanggan.');

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'edited_photo_url' => 'https://drive.google.com/edited',
        ]);

        $this->get(route('reservasi.result', ['token' => $reservation->token]))
            ->assertOk()
            ->assertSee('Lihat hasil edit')
            ->assertSee('https://drive.google.com/edited');
    }

    public function test_paid_reservation_goes_directly_to_photo_queue_without_waiting_for_session_complete(): void
    {
        $package = $this->createPackage();
        $reservation = $this->createReservation($package, 'PAID-PHOTO-001', 'Pelanggan', now()->addDay()->toDateString(), 'Selesai');
        $reservation->payment->update(['total_paid' => 1000000]);

        $this->assertNull($reservation->session_completed_at);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Menunggu hasil foto')
            ->assertDontSee('Sesi mendatang · lunas');

        $this->put(route('admin.reservasi.photos', $reservation), [
            'photo_delivery_url' => 'https://drive.google.com/folder',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'photo_status' => 'siap',
            'photo_delivery_url' => 'https://drive.google.com/folder',
        ]);

        $this->get(route('reservasi.result', ['token' => $reservation->token]))
            ->assertOk()
            ->assertSee('Hasil foto sudah siap. Gunakan tautan di bawah untuk membukanya.')
            ->assertSee('Buka hasil foto')
            ->assertSee('https://drive.google.com/folder');
    }

    public function test_customer_cannot_pay_balance_before_the_scheduled_session_ends(): void
    {
        Storage::fake('public');
        $package = $this->createPackage();
        $reservation = $this->createReservation(
            $package,
            'BALANCE-EARLY-001',
            'Pelanggan',
            now()->addDay()->toDateString(),
            'Menunggu pelunasan'
        );

        $this->get(route('reservasi.pembayaran', ['token' => $reservation->token]))
            ->assertOk()
            ->assertSee('Form pelunasan tersedia setelah waktu sesi pemotretan berakhir.')
            ->assertDontSee('NOMINAL PELUNASAN');

        $this->from(route('reservasi.pembayaran', ['token' => $reservation->token]))
            ->post(route('reservasi.payment.store'), [
                'token' => $reservation->token,
                'amount' => 500000,
                'payment_method' => 'Transfer Bank',
                'receipt_photo' => $this->fakeReceipt(),
            ])
            ->assertRedirect(route('reservasi.pembayaran', ['token' => $reservation->token]))
            ->assertSessionHasErrors('token');

        $this->assertDatabaseCount('payment_histories', 0);
    }

    public function test_customer_can_view_and_submit_balance_payment_after_the_scheduled_session_ends(): void
    {
        Storage::fake('public');
        $package = $this->createPackage();
        $reservation = $this->createReservation(
            $package,
            'BALANCE-READY-001',
            'Pelanggan',
            now()->subDay()->toDateString(),
            'Menunggu pelunasan'
        );
        $reservation->payment->update(['total_paid' => 500000]);

        $this->get(route('reservasi.result', ['token' => $reservation->token]))
            ->assertOk()
            ->assertSee('Bayar pelunasan')
            ->assertSee('Jadwal sesi pemotretan telah berakhir.');

        $this->get(route('reservasi.pembayaran', ['token' => $reservation->token]))
            ->assertOk()
            ->assertSee('NOMINAL PELUNASAN')
            ->assertSee('max="500000"', false);

        $this->post(route('reservasi.payment.store'), [
            'token' => $reservation->token,
            'amount' => 500000,
            'payment_method' => 'Transfer Bank',
            'receipt_photo' => $this->fakeReceipt(),
        ])->assertRedirect(route('reservasi.pembayaran', ['token' => $reservation->token]));

        $this->assertDatabaseHas('payments', [
            'id' => $reservation->payment->id,
            'status' => 'Verifikasi pelunasan',
            'total_paid' => 500000,
        ]);
        $this->assertDatabaseHas('payment_histories', [
            'payment_id' => $reservation->payment->id,
            'status' => 'menunggu_verifikasi',
            'amount' => 500000,
        ]);
    }

    private function seedDashboardReservations(): void
    {
        $package = $this->createPackage();

        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();
        $tomorrow = now()->addDay()->toDateString();

        $this->createReservation($package, 'NEW-0001', 'Reservasi Baru', $tomorrow, 'Menunggu pembayaran DP');
        $this->createReservation($package, 'NEW-0002', 'Tanpa Pembayaran', $today);
        $this->createReservation($package, 'VERIFY-0001', 'Menunggu Verifikasi', $yesterday, 'Konfirmasi pembayaran');
        $this->createReservation($package, 'UPCOMING-0001', 'Sesi Mendatang', $tomorrow, 'Menunggu pelunasan');
        $this->createReservation($package, 'BALANCE-0001', 'Menunggu Pelunasan', $yesterday, 'Menunggu pelunasan', now()->subDay());
        $this->createReservation($package, 'PHOTO-0001', 'Menunggu Hasil Foto', $yesterday, 'Selesai', now()->subDay());
        $this->createReservation($package, 'CANCELLED-0001', 'Reservasi Dibatalkan', $tomorrow, 'Dibatalkan');
    }

    private function createPackage(): PhotographyPackage
    {
        return PhotographyPackage::create([
            'name' => 'Paket Tes',
            'price' => 1000000,
            'duration' => '2 jam',
            'facilities' => 'Studio',
        ]);
    }

    private function createReservation(
        PhotographyPackage $package,
        string $token,
        string $name,
        string $date,
        ?string $status = null,
        ?\DateTimeInterface $sessionCompletedAt = null
    ): Reservation {
        $reservation = Reservation::create([
            'photography_package_id' => $package->id,
            'token' => $token,
            'name' => $name,
            'whatsapp' => '081234567890',
            'date' => $date,
            'time' => '10:00',
            'duration' => '2 jam',
            'location' => 'Studio',
            'session_completed_at' => $sessionCompletedAt,
        ]);

        if ($status !== null) {
            Payment::create([
                'reservation_id' => $reservation->id,
                'token' => $token,
                'status' => $status,
                'tagihan' => 1000000,
                'total_paid' => 0,
            ]);
        }

        return $reservation;
    }

    private function createPaymentHistory(Payment $payment, float $amount): PaymentHistory
    {
        return PaymentHistory::create([
            'payment_id' => $payment->id,
            'amount' => $amount,
            'payment_method' => 'Transfer Bank',
            'receipt_photo' => 'receipts/test.png',
            'status' => 'menunggu_verifikasi',
        ]);
    }

    private function fakeReceipt(): UploadedFile
    {
        $content = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADUlEQVR4nGP4z8AAAAMBAQDJ/pLvAAAAAElFTkSuQmCC',
            true
        );

        return UploadedFile::fake()->createWithContent('receipt.png', $content);
    }
}
