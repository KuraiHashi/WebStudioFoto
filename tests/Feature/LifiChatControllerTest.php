<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\PhotographyPackage;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LifiChatControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_chat_returns_only_the_reservation_summary_for_a_valid_token(): void
    {
        $payment = $this->createPayment('RSV-CHAT01', [
            'name' => 'Nama Rahasia',
            'whatsapp' => '081234567890',
            'location' => 'Alamat Rahasia',
            'date' => '2026-12-20',
            'time' => '14:30',
        ]);

        $response = $this->postJson(route('lifi.chat'), [
            'message' => 'Tolong cek status dan jadwal saya',
            'token' => strtolower($payment->token),
        ]);

        $response->assertOk();
        $reply = $response->json('reply');
        $this->assertStringContainsString('Paket Wedding', $reply);
        $this->assertStringContainsString('20-12-2026, pukul 14:30', $reply);
        $this->assertStringContainsString('Menunggu pembayaran DP', $reply);
        $this->assertStringNotContainsString('Nama Rahasia', $reply);
        $this->assertStringNotContainsString('081234567890', $reply);
        $this->assertStringNotContainsString('Alamat Rahasia', $reply);
    }

    public function test_chat_does_not_return_reservation_details_for_an_unknown_token(): void
    {
        $response = $this->postJson(route('lifi.chat'), [
            'message' => 'Cek status reservasi saya',
            'token' => 'RSV-UNKNOWN',
        ]);

        $response->assertOk()
            ->assertJsonPath('reply', 'Aku belum menemukan reservasi dengan token itu. Coba cek lagi penulisan tokennya, ya.');
    }

    public function test_chat_checks_schedule_without_revealing_customer_details_and_ignores_cancelled_bookings(): void
    {
        $this->createPayment('RSV-ACTIVE1', [
            'name' => 'Pelanggan Aktif',
            'date' => '2026-12-20',
            'time' => '10:00',
        ]);
        $cancelledPayment = $this->createPayment('RSV-CANCEL1', [
            'name' => 'Pelanggan Batal',
            'date' => '2026-12-20',
            'time' => '13:00',
        ]);
        $cancelledPayment->update(['status' => 'Dibatalkan']);

        $response = $this->postJson(route('lifi.chat'), [
            'message' => 'Cek jadwal tanggal 20/12/2026',
        ]);

        $response->assertOk();
        $reply = $response->json('reply');
        $this->assertStringContainsString('10:00', $reply);
        $this->assertStringNotContainsString('13:00', $reply);
        $this->assertStringNotContainsString('Pelanggan Aktif', $reply);
        $this->assertStringNotContainsString('Pelanggan Batal', $reply);
    }

    public function test_chat_lists_current_packages_and_prices(): void
    {
        $this->createPackage();

        $this->postJson(route('lifi.chat'), [
            'message' => 'Ada paket dan harga apa saja?',
        ])
            ->assertOk()
            ->assertJsonFragment(['reply' => "Ini daftar paket yang tersedia saat ini:\nPaket Wedding (Wedding) — Rp 5.000.000 · Sehari\n\nKalau ada yang menarik, aku bisa bantu arahkan ke halaman reservasi."]);
    }

    public function test_chat_rejects_a_missing_message(): void
    {
        $this->postJson(route('lifi.chat'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['message']);
    }

    public function test_homepage_renders_lifi_chat_and_its_welcome_message(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('class="lifi-chat"', false)
            ->assertSee('Tanya Lifi')
            ->assertSee('Hai! Aku Lifi. Senang kamu mampir');
    }

    /**
     * @param  array<string, mixed>  $reservationAttributes
     */
    private function createPayment(string $token, array $reservationAttributes = []): Payment
    {
        $package = $this->createPackage();
        $reservation = Reservation::query()->create(array_merge([
            'photography_package_id' => $package->id,
            'token' => $token,
            'name' => 'Pelanggan',
            'whatsapp' => '081200000000',
            'date' => '2026-12-20',
            'time' => '10:00',
            'duration' => 'Sehari',
            'location' => 'Studio',
        ], $reservationAttributes));

        return Payment::query()->create([
            'reservation_id' => $reservation->id,
            'token' => $token,
            'status' => 'Menunggu pembayaran DP',
            'tagihan' => 5000000,
            'total_paid' => 0,
        ]);
    }

    private function createPackage(): PhotographyPackage
    {
        return PhotographyPackage::query()->create([
            'name' => 'Paket Wedding',
            'category' => 'Wedding',
            'price' => 5000000,
            'duration' => 'Sehari',
            'facilities' => 'Dokumentasi acara',
            'is_wedding' => true,
        ]);
    }
}
