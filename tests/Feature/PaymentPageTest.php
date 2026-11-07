<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\PhotographyPackage;
use App\Models\Rekening;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_page_displays_the_saved_qris_image(): void
    {
        $payment = $this->createPayment();
        Rekening::query()->create([
            'nama_bank' => 'Bank Contoh',
            'no_rekening' => '1234567890',
            'nama_pemilik' => 'Studio',
        ]);
        Rekening::query()->create([
            'nama_bank' => 'QRIS Studio',
            'no_rekening' => '-',
            'nama_pemilik' => 'Studio',
            'foto_qris' => 'qris/studio.png',
        ]);

        $response = $this->get(route('reservasi.pembayaran', ['token' => $payment->token]));

        $response
            ->assertOk()
            ->assertSee('src="'.asset('storage/qris/studio.png').'"', false)
            ->assertDontSee('Assets/img/qris-default.png');
    }

    public function test_payment_page_explains_when_no_qris_image_is_configured(): void
    {
        $payment = $this->createPayment();

        $response = $this->get(route('reservasi.pembayaran', ['token' => $payment->token]));

        $response
            ->assertOk()
            ->assertSee('Foto QRIS belum tersedia. Silakan hubungi admin studio.')
            ->assertDontSee('Assets/img/qris-default.png');
    }

    private function createPayment(): Payment
    {
        $package = PhotographyPackage::query()->create([
            'name' => 'Paket Tes',
            'price' => 1000000,
            'duration' => '2 jam',
            'facilities' => 'Studio',
        ]);
        $reservation = Reservation::query()->create([
            'photography_package_id' => $package->id,
            'token' => 'PAYMENT-TEST-001',
            'name' => 'Pelanggan',
            'whatsapp' => '081234567890',
            'date' => now()->addDay()->toDateString(),
            'time' => '10:00',
            'duration' => '2 jam',
            'location' => 'Studio',
        ]);

        return Payment::query()->create([
            'reservation_id' => $reservation->id,
            'token' => $reservation->token,
            'status' => 'Menunggu pembayaran DP',
            'tagihan' => 1000000,
            'total_paid' => 0,
        ]);
    }
}
